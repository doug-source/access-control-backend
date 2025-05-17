<?php

declare(strict_types=1);

use App\Library\Builders\Phrase;
use App\Library\Enums\ColumnSize\UserSize;
use App\Library\Enums\PasswordRules;
use App\Library\Enums\PhraseKey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Uri;
use Illuminate\Support\Facades\Password;
use Illuminate\Contracts\Auth\PasswordBroker;

uses(RefreshDatabase::class);

describe('Reset password from api routes', function () {
    describe('fails because', function () {
        it('has no token parameter', function () {
            $uri = Uri::of(route('password.update'))->value();
            assertFailedResponse(
                response: $this->postJson($uri),
                errorKey: 'token',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterRequired)
            );
        });
        it('has no email parameter', function () {
            $uri = Uri::of(route('password.update'))->value();
            assertFailedResponse(
                response: $this->postJson($uri, [
                    'token' => 'whatever'
                ]),
                errorKey: 'email',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterRequired)
            );
        });
        it('has no password parameter', function () {
            $uri = Uri::of(route('password.update'))->value();
            assertFailedResponse(
                response: $this->postJson($uri, [
                    'token' => 'whatever',
                    'email' => fake()->email()
                ]),
                errorKey: 'password',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterRequired)
            );
        });
        it('has no password_confirmation parameter', function () {
            $uri = Uri::of(route('password.update'))->value();
            assertFailedResponse(
                response: $this->postJson($uri, [
                    'token' => 'whatever',
                    'email' => fake()->email(),
                    'password' => fake()->password(minLength: 8)
                ]),
                errorKey: 'password',
                errorMsg: Phrase::pickSentence(PhraseKey::PassConfirmInvalid)
            );
        });
        it('has invalid email parameter', function () {
            $uri = Uri::of(route('password.update'))->value();
            assertFailedResponse(
                response: $this->postJson($uri, [
                    'token' => 'whatever',
                    'email' => 'whatever',
                ]),
                errorKey: 'email',
                errorMsg: Phrase::pickSentence(PhraseKey::EmailInvalid)
            );
        });
        it('has invalid password_confirmation parameter', function () {
            $uri = Uri::of(route('password.update'))->value();
            assertFailedResponse(
                response: $this->postJson($uri, [
                    'token' => 'whatever',
                    'email' => fake()->email(),
                    'password' => fake()->password(minLength: 8),
                    'password_confirmation' => 'whatever',
                ]),
                errorKey: 'password',
                errorMsg: Phrase::pickSentence(PhraseKey::PassConfirmInvalid)
            );
        });
        it('has password with minimum size invalid', function () {
            $uri = Uri::of(route('password.update'))->value();
            $password = fake()->password(maxLength: 3);
            assertFailedResponse(
                response: $this->postJson($uri, [
                    'token' => 'whatever',
                    'email' => fake()->email(),
                    'password' => $password,
                    'password_confirmation' => $password,
                ]),
                errorKey: 'password',
                errorMsg: Phrase::pickSentence(PhraseKey::MinSizeInvalid)
            );
        });
        it('has password with maximum size invalid', function () {
            $uri = Uri::of(route('password.update'))->value();
            $initial = 'Aa1!';
            $password = $initial . generateWordBySize((UserSize::PASSWORD->get() - mb_strlen($initial)) + 1);
            assertFailedResponse(
                response: $this->postJson($uri, [
                    'token' => 'whatever',
                    'email' => 'test@test.com',
                    'password' => $password,
                    'password_confirmation' => $password,
                ]),
                errorKey: 'password',
                errorMsg: Phrase::pickSentence(PhraseKey::MaxSizeInvalid)
            );
        });
        it('has password no letters', function () {
            $uri = Uri::of(route('password.update'))->value();
            $password = $password = '12345678';
            $minQtyLetters = PasswordRules::QtyLetters->get();
            assertFailedResponse(
                response: $this->postJson($uri, [
                    'token' => 'whatever',
                    'email' => fake()->email(),
                    'password' => $password,
                    'password_confirmation' => $password,
                ]),
                errorKey: 'password',
                errorMsg: Phrase::pickSentence(PhraseKey::MinLettersInvalid, ": ({$minQtyLetters})")
            );
        });
        it('has password no uppercase letters', function () {
            $uri = Uri::of(route('password.update'))->value();
            $password = 'abcdefgh';
            $minQtyUppercase = PasswordRules::QtyUppercase->get();
            assertFailedResponse(
                response: $this->postJson($uri, [
                    'token' => 'whatever',
                    'email' => fake()->email(),
                    'password' => $password,
                    'password_confirmation' => $password,
                ]),
                errorKey: 'password',
                errorMsg: Phrase::pickSentence(PhraseKey::MinUppercaseInvalid, ": ({$minQtyUppercase})")
            );
        });
        it('has password no lowercase letters', function () {
            $uri = Uri::of(route('password.update'))->value();
            $password = $password = 'ABCDEFGH';
            $minQtyLowercase = PasswordRules::QtyLowercase->get();
            assertFailedResponse(
                response: $this->postJson($uri, [
                    'token' => 'whatever',
                    'email' => fake()->email(),
                    'password' => $password,
                    'password_confirmation' => $password,
                ]),
                errorKey: 'password',
                errorMsg: Phrase::pickSentence(PhraseKey::MinLowercaseInvalid, ": ({$minQtyLowercase})")
            );
        });
        it('has password no digits', function () {
            $uri = Uri::of(route('password.update'))->value();
            $password = 'ABCDefgh';
            $minQtyDigits = PasswordRules::QtyDigits->get();
            assertFailedResponse(
                response: $this->postJson($uri, [
                    'token' => 'whatever',
                    'email' => fake()->email(),
                    'password' => $password,
                    'password_confirmation' => $password,
                ]),
                errorKey: 'password',
                errorMsg: Phrase::pickSentence(PhraseKey::MinDigitsInvalid, ": ({$minQtyDigits})")
            );
        });
        it('has password no specialchars', function () {
            $uri = Uri::of(route('password.update'))->value();
            $password = 'ABCDef12';
            $minQtySpecialChars = PasswordRules::QtySpecialChars->get();
            assertFailedResponse(
                response: $this->postJson($uri, [
                    'token' => 'whatever',
                    'email' => fake()->email(),
                    'password' => $password,
                    'password_confirmation' => $password,
                ]),
                errorKey: 'password',
                errorMsg: Phrase::pickSentence(PhraseKey::MinSpecialCharsInvalid, ": ({$minQtySpecialChars})")
            );
        });
        it('has email user nonexitent into database', function () {
            $uri = Uri::of(route('password.update'))->value();
            $password = 'P@ssword1';
            $this->postJson($uri, [
                'token' => 'whatever',
                'email' => fake()->email(),
                'password' => $password,
                'password_confirmation' => $password,
            ])
                ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
                ->assertExactJson([
                    'errors' => [
                        'status' => [Phrase::pickSentence(PhraseKey::PasswordsUser)]
                    ]
                ]);
        });
        it('has invalid token', function () {
            $password = 'P@ssword1';
            $email = fake()->email();
            $userModel = createUserDB(email: $email, password: $password);
            $uri = Uri::of(route('password.update'))->value();
            $this->postJson($uri, [
                'token' => 'whatever',
                'email' => $userModel->email,
                'password' => $password,
                'password_confirmation' => $password,
            ])
                ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
                ->assertExactJson([
                    'errors' => [
                        'status' => [Phrase::pickSentence(PhraseKey::PasswordsToken)]
                    ]
                ]);
        });
        it('receives throttled response', function () {
            $email = fake()->email();
            Password::expects('reset')->andReturn(PasswordBroker::RESET_THROTTLED);
            $password = 'P@ssword1';
            $userModel = createUserDB(email: $email, password: $password);
            $uri = Uri::of(route('password.update'))->value();
            $this->postJson($uri, [
                'token' => 'whatever',
                'email' => $userModel->email,
                'password' => $password,
                'password_confirmation' => $password,
            ])
                ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
                ->assertExactJson([
                    'errors' => [
                        'status' => [Phrase::pickSentence(PhraseKey::PasswordsThrottled)]
                    ]
                ]);
        });
    });
    describe('receives successful because', function () {
        it('has valid parameters', function () {
            $password = 'P@ssword1';
            $email = fake()->email();
            $userModel = createUserDB(email: $email, password: $password);
            $uri = Uri::of(route('password.update'))->value();
            $token = Password::createToken($userModel);

            $response = $this->postJson($uri, [
                'token' => $token,
                'email' => $userModel->email,
                'password' => $password,
                'password_confirmation' => $password,
            ])
                ->assertStatus(Response::HTTP_OK);

            expect($response->json())->toBe(
                Phrase::pickSentence(PhraseKey::PasswordsReset)->toString()
            );
        });
    });
});
