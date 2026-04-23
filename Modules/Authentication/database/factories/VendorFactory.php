<?php

namespace Modules\Authentication\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class VendorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = \Modules\Authentication\Models\Vendor::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [];
    }
}

