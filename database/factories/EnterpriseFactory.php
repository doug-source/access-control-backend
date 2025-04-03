<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Enterprise>
 */
class EnterpriseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'icon' => fake()->imageUrl()
        ];
    }
}
