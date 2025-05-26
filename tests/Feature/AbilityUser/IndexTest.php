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

describe("User's Ability Index from api routes", function () {
    describe('fails because', function () {
        it('has no authentication', function () {
            $this->getJson(route('user.ability.index', ['user' => 'whatever']))->assertUnauthorized();
        });
        it('has user no super-admin role authenticated', function () {
            Role::factory(count: 1)->createOne();
            ['token' => $token] = authenticate(scope: $this);
            $this->getJson(route('user.ability.index', ['user' => '1']), [
                'Authorization' => "Bearer $token"
            ])
                ->assertForbidden();
        });
        it('has invalid user route parameter', function () {
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $this->getJson(route('user.ability.index', ['user' => fake()->word()]), [
                'Authorization' => "Bearer $token"
            ])
                ->assertNotFound();
        });
        it('has user not persisted into database', function () {
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $this->getJson(route('user.ability.index', ['user' => '7']), [
                'Authorization' => "Bearer $token"
            ])
                ->assertNotFound();
        });
        it('has invalid page parameter', function () {
            $count = 2;
            Ability::factory(count: $count)->create();
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $uri = Uri::of(route('user.ability.index', [
                'user' => 1
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
            $uri = Uri::of(route('user.ability.index', [
                'user' => 1
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
            $uri = Uri::of(route('user.ability.index', [
                'user' => 1
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
            $uri = Uri::of(route('user.ability.index', [
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
        it('has invalid owner parameter', function () {
            Ability::factory(count: 2)->create();
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $uri = Uri::of(route('user.ability.index', [
                'user' => 1
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
            $abilityFirstList = Ability::factory(count: 2)->create();
            $roleDummy->abilities()->attach($abilityFirstList->pluck('id')->all());
            $abilitySecondList = Ability::factory(count: 2)->create();
            $userDummy = createUserDB(
                email: fake()->email(),
                password: fake()->password(),
            );
            $userDummy->roles()->attach($roleDummy->id);
            $userDummy->abilities()->attach($abilitySecondList->pluck('id')->all());
            $allAbilities = $abilityFirstList->concat($abilitySecondList);

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);

            $uri = Uri::of(
                route(
                    'user.ability.index',
                    ['user' => $userDummy->id]
                )
            )
                ->withQuery([
                    'page' => 1,
                    'group' => $allAbilities->count()
                ])
                ->value();
            $this->getJson($uri, [
                'Authorization' => "Bearer $token"
            ])
                ->assertOk()
                ->assertJson(function (AssertableJson $json) use (&$allAbilities) {
                    $json = $json->has('data', $allAbilities->count());
                    foreach ($allAbilities as $i => $ability) {
                        $json = $json
                            ->has("data.{$i}", function (AssertableJson $json) use ($ability) {
                                $data = $ability->ui;
                                $json
                                    ->where('id', $data['id'])
                                    ->where('name', $data['name'])
                                    ->where('createdAt', $data['createdAt'])
                                    ->where('updatedAt', $data['updatedAt'])
                                    ->etc();
                            });
                    }
                    $json->etc();
                });
        });
        it('has user super-admin authenticated (owner parameter sent as yes)', function () {
            $roleDummy = Role::factory(count: 1)->createOne();
            $abilityFirstList = Ability::factory(count: 2)->create();
            $roleDummy->abilities()->attach($abilityFirstList->pluck('id')->all());
            $abilitySecondList = Ability::factory(count: 2)->create();
            $userDummy = createUserDB(
                email: fake()->email(),
                password: fake()->password(),
            );
            $userDummy->roles()->attach($roleDummy->id);
            $userDummy->abilities()->attach($abilitySecondList->pluck('id')->all());
            $allAbilities = $abilityFirstList->concat($abilitySecondList);

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);

            $uri = Uri::of(
                route(
                    'user.ability.index',
                    ['user' => $userDummy->id]
                )
            )
                ->withQuery([
                    'page' => 1,
                    'group' => $allAbilities->count(),
                    'owner' => 'yes',
                ])
                ->value();
            $this->getJson($uri, [
                'Authorization' => "Bearer $token"
            ])
                ->assertOk()
                ->assertJson(function (AssertableJson $json) use (&$allAbilities) {
                    $json = $json->has('data', $allAbilities->count());
                    foreach ($allAbilities as $i => $ability) {
                        $json = $json
                            ->has("data.{$i}", function (AssertableJson $json) use ($ability) {
                                $data = $ability->ui;
                                $json
                                    ->where('id', $data['id'])
                                    ->where('name', $data['name'])
                                    ->where('createdAt', $data['createdAt'])
                                    ->where('updatedAt', $data['updatedAt'])
                                    ->etc();
                            });
                    }
                    $json->etc();
                });
        });
        it('has user super-admin authenticated (owner parameter sent as no)', function () {
            $roleDummy = Role::factory(count: 1)->createOne();
            $abilityFirstList = Ability::factory(count: 2)->create();
            $roleDummy->abilities()->attach($abilityFirstList->pluck('id')->all());
            $abilitySecondList = Ability::factory(count: 2)->create();
            $userDummy = createUserDB(
                email: fake()->email(),
                password: fake()->password(),
            );
            $userDummy->roles()->attach($roleDummy->id);
            $userDummy->abilities()->attach($abilitySecondList->pluck('id')->all());

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);

            $superAdminAbilities = $user->roles->map(fn(Role $role) => $role->abilities)->flatten(1);
            ['data' => $data] = json_decode(
                json: app(AbilityRepository::class)->paginate(
                    page: 1,
                    group: 3,
                    exclude: $superAdminAbilities->pluck('id')->all(),
                )->toJson(),
                associative: TRUE
            );

            $uri = Uri::of(
                route(
                    'user.ability.index',
                    ['user' => $userDummy->id]
                )
            )
                ->withQuery([
                    'page' => 1,
                    'group' => $superAdminAbilities->count(),
                    'owner' => 'no',
                ])
                ->value();
            $this->getJson($uri, [
                'Authorization' => "Bearer $token"
            ])
                ->assertOk()
                ->assertJson(function (AssertableJson $json) use (&$superAdminAbilities) {
                    $json = $json->has('data', $superAdminAbilities->count());
                    foreach ($superAdminAbilities as $i => $ability) {
                        $json = $json
                            ->has("data.{$i}", function (AssertableJson $json) use ($ability) {
                                $data = $ability->ui;
                                $json
                                    ->where('id', $data['id'])
                                    ->where('name', $data['name'])
                                    ->where('createdAt', $data['createdAt'])
                                    ->where('updatedAt', $data['updatedAt'])
                                    ->etc();
                            });
                    }
                    $json->etc();
                });
        });
    });
});
