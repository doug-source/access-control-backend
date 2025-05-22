<?php

declare(strict_types=1);

use App\Library\Builders\Phrase;
use App\Library\Enums\ColumnSize\RoleSize;
use App\Library\Enums\PhraseKey;
use App\Models\Role;
use App\Repositories\RoleRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Abilities from api routes', function () {
    describe('fails because', function () {
        it('has no authentication', function () {
            $this->patchJson(route('role.update', ['role' => 'whatever']))->assertUnauthorized();
        });
        it('has no name parameter', function () {
            Role::factory(count: 1)->create();
            ['token' => $token] = authenticate(scope: $this);
            assertFailedResponse(
                response: $this->patchJson(route('role.update', ['role' => '1']), [
                    'Authorization' => "Bearer $token"
                ]),
                errorKey: 'name',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterRequired)
            );
        });
        it('has name parameter overflowing the column size', function () {
            Role::factory(count: 1)->create();
            $maxColumnSize = RoleSize::NAME->get();
            $name = generateWordBySize(size: $maxColumnSize + 1);
            ['token' => $token] = authenticate(scope: $this);
            assertFailedResponse(
                response: $this->patchJson(route('role.update', ['role' => '1']), [
                    'Authorization' => "Bearer $token",
                    'name' => $name
                ]),
                errorKey: 'name',
                errorMsg: Phrase::pickSentence(PhraseKey::MaxSizeInvalid, " ({$maxColumnSize})")
            );
        });
        it('has not found role into database', function () {
            ['token' => $token] = authenticate(scope: $this);
            $this->patchJson(route('role.update', ['role' => '1']), [
                'Authorization' => "Bearer $token",
                'name' => '1'
            ])
                ->assertNotFound();
        });
        it('has invalid role route parameter', function () {
            ['token' => $token] = authenticate(scope: $this);
            $this->patchJson(route('role.update', ['role' => 'whatever']), [
                'Authorization' => "Bearer $token",
                'name' => 'whatever'
            ])
                ->assertNotFound();
        });
        it('has user no super-admin role authenticated', function () {
            Role::factory(count: 1)->create();
            ['token' => $token] = authenticate(scope: $this);
            $this->patchJson(route('role.update', ['role' => '1']), [
                'Authorization' => "Bearer $token",
                'name' => 'whatever'
            ])
                ->assertForbidden();
        });
    });
    describe('succeed because', function () {
        it('has user super-admin authenticated', function () {
            $preRole = Role::factory(count: 1)->createOne([
                'name' => fake()->word()
            ]);
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $newName = fake()->word();
            $this->patchJson(route('role.update', ['role' => '1']), [
                'Authorization' => "Bearer $token",
                'name' => $newName
            ])->assertNoContent();
            $postRole = app(RoleRepository::class)->findByName($newName);
            expect($postRole->name)->toBe($newName);
            expect($preRole->name)->not()->toBe($postRole->name);
        });
    });
});
