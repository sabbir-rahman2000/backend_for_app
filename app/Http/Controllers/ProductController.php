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
}
