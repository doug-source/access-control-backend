<?php

use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
use Illuminate\Http\Response;
use App\Library\Enums\UserColumnSize;
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
            $maxColumnSize = UserColumnSize::EMAIL->get();
            $email = generateOverflowInvalidEmail($maxColumnSize);

            $token = login($this);
            $route = route('register.request.index');
            $qs = http_build_query([
                'page' => 1,
                'group' => 1,
                'email' => $email->toString()
            ]);

            $responseRegReq = $this->getJson("{$route}?{$qs}", [
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/json',
            ]);
            assertFailedResponse($responseRegReq, 'email', Phrase::pickSentence(PhraseKey::MaxSizeInvalid, " ({$maxColumnSize})"));
        });
    });
    describe('receives successful because', function () {
        it('has complete parameters', function () {
            $token = login($this);
            $route = route('register.request.index');
            $qs = http_build_query([
                'page' => 1,
                'group' => 1,
                'email' => fake()->email()
            ]);
            $this->getJson("{$route}?{$qs}", [
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/json',
            ])->assertStatus(200);
        });
        it('has no email parameter', function () {
            $token = login($this);
            $route = route('register.request.index');
            $qs = http_build_query([
                'page' => 1,
                'group' => 1,
            ]);
            $this->getJson("{$route}?{$qs}", [
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/json',
            ])->assertStatus(200);
        });
    });
});
