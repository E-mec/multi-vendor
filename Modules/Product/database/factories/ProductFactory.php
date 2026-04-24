<?php

namespace Modules\Product\database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Authentication\Models\Vendor;
use Modules\Product\Enums\ProductStatusEnum;
use Modules\Product\Models\Product;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'vendor_id' => Vendor::factory(),
            'name' => fake()->name,
            'description' => fake()->sentence,
            'price' => fake()->numberBetween(100, 1000),
            'stock_quantity' => fake()->numberBetween(100, 1000),
            'status' => ProductStatusEnum::ACTIVE
        ];
    }
}

