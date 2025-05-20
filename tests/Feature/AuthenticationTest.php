<?php

use App\Library\Builders\Phrase;
use App\Library\Enums\PasswordRules;
use App\Library\Enums\PhraseKey;
use App\Models\Provider;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

describe('Authentication', function () {
    describe('fails because', function () {
        it('has no field parameter', function () {
            assertFailedResponse(
                response: $this->postJson(route('auth.login')),
                errorKey: 'email',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterRequired),
            );
        });
        it('has only password parameter', function () {
            assertFailedResponse(
                response: $this->postJson(route('auth.login'), ['password' => 'whatever']),
                errorKey: 'email',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterRequired)
            );
        });
        it('has only email parameter', function () {
            assertFailedResponse(
                response: $this->postJson(route('auth.login'), ['email' => 'someone@test.com']),
                errorKey: 'password',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterRequired),
            );
        });
        it('has invalid email parameter', function () {
            assertFailedResponse(
                response: $this->postJson(route('auth.login'), ['email' => 'someone@', 'password' => 'whatever']),
                errorKey: 'email',
                errorMsg: Phrase::pickSentence(PhraseKey::EmailInvalid)
            );
        });
        it('has incorrect password parameter', function () {
            $user = createUserDB(password: 'password', email: 'someone@test.com');
            $response = $this->postJson(route('auth.login'), ['email' => $user->email, 'password' => 'another-password']);
            $response
                ->assertExactJson([
                    'errors' => [
                        'status' => [
                            Phrase::pickSentence(PhraseKey::LoginInvalid)
                        ]
                    ]
                ])
                ->assertInvalid(['status']);
        });
        it('user already was registered by provider', function () {
            $user = createUserDB(password: NULL, email: 'someone@test.com');
            Provider::factory(count: 1)->create([
                'user_id' => $user->id
            ]);
            assertFailedResponse(
                response: $this->postJson(route('auth.login'), ['email' => $user->email, 'password' => 'whatever']),
                errorKey: 'email',
                errorMsg: Phrase::pickSentence(PhraseKey::LoginByProvider)
            );
        });
        it('logout needs authentication', function () {
            $response = $this->postJson(route('auth.logout'));
            $response->assertUnauthorized();
        });
    });
    describe('succeed because', function () {
        it('has complete parameters during login', function () {
            authenticate(
                scope: $this,
                email: 'someone@test.com',
                password: fake()->password(minLength: PasswordRules::MinSize->get())
            );
            $this->assertAuthenticated();
        });
        it('has complete parameters during logout', function () {
            ['token' => $token] = authenticate(
                scope: $this,
                email: 'someone@test.com',
                password: fake()->password(minLength: PasswordRules::MinSize->get())
            );
            $this->postJson(route('auth.logout'), [
                'Authorization' => "Bearer {$token}"
            ])->assertOk();
        });
    });
});
