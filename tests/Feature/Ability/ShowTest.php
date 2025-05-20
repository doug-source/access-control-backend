<?php

declare(strict_types=1);

use App\Models\Ability;
use Illuminate\Support\Uri;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Abilities from api routes', function () {
    describe('fails because', function () {
        it('has no authentication', function () {
            $this->getJson(route('ability.show', ['ability' => 'whatever']))->assertUnauthorized();
        });
        it('has user no super-admin role authenticated', function () {
            Ability::factory(count: 1)->create();
            ['token' => $token] = authenticate(scope: $this);
            $this->getJson(route('ability.show', ['ability' => '1']), [
                'Authorization' => "Bearer $token"
            ])
                ->assertForbidden();
        });
    });
    describe('succeed because', function () {
        it('has user super-admin authenticated', function () {
            $abilityData = Ability::factory(count: 1)->createOne()->ui;
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $uri = Uri::of(route('ability.show', ['ability' => '1']))->withQuery([
                'page' => 1,
                'group' => 1
            ])->value();
            $this->getJson($uri, [
                'Authorization' => "Bearer $token"
            ])
                ->assertOk()
                ->assertJson(function (AssertableJson $json) use ($abilityData) {
                    $json
                        ->where('id', $abilityData['id'])
                        ->where('name', $abilityData['name'])
                        ->where('createdAt', $abilityData['createdAt'])
                        ->where('updatedAt', $abilityData['updatedAt'])
                        ->etc();
                });
        });
    });
});
