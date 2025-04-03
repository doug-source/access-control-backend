<?php

use App\Models\Enterprise;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

describe('Authentication', function () {
    it('receives no field and returns email invalidation', function () {
        $response = $this->postJson('/api/login');
        $response
            ->assertStatus(422)
            ->assertInvalid(['email'])
            ->assertJson([
                "errors" => [
                    "email" => [
                        "Required"
                    ]
                ]
            ]);
    });
    it('receives only password and returns email invalidation', function () {
        $response = $this->postJson('/api/login', ['password' => 'whatever']);
        $response
            ->assertStatus(422)
            ->assertInvalid(['email'])
            ->assertJson([
                "errors" => [
                    "email" => [
                        "Required"
                    ]
                ]
            ]);
    });
    it('receives only email and returns password invalidation', function () {
        $response = $this->postJson('/api/login', ['email' => 'someone@test.com']);
        $response
            ->assertStatus(422)
            ->assertInvalid(['password'])
            ->assertJson([
                "errors" => [
                    "password" => [
                        "Required"
                    ]
                ]
            ]);
    });
    it('receives invalid email and returns email invalidation', function () {
        $response = $this->postJson('/api/login', ['email' => 'someone@', 'password' => 'whatever']);
        $response
            ->assertStatus(422)
            ->assertInvalid(['email'])
            ->assertJson([
                "errors" => [
                    "email" => [
                        "Invalid"
                    ]
                ]
            ]);
    });
    it('receives failing login validation', function () {
        $email = 'someone@test.com';
        $password = 'password';
        $enterpriseList = Enterprise::factory(count: 1)->create();
        User::factory(count: 1)->create([
            'enterprises_id' => $enterpriseList->first()->id,
            'email' => $email,
            'password' => Hash::make($password)
        ])->first();

        $response = $this->postJson('/api/login', ['email' => $email, 'password' => 'another-password']);
        $response->assertInvalid(['status']);
    });
    it('receives successful login validation', function () {
        $email = 'someone@test.com';
        $password = 'password';
        $enterpriseList = Enterprise::factory(count: 1)->create();
        User::factory(count: 1)->create([
            'enterprises_id' => $enterpriseList->first()->id,
            'email' => $email,
            'password' => Hash::make($password)
        ])->first();

        $this->postJson('/api/login', ['email' => $email, 'password' => $password]);
        $this->assertAuthenticated();
    });
});
