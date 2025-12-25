<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\SellController;

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
    Route::post('/resend-code', [AuthController::class, 'resendCode'])->name('auth.resend-code');
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
    Route::delete('/products/{id}/delete', [ProductController::class, 'deleteOwn'])->name('products.delete-own');

    // Messages (Authenticated)
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::post('/messages', [MessageController::class, 'store'])->name('messages.store');

    // Wishlist (Authenticated)
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist', [WishlistController::class, 'store'])->name('wishlist.store');
    Route::delete('/wishlist/{product_id}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');
    Route::get('/wishlist/check/{product_id}', [WishlistController::class, 'check'])->name('wishlist.check');

    // Sells (Authenticated)
    Route::post('/sells', [SellController::class, 'store'])->name('sells.store');
    Route::get('/sells', [SellController::class, 'index'])->name('sells.index');

    // Wishlist products by user id (Authenticated)
    Route::get('/users/{user_id}/wishlist-products', [WishlistController::class, 'userWishlist'])->name('users.wishlist-products');

    // Users (Authenticated) - fetch user info by id (for chat header)
    Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
});



// Public product endpoints (testing)
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');
Route::get('/products/{product_id}/owner', [ProductController::class, 'getOwnerByProduct'])->name('products.owner-info');
Route::get('/users/{user_id}/products', [ProductController::class, 'userProducts'])->name('users.products');
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

// for db cleanup during testing
Route::delete('/cleanup-database', function () {
    try {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Truncate all tables
        DB::table('sells')->truncate();
        DB::table('wishlists')->truncate();
        DB::table('messages')->truncate();
        DB::table('products')->truncate();
        DB::table('personal_access_tokens')->truncate();
        DB::table('users')->truncate();
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        return response()->json([
            'success' => true,
            'message' => 'All database tables truncated successfully',
            'timestamp' => now()->toDateTimeString()
        ]);
    } catch (\Throwable $e) {
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        Log::error('Database cleanup failed: '.$e->getMessage());
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
        ], 500);
    }
})->name('cleanup.database');

// Ping to verify deployment/routing
Route::get('/run-migrations/ping', function () {
    return response()->json(['success' => true, 'message' => 'route-ok']);
});

// Temporary: run migrations via HTTP (protect with secret token)
$runMigrationsHandler = function (Request $request) {
    $secret = env('MIGRATE_SECRET');
    if (!$secret || $request->header('X-Migrate-Secret') !== $secret) {
        return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
    }

    try {
        Artisan::call('migrate', ['--force' => true]);
        return response()->json([
            'success' => true,
            'message' => 'Migrations executed',
            'output' => Artisan::output(),
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'success' => false,
            'message' => 'Migration failed',
            'error' => $e->getMessage(),
        ], 500);
    }
};

Route::post('/run-migrations', $runMigrationsHandler);
Route::get('/run-migrations', $runMigrationsHandler);
