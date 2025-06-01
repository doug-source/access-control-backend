<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('User soft-deleted destroy request', function () {
    describe('fails because', function () {
        it('has no authentication', function () {
            $response = $this->deleteJson(route('user.removed.destroy', ['user' => 1]));
            $response->assertUnauthorized();
        });
        it('has not found user into database', function () {
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $this->deleteJson(route('user.removed.destroy', ['user' => '5']), [
                'Authorization' => "Bearer $token",
            ])
                ->assertNotFound();
        });
        it('has invalid user route parameter', function () {
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $this->deleteJson(route('user.removed.destroy', ['user' => 'whatever']), [
                'Authorization' => "Bearer $token",
            ])
                ->assertMethodNotAllowed();
        });
        it('has user no super-admin user authenticated', function () {
            User::factory(count: 1)->createOne([
                'deleted_at' => now()
            ]);
            User::factory(count: 1)->create();
            ['token' => $token] = authenticate(scope: $this);
            $this->deleteJson(route('user.removed.destroy', ['user' => '1']), [
                'Authorization' => "Bearer $token",
            ])
                ->assertForbidden();
        });
        it('has user route parameter not previously soft-deleted', function () {
            $userToDelete = User::factory(count: 1)->createOne();
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);

            $this->deleteJson(route('user.removed.destroy', ['user' => $userToDelete->id]), [
                'Authorization' => "Bearer $token",
            ])
                ->assertNotFound();
        });
    });
    describe('succeed because', function () {
        it('removes the user instance from database correctly', function () {
            $userToDelete = User::factory(count: 1)->createOne([
                'deleted_at' => now()
            ]);

            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);

            $this->deleteJson(route('user.removed.destroy', ['user' => $userToDelete->id]), [
                'Authorization' => "Bearer $token",
            ])
                ->assertOk();

            $this->assertDatabaseMissing('users', [
                'id' => $userToDelete->id,
            ]);
        });
    });
});
