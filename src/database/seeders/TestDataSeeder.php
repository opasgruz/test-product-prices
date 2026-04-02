<?php

namespace Database\Seeders;

use App\Models\Manufacturer;
use App\Models\Product;
use App\Models\Price;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Создаем производителей
        $manufacturers = Manufacturer::factory()->count(10)->create();

        // 2. Создаем товары для этих производителей
        $products = Product::factory()->count(50)->recycle($manufacturers)->create();

        // 3. Создаем цены (по 20 записей на каждый товар за разные даты)
        foreach ($products as $product) {
            Price::factory()->count(20)->create([
                'product_id' => $product->product_id,
            ]);
        }
    }
}
