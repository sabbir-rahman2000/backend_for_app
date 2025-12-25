<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Mail\VerifyEmailMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        try {
            $validated = $request->validate($this->rules());

            // Generate a 6-digit verification code
            $verificationCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'phone' => $validated['phone'],
                'student_id' => $validated['student_id'],
                'email_verification_token' => null,
                'email_verification_code' => $verificationCode,
                'email_verification_expires_at' => now()->addMinutes(15),
            ]);

            // Try to send verification code email without failing registration
            $emailSent = true;
            try {
                Mail::to($user->email)->send(new VerifyEmailMail($user, $verificationCode));
            } catch (\Throwable $e) {
                $emailSent = false;
                Log::error('Failed to send verification email', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                ]);
            }

            $token = $user->createToken('api_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => $emailSent
                    ? 'User registered successfully. Verification code sent to your email.'
                    : 'User registered successfully. Failed to send verification email, please request a new code.',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'is_verified' => false,
                    ],
                    'token' => $token,
                    'email_sent' => $emailSent,
                ]
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|min:2|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'required|string|min:10|max:20',
            'student_id' => 'required|string|min:6|max:50',
        ];
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            $user = User::where('email', $validated['email'])->first();

            if (!$user || !Hash::check($validated['password'], $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }

            $token = $user->createToken('api_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'is_verified' => $user->email_verified_at !== null,
                    ],
                    'token' => $token,
                ]
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Verify user email with token
     */
    public function verifyEmail(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|string|email',
                'verification_code' => 'required|string',
            ]);

            $user = User::where('email', $validated['email'])
                ->where('email_verification_code', $validated['verification_code'])
                ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid verification code'
                ], 400);
            }

            if ($user->email_verified_at !== null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email already verified'
                ], 400);
            }

            if ($user->email_verification_expires_at && now()->greaterThan($user->email_verification_expires_at)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Verification code expired'
                ], 400);
            }

            $user->update([
                'email_verified_at' => now(),
                'email_verification_code' => null,
                'email_verification_expires_at' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Email verified successfully',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'is_verified' => true,
                    ]
                ]
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Send password reset code to email
     */
    public function forgotPassword(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|string|email',
            ]);

            $user = User::where('email', $validated['email'])->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Generate 6-digit reset code
            $resetCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            $user->update([
                'password_reset_code' => $resetCode,
                'password_reset_expires_at' => now()->addMinutes(15),
            ]);

            // Try to send reset code email
            $emailSent = true;
            try {
                Mail::to($user->email)->send(new VerifyEmailMail($user, $resetCode));
            } catch (\Throwable $e) {
                $emailSent = false;
                Log::error('Failed to send password reset email', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => $emailSent
                    ? 'Password reset code sent to your email'
                    : 'Failed to send password reset email, please try again',
                'data' => [
                    'email' => $user->email,
                    'email_sent' => $emailSent,
                    // For testing only: return reset code and expiry so mobile can verify; remove in production
                    'reset_code' => $resetCode,
                    'expires_at' => $user->password_reset_expires_at,
                ]
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Reset password with verification code
     */
    public function resetPassword(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|string|email',
                'reset_code' => 'required|string',
                'password' => 'required|string|min:6|confirmed',
            ]);

            $user = User::where('email', $validated['email'])
                ->where('password_reset_code', $validated['reset_code'])
                ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid reset code or email'
                ], 400);
            }

            if ($user->password_reset_expires_at && now()->greaterThan($user->password_reset_expires_at)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reset code expired'
                ], 400);
            }

            $user->update([
                'password' => Hash::make($validated['password']),
                'password_reset_code' => null,
                'password_reset_expires_at' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password reset successfully',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ]
                ]
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Refresh authentication token
     */
    public function refreshToken(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            // Revoke old token
            $request->user()->currentAccessToken()->delete();

            // Create new token
            $newToken = $user->createToken('api_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Token refreshed successfully',
                'data' => [
                    'token' => $newToken,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ]
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to refresh token',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout successful'
        ], 200);
    }

    /**
     * Resend verification code to email
     */
    public function resendCode(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|string|email',
            ]);

            $user = User::where('email', $validated['email'])->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            if ($user->email_verified_at !== null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email already verified'
                ], 400);
            }

            // Generate new 6-digit verification code
            $verificationCode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            $user->update([
                'email_verification_code' => $verificationCode,
                'email_verification_expires_at' => now()->addMinutes(15),
            ]);

            // Try to send verification code email
            $emailSent = true;
            try {
                Mail::to($user->email)->send(new VerifyEmailMail($user, $verificationCode));
            } catch (\Throwable $e) {
                $emailSent = false;
                Log::error('Failed to resend verification email', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => $emailSent
                    ? 'Verification code sent to your email'
                    : 'Failed to send verification email, please try again',
                'data' => [
                    'email' => $user->email,
                    'email_sent' => $emailSent,
                ]
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Get current user
     */
    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'is_verified' => $request->user()->email_verified_at !== null,
                ]
            ]
        ], 200);
    }
}

