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

    private array $remainAbilities = [
        'role-screen',
        'ability-screen',
        'register-permission-screen',
        'add-user-screen',
        'add-role-screen',
        'add-ability-screen',
        'show-user-screen',
        'show-role-screen',
        'show-ability-screen',
        'show-register-request-screen',
        'show-register-permission-screen',
        'remove-user',
        'restore-user',
        'remove-register-request',
        'approve-register-request',
        'attach-role-and-ability',
        'attach-ability-to-role',
        'ability-from-role-screen',
        'remove-role'
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
            class: AbilitiesTableSeeder::class,
            parameters: [
                'abilities' => $this->remainAbilities
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
