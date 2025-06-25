<?php

use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
use App\Models\Ability;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Role;
use App\Models\User;

uses(RefreshDatabase::class);

describe("User's role patch route request", function () {
    describe('fails because', function () {
        it('has no authentication', function () {
            $this->patchJson(route('user.role.update', ['user' => '1']))->assertUnauthorized();
        });
        it('has user no super-admin role authenticated', function () {
            User::factory(count: 1)->createOne();
            ['token' => $token] = authenticate(scope: $this);
            $this->patchJson(route('user.role.update', ['user' => '1']), [
                'Authorization' => "Bearer $token"
            ])
                ->assertForbidden();
        });
        it('has invalid user route parameter', function () {
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $this->patchJson(route('user.role.update', ['user' => fake()->word()]), [
                'Authorization' => "Bearer $token"
            ])
                ->assertMethodNotAllowed();
        });
        it('has user not persisted into database', function () {
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $this->patchJson(route('user.role.update', ['user' => '7']), [
                'Authorization' => "Bearer $token"
            ])
                ->assertNotFound();
        });
        it('has removed parameter as invalid array', function () {
            $userDummy = createUserDB(email: fake()->email(), password: fake()->password());
            $roleDummyOne = Role::factory(count: 1)->createOne();
            $userDummy->roles()->attach($roleDummyOne->id);

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            assertFailedResponse(
                response: $this->patchJson(route('user.role.update', ['user' => $userDummy->id]), [
                    'Authorization' => "Bearer $token",
                    'removed' => fake()->word()
                ]),
                errorKey: 'removed',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterInvalid)
            );
        });
        it('has invalid removed.0.exists parameter', function () {
            $userDummy = createUserDB(email: fake()->email(), password: fake()->password());
            $roleDummyOne = Role::factory(count: 1)->createOne();
            $userDummy->roles()->attach($roleDummyOne->id);

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            assertFailedResponse(
                response: $this->patchJson(route('user.role.update', ['user' => $userDummy->id]), [
                    'Authorization' => "Bearer $token",
                    'removed' => [fake()->word()]
                ]),
                errorKey: 'removed.0',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterInvalid)
            );
        });
        it('has prohibited removed parameter', function () {
            $userDummy = createUserDB(email: fake()->email(), password: fake()->password());
            $roleDummyOne = Role::factory(count: 1)->createOne();

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            assertFailedResponse(
                response: $this->patchJson(route('user.role.update', ['user' => $userDummy->id]), [
                    'Authorization' => "Bearer $token",
                    'removed' => [$roleDummyOne->name]
                ]),
                errorKey: 'removed.0',
                errorMsg: Phrase::pickSentence(PhraseKey::InvalidRoleRemotion)
            );
        });
        it('has removed parameter not attached', function () {
            $userDummy = createUserDB(email: fake()->email(), password: fake()->password());
            [$roleDummyOne, $roleDummyTwo] = Role::factory(count: 2)->create()->all();
            $userDummy->roles()->attach($roleDummyOne->id);

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            assertFailedResponse(
                response: $this->patchJson(route('user.role.update', ['user' => $userDummy->id]), [
                    'Authorization' => "Bearer $token",
                    'removed' => [$roleDummyTwo->name]
                ]),
                errorKey: 'removed.0',
                errorMsg: Phrase::pickSentence(PhraseKey::InvalidRoleRemotion)
            );
        });
        it('has included parameter as invalid array', function () {
            $userDummy = createUserDB(email: fake()->email(), password: fake()->password());
            $roleDummyOne = Role::factory(count: 1)->createOne();
            $userDummy->roles()->attach($roleDummyOne->id);

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            assertFailedResponse(
                response: $this->patchJson(route('user.role.update', ['user' => $userDummy->id]), [
                    'Authorization' => "Bearer $token",
                    'included' => fake()->word()
                ]),
                errorKey: 'included',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterInvalid)
            );
        });
        it('has invalid included.0.exists parameter', function () {
            $userDummy = createUserDB(email: fake()->email(), password: fake()->password());
            $roleDummyOne = Role::factory(count: 1)->createOne();
            $userDummy->roles()->attach($roleDummyOne->id);

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            assertFailedResponse(
                response: $this->patchJson(route('user.role.update', ['user' => $userDummy->id]), [
                    'Authorization' => "Bearer $token",
                    'included' => [fake()->word()]
                ]),
                errorKey: 'included.0',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterInvalid)
            );
        });
        it('has included parameter already attached', function () {
            $userDummy = createUserDB(email: fake()->email(), password: fake()->password());
            $roleDummyOne = Role::factory(count: 1)->createOne();
            $userDummy->roles()->attach($roleDummyOne->id);

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            assertFailedResponse(
                response: $this->patchJson(route('user.role.update', ['user' => $userDummy->id]), [
                    'Authorization' => "Bearer $token",
                    'included' => [$roleDummyOne->name]
                ]),
                errorKey: 'included.0',
                errorMsg: Phrase::pickSentence(PhraseKey::InvalidRoleInclusion)
            );
        });
    });
    describe('succeed because', function () {
        it('updates the user*s roles including and removing (no dependencies)', function () {
            $userDummy = createUserDB(email: fake()->email(), password: fake()->password());
            [$roleDummyOne, $roleDummyTwo] = Role::factory(count: 2)->create()->all();
            $userDummy->roles()->attach($roleDummyOne->id);

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);

            $this->assertDatabaseHas('role_user', [
                'user_id' => $userDummy->id,
                'role_id' => $roleDummyOne->id,
            ]);
            $this->assertDatabaseMissing('role_user', [
                'user_id' => $userDummy->id,
                'role_id' => $roleDummyTwo->id,
            ]);

            $this->patchJson(route('user.role.update', ['user' => $userDummy->id]), [
                'Authorization' => "Bearer $token",
                'removed' => [$roleDummyOne->name],
                'included' => [$roleDummyTwo->name],
            ])
                ->assertNoContent();

            $this->assertDatabaseHas('role_user', [
                'user_id' => $userDummy->id,
                'role_id' => $roleDummyTwo->id
            ]);
            $this->assertDatabaseMissing('role_user', [
                'user_id' => $userDummy->id,
                'role_id' => $roleDummyOne->id
            ]);
        });
        it('updates the user*s roles including and removing (with inclusion dependencies)', function () {
            $userDummy = createUserDB(email: fake()->email(), password: fake()->password());
            [$roleDummyOne, $roleDummyTwo, $roleDummyThree] = Role::factory(count: 3)->create()->all();
            $abilities = Ability::factory(count: 5)->create()->all();
            $roleDummyOne->abilities()->attach($abilities[0]->id);
            $roleDummyTwo->abilities()->attach([
                $abilities[1]->id,
                $abilities[3]->id
            ]);
            $roleDummyThree->abilities()->attach($abilities[4]->id);

            $userDummy->roles()->attach($roleDummyOne->id);
            $userDummy->abilities()->attach($abilities[1]->id);
            $userDummy->abilities()->attach($abilities[2]->id);
            $userDummy->abilities()->attach($abilities[3]->id);
            $userDummy->abilities()->attach($abilities[4]->id);

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);

            collect(range(1, 4))->each(fn($index) => $this->assertDatabaseHas('ability_user', [
                'user_id' => $userDummy->id,
                'ability_id' => $abilities[$index]->id,
                'include' => TRUE
            ]));

            $this->patchJson(route('user.role.update', ['user' => $userDummy->id]), [
                'Authorization' => "Bearer $token",
                'included' => [$roleDummyTwo->name, $roleDummyThree->name],
            ])
                ->assertNoContent();

            collect([1, 3])->each(fn($index) => $this->assertDatabaseMissing('ability_user', [
                'user_id' => $userDummy->id,
                'ability_id' => $abilities[$index]->id,
            ]));
        });
    });
});
