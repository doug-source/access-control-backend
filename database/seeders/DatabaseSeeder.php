<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /** @var array{name: string, email: string, role: string, abilities: array<string>} */
    private array $superAdmin = [
        'name' => 'Douglas Leandro',
        'email' => 'douglas.gtads@gmail.com',
        'role' => 'super-admin',
        'abilities' => [
            'settings',
            'user-screen',
            'register-request-screen',
        ],
    ];

    private array $roles = [
        'super-admin',
        'admin',
        'common'
    ];

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(
            class: UsersTableSeeder::class,
            parameters: [
                'name' => $this->superAdmin['name'],
                'email' => $this->superAdmin['email'],
            ]
        );
        $this->call(class: ProvidersTableSeeder::class, parameters: [
            'email' => $this->superAdmin['email'],
        ]);
        $this->call(
            class: RolesTableSeeder::class,
            parameters: [
                'roles' => $this->roles
            ],
        );
        $this->call(
            class: AbilitiesTableSeeder::class,
            parameters: [
                'abilities' => $this->superAdmin['abilities']
            ]
        );
        $this->call(
            class: RoleUserTableSeeder::class,
            parameters: [
                'email' => $this->superAdmin['email'],
                'role' => $this->superAdmin['role'],
            ]
        );
        $this->call(
            class: AbilityRoleTableSeeder::class,
            parameters: [
                'role' => $this->superAdmin['role'],
                'abilities' => $this->superAdmin['abilities'],
            ]
        );
    }
}
