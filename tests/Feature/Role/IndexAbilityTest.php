<?php

declare(strict_types=1);

use App\Models\Ability;
use App\Models\Role;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
    });
    describe('succeed because', function () {
        it('has user super-admin authenticated', function () {
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
