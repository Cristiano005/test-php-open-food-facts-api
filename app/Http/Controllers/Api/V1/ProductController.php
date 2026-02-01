<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse {
        return response()->json([
            'data' => ProductResource::collection(Product::paginate(10)),
        ], 200);
    }
}
