<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Product A',
                'description' => 'Sample product A for orders.',
                'price' => 10.50,
            ],
            [
                'name' => 'Product B',
                'description' => 'Sample product B for orders.',
                'price' => 25.00,
            ],
            [
                'name' => 'Product C',
                'description' => 'Sample product C for orders.',
                'price' => 15.99,
            ],
        ];

        foreach ($products as $data) {
            Product::firstOrCreate(
                ['name' => $data['name']],
                $data
            );
        }
    }
}
