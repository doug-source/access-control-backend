<?php

declare(strict_types=1);

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Uri;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('User show from api routes', function () {
    describe('fails because', function () {
        it('has no authentication', function () {
            $this->getJson(route('user.show', ['user' => 'whatever']))->assertUnauthorized();
        });
        it('has user no super-admin role authenticated', function () {
            User::factory(count: 1)->create();
            ['token' => $token] = authenticate(scope: $this);
            $this->getJson(route('user.show', ['user' => '1']), [
                'Authorization' => "Bearer $token",
            ])
                ->assertForbidden();
        });
    });
    describe('succeed because', function () {
        it('has user super-admin authenticated', function () {
            $userData = User::factory(count: 1)->createOne()->ui;
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $uri = Uri::of(route('user.show', ['user' => '1']))->withQuery([
                'page' => 1,
                'group' => 1
            ])->value();
            $this->getJson($uri, [
                'Authorization' => "Bearer $token"
            ])
                ->assertOk()
                ->assertJson(function (AssertableJson $json) use ($userData) {
                    $json
                        ->where('id', $userData['id'])
                        ->where('name', $userData['name'])
                        ->where('createdAt', $userData['createdAt'])
                        ->where('updatedAt', $userData['updatedAt'])
                        ->etc();
                });
        });
    });
});
