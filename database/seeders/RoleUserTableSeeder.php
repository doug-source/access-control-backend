<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Repositories\UserRepository;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleUserTableSeeder extends Seeder
{
    public function __construct(private readonly UserRepository $userRepository)
    {
        // ...
    }

    /**
     * Run the database seeds.
     */
    public function run(string $email, string $role): void
    {
        $superAdminUser = $this->userRepository->findByEmail(email: $email);
        $superAdminRoleID = DB::table('roles')->where('name', $role)->value('id');
        if (is_null($superAdminUser) || is_null($superAdminRoleID)) {
            return;
        }
        $superAdminUser->roles()->attach($superAdminRoleID);
    }
}
