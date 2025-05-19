<?php

use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
use App\Repositories\UserRepository;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;

uses(RefreshDatabase::class);

describe('Email Verification from api routes', function () {
    describe('fails because', function () {
        it('has user no email verified into database', function () {
            $password = 'whatever';
            $userModel = buildSocialite(
                password: $password,
                email: 'someone@test.com',
                emailVerified: FALSE
            );
            $responseLogin = $this->postJson(route('auth.login'), [
                'email' => $userModel->email,
                'password' => $password
            ]);
            $token = $responseLogin->json()['user']['token'];
            // TO-DO: Change by commom user profile access in future
            $this->getJson(route('register.request.index'), [
                'Authorization' => "Bearer {$token}"
            ])
                ->assertStatus(Response::HTTP_FORBIDDEN)
                ->assertJson([
                    'message' => 'Your email address is not verified.'
                ]);
        });
        it('has email already verified during verification.send route request', function () {
            $password = 'whatever';
            $userModel = buildSocialite(
                password: $password,
                email: 'someone@test.com',
            );
            $responseLogin = $this->postJson(route('auth.login'), [
                'email' => $userModel->email,
                'password' => $password
            ]);
            $token = $responseLogin->json()['user']['token'];
            $this->postJson(route('verification.send'), [
                'Authorization' => "Bearer {$token}"
            ])
                ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
                ->assertExactJson([
                    "message" => Phrase::pickSentence(PhraseKey::EmailAlreadyVerified),
                    "errors" =>  [
                        "status" => [
                            Phrase::pickSentence(PhraseKey::EmailAlreadyVerified)
                        ]
                    ]
                ])
            ;
        });
    });
    describe('receives successful because', function () {
        it('sends verification.send route request', function () {
            Notification::fake();
            $password = 'whatever';
            $userModel = buildSocialite(
                password: $password,
                email: 'someone@test.com',
                emailVerified: FALSE
            );
            Notification::assertNothingSent();
            $responseLogin = $this->postJson(route('auth.login'), [
                'email' => $userModel->email,
                'password' => $password
            ]);
            $token = $responseLogin->json()['user']['token'];
            $this->postJson(route('verification.send'), [
                'Authorization' => "Bearer {$token}"
            ])->assertStatus(Response::HTTP_OK);
            Notification::assertSentToTimes($userModel, VerifyEmail::class, 1);
        });
        it('sends verification.verify route request with email not validated', function () {
            $password = 'whatever';
            $userModel = buildSocialite(
                password: $password,
                email: 'someone@test.com',
                emailVerified: FALSE
            );
            $responseLogin = $this->postJson(route('auth.login'), [
                'email' => $userModel->email,
                'password' => $password
            ]);
            $token = $responseLogin->json()['user']['token'];
            $url = URL::signedRoute('verification.verify', [
                'id' => $userModel->id,
                'hash' => sha1($userModel->getEmailForVerification())
            ]);
            $this->getJson($url, [
                'Authorization' => "Bearer {$token}"
            ])->assertOk();
            expect((new UserRepository())->find($userModel->id)?->hasVerifiedEmail())->toBe(TRUE);
        });
        it('sends verification.verify route request with email validated', function () {
            $password = 'whatever';
            $userModel = buildSocialite(
                password: $password,
                email: 'someone@test.com',
            );
            $responseLogin = $this->postJson(route('auth.login'), [
                'email' => $userModel->email,
                'password' => $password
            ]);
            $token = $responseLogin->json()['user']['token'];
            $url = URL::signedRoute('verification.verify', [
                'id' => $userModel->id,
                'hash' => sha1($userModel->getEmailForVerification())
            ]);
            $this->getJson($url, [
                'Authorization' => "Bearer {$token}"
            ])->assertOk();
            expect((new UserRepository())->find($userModel->id)?->hasVerifiedEmail())->toBe(TRUE);
        });
    });
});
