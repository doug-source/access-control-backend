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
            $token = login($this);
            $responseRegReq = $this->getJson(route('register.request.index'), [
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/json',
            ]);
            assertFailedResponse($responseRegReq, 'page', Phrase::pickSentence(PhraseKey::ParameterRequired));
        });
        it('has invalid page parameter', function () {
            $token = login($this);
            $route = route('register.request.index');
            $responseRegReq = $this->getJson("{$route}?page=any", [
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/json',
            ]);
            assertFailedResponse($responseRegReq, 'page', Phrase::pickSentence(PhraseKey::ParameterInvalid));
        });
        it('has page parameter lower then minimal size', function () {
            $token = login($this);
            $route = route('register.request.index');
            $responseRegReq = $this->getJson("{$route}?page=0", [
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/json',
            ]);
            assertFailedResponse($responseRegReq, 'page', Phrase::pickSentence(PhraseKey::MinSizeInvalid, " (1)"));
        });
        it('has no group parameter', function () {
            $token = login($this);
            $route = route('register.request.index');
            $responseRegReq = $this->getJson("{$route}?page=1", [
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/json',
            ]);
            assertFailedResponse($responseRegReq, 'group', Phrase::pickSentence(PhraseKey::ParameterRequired));
        });
        it('has invalid group parameter', function () {
            $token = login($this);
            $route = route('register.request.index');
            $responseRegReq = $this->getJson("{$route}?page=1&group=any", [
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/json',
            ]);
            assertFailedResponse($responseRegReq, 'group', Phrase::pickSentence(PhraseKey::ParameterInvalid));
        });
        it('has group parameter lower then minimal size', function () {
            $token = login($this);
            $route = route('register.request.index');
            $responseRegReq = $this->getJson("{$route}?page=1&group=0", [
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/json',
            ]);
            assertFailedResponse($responseRegReq, 'group', Phrase::pickSentence(PhraseKey::MinSizeInvalid, " (1)"));
        });
        it('has email parameter greater then maximun size', function () {
            $maxColumnSize = RegisterRequestSize::EMAIL->get();
            $email = generateOverflowInvalidEmail($maxColumnSize);

            $token = login($this);
            $uri = Uri::of(route('register.request.index'))->withQuery([
                'page' => 1,
                'group' => 1,
                'email' => $email->toString()
            ])->value();
            $responseRegReq = $this->getJson($uri, [
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/json',
            ]);
            assertFailedResponse($responseRegReq, 'email', Phrase::pickSentence(PhraseKey::MaxSizeInvalid, " ({$maxColumnSize})"));
        });
        it('executes by user no super-admin role', function () {
            RegisterRequest::factory(count: 1)->createOne([
                'email' => fake()->email(),
                'phone' => '12345678901'
            ]);
            $email = fake()->email();
            $password = 'Test123!';
            $token = login(scope: $this, email: $email, password: $password);

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
    describe('receives successful because', function () {
        it('has complete parameters', function () {
            $email = fake()->email();
            $token = login(scope: $this, email: $email);
            createSuperAdminRelationship(
                findUserFromDB(email: $email)
            );
            $uri = Uri::of(route('register.request.index'))
                ->withQuery([
                    'page' => 1,
                    'group' => 1,
                    'email' => $email
                ])->value();

            $this->getJson($uri, [
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/json',
            ])->assertStatus(200);
        });
        it('has no email parameter', function () {
            $email = fake()->email();
            $token = login(scope: $this, email: $email);
            createSuperAdminRelationship(
                findUserFromDB(email: $email)
            );
            $uri = Uri::of(route('register.request.index'))
                ->withQuery([
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
