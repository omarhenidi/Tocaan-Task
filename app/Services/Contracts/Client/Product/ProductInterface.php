<?php

namespace App\Services\Contracts\Client\Product;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductInterface
{
    public function listProducts(int $perPage = 15): LengthAwarePaginator;
}
