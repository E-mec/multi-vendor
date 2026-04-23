<?php

namespace Modules\Inventory\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = \Modules\Inventory\Models\Order::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [];
    }
}

