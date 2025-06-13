<?php

declare(strict_types=1);

use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
use App\Models\Ability;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Support\Uri;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Abilities from api routes', function () {
    describe('fails because', function () {
        it('has no authentication', function () {
            $this->getJson(route('ability.index'))->assertUnauthorized();
        });
        it('has user no super-admin role authenticated', function () {
            ['token' => $token] = authenticate(scope: $this);
            $this->getJson(route('ability.index'), [
                'Authorization' => "Bearer $token"
            ])
                ->assertForbidden();
        });
        it('has invalid page parameter', function () {
            $count = 1;
            Ability::factory(count: $count)->create();
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $uri = Uri::of(route('ability.index'))->withQuery([
                'page' => 'whatever',
                'group' => $count
            ])->value();
            assertFailedResponse(
                response: $this->getJson($uri, [
                    'Authorization' => "Bearer $token"
                ]),
                errorKey: 'page',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterInvalid)
            );
        });
        it('has page parameter lower then minimal size', function () {
            $count = 1;
            Ability::factory(count: $count)->create();
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $uri = Uri::of(route('ability.index'))->withQuery([
                'page' => '0',
                'group' => $count
            ])->value();
            assertFailedResponse(
                response: $this->getJson($uri, [
                    'Authorization' => "Bearer $token"
                ]),
                errorKey: 'page',
                errorMsg: Phrase::pickSentence(PhraseKey::MinSizeInvalid, " (1)")
            );
        });
        it('has invalid group parameter', function () {
            Ability::factory(count: 1)->create();
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $uri = Uri::of(route('ability.index'))->withQuery([
                'page' => '1',
                'group' => 'whatever'
            ])->value();
            assertFailedResponse(
                response: $this->getJson($uri, [
                    'Authorization' => "Bearer $token"
                ]),
                errorKey: 'group',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterInvalid)
            );
        });
        it('has group parameter lower then minimal size', function () {
            Ability::factory(count: 1)->create();
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $uri = Uri::of(route('ability.index'))->withQuery([
                'page' => '1',
                'group' => '0'
            ])->value();
            assertFailedResponse(
                response: $this->getJson($uri, [
                    'Authorization' => "Bearer $token"
                ]),
                errorKey: 'group',
                errorMsg: Phrase::pickSentence(PhraseKey::MinSizeInvalid, " (1)")
            );
        });
    });
    describe('succeed because', function () {
        it('has user super-admin authenticated', function () {
            $count = 2;
            $abilityList = Ability::factory($count)->create()->map(fn(Ability $ability) => $ability->ui)->toArray();
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $uri = Uri::of(route('ability.index'))->withQuery([
                'page' => 1,
                'group' => $count
            ])->value();
            $this->getJson($uri, [
                'Authorization' => "Bearer $token"
            ])
                ->assertOk()
                ->assertJson(function (AssertableJson $json) use ($abilityList, $count) {
                    $json = $json->has('data', $count);
                    foreach ($abilityList as $i => $ability) {
                        $json = $json
                            ->has("data.{$i}", function (AssertableJson $json) use ($ability) {
                                $json
                                    ->where('id', $ability['id'])
                                    ->where('name', $ability['name'])
                                    ->etc();
                            });
                    }
                    $json->etc();
                });
        });
    });
});
