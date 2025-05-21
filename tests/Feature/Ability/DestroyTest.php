<?php

declare(strict_types=1);

use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
use App\Models\Ability;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Ability destroy request', function () {
    describe('fails because', function () {
        it('has no authentication', function () {
            $response = $this->deleteJson(route('ability.destroy', ['ability' => 1]));
            $response->assertUnauthorized();
        });
        it('has not found ability into database', function () {
            ['token' => $token] = authenticate(scope: $this);
            $this->deleteJson(route('ability.destroy', ['ability' => '1']), [
                'Authorization' => "Bearer $token",
            ])
                ->assertNotFound();
        });
        it('has invalid ability route parameter', function () {
            ['token' => $token] = authenticate(scope: $this);
            $this->deleteJson(route('ability.destroy', ['ability' => 'whatever']), [
                'Authorization' => "Bearer $token",
            ])
                ->assertNotFound();
        });
        it('has user no super-admin role authenticated', function () {
            Ability::factory(count: 1)->create();
            ['token' => $token] = authenticate(scope: $this);
            $this->deleteJson(route('ability.destroy', ['ability' => '1']), [
                'Authorization' => "Bearer $token",
            ])
                ->assertForbidden();
        });
        it('has ability linked with role into database', function () {
            $ability = Ability::factory(count: 1)->createOne();
            $role = Role::factory(count: 1)->createOne();
            $role->abilities()->attach($ability->id);

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);

            assertFailedResponse(
                response: $this->deleteJson(route('ability.destroy', ['ability' => '1']), [
                    'Authorization' => "Bearer $token",
                ]),
                errorKey: 'ability',
                errorMsg: Phrase::pickSentence(PhraseKey::LinkNotAllowed, ' (Role)')
            );
        });
        it('has ability linked with user into database', function () {
            $ability = Ability::factory(count: 1)->createOne();

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);

            $ability->users()->attach($user->id);

            assertFailedResponse(
                response: $this->deleteJson(route('ability.destroy', ['ability' => '1']), [
                    'Authorization' => "Bearer $token",
                ]),
                errorKey: 'ability',
                errorMsg: Phrase::pickSentence(PhraseKey::LinkNotAllowed, ' (User)')
            );
        });
    });
    describe('succeed because', function () {
        it('removes the ability instance from database correctly', function () {
            Ability::factory(count: 1)->create();

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);

            $this->deleteJson(route('ability.destroy', ['ability' => '1']), [
                'Authorization' => "Bearer $token",
            ])
                ->assertOk();
        });
    });
});
