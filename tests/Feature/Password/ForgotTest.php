<?php

declare(strict_types=1);

use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
use App\Models\Provider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Uri;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Mockery\MockInterface;
use Illuminate\Notifications\Messages\MailMessage;

uses(RefreshDatabase::class);

describe('Forgot password from api routes', function () {
    describe('fails because', function () {
        it('has no email parameter', function () {
            $uri = Uri::of(route('password.request'))->value();
            assertFailedResponse(
                response: $this->getJson($uri),
                errorKey: 'email',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterRequired)
            );
        });
        it('has invalid email parameter', function () {
            $uri = Uri::of(route('password.request'))->withQuery([
                'email' => 'whatever'
            ])->value();
            assertFailedResponse(
                response: $this->getJson($uri),
                errorKey: 'email',
                errorMsg: Phrase::pickSentence(PhraseKey::EmailInvalid)
            );
        });
        it('has email parameter nonexistent inside database', function () {
            $uri = Uri::of(route('password.request'))->withQuery([
                'email' => fake()->email()
            ])->value();
            assertFailedResponse(
                response: $this->getJson($uri),
                errorKey: 'email',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterInvalid)
            );
        });
        it('has email parameter from user into database using provider', function () {
            $email = fake()->email();
            $user = createUserDB(email: $email);
            Provider::factory()->createOne([
                'user_id' => $user->id
            ]);
            $uri = Uri::of(route('password.request'))->withQuery([
                'email' => $email
            ])->value();
            assertFailedResponse(
                response: $this->getJson($uri),
                errorKey: 'email',
                errorMsg: Phrase::pickSentence(PhraseKey::LoginByProvider)
            );
        });
        it("has email parameter that returns invalid message 'INVALID_USER' correctly", function () {
            $email = fake()->email();
            Password::expects('sendResetLink')->with([
                'email' => $email
            ])->andReturn(PasswordBroker::INVALID_USER);
            $password = fake()->password();
            createUserDB(email: $email, password: $password);
            $uri = Uri::of(route('password.request'))->withQuery([
                'email' => $email
            ])->value();
            $this->getJson($uri)
                ->assertExactJson([
                    "errors" => [
                        "status" => [
                            Phrase::pickSentence(key: PhraseKey::PasswordsUser)
                        ]
                    ]
                ])
                ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        });
    });
    describe('receives successful because', function () {
        it('has valid email parameter and sending reset link notification correctly', function () {
            Notification::fake();
            $email = fake()->email();
            $password = fake()->password();
            $userModel = createUserDB(email: $email, password: $password);
            $uri = Uri::of(route('password.request'))->withQuery([
                'email' => $email
            ])->value();
            Notification::assertNothingSent();
            $this->getJson($uri)->assertStatus(Response::HTTP_OK);
            Notification::assertSentToTimes($userModel, ResetPassword::class, 1);
        });
        it('has valid email parameter and sending email correctly', function () {
            Mail::fake();
            $email = fake()->email();
            $password = fake()->password();
            createUserDB(email: $email, password: $password);
            $uri = Uri::of(route('password.request'))->withQuery([
                'email' => $email
            ])->value();

            $response = $this->getJson($uri)->assertStatus(Response::HTTP_OK);
            expect($response->json())->toBe(
                Phrase::pickSentence(PhraseKey::PasswordsSent)->toString()
            );
        });
    });
});
