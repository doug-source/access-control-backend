<?php

declare(strict_types=1);

use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
use App\Models\Ability;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Role destroy request', function () {
    describe('fails because', function () {
        it('has no authentication', function () {
            $response = $this->deleteJson(route('role.destroy', ['role' => 1]));
            $response->assertUnauthorized();
        });
        it('has not found role into database', function () {
            ['token' => $token] = authenticate(scope: $this);
            $this->deleteJson(route('role.destroy', ['role' => '1']), [
                'Authorization' => "Bearer $token",
            ])
                ->assertNotFound();
        });
        it('has invalid role route parameter', function () {
            ['token' => $token] = authenticate(scope: $this);
            $this->deleteJson(route('role.destroy', ['role' => 'whatever']), [
                'Authorization' => "Bearer $token",
            ])
                ->assertNotFound();
        });
        it('has user no super-admin role authenticated', function () {
            Role::factory(count: 1)->create();
            ['token' => $token] = authenticate(scope: $this);
            $this->deleteJson(route('role.destroy', ['role' => '1']), [
                'Authorization' => "Bearer $token",
            ])
                ->assertForbidden();
        });
        it('has role linked with ability into database', function () {
            $ability = Ability::factory(count: 1)->createOne();
            $role = Role::factory(count: 1)->createOne();
            $role->abilities()->attach($ability->id);

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);

            assertFailedResponse(
                response: $this->deleteJson(route('role.destroy', ['role' => '1']), [
                    'Authorization' => "Bearer $token",
                ]),
                errorKey: 'role',
                errorMsg: Phrase::pickSentence(PhraseKey::LinkNotAllowed, ' (Ability)')
            );
        });
        it('has role linked with user into database', function () {
            $role = Role::factory(count: 1)->createOne();

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);

            $role->users()->attach($user->id);

            assertFailedResponse(
                response: $this->deleteJson(route('role.destroy', ['role' => '1']), [
                    'Authorization' => "Bearer $token",
                ]),
                errorKey: 'role',
                errorMsg: Phrase::pickSentence(PhraseKey::LinkNotAllowed, ' (User)')
            );
        });
    });
    describe('succeed because', function () {
        it('removes the role instance from database correctly', function () {
            Role::factory(count: 1)->create();

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);

            $this->deleteJson(route('role.destroy', ['role' => '1']), [
                'Authorization' => "Bearer $token",
            ])
                ->assertOk();
        });
    });
});
