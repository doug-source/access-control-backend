<?php

declare(strict_types=1);

use App\Library\Builders\Phrase;
use App\Library\Enums\ColumnSize\RegisterRequestSize;
use App\Library\Enums\PhraseKey;
use App\Models\RegisterRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Uri;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('RegisterRequest index request', function () {
    describe('fails because', function () {
        it('has no authentication', function () {
            $response = $this->getJson(route('register.request.index'));
            $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        });
        it('has no page parameter', function () {
            ['token' => $token] = authenticate(scope: $this);
            assertFailedResponse(
                response: $this->getJson(route('register.request.index'), [
                    'Authorization' => "Bearer {$token}",
                    'Accept' => 'application/json',
                ]),
                errorKey: 'page',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterRequired)
            );
        });
        it('has invalid page parameter', function () {
            ['token' => $token] = authenticate(scope: $this);
            $uri = Uri::of(route('register.request.index'))->withQuery([
                'page' => 'whatever'
            ])->value();
            assertFailedResponse(
                response: $this->getJson($uri, [
                    'Authorization' => "Bearer {$token}",
                    'Accept' => 'application/json',
                ]),
                errorKey: 'page',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterInvalid)
            );
        });
        it('has page parameter lower then minimal size', function () {
            ['token' => $token] = authenticate(scope: $this);
            $uri = Uri::of(route('register.request.index'))->withQuery([
                'page' => '0'
            ])->value();
            assertFailedResponse(
                response: $this->getJson($uri, [
                    'Authorization' => "Bearer {$token}",
                    'Accept' => 'application/json',
                ]),
                errorKey: 'page',
                errorMsg: Phrase::pickSentence(PhraseKey::MinSizeInvalid, " (1)")
            );
        });
        it('has no group parameter', function () {
            ['token' => $token] = authenticate(scope: $this);
            $uri = Uri::of(route('register.request.index'))->withQuery([
                'page' => '1'
            ])->value();
            assertFailedResponse(
                response: $this->getJson($uri, [
                    'Authorization' => "Bearer {$token}",
                    'Accept' => 'application/json',
                ]),
                errorKey: 'group',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterRequired)
            );
        });
        it('has invalid group parameter', function () {
            ['token' => $token] = authenticate(scope: $this);
            $uri = Uri::of(route('register.request.index'))->withQuery([
                'page' => '1',
                'group' => 'whatever'
            ])->value();
            assertFailedResponse(
                response: $this->getJson($uri, [
                    'Authorization' => "Bearer {$token}",
                    'Accept' => 'application/json',
                ]),
                errorKey: 'group',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterInvalid)
            );
        });
        it('has group parameter lower then minimal size', function () {
            ['token' => $token] = authenticate(scope: $this);
            $uri = Uri::of(route('register.request.index'))->withQuery([
                'page' => '1',
                'group' => '0'
            ])->value();
            assertFailedResponse(
                response: $this->getJson($uri, [
                    'Authorization' => "Bearer {$token}",
                    'Accept' => 'application/json',
                ]),
                errorKey: 'group',
                errorMsg: Phrase::pickSentence(PhraseKey::MinSizeInvalid, " (1)")
            );
        });
        it('has email parameter greater then maximun size', function () {
            $maxColumnSize = RegisterRequestSize::EMAIL->get();
            $email = generateOverflowInvalidEmail($maxColumnSize)->toString();
            $uri = Uri::of(route('register.request.index'))->withQuery([
                'page' => 1,
                'group' => 1,
                'email' => $email
            ])->value();

            ['token' => $token] = authenticate(scope: $this);
            assertFailedResponse(
                response: $this->getJson($uri, [
                    'Authorization' => "Bearer {$token}",
                    'Accept' => 'application/json',
                ]),
                errorKey: 'email',
                errorMsg: Phrase::pickSentence(PhraseKey::MaxSizeInvalid, " ({$maxColumnSize})")
            );
        });
        it('executes by user no super-admin role', function () {
            RegisterRequest::factory(count: 1)->createOne([
                'email' => fake()->email(),
                'phone' => '12345678901'
            ]);
            ['token' => $token] = authenticate(scope: $this, password: 'Test123!');

            $uri = Uri::of(route('register.request.index'))->withQuery([
                'page' => '1',
                'group' => '1',
            ])->value();
            $this->getJson($uri, [
                'Authorization' => "Bearer $token"
            ])
                ->assertForbidden()
                ->assertJson([
                    'message' => 'This action is unauthorized.'
                ]);
        });
    });
    describe('succeed because', function () {
        it('has complete parameters', function () {
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);

            $uri = Uri::of(route('register.request.index'))->withQuery([
                'page' => 1,
                'group' => 1,
                'email' => fake()->email()
            ])->value();
            $this->getJson($uri, [
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/json',
            ])->assertOk();
        });
        it('has no email parameter', function () {
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);

            $uri = Uri::of(route('register.request.index'))->withQuery([
                'page' => 1,
                'group' => 1,
            ])->value();
            $this->getJson($uri, [
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/json',
            ])->assertStatus(200);
        });
    });
});
