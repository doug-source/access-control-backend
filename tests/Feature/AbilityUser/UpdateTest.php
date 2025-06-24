<?php

use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
use App\Models\Ability;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Role;

uses(RefreshDatabase::class);

describe("User's abilities patch route request", function () {
    describe('fails because', function () {
        it('has no authentication', function () {
            $this->patchJson(route('user.ability.update', ['user' => '1']))->assertUnauthorized();
        });
        it('has user no super-admin role authenticated', function () {
            Role::factory(count: 1)->createOne();
            ['token' => $token] = authenticate(scope: $this);
            $this->patchJson(route('user.ability.update', ['user' => '1']), [
                'Authorization' => "Bearer $token"
            ])
                ->assertForbidden();
        });
        it('has invalid user route parameter', function () {
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $this->patchJson(route('user.ability.update', ['user' => fake()->word()]), [
                'Authorization' => "Bearer $token"
            ])
                ->assertMethodNotAllowed();
        });
        it('has user not persisted into database', function () {
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $this->patchJson(route('user.ability.update', ['user' => '7']), [
                'Authorization' => "Bearer $token"
            ])
                ->assertNotFound();
        });
        it('has removed parameter as invalid array', function () {
            $userDummy = createUserDB(fake()->email());
            $abilityDummyOne = Ability::factory(count: 1)->createOne();
            $userDummy->abilities()->attach($abilityDummyOne->id);

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            assertFailedResponse(
                response: $this->patchJson(route('user.ability.update', ['user' => $userDummy->id]), [
                    'Authorization' => "Bearer $token",
                    'removed' => fake()->word()
                ]),
                errorKey: 'removed',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterInvalid)
            );
        });
        it('has invalid removed.0.exists parameter', function () {
            $userDummy = createUserDB(fake()->email());
            $abilityDummyOne = Ability::factory(count: 1)->createOne();
            $userDummy->abilities()->attach($abilityDummyOne->id);

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            assertFailedResponse(
                response: $this->patchJson(route('user.ability.update', ['user' => $userDummy->id]), [
                    'Authorization' => "Bearer $token",
                    'removed' => [fake()->word()]
                ]),
                errorKey: 'removed.0',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterInvalid)
            );
        });
        it('has removed parameter already removed', function () {
            $userDummy = createUserDB(fake()->email());
            $abilityDummyOne = Ability::factory(count: 1)->createOne();
            $userDummy->abilities()->attach($abilityDummyOne->id, ['include' => FALSE]);

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            assertFailedResponse(
                response: $this->patchJson(route('user.ability.update', ['user' => $userDummy->id]), [
                    'Authorization' => "Bearer $token",
                    'removed' => [$abilityDummyOne->name]
                ]),
                errorKey: 'removed.0',
                errorMsg: Phrase::pickSentence(PhraseKey::InvalidAbilityRemotion)
            );
        });
        it('has prohibited removed parameter', function () {
            $userDummy = createUserDB(fake()->email());
            $abilityDummyOne = Ability::factory(count: 1)->createOne();

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            assertFailedResponse(
                response: $this->patchJson(route('user.ability.update', ['user' => $userDummy->id]), [
                    'Authorization' => "Bearer $token",
                    'removed' => [$abilityDummyOne->name]
                ]),
                errorKey: 'removed.0',
                errorMsg: Phrase::pickSentence(PhraseKey::InvalidAbilityRemotion)
            );
        });
        it('has removed parameter not attached', function () {
            $userDummy = createUserDB(fake()->email());
            [$abilityDummyOne, $abilityDummyTwo] = Ability::factory(count: 2)->create()->all();
            $userDummy->abilities()->attach($abilityDummyOne->id);

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            assertFailedResponse(
                response: $this->patchJson(route('user.ability.update', ['user' => $userDummy->id]), [
                    'Authorization' => "Bearer $token",
                    'removed' => [$abilityDummyTwo->name]
                ]),
                errorKey: 'removed.0',
                errorMsg: Phrase::pickSentence(PhraseKey::InvalidAbilityRemotion)
            );
        });
        it('has removed parameter not present in included ability', function () {
            $userDummy = createUserDB(fake()->email());
            $abilityDummyFirst = Ability::factory(count: 1)->createOne();
            $userDummy->abilities()->attach(
                Ability::factory(count: 1)->createOne()->id,
                ['include' => TRUE]
            );

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            assertFailedResponse(
                response: $this->patchJson(route('user.ability.update', ['user' => $userDummy->id]), [
                    'Authorization' => "Bearer $token",
                    'removed' => [$abilityDummyFirst->name]
                ]),
                errorKey: 'removed.0',
                errorMsg: Phrase::pickSentence(PhraseKey::InvalidAbilityRemotion)
            );
        });
        it("has removed parameter not present in abilities from user's role", function () {
            $userDummy = createUserDB(fake()->email());
            $abilityDummyFirst = Ability::factory(count: 1)->createOne();
            $roleDummy = Role::factory(count: 1)->createOne();
            $roleDummy->abilities()->attach($abilityDummyFirst->id);
            $userDummy->roles()->attach($roleDummy->id);

            $abilityDummySecond = Ability::factory(count: 1)->createOne();

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            assertFailedResponse(
                response: $this->patchJson(route('user.ability.update', ['user' => $userDummy->id]), [
                    'Authorization' => "Bearer $token",
                    'removed' => [$abilityDummySecond->name]
                ]),
                errorKey: 'removed.0',
                errorMsg: Phrase::pickSentence(PhraseKey::InvalidAbilityRemotion)
            );
        });
        it('has some removed parameter and included parameter equal', function () {
            $userDummy = createUserDB(fake()->email());
            $abilityDummyFirst = Ability::factory(count: 1)->createOne();
            $roleDummy = Role::factory(count: 1)->createOne();
            $roleDummy->abilities()->attach($abilityDummyFirst->id);
            $userDummy->roles()->attach($roleDummy->id);

            $abilityDummySecond = Ability::factory(count: 1)->createOne();
            $userDummy->abilities()->attach($abilityDummySecond->id, ['include' => TRUE]);

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            assertFailedResponse(
                response: $this->patchJson(route('user.ability.update', ['user' => $userDummy->id]), [
                    'Authorization' => "Bearer $token",
                    'removed' => [$abilityDummyFirst->name, $abilityDummySecond->name],
                    'included' => [$abilityDummyFirst->name]
                ]),
                errorKey: 'removed.0',
                errorMsg: Phrase::pickSentence(PhraseKey::InvalidAbilityRemotion)
            );
        });

        it('has included parameter as invalid array', function () {
            $userDummy = createUserDB(fake()->email());
            $abilityDummyOne = Ability::factory(count: 1)->createOne();
            $userDummy->abilities()->attach($abilityDummyOne->id);

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            assertFailedResponse(
                response: $this->patchJson(route('user.ability.update', ['user' => $userDummy->id]), [
                    'Authorization' => "Bearer $token",
                    'included' => fake()->word()
                ]),
                errorKey: 'included',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterInvalid)
            );
        });
        it('has invalid included.0.exists parameter', function () {
            $userDummy = createUserDB(fake()->email());
            $abilityDummyOne = Ability::factory(count: 1)->createOne();
            $userDummy->abilities()->attach($abilityDummyOne->id);

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            assertFailedResponse(
                response: $this->patchJson(route('user.ability.update', ['user' => $userDummy->id]), [
                    'Authorization' => "Bearer $token",
                    'included' => [fake()->word()]
                ]),
                errorKey: 'included.0',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterInvalid)
            );
        });
        it('has included parameter already attached', function () {
            $userDummy = createUserDB(fake()->email());
            $abilityDummyOne = Ability::factory(count: 1)->createOne();
            $userDummy->abilities()->attach($abilityDummyOne->id);

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            assertFailedResponse(
                response: $this->patchJson(route('user.ability.update', ['user' => $userDummy->id]), [
                    'Authorization' => "Bearer $token",
                    'included' => [$abilityDummyOne->name]
                ]),
                errorKey: 'included.0',
                errorMsg: Phrase::pickSentence(PhraseKey::InvalidAbilityInclusion)
            );
        });
        it("has included parameter present in abilities from user's role", function () {
            $userDummy = createUserDB(fake()->email());
            $abilityDummyFirst = Ability::factory(count: 1)->createOne();
            $roleDummy = Role::factory(count: 1)->createOne();
            $roleDummy->abilities()->attach($abilityDummyFirst->id);
            $userDummy->roles()->attach($roleDummy->id);

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            assertFailedResponse(
                response: $this->patchJson(route('user.ability.update', ['user' => $userDummy->id]), [
                    'Authorization' => "Bearer $token",
                    'included' => [$abilityDummyFirst->name]
                ]),
                errorKey: 'included.0',
                errorMsg: Phrase::pickSentence(PhraseKey::InvalidAbilityInclusion)
            );
        });
        it("has included parameter present in abilities from user's role but not in already removed abilities", function () {
            $userDummy = createUserDB(fake()->email());
            $abilityDummyFirst = Ability::factory()->createOne();
            $abilityDummySecond = Ability::factory()->createOne();
            $roleDummy = Role::factory(count: 1)->createOne();
            $roleDummy->abilities()->attach([$abilityDummyFirst->id, $abilityDummySecond->id]);
            $userDummy->roles()->attach($roleDummy->id);

            $userDummy->abilities()->attach($abilityDummyFirst->id, ['include' => FALSE]);

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            assertFailedResponse(
                response: $this->patchJson(route('user.ability.update', ['user' => $userDummy->id]), [
                    'Authorization' => "Bearer $token",
                    'included' => [$abilityDummySecond->name]
                ]),
                errorKey: 'included.0',
                errorMsg: Phrase::pickSentence(PhraseKey::InvalidAbilityInclusion)
            );
        });
    });
    describe('succeed because', function () {
        it("has removed parameter hidding a role's ability", function () {
            $userDummy = createUserDB(fake()->email());
            $ability = Ability::factory()->createOne();
            $roleDummy = Role::factory()->createOne();
            $roleDummy->abilities()->attach($ability->id);
            $userDummy->roles()->attach($roleDummy->id);

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);

            $this->assertDatabaseMissing('ability_user', [
                'user_id' => $userDummy->id,
            ]);

            $this->patchJson(route('user.ability.update', ['user' => $userDummy->id]), [
                'Authorization' => "Bearer $token",
                'removed' => [$ability->name],
            ])
                ->assertNoContent();

            $this->assertDatabaseHas('ability_user', [
                'ability_id' => $ability->id,
                'user_id' => $userDummy->id,
                'include' => FALSE,
            ]);
        });
        it("has removed parameter hidding a user's ability already included", function () {
            $userDummy = createUserDB(fake()->email());
            $ability = Ability::factory()->createOne();
            $userDummy->abilities()->attach($ability->id, ['include' => TRUE]);

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);

            $this->assertDatabaseHas('ability_user', [
                'ability_id' => $ability->id,
                'user_id' => $userDummy->id,
                'include' => TRUE,
            ]);

            $this->patchJson(route('user.ability.update', ['user' => $userDummy->id]), [
                'Authorization' => "Bearer $token",
                'removed' => [$ability->name],
            ])
                ->assertNoContent();

            $this->assertDatabaseMissing('ability_user', [
                'ability_id' => $ability->id,
                'user_id' => $userDummy->id,
                'include' => TRUE,
            ]);
        });
        it("has included parameter showing a new ability", function () {
            $userDummy = createUserDB(fake()->email());
            $ability = Ability::factory()->createOne();

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);

            $this->assertDatabaseMissing('ability_user', [
                'user_id' => $userDummy->id,
            ]);

            $this->patchJson(route('user.ability.update', ['user' => $userDummy->id]), [
                'Authorization' => "Bearer $token",
                'included' => [$ability->name],
            ])
                ->assertNoContent();

            $this->assertDatabaseHas('ability_user', [
                'ability_id' => $ability->id,
                'user_id' => $userDummy->id,
                'include' => TRUE,
            ]);
        });
        it("has included parameter showing a ability already hidden", function () {
            $userDummy = createUserDB(fake()->email());
            $ability = Ability::factory()->createOne();
            [$abilityOne, $abilityTwo] = Ability::factory(count: 2)->create()->all();
            $roleDummy = Role::factory()->createOne();
            $roleDummy->abilities()->attach($abilityOne->id);
            $userDummy->roles()->attach($roleDummy->id);
            $userDummy->abilities()->attach($abilityTwo->id);

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);

            $this->assertDatabaseHas('role_user', [
                'role_id' => $roleDummy->id,
                'user_id' => $userDummy->id,
            ]);
            $this->assertDatabaseHas('ability_role', [
                'role_id' => $roleDummy->id,
                'ability_id' => $abilityOne->id,
            ]);
            $this->assertDatabaseMissing('ability_user', [
                'user_id' => $userDummy->id,
                'ability_id' => $abilityOne->id,
            ]);
            $this->assertDatabaseHas('ability_user', [
                'user_id' => $userDummy->id,
                'ability_id' => $abilityTwo->id,
                'include' => TRUE
            ]);
            $this->patchJson(route('user.ability.update', ['user' => $userDummy->id]), [
                'Authorization' => "Bearer $token",
                'removed' => [$abilityTwo->name],
            ])
                ->assertNoContent();

            $this->assertDatabaseHas('role_user', [
                'role_id' => $roleDummy->id,
                'user_id' => $userDummy->id,
            ]);
            $this->assertDatabaseHas('ability_role', [
                'role_id' => $roleDummy->id,
                'ability_id' => $abilityOne->id,
            ]);
            $this->assertDatabaseMissing('ability_user', [
                'ability_id' => $ability->id,
                'user_id' => $abilityTwo->id,
            ]);
        });
        it('has included and removed parameter happening at the same time', function () {
            $userDummy = createUserDB(fake()->email());
            $abilitiesColl = Ability::factory(count: 4)->createMany();

            $roleDummy = Role::factory(count: 1)->createOne();
            $roleDummy->abilities()->attach(collect([0, 1, 2])->map(fn($i) => $abilitiesColl->get($i)->id));
            $userDummy->roles()->attach($roleDummy->id);
            $userDummy->abilities()->attach($abilitiesColl->get(0)->id, ['include' => FALSE]); // 1

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);

            $this->assertDatabaseHas('ability_user', [
                'ability_id' => $abilitiesColl->get(0)->id,
                'user_id' => $userDummy->id,
                'include' => FALSE
            ]);
            collect([1, 2, 3])->each(
                fn($i) => $this->assertDatabaseMissing('ability_user', [
                    'ability_id' => $abilitiesColl->get($i)->id,
                    'user_id' => $userDummy->id,
                ])
            );

            $this->patchJson(route('user.ability.update', ['user' => $userDummy->id]), [
                'Authorization' => "Bearer $token",
                'removed' => [$abilitiesColl->get(2)->name], // 3
                'included' => [$abilitiesColl->get(0)->name, $abilitiesColl->get(3)->name], // 1 - 4
            ])
                ->assertNoContent();

            collect([0, 1])->each(
                fn($i) => $this->assertDatabaseMissing('ability_user', [
                    'ability_id' => $abilitiesColl->get($i)->id,
                    'user_id' => $userDummy->id,
                ])
            );
            $this->assertDatabaseHas('ability_user', [
                'ability_id' => $abilitiesColl->get(2)->id,
                'user_id' => $userDummy->id,
                'include' => FALSE,
            ]);
            $this->assertDatabaseHas('ability_user', [
                'ability_id' => $abilitiesColl->get(3)->id,
                'user_id' => $userDummy->id,
                'include' => TRUE,
            ]);
        });
    });
});
