<?php

declare(strict_types=1);

use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Uri;

uses(RefreshDatabase::class);

describe('User soft-deleted Index from api routes', function () {
    describe('fails because', function () {
        it('has no authentication', function () {
            $this->getJson(route('user.removed.index'))->assertUnauthorized();
        });
        it('has user no super-admin role authenticated', function () {
            ['token' => $token] = authenticate(scope: $this);
            $this->getJson(route('user.removed.index'), [
                'Authorization' => "Bearer $token"
            ])
                ->assertForbidden();
        });
        it('has invalid page parameter', function () {
            createUserDB(email: fake()->email(), password: fake()->password());
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $uri = Uri::of(route('user.removed.index'))->withQuery([
                'page' => 'whatever',
                'group' => 1
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
            createUserDB(email: fake()->email(), password: fake()->password());
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $uri = Uri::of(route('user.removed.index'))->withQuery([
                'page' => '0',
                'group' => 1
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
            createUserDB(email: fake()->email(), password: fake()->password());
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $uri = Uri::of(route('user.removed.index'))->withQuery([
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
            createUserDB(email: fake()->email(), password: fake()->password());
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $uri = Uri::of(route('user.removed.index', [
                'user' => 1
            ]))->withQuery([
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
        it('has user super-admin authenticated (no name query parameter)', function () {
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $userList = User::factory(count: 2)->create([
                'deleted_at' => now()
            ])->all();

            $uri = Uri::of(route('user.removed.index', [
                'user' => 1
            ]))->withQuery([
                'page' => '1',
                'group' => sizeof($userList),
            ])->value();
            $this->getJson($uri, [
                'Authorization' => "Bearer $token"
            ])
                ->assertJson(function (AssertableJson $json) use (&$userList) {
                    $json = $json->has('data', sizeof($userList));
                    foreach ($userList as $i => $user) {
                        $json = $json
                            ->has("data.{$i}", function (AssertableJson $json) use ($user) {
                                $data = $user->ui;
                                $json
                                    ->where('id', $data['id'])
                                    ->where('name', $data['name'])
                                    ->etc();
                            });
                    }
                    $json->etc();
                });
        });
        it('has user super-admin authenticated (with name query parameter)', function () {
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $userList = User::factory(count: 2)->create([
                'deleted_at' => now()
            ])->all();

            $firstUser = $userList[0];

            $uri = Uri::of(route('user.removed.index', [
                'user' => 1
            ]))->withQuery([
                'page' => '1',
                'group' => sizeof($userList),
                'name' => $firstUser->name,
            ])->value();
            $this->getJson($uri, [
                'Authorization' => "Bearer $token"
            ])
                ->assertJson(function (AssertableJson $json) use (&$firstUser) {
                    $json
                        ->has('data', 1)
                        ->has("data.0", function (AssertableJson $json) use (&$firstUser) {
                            $data = $firstUser->ui;
                            $json
                                ->where('id', $data['id'])
                                ->where('name', $data['name'])
                                ->etc();
                        })
                        ->etc();
                });
        });
    });
});
