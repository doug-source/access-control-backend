<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProvidersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(string $email): void
    {
        $user = DB::table('users')->where('email', $email)->first();
        if (is_null($user)) {
            return;
        }
        DB::table('providers')->insert([
            'provider' => 'google',
            'provider_id' => 'google_id',
            'user_id' => $user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
