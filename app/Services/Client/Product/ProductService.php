<?php

namespace App\Services\Client\Product;

use App\Models\Product;
use App\Services\Contracts\Client\Product\ProductInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductService implements ProductInterface
{
    public function listProducts(int $perPage = 15): LengthAwarePaginator
    {
        return Product::query()->latest('id')->paginate($perPage);
    }
}


