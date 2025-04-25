<?php

use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
use App\Models\Provider;
use Illuminate\Support\Str;

describe('Authentication', function () {
    it('receives no field and returns email invalidation', function () {
        $response = $this->postJson(route('auth.login'));
        $response
            ->assertStatus(422)
            ->assertInvalid(['email'])
            ->assertJson([
                'errors' => [
                    'email' => [
                        Phrase::pickSentence(PhraseKey::EmailRequired)->toString()
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
                'errors' => [
                    'email' => [
                        Phrase::pickSentence(PhraseKey::EmailRequired)->toString()
                    ]
                ]
            ]);
    });
    it('receives only email and returns password invalidation', function () {
        $errorMsg = Phrase::pickSentence(PhraseKey::PasswordRequired);
        $response = $this->postJson(route('auth.login'), ['email' => 'someone@test.com']);
        $response
            ->assertStatus(422)
            ->assertInvalid(['password'])
            ->assertJson([
                'message' => $errorMsg,
                'errors' => [
                    'password' => [
                        $errorMsg
                    ]
                ]
            ]);
    });
    it('receives invalid email and returns email invalidation', function () {
        $errorMsg = Phrase::pickSentence(PhraseKey::EmailInvalid);
        $response = $this->postJson(route('auth.login'), ['email' => 'someone@', 'password' => 'whatever']);
        $response
            ->assertStatus(422)
            ->assertInvalid(['email'])
            ->assertJson([
                'message' => $errorMsg,
                'errors' => [
                    'email' => [
                        $errorMsg
                    ]
                ]
            ]);
    });
    it('receives failing login validation', function () {
        $user = createUserDB(password: 'password', email: 'someone@test.com');
        $response = $this->postJson(route('auth.login'), ['email' => $user->email, 'password' => 'another-password']);
        $response
            ->assertJson([
                'errors' => [
                    'status' => [
                        Phrase::pickSentence(PhraseKey::LoginInvalid)
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
        $user = createUserDB(password: NULL, email: 'someone@test.com');
        Provider::factory(count: 1)->create([
            'user_id' => $user->id
        ])->first();
        $response = $this->postJson(route('auth.login'), ['email' => $user->email, 'password' => 'whatever']);
        $response
            ->assertInvalid(['email'])
            ->assertJson([
                'errors' => [
                    'email' => [
                        Phrase::pickSentence(PhraseKey::LoginByProvider)->toString()
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
