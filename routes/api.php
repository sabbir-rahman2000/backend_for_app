<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Test API endpoint
Route::get('/test', function () {
    return response()->json(['message' => 'api is working']);
});

// DB connectivity diagnostic
Route::get('/db-ping', function () {
    try {
        DB::select('select 1 as ok');
        return response()->json(['db' => 'ok']);
    } catch (\Throwable $e) {
        Log::error('DB ping failed: '.$e->getMessage());
        return response()->json([
            'db' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
});

// Public auth routes (no authentication required)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
    Route::post('/verify-email', [AuthController::class, 'verifyEmail'])->name('auth.verify-email');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('auth.forgot-password');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('auth.reset-password');
});

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::get('/refresh-token', [AuthController::class, 'refreshToken'])->name('auth.refresh-token');
        Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('/me', [AuthController::class, 'me'])->name('auth.me');
    });
    
    Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
        return $request->user();
    });
});

// Public endpoint to list users (name, email, phone)
Route::get('/users', [UserController::class, 'index'])->name('users.index');

// Temporary debug endpoint to surface DB errors safely
Route::get('/users-debug', function () {
    try {
        $rows = DB::table('users')->select('name', 'email', 'phone')->limit(5)->get();
        return response()->json(['data' => $rows]);
    } catch (\Throwable $e) {
        Log::error('Users debug failed: '.$e->getMessage());
        return response()->json([
            'error' => 'db_error',
            'message' => $e->getMessage(),
        ], 500);
    }
});
