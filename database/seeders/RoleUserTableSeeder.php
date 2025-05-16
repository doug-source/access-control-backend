<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleUserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(string $email, string $role): void
    {
        $superAdminUser = User::firstWhere('email', $email);
        $superAdminRoleID = DB::table('roles')->where('name', $role)->value('id');
        if (is_null($superAdminUser) || is_null($superAdminRoleID)) {
            return;
        }
        $superAdminUser->roles()->attach($superAdminRoleID);
    }
}
