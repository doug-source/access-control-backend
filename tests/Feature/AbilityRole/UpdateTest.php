<?php

use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
use App\Models\Ability;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Role;

uses(RefreshDatabase::class);

describe("Role's abilities patch route request", function () {
    describe('fails because', function () {
        it('has no authentication', function () {
            $this->patchJson(route('role.ability.update', ['role' => '1']))->assertUnauthorized();
        });
        it('has user no super-admin role authenticated', function () {
            Role::factory(count: 1)->createOne();
            ['token' => $token] = authenticate(scope: $this);
            $this->patchJson(route('role.ability.update', ['role' => '1']), [
                'Authorization' => "Bearer $token"
            ])
                ->assertForbidden();
        });
        it('has invalid user route parameter', function () {
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $this->patchJson(route('role.ability.update', ['role' => fake()->word()]), [
                'Authorization' => "Bearer $token"
            ])
                ->assertMethodNotAllowed();
        });
        it('has user not persisted into database', function () {
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $this->patchJson(route('role.ability.update', ['role' => '7']), [
                'Authorization' => "Bearer $token"
            ])
                ->assertNotFound();
        });
        it('has removed parameter as invalid array', function () {
            $roleDummy = Role::factory(count: 1)->createOne();
            $abilityDummyOne = Ability::factory(count: 1)->createOne();
            $roleDummy->abilities()->attach($abilityDummyOne->id);

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            assertFailedResponse(
                response: $this->patchJson(route('role.ability.update', ['role' => $roleDummy->id]), [
                    'Authorization' => "Bearer $token",
                    'removed' => fake()->word()
                ]),
                errorKey: 'removed',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterInvalid)
            );
        });
        it('has invalid removed.0.exists parameter', function () {
            $roleDummy = Role::factory(count: 1)->createOne();
            $abilityDummyOne = Ability::factory(count: 1)->createOne();
            $roleDummy->abilities()->attach($abilityDummyOne->id);

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            assertFailedResponse(
                response: $this->patchJson(route('role.ability.update', ['role' => $roleDummy->id]), [
                    'Authorization' => "Bearer $token",
                    'removed' => [fake()->word()]
                ]),
                errorKey: 'removed.0',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterInvalid)
            );
        });
        it('has prohibited removed parameter', function () {
            $roleDummy = Role::factory(count: 1)->createOne();
            $abilityDummyOne = Ability::factory(count: 1)->createOne();

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            assertFailedResponse(
                response: $this->patchJson(route('role.ability.update', ['role' => $roleDummy->id]), [
                    'Authorization' => "Bearer $token",
                    'removed' => [$abilityDummyOne->name]
                ]),
                errorKey: 'removed.0',
                errorMsg: Phrase::pickSentence(PhraseKey::InvalidRoleRemotion)
            );
        });
        it('has removed parameter not attached', function () {
            $roleDummy = Role::factory(count: 1)->createOne();
            [$abilityDummyOne, $abilityDummyTwo] = Ability::factory(count: 2)->create()->all();
            $roleDummy->abilities()->attach($abilityDummyOne->id);

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            assertFailedResponse(
                response: $this->patchJson(route('role.ability.update', ['role' => $roleDummy->id]), [
                    'Authorization' => "Bearer $token",
                    'removed' => [$abilityDummyTwo->name]
                ]),
                errorKey: 'removed.0',
                errorMsg: Phrase::pickSentence(PhraseKey::InvalidRoleRemotion)
            );
        });
        it('has included parameter as invalid array', function () {
            $roleDummy = Role::factory(count: 1)->createOne();
            $abilityDummyOne = Ability::factory(count: 1)->createOne();
            $roleDummy->abilities()->attach($abilityDummyOne->id);

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            assertFailedResponse(
                response: $this->patchJson(route('role.ability.update', ['role' => $roleDummy->id]), [
                    'Authorization' => "Bearer $token",
                    'included' => fake()->word()
                ]),
                errorKey: 'included',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterInvalid)
            );
        });
        it('has invalid included.0.exists parameter', function () {
            $roleDummy = Role::factory(count: 1)->createOne();
            $abilityDummyOne = Ability::factory(count: 1)->createOne();
            $roleDummy->abilities()->attach($abilityDummyOne->id);

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            assertFailedResponse(
                response: $this->patchJson(route('role.ability.update', ['role' => $roleDummy->id]), [
                    'Authorization' => "Bearer $token",
                    'included' => [fake()->word()]
                ]),
                errorKey: 'included.0',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterInvalid)
            );
        });
        it('has included parameter already attached', function () {
            $roleDummy = Role::factory(count: 1)->createOne();
            $abilityDummyOne = Ability::factory(count: 1)->createOne();
            $roleDummy->abilities()->attach($abilityDummyOne->id);

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            assertFailedResponse(
                response: $this->patchJson(route('role.ability.update', ['role' => $roleDummy->id]), [
                    'Authorization' => "Bearer $token",
                    'included' => [$abilityDummyOne->name]
                ]),
                errorKey: 'included.0',
                errorMsg: Phrase::pickSentence(PhraseKey::InvalidRoleInclusion)
            );
        });
    });
    describe('succeed because', function () {
        it('has user super-admin authenticated', function () {
            $roleDummy = Role::factory(count: 1)->createOne();
            [$abilityDummyOne, $abilityDummyTwo] = Ability::factory(count: 2)->create()->all();
            $roleDummy->abilities()->attach($abilityDummyOne->id);

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);

            $this->assertDatabaseHas('ability_role', [
                'role_id' => $roleDummy->id,
                'ability_id' => $abilityDummyOne->id,
            ]);
            $this->assertDatabaseMissing('ability_role', [
                'role_id' => $roleDummy->id,
                'ability_id' => $abilityDummyTwo->id,
            ]);

            $this->patchJson(route('role.ability.update', ['role' => $roleDummy->id]), [
                'Authorization' => "Bearer $token",
                'removed' => [$abilityDummyOne->name],
                'included' => [$abilityDummyTwo->name],
            ])
                ->assertNoContent();

            $this->assertDatabaseHas('ability_role', [
                'role_id' => $roleDummy->id,
                'ability_id' => $abilityDummyTwo->id
            ]);
            $this->assertDatabaseMissing('ability_role', [
                'role_id' => $roleDummy->id,
                'ability_id' => $abilityDummyOne->id
            ]);
        });
    });
});
