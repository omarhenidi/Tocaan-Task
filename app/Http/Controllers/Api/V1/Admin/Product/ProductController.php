<?php

namespace App\Http\Controllers\Api\V1\Admin\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\Contracts\Admin\Product\ProductManagementInterface;
use App\Support\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use ResponseTrait;

    public function __construct(protected ProductManagementInterface $productInterface)
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

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->productInterface->store($request->validated());

        return $this->successResponse(new ProductResource($product), 'Product created successfully.', 201);
    }

    public function update(Product $product, UpdateProductRequest $request): JsonResponse
    {
        $product = $this->productInterface->update($product, $request->validated());

        return $this->successResponse(new ProductResource($product), 'Product updated successfully.');
    }

    public function show(Product $product): JsonResponse
    {
        return $this->successResponse(new ProductResource($product), 'Product retrieved successfully.');
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->productInterface->delete($product);

        return $this->successResponse([], 'Product deleted successfully.');
    }
}
