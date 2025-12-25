<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WishlistController extends Controller
{
    /**
     * GET /api/wishlist
     * Get all wishlist items for authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $wishlist = Wishlist::with('product')
                ->where('user_id', $userId)
                ->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $wishlist,
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Wishlist fetch failed: '.$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch wishlist',
            ], 500);
        }
    }

    /**
     * POST /api/wishlist
     * Add a product to authenticated user's wishlist.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
            ]);

            $userId = $request->user()->id;

            // Check if already in wishlist
            $exists = Wishlist::where('user_id', $userId)
                ->where('product_id', $validated['product_id'])
                ->first();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product already in wishlist',
                ], 409);
            }

            $wishlistItem = Wishlist::create([
                'user_id' => $userId,
                'product_id' => $validated['product_id'],
            ]);

            $wishlistItem->load('product');

            return response()->json([
                'success' => true,
                'message' => 'Product added to wishlist',
                'data' => $wishlistItem,
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Wishlist add failed: '.$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to add to wishlist',
            ], 500);
        }
    }

    /**
     * DELETE /api/wishlist/{product_id}
     * Remove a product from authenticated user's wishlist.
     */
    public function destroy(int $product_id, Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;

            $wishlistItem = Wishlist::where('user_id', $userId)
                ->where('product_id', $product_id)
                ->first();

            if (!$wishlistItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item not found in wishlist',
                ], 404);
            }

            $wishlistItem->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product removed from wishlist',
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Wishlist delete failed: '.$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove from wishlist',
            ], 500);
        }
    }

    /**
     * GET /api/wishlist/check/{product_id}
     * Check if a product is in authenticated user's wishlist.
     */
    public function check(int $product_id, Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;

            $exists = Wishlist::where('user_id', $userId)
                ->where('product_id', $product_id)
                ->exists();

            return response()->json([
                'success' => true,
                'in_wishlist' => $exists,
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Wishlist check failed: '.$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to check wishlist',
            ], 500);
        }
    }
}
