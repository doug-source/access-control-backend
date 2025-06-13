<?php

declare(strict_types=1);

use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
use App\Models\Ability;
use App\Models\Role;
use App\Repositories\AbilityRepository;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Uri;

uses(RefreshDatabase::class);

describe("Role's Ability Index from api routes", function () {
    describe('fails because', function () {
        it('has no authentication', function () {
            $this->getJson(route('role.ability.index', ['role' => 'whatever']))->assertUnauthorized();
        });
        it('has user no super-admin role authenticated', function () {
            Role::factory(count: 1)->createOne();
            ['token' => $token] = authenticate(scope: $this);
            $this->getJson(route('role.ability.index', ['role' => '1']), [
                'Authorization' => "Bearer $token"
            ])
                ->assertForbidden();
        });
        it('has invalid role route parameter', function () {
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $this->getJson(route('role.ability.index', ['role' => fake()->word()]), [
                'Authorization' => "Bearer $token"
            ])
                ->assertNotFound();
        });
        it('has role not persisted into database', function () {
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $this->getJson(route('role.ability.index', ['role' => '7']), [
                'Authorization' => "Bearer $token"
            ])
                ->assertNotFound();
        });
        it('has invalid page parameter', function () {
            $count = 2;
            Ability::factory(count: $count)->create();
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $uri = Uri::of(route('role.ability.index', [
                'role' => 1
            ]))->withQuery([
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
            Ability::factory(count: $count)->create();
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $uri = Uri::of(route('role.ability.index', [
                'role' => 1
            ]))->withQuery([
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
            Ability::factory(count: 2)->create();
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $uri = Uri::of(route('role.ability.index', [
                'role' => 1
            ]))->withQuery([
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
            Ability::factory(count: 2)->create();
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $uri = Uri::of(route('role.ability.index', [
                'role' => 1
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
        it('has invalid owner parameter', function () {
            Ability::factory(count: 2)->create();
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $uri = Uri::of(route('role.ability.index', [
                'role' => 1
            ]))->withQuery([
                'page' => '1',
                'group' => '1',
                'owner' => 'whatever',
            ])->value();
            assertFailedResponse(
                response: $this->getJson($uri, [
                    'Authorization' => "Bearer $token"
                ]),
                errorKey: 'owner',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterInvalid)
            );
        });
    });
    describe('succeed because', function () {
        it('has user super-admin authenticated (no owner parameter)', function () {
            $roleDummy = Role::factory(count: 1)->createOne();
            $abilities = Ability::factory(count: 2)->create();
            $roleDummy->abilities()->attach($abilities->pluck('id')->all());

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $this->getJson(route('role.ability.index', ['role' => $roleDummy->id]), [
                'Authorization' => "Bearer $token"
            ])
                ->assertOk()
                ->assertJson(function (AssertableJson $json) use (&$abilities) {
                    $json = $json->has('data', sizeof($abilities));
                    foreach ($abilities as $i => $ability) {
                        $json = $json
                            ->has("data.{$i}", function (AssertableJson $json) use ($ability) {
                                $data = $ability->ui;
                                $json
                                    ->where('id', $data['id'])
                                    ->where('name', $data['name'])
                                    ->etc();
                            });
                    }
                    $json->etc();
                });
        });
        it('has user super-admin authenticated (owner parameter sent as yes)', function () {
            $abilityQty = 2;
            $roleDummy = Role::factory(count: 1)->createOne();
            $abilities = Ability::factory(count: $abilityQty)->create();
            $roleDummy->abilities()->attach($abilities->pluck('id')->all());

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);

            $uri = Uri::of(route('role.ability.index', [
                'role' => $roleDummy->id
            ]))->withQuery([
                'page' => '1',
                'group' => $abilityQty,
                'owner' => 'yes',
            ])->value();
            $this->getJson($uri, [
                'Authorization' => "Bearer $token"
            ])
                ->assertOk()
                ->assertJson(function (AssertableJson $json) use (&$abilities) {
                    $json = $json->has('data', sizeof($abilities));
                    foreach ($abilities as $i => $ability) {
                        $json = $json
                            ->has("data.{$i}", function (AssertableJson $json) use ($ability) {
                                $data = $ability->ui;
                                $json
                                    ->where('id', $data['id'])
                                    ->where('name', $data['name'])
                                    ->etc();
                            });
                    }
                    $json->etc();
                });
        });
        it('has user super-admin authenticated (owner parameter sent as no)', function () {
            $abilityQty = 2;
            $roleDummy = Role::factory(count: 1)->createOne();
            $abilities = Ability::factory(count: $abilityQty)->create();
            $roleDummy->abilities()->attach($abilities->pluck('id')->all());

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);

            ['data' => $data] = json_decode(
                json: app(AbilityRepository::class)->paginate(
                    page: 1,
                    group: $abilityQty,
                    exclude: $roleDummy->abilities->pluck('id')->all(),
                )->toJson(),
                associative: TRUE
            );

            $uri = Uri::of(route('role.ability.index', [
                'role' => $roleDummy->id
            ]))->withQuery([
                'page' => '1',
                'group' => $abilityQty,
                'owner' => 'no',
            ])->value();
            $this->getJson($uri, [
                'Authorization' => "Bearer $token"
            ])
                ->assertOk()
                ->assertJson(function (AssertableJson $json) use (&$data) {
                    $json = $json->has('data', 2);
                    foreach ($data as $i => $ability) {
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
