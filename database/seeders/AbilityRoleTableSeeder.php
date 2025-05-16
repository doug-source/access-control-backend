<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AbilityRoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @param array<string> $abilities
     */
    public function run(string $role, array $abilities): void
    {
        $superAdminRole = Role::firstWhere('name', $role);
        $abilityIDs = DB::table('abilities')->select('id')->whereIn(
            'name',
            $abilities
        )->get()->pluck('id')->toArray();
        $superAdminRole->abilities()->attach($abilityIDs);
    }
}
