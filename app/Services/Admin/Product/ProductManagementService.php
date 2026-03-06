<?php

namespace App\Services\Admin\Product;

use App\Models\Product;
use App\Services\Contracts\Admin\Product\ProductManagementInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductManagementService implements ProductManagementInterface
{
    public function listProducts(int $perPage = 15): LengthAwarePaginator
    {
        return Product::query()->latest('id')->paginate($perPage);
    }

    public function store(array $data): Product
    {
        return Product::create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);

        return $product->fresh();
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }
}
