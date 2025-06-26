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
        it('updates the role*s abilities including and removing (no dependencies)', function () {
            $roleDummy = Role::factory(count: 1)->createOne();
            $abilityDummies = Ability::factory(count: 2)->create()->all();
            $roleDummy->abilities()->attach($abilityDummies[0]->id);

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);

            $this->assertDatabaseHas('ability_role', [
                'role_id' => $roleDummy->id,
                'ability_id' => $abilityDummies[0]->id,
            ]);
            $this->assertDatabaseMissing('ability_role', [
                'role_id' => $roleDummy->id,
                'ability_id' => $abilityDummies[1]->id,
            ]);

            $this->patchJson(route('role.ability.update', ['role' => $roleDummy->id]), [
                'Authorization' => "Bearer $token",
                'removed' => [$abilityDummies[0]->name],
                'included' => [$abilityDummies[1]->name],
            ])
                ->assertNoContent();

            $this->assertDatabaseHas('ability_role', [
                'role_id' => $roleDummy->id,
                'ability_id' => $abilityDummies[1]->id
            ]);
            $this->assertDatabaseMissing('ability_role', [
                'role_id' => $roleDummy->id,
                'ability_id' => $abilityDummies[0]->id
            ]);
        });
        it('updates the role*s abilities including and removing (with inclusion dependencies)', function () {
            $keyAbility = Ability::factory(count: 1)->createOne();
            $roles = Role::factory(4)->create()->all();
            $userDummies = collect([1, 2, 3])->map(function () use ($roles) {
                $user = createUserDB(fake()->email());
                collect(range(0, 3))->each(function ($index) use ($roles, $user) {
                    $roles[$index]->abilities()->attach(
                        Ability::factory()->createOne()->id
                    );
                    $user->roles()->attach($roles[$index]->id);
                });
                return $user;
            });
            $userDummies->each(
                function ($user) use ($keyAbility) {
                    $user->abilities()->attach($keyAbility->id, ['include' => TRUE]);
                }
            );
            $userDummies->each(
                function ($user) use ($keyAbility) {
                    $this->assertDatabaseHas('ability_user', [
                        'user_id' => $user->id,
                        'ability_id' => $keyAbility->id,
                        'include' => TRUE
                    ]);
                }
            );

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);

            $this->patchJson(route('role.ability.update', ['role' => $roles[0]->id]), [
                'Authorization' => "Bearer $token",
                'included' => [$keyAbility->name],
            ])
                ->assertNoContent();

            $userDummies->each(
                function ($user) use ($keyAbility) {
                    $this->assertDatabaseMissing('ability_user', [
                        'user_id' => $user->id,
                        'ability_id' => $keyAbility->id,
                    ]);
                }
            );
        });
        it('updates the role*s abilities including and removing (with remotion dependencies)', function () {
            $keyAbility = Ability::factory()->createOne();
            $roles = Role::factory(3)->create()->all();
            $roles[0]->abilities()->attach($keyAbility->id);
            $userDummies = collect([1, 2, 3])->map(function () use ($roles) {
                $user = createUserDB(fake()->email());
                $user->roles()->attach($roles[0]->id);
                collect(range(1, 2))->each(function ($index) use ($roles, $user) {
                    $roles[$index]->abilities()->attach(
                        Ability::factory()->createOne()->id
                    );
                    $user->roles()->attach($roles[$index]->id);
                });
                return $user;
            });
            $userDummies->each(
                fn($user) => $user->abilities()->attach($keyAbility->id, ['include' => FALSE])
            );
            $userDummies->each(
                fn($user) => $this->assertDatabaseHas('ability_user', [
                    'user_id' => $user->id,
                    'ability_id' => $keyAbility->id,
                    'include' => FALSE
                ])
            );

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);

            $this->patchJson(route('role.ability.update', ['role' => $roles[0]->id]), [
                'Authorization' => "Bearer $token",
                'removed' => [$keyAbility->name],
            ])
                ->assertNoContent();

            $userDummies->each(
                fn($user) => $this->assertDatabaseMissing('ability_user', [
                    'user_id' => $user->id,
                    'ability_id' => $keyAbility->id,
                ])
            );
        });
    });
});
