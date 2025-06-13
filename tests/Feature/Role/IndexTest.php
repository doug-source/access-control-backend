<?php

declare(strict_types=1);

use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
use App\Models\Role;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Support\Uri;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Abilities from api routes', function () {
    describe('fails because', function () {
        it('has no authentication', function () {
            $this->getJson(route('role.index'))->assertUnauthorized();
        });
        it('has user no super-admin role authenticated', function () {
            ['token' => $token] = authenticate(scope: $this);
            $this->getJson(route('role.index'), [
                'Authorization' => "Bearer $token"
            ])
                ->assertForbidden();
        });
        it('has invalid page parameter', function () {
            $count = 2;
            Role::factory(count: $count)->create();
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $uri = Uri::of(route('role.index'))->withQuery([
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
            $count = 2;
            Role::factory(count: $count)->create();
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $uri = Uri::of(route('role.index'))->withQuery([
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
            Role::factory(count: 2)->create();
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $uri = Uri::of(route('role.index'))->withQuery([
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
            Role::factory(count: 2)->create();
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $uri = Uri::of(route('role.index'))->withQuery([
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
            $roleList = Role::factory($count)->create()->map(fn(Role $role) => $role->ui)->toArray();
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $uri = Uri::of(route('role.index'))->withQuery([
                'page' => 1,
                'group' => $count
            ])->value();
            $this->getJson($uri, [
                'Authorization' => "Bearer $token"
            ])
                ->assertOk()
                ->assertJson(function (AssertableJson $json) use ($roleList, $count) {
                    $json = $json->has('data', $count);
                    foreach ($roleList as $i => $role) {
                        $json = $json
                            ->has("data.{$i}", function (AssertableJson $json) use ($role) {
                                $json
                                    ->where('id', $role['id'])
                                    ->where('name', $role['name'])
                                    ->etc();
                            });
                    }
                    $json->etc();
                });
        });
    });
});
