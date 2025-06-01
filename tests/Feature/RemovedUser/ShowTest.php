<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Uri;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('User soft-deleted show from api routes', function () {
    describe('fails because', function () {
        it('has no authentication', function () {
            $this->getJson(route('user.removed.show', ['user' => '1']))->assertUnauthorized();
        });
        it('has user no super-admin role authenticated', function () {
            User::factory(count: 1)->create([
                'deleted_at' => now()
            ]);
            ['token' => $token] = authenticate(scope: $this);
            $this->getJson(route('user.removed.show', ['user' => '1']), [
                'Authorization' => "Bearer $token",
            ])
                ->assertForbidden();
        });
        it('has not found id route parameter', function () {
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $uri = Uri::of(route('user.removed.show', ['user' => '1']))->withQuery([
                'page' => 1,
                'group' => 1
            ])->value();
            $this->getJson($uri, [
                'Authorization' => "Bearer $token"
            ])
                ->assertNotFound();
        });
        it('has invalid id route parameter', function () {
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $uri = Uri::of(route('user.removed.show', ['user' => 'whatever']))->withQuery([
                'page' => 1,
                'group' => 1
            ])->value();
            $this->getJson($uri, [
                'Authorization' => "Bearer $token"
            ])
                ->assertNotFound();
        });
    });
    describe('succeed because', function () {
        it('has user super-admin authenticated', function () {
            $userData = User::factory(count: 1)->createOne([
                'deleted_at' => now()
            ])->ui;
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $uri = Uri::of(route('user.removed.show', ['user' => '1']))->withQuery([
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
