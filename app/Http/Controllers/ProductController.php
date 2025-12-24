<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    /**
     * POST /api/products
     * Create a product for the authenticated user.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|min:2|max:255',
                'category' => 'required|string|max:100',
                'price' => 'required|numeric|min:0',
                'description' => 'nullable|string',
                'images' => 'nullable|array',
                'images.*' => 'file|image|max:5120', // each file up to 5MB
            ]);

            $images = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $file) {
                    $path = $file->store('products', 'public');
                    $images[] = asset('storage/' . $path);
                }
            }

            $product = Product::create([
                'user_id' => $request->user()->id,
                'title' => $validated['title'],
                'category' => $validated['category'],
                'price' => $validated['price'],
                'description' => $validated['description'] ?? null,
                'images' => $images,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => $product,
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Product create failed: '.$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product',
            ], 500);
        }
    }

    /**
     * GET /api/products
     * Get all products with pagination.
     */
    public function index(): JsonResponse
    {
        try {
            $products = Product::paginate(15);

            return response()->json([
                'success' => true,
                'data' => $products,
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Products index failed: '.$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch products',
            ], 500);
        }
    }

    /**
     * GET /api/products/{id}
     * Get a single product by ID.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $product,
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Product show failed: '.$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch product',
            ], 500);
        }
    }

    /**
     * DELETE /api/products/{id}
     * Delete a product by ID (public testing endpoint).
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found',
                ], 404);
            }

            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully',
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Product delete failed: '.$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product',
            ], 500);
        }
    }
}
