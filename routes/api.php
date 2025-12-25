<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\MessageController;

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

// Test API endpoint with DB check
Route::get('/test', function () {
    try {
        $dbStatus = DB::select('SELECT name, email, phone FROM users LIMIT 3');
        return response()->json([
            'message' => 'api is working',
            'db' => 'connected',
            'users' => $dbStatus,
            'timestamp' => now()->toDateTimeString()
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'message' => 'api is working',
            'db' => 'error',
            'error' => $e->getMessage(),
            'db_config' => [
                'host' => config('database.connections.mysql.host'),
                'port' => config('database.connections.mysql.port'),
                'database' => config('database.connections.mysql.database'),
            ],
            'timestamp' => now()->toDateTimeString()
        ]);
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

    // Products (Authenticated)
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/my-products', [ProductController::class, 'myProducts'])->name('products.my');
    Route::get('/authenticated-products', [ProductController::class, 'indexAuthenticated'])->name('products.index-auth');

    // Messages (Authenticated)
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::post('/messages', [MessageController::class, 'store'])->name('messages.store');
});



// Public product endpoints (testing)
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');
Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');

// Temporary diagnostics route - shows exact error
Route::get('/test-db', function () {
    try {
        $users = DB::select('SELECT name, email, phone FROM users LIMIT 5');
        return response()->json(['data' => $users, 'status' => 'ok']);
    } catch (\Throwable $e) {
        Log::error('Test DB failed: '.$e->getMessage());
        return response()->json([
            'status' => 'error',
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ], 500);
    }
});



// fort testing api route
// Public endpoint to list users (name, email, phone)
Route::get('/users', [UserController::class, 'index'])->name('users.index');

// Public product endpoints (testing)
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');
// Public delete user endpoint (development/testing only)
Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
