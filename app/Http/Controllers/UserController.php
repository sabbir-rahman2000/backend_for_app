<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * GET /api/users
     * Returns list of users with name, email, and phone.
     */
    public function index(): JsonResponse
    {
        try {
            // Return only safe, relevant columns (avoid password/remember_token)
            $users = User::select([
                'id',
                'name',
                'email',
                'phone',
                'email_verified_at',
                'created_at',
                'updated_at',
            ])->get();

            return response()->json([
                'success' => true,
                'data' => $users,
            ]);
        } catch (\Throwable $e) {
            Log::error('Users index failed: '.$e->getMessage());
            return response()->json([
                'error' => 'internal_error',
                'message' => 'Failed to fetch users',
            ], 500);
        }
    }

    /**
     * DELETE /api/users/{id}
     * Deletes a user by ID.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                ], 404);
            }

            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully',
            ]);
        } catch (\Throwable $e) {
            Log::error('User delete failed: '.$e->getMessage(), ['user_id' => $id]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user',
            ], 500);
        }
    }
}
