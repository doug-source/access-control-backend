<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class AbilitiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @param array<string> $abilities
     */
    public function run(array $abilities): void
    {
        DB::table('abilities')->insert(
            array_map(
                fn($abilityName) => [
                    'name' => $abilityName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                $abilities
            )
        );
    }
}
