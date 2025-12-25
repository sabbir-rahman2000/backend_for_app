<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
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
                $uploadPath = public_path('products');
                if (!File::exists($uploadPath)) {
                    File::makeDirectory($uploadPath, 0755, true);
                }

                foreach ($request->file('images') as $file) {
                    $filename = $file->hashName();
                    $file->move($uploadPath, $filename);
                    $images[] = asset('products/' . $filename);
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

            // Get owner info
            $owner = User::select([
                'id',
                'name',
                'email',
                'phone',
            ])->find($product->user_id);

            return response()->json([
                'success' => true,
                'data' => [
                    'product' => $product,
                    'owner' => $owner,
                ],
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

    /**
     * GET /api/my-products
     * Get all products for authenticated user.
     */
    public function myProducts(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $products = Product::where('user_id', $userId)->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $products,
                'message' => 'Your products fetched successfully',
            ], 200);
        } catch (\Throwable $e) {
            Log::error('My products fetch failed: '.$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch your products',
            ], 500);
        }
    }

    /**
     * GET /api/authenticated-products
     * Get all products (requires authentication).
     */
    public function indexAuthenticated(Request $request): JsonResponse
    {
        try {
            $products = Product::paginate(15);

            return response()->json([
                'success' => true,
                'data' => $products,
                'message' => 'All products fetched successfully',
                'authenticated_user_id' => $request->user()->id,
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Authenticated products index failed: '.$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch products',
            ], 500);
        }
    }

    /**
     * GET /api/products/{id}/user
     * Get the owner's info for a product (authenticated required).
     */
    public function productOwner(int $id, Request $request): JsonResponse
    {
        try {
            $product = Product::find($id);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found',
                ], 404);
            }

            $owner = User::select([
                'id',
                'name',
                'email',
                'phone',
                'email_verified_at',
                'created_at',
            ])->find($product->user_id);

            if (!$owner) {
                return response()->json([
                    'success' => false,
                    'message' => 'Owner not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'product_id' => $product->id,
                    'product_title' => $product->title,
                    'owner' => $owner,
                ],
            ], 200);
        } catch (\Throwable $e) {
            Log::error('Product owner fetch failed: '.$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch product owner',
            ], 500);
        }
    }

    /**
     * GET /api/users/{user_id}/products
     * Get all products for a specific user.
     */
    public function userProducts(int $user_id): JsonResponse
    {
        try {
            $products = Product::where('user_id', $user_id)->paginate(15);

            if ($products->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'No products found for this user',
                    'data' => $products,
                ], 200);
            }

            return response()->json([
                'success' => true,
                'data' => $products,
            ], 200);
        } catch (\Throwable $e) {
            Log::error('User products fetch failed: '.$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user products',
            ], 500);
        }
    }
}
