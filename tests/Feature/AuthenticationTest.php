<?php

use App\Models\Provider;
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
        $user = createUserDB(password: 'password', email: 'someone@test.com');
        $response = $this->postJson(route('auth.login'), ['email' => $user->email, 'password' => 'another-password']);
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
        $password = 'password';
        $user = createUserDB(password: $password, email: 'someone@test.com');

        $this->postJson(route('auth.login'), ['email' => $user->email, 'password' => $password]);
        $this->assertAuthenticated();
    });
    it('receives failing login because user was registered by provider', function () {
        $providers = config('services.providers');
        $user = createUserDB(password: NULL, email: 'someone@test.com');
        Provider::factory(count: 1)->create([
            'user_id' => $user->id
        ])->first();
        $response = $this->postJson(route('auth.login'), ['email' => $user->email, 'password' => 'whatever']);
        $response
            ->assertInvalid(['email'])
            ->assertJson([
                "errors" => [
                    "email" => [
                        Str::of(__('login-by-provider', [
                            'log-in' => __('log-in'),
                            'with' => __('with'),
                            'provider' => implode(' ' . _('or') . ' ', $providers),
                            'required' => __('required')
                        ]))->ucfirst()->toString()
                    ]
                ]
            ]);
    });
    it('receives failing logout validation by unauthorization', function () {
        $response = $this->postJson(route('auth.logout'));
        $response->assertUnauthorized();
    });
    it('receives successful logout validation', function () {
        $password = 'password';
        $user = createUserDB(password: $password, email: 'someone@test.com');
        $responseLogin = $this->postJson(route('auth.login'), [
            'email' => $user->email,
            'password' => $password
        ]);
        $token = Str::after($responseLogin->baseResponse->original['user']['token'], '|');

        $this->postJson(route('auth.logout'), [], [
            'Authorization' => "Bearer {$token}"
        ])->assertOk();
    });
    it('receives double successful logout validation', function () {
        $password = 'password';
        $user = createUserDB(password: $password, email: 'someone@test.com');
        $responseLogin = $this->postJson(route('auth.login'), [
            'email' => $user->email,
            'password' => $password
        ]);
        $token = Str::after($responseLogin->baseResponse->original['user']['token'], '|');

        $this->postJson(route('auth.logout'), [], [
            'Authorization' => "Bearer {$token}"
        ]);
        $responseLogin = $this->postJson(route('auth.login'), [
            'email' => $user->email,
            'password' => $password
        ]);
        $this->assertAuthenticated();
    });
});
