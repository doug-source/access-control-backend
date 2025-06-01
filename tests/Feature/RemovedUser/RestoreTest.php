<?php

declare(strict_types=1);

use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('User soft-deleted restore request', function () {
    describe('fails because', function () {
        it('has no authentication', function () {
            $response = $this->postJson(route('user.restore'));
            $response->assertUnauthorized();
        });
        it('has no id parameter', function () {
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            assertFailedResponse(
                response: $this->postJson(route('user.restore'), [
                    'Authorization' => "Bearer $token",
                ]),
                errorKey: 'id',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterRequired),
            );
        });
        it('has not found user into database', function () {
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            assertFailedResponse(
                response: $this->postJson(route('user.restore'), [
                    'Authorization' => "Bearer $token",
                    'id' => '5'
                ]),
                errorKey: 'id',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterInvalid),
            );
        });
        it('has invalid user route parameter', function () {
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            assertFailedResponse(
                response: $this->postJson(route('user.restore'), [
                    'Authorization' => "Bearer $token",
                    'id' => 'whatever'
                ]),
                errorKey: 'id',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterInvalid),
            );
        });
        it('has user no super-admin user authenticated', function () {
            $user = User::factory(count: 1)->createOne([
                'deleted_at' => now()
            ]);
            User::factory(count: 1)->create();
            ['token' => $token] = authenticate(scope: $this);
            $this->postJson(route('user.restore'), [
                'Authorization' => "Bearer $token",
                'id' => $user->id,
            ])
                ->assertForbidden();
        });
        it('has user route parameter not previously soft-deleted', function () {
            $userToDelete = User::factory(count: 1)->createOne();
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);

            $this->postJson(route('user.restore'), [
                'Authorization' => "Bearer $token",
                'id' => $userToDelete->id,
            ])
                ->assertNotFound();
        });
    });
    describe('succeed because', function () {
        it('restores the user instance into database correctly', function () {
            $userToDelete = User::factory(count: 1)->createOne([
                'deleted_at' => now()
            ]);

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);

            $this->assertDatabaseHas('users', [
                'id' => $userToDelete->id,
                'deleted_at' => $userToDelete->deleted_at
            ]);
            $this->postJson(route('user.restore'), [
                'Authorization' => "Bearer $token",
                'id' => $userToDelete->id,
            ])
                ->assertOk();

            $this->assertDatabaseHas('users', [
                'id' => $userToDelete->id,
                'deleted_at' => NULL
            ]);
        });
    });
});
