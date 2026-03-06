<?php

namespace App\Services\Contracts\Admin\Product;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductManagementInterface
{
    public function listProducts(int $perPage = 15): LengthAwarePaginator;

    public function store(array $data): Product;

    public function update(Product $product, array $data): Product;

    public function delete(Product $product): void;
}
