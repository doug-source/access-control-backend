<?php

declare(strict_types=1);

use App\Library\Builders\Phrase;
use App\Library\Enums\ColumnSize\AbilitySize;
use App\Library\Enums\PhraseKey;
use App\Models\Ability;
use App\Repositories\AbilityRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Abilities from api routes', function () {
    describe('fails because', function () {
        it('has no authentication', function () {
            $this->patchJson(route('ability.update', ['ability' => 'whatever']))->assertUnauthorized();
        });
        it('has no name parameter', function () {
            Ability::factory(count: 1)->create();
            ['token' => $token] = authenticate(scope: $this);
            assertFailedResponse(
                response: $this->patchJson(route('ability.update', ['ability' => '1']), [
                    'Authorization' => "Bearer $token"
                ]),
                errorKey: 'name',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterRequired)
            );
        });
        it('has name parameter overflowing the column size', function () {
            Ability::factory(count: 1)->create();
            $maxColumnSize = AbilitySize::NAME->get();
            $name = generateWordBySize(size: $maxColumnSize + 1);
            ['token' => $token] = authenticate(scope: $this);
            assertFailedResponse(
                response: $this->patchJson(route('ability.update', ['ability' => '1']), [
                    'Authorization' => "Bearer $token",
                    'name' => $name
                ]),
                errorKey: 'name',
                errorMsg: Phrase::pickSentence(PhraseKey::MaxSizeInvalid, " ({$maxColumnSize})")
            );
        });
        it('has not found ability into database', function () {
            ['token' => $token] = authenticate(scope: $this);
            $this->patchJson(route('ability.update', ['ability' => '1']), [
                'Authorization' => "Bearer $token",
                'name' => '1'
            ])
                ->assertNotFound();
        });
        it('has invalid ability route parameter', function () {
            ['token' => $token] = authenticate(scope: $this);
            $this->patchJson(route('ability.update', ['ability' => 'whatever']), [
                'Authorization' => "Bearer $token",
                'name' => 'whatever'
            ])
                ->assertNotFound();
        });
        it('has user no super-admin role authenticated', function () {
            Ability::factory(count: 1)->create();
            ['token' => $token] = authenticate(scope: $this);
            $this->patchJson(route('ability.update', ['ability' => '1']), [
                'Authorization' => "Bearer $token",
                'name' => 'whatever'
            ])
                ->assertForbidden();
        });
    });
    describe('succeed because', function () {
        it('has user super-admin authenticated', function () {
            $preAbility = Ability::factory(count: 1)->createOne([
                'name' => fake()->word()
            ]);
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $newName = fake()->word();
            $this->patchJson(route('ability.update', ['ability' => '1']), [
                'Authorization' => "Bearer $token",
                'name' => $newName
            ])->assertNoContent();
            $postAbility = app(AbilityRepository::class)->findByName($newName);
            expect($postAbility->name)->toBe($newName);
            expect($preAbility->name)->not()->toBe($postAbility->name);
        });
    });
});
