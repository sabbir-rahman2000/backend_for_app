<?php

namespace App\Http\Controllers;

use App\Models\Sell;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SellController extends Controller
{
    /**
     * POST /api/sells
     * Create a sell record (mark product as sold).
     * Authenticated user must be the product owner (seller).
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'product_id' => 'required|exists:products,id',
                'buyer_user_id' => 'nullable|exists:users,id',
            ]);

            $userId = $request->user()->id;
            $product = Product::find($validated['product_id']);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found',
                ], 404);
            }

            // Verify authenticated user is the product owner (seller)
            if ($product->user_id !== $userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. You can only sell your own products',
                ], 403);
            }

            // Check if product is already sold
            if ($product->sold == 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product already sold',
                ], 409);
            }

            DB::beginTransaction();

            try {
                // Create sell record
                $sell = Sell::create([
                    'product_id' => $validated['product_id'],
                    'seller_user_id' => $userId,
                    'buyer_user_id' => $validated['buyer_user_id'] ?? null,
                ]);

                // Update product sold status
                $product->update(['sold' => 1]);

                DB::commit();

                $sell->load(['product', 'seller:id,name,email', 'buyer:id,name,email']);

                return response()->json([
                    'success' => true,
                    'message' => 'Product marked as sold successfully',
                    'data' => $sell,
                ], 201);
            } catch (\Throwable $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Throwable $e) {
            Log::error('Sell creation failed: '.$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create sell record',
            ], 500);
        }
    }

    /**
     * GET /api/sells
     * Get all sells for authenticated user (as seller).
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;

            $sells = Sell::with(['product', 'seller:id,name,email', 'buyer:id,name,email'])
                ->where('seller_user_id', $userId)
                ->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $sells,
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Sells fetch failed: '.$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch sells',
            ], 500);
        }
    }
}
