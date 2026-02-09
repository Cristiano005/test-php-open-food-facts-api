<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Throwable;

class ProductController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => ProductResource::collection(Product::paginate(10)),
        ], 200);
    }

    public function show(Product $product): JsonResponse
    {
        return response()->json([
            'data' => new ProductResource($product),
        ], 200);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'product_name' => ['nullable', 'string', 'max:255'],
            'quantity' => ['nullable', 'string', 'max:75'],
            'status' => ['required', 'string', 'in:published'],
        ]);

        $product->update($validated);

        return response()->json(['data' => $product], 200);
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->status = 'trash';
        $product->save();

        return response()->json(['data' => $product], 200);
    }
}
