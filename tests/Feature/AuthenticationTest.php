<?php

use App\Models\Enterprise;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

describe('Authentication', function () {
    it('receives no field and returns email invalidation', function () {
        $response = $this->postJson(route('auth.login'));
        $response
            ->assertStatus(422)
            ->assertInvalid(['email'])
            ->assertJson([
                "errors" => [
                    "email" => [
                        __('email') . ': ' . __('required')
                    ]
                ]
            ]);
    });
    it('receives only password and returns email invalidation', function () {
        $response = $this->postJson(route('auth.login'), ['password' => 'whatever']);
        $response
            ->assertStatus(422)
            ->assertInvalid(['email'])
            ->assertJson([
                "errors" => [
                    "email" => [
                        __('email') . ': ' . __('required')
                    ]
                ]
            ]);
    });
    it('receives only email and returns password invalidation', function () {
        $response = $this->postJson(route('auth.login'), ['email' => 'someone@test.com']);
        $response
            ->assertStatus(422)
            ->assertInvalid(['password'])
            ->assertJson([
                "errors" => [
                    "password" => [
                        __('password') . ': ' . __('required')
                    ]
                ]
            ]);
    });
    it('receives invalid email and returns email invalidation', function () {
        $response = $this->postJson(route('auth.login'), ['email' => 'someone@', 'password' => 'whatever']);
        $response
            ->assertStatus(422)
            ->assertInvalid(['email'])
            ->assertJson([
                "errors" => [
                    "email" => [
                        __('email') . ': ' . __('invalid')
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

        $response = $this->postJson(route('auth.login'), ['email' => $email, 'password' => 'another-password']);
        $response
            ->assertJson([
                "errors" => [
                    "status" => [
                        __('log-in') . ' ' . __('invalid')
                    ]
                ]
            ])
            ->assertInvalid(['status']);
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

        $this->postJson(route('auth.login'), ['email' => $email, 'password' => $password]);
        $this->assertAuthenticated();
    });
    it('receives failing logout validation by unauthorization', function () {
        $email = 'someone@test.com';
        $password = 'password';
        $token = 'anyToken';
        $enterpriseList = Enterprise::factory(count: 1)->create();
        User::factory(count: 1)->create([
            'enterprises_id' => $enterpriseList->first()->id,
            'email' => $email,
            'password' => Hash::make($password)
        ])->first();
        $response = $this->postJson('/api/logout', ['tokenAuthApi' => $token]);
        $response->assertUnauthorized();
    });
    it('receives successful logout validation', function () {
        $email = 'someone@test.com';
        $password = 'password';
        $enterpriseList = Enterprise::factory(count: 1)->create();
        $user = User::factory(count: 1)->create([
            'enterprises_id' => $enterpriseList->first()->id,
            'email' => $email,
            'password' => Hash::make($password)
        ])->first();
        $responseLogin = $this->postJson(route('auth.login'), [
            'email' => $email,
            'password' => $password
        ]);

        $token = Str::after($responseLogin->baseResponse->original['data']['token'], '|');

        $this->postJson(route('auth.logout'), [], [
            'Authorization' => "Bearer {$token}"
        ])->assertOk();
    });
});
