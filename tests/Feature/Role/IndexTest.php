<?php

declare(strict_types=1);

use App\Models\Ability;
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
                                    ->where('createdAt', $role['createdAt'])
                                    ->where('updatedAt', $role['updatedAt'])
                                    ->etc();
                            });
                    }
                    $json->etc();
                });
        });
    });
});
