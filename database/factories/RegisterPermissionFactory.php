<?php

namespace Database\Factories;

use App\Library\Converters\Phone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RegisterPermission>
 */
class RegisterPermissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => fake()->email(),
            'phone' => Phone::clear(fake()->phoneNumber()),
            'token' => fake()->sha1(),
            'expiration_data' => now()->addHours(2)
        ];
    }
}
