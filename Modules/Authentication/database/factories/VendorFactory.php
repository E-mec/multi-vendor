<?php

namespace Modules\Authentication\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Authentication\Models\User;
use Modules\Authentication\Models\Vendor;

class VendorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Vendor::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'store_name' => $this->faker->name(),
        ];
    }
}

