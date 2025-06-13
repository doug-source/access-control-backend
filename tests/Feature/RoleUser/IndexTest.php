<?php

declare(strict_types=1);

use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
use App\Models\Role;
use App\Repositories\RoleRepository;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Uri;

uses(RefreshDatabase::class);

describe("User's Role Index from api routes", function () {
    describe('fails because', function () {
        it('has no authentication', function () {
            $this->getJson(route('user.role.index', ['user' => '1']))->assertUnauthorized();
        });
        it('has user no super-admin role authenticated', function () {
            ['token' => $token] = authenticate(scope: $this);
            createUserDB(email: fake()->email(), password: fake()->password());
            $this->getJson(route('user.role.index', ['user' => '2']), [
                'Authorization' => "Bearer $token"
            ])
                ->assertForbidden();
        });
        it('has invalid user route parameter', function () {
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $this->getJson(route('user.role.index', ['user' => fake()->word()]), [
                'Authorization' => "Bearer $token"
            ])
                ->assertNotFound();
        });
        it('has user not persisted into database', function () {
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $this->getJson(route('user.role.index', ['user' => '7']), [
                'Authorization' => "Bearer $token"
            ])
                ->assertNotFound();
        });
        it('has invalid page parameter', function () {
            $count = 2;
            Role::factory(count: $count)->create();
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $uri = Uri::of(route('user.role.index', [
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
            Role::factory(count: $count)->create();
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $uri = Uri::of(route('user.role.index', [
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
            Role::factory(count: 2)->create();
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $uri = Uri::of(route('user.role.index', [
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
            Role::factory(count: 2)->create();
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $uri = Uri::of(route('user.role.index', [
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
            Role::factory(count: 2)->create();
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $uri = Uri::of(route('user.role.index', [
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
            $userDummy = createUserDB(
                email: fake()->email(),
                password: fake()->password(),
            );
            $roles = Role::factory(count: 2)->create();
            $userDummy->roles()->attach($roles->pluck('id')->all());

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $this->getJson(route('user.role.index', ['user' => $userDummy->id]), [
                'Authorization' => "Bearer $token"
            ])
                ->assertOk()
                ->assertJson(function (AssertableJson $json) use (&$roles) {
                    $json = $json->has('data', sizeof($roles));
                    foreach ($roles as $i => $role) {
                        $json = $json
                            ->has("data.{$i}", function (AssertableJson $json) use ($role) {
                                $data = $role->ui;
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
            $roleQty = 2;
            $userDummy = createUserDB(
                email: fake()->email(),
                password: fake()->password(),
            );
            $roles = Role::factory(count: $roleQty)->create();
            $userDummy->roles()->attach($roles->pluck('id')->all());

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);

            $uri = Uri::of(route('user.role.index', [
                'user' => $userDummy->id
            ]))->withQuery([
                'page' => '1',
                'group' => $roleQty,
                'owner' => 'yes',
            ])->value();
            $this->getJson($uri, [
                'Authorization' => "Bearer $token"
            ])
                ->assertOk()
                ->assertJson(function (AssertableJson $json) use (&$roles) {
                    $json = $json->has('data', $roles->count());
                    foreach ($roles as $i => $role) {
                        $json = $json
                            ->has("data.{$i}", function (AssertableJson $json) use ($role) {
                                $data = $role->ui;
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
            $roleQty = 2;
            $userDummy = createUserDB(
                email: fake()->email(),
                password: fake()->password(),
            );
            $roles = Role::factory(count: $roleQty)->create();
            $userDummy->roles()->attach($roles->pluck('id')->all());

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);

            ['data' => [$data]] = json_decode(
                json: app(RoleRepository::class)->findRoleListFiltered(
                    page: 1,
                    group: $roleQty,
                    exclude: $userDummy->roles->pluck('id')->all(),
                )->toJson(),
                associative: TRUE
            );

            $uri = Uri::of(route('user.role.index', [
                'user' => $userDummy->id
            ]))->withQuery([
                'page' => 1,
                'group' => $roleQty,
                'owner' => 'no',
            ])->value();

            $this->getJson($uri, [
                'Authorization' => "Bearer $token"
            ])
                ->assertOk()
                ->assertJson(function (AssertableJson $json) use (&$data) {
                    $json
                        ->has('data', 1)
                        ->has("data.0", function (AssertableJson $json) use (&$data) {
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
