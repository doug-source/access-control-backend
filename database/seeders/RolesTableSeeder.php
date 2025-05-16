<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @param array<string> $roles
     */
    public function run(array $roles): void
    {
        DB::table('roles')->insert(
            array_map(
                fn(string $roleName) => [
                    'name' => $roleName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                $roles
            )
        );
    }
}
