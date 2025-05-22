<?php

declare(strict_types=1);

use App\Models\Role;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe("User's Role Index from api routes", function () {
    describe('fails because', function () {
        it('has no authentication', function () {
            $this->getJson(route('user.role.index', ['user' => 'whatever']))->assertUnauthorized();
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
    });
    describe('succeed because', function () {
        it('has user super-admin authenticated', function () {
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
