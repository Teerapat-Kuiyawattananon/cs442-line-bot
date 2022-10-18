<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $product = new Product();
        $product->name = "RTX 3090";
        $product->price = 50000.0;
        $product->save();

        $product = new Product();
        $product->name = "RTX 3080";
        $product->price = 30000.0;
        $product->save();

        $product = new Product();
        $product->name = "RTX 3070";
        $product->price = 20000.0;
        $product->save();

        $product = new Product();
        $product->name = "RTX 3060";
        $product->price = 10000.0;
        $product->save();
    }
}
