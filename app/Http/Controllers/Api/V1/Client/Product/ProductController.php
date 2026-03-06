<?php

namespace App\Http\Controllers\Api\V1\Client\Product;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\Contracts\Client\Product\ProductInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(protected ProductInterface $productInterface)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->query('per_page', 15), 100);
        $products = $this->productInterface->listProducts($perPage);

        return $this->successResponse([
            'data' => ProductResource::collection($products->items()),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ], 'Products retrieved successfully');
    }

    public function show(Product $product): JsonResponse
    {
        return $this->successResponse(new ProductResource($product), 'Product retrieved successfully');
    }
}
