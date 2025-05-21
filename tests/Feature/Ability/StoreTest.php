<?php

declare(strict_types=1);

use App\Library\Builders\Phrase;
use App\Library\Enums\ColumnSize\AbilitySize;
use App\Library\Enums\PhraseKey;
use App\Models\Ability;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Ability store request', function () {
    describe('fails because', function () {
        it('has no authentication', function () {
            $response = $this->postJson(route('ability.store'));
            $response->assertUnauthorized();
        });
        it('has no name parameter', function () {
            ['token' => $token] = authenticate(scope: $this);
            assertFailedResponse(
                response: $this->postJson(route('ability.store'), [
                    'Authorization' => "Bearer $token",
                ]),
                errorKey: 'name',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterRequired),
            );
        });
        it('has name parameter already into database', function () {
            $ability = Ability::factory(count: 1)->createOne();
            ['token' => $token] = authenticate(scope: $this);
            assertFailedResponse(
                response: $this->postJson(route('ability.store'), [
                    'Authorization' => "Bearer $token",
                    'name' => $ability->name,
                ]),
                errorKey: 'name',
                errorMsg: Phrase::pickSentence(PhraseKey::NameAlreadyUsed),
            );
        });
        it('has name parameter overflowing the column size', function () {
            $maxColumnSize = AbilitySize::NAME->get();
            $name = generateWordBySize(size: $maxColumnSize + 1);
            ['token' => $token] = authenticate(scope: $this);
            assertFailedResponse(
                response: $this->postJson(route('ability.store'), [
                    'Authorization' => "Bearer $token",
                    'name' => $name,
                ]),
                errorKey: 'name',
                errorMsg: Phrase::pickSentence(PhraseKey::MaxSizeInvalid, " ({$maxColumnSize})"),
            );
        });
        it('has user no super-admin role authenticated', function () {
            Ability::factory(count: 1)->create();
            ['token' => $token] = authenticate(scope: $this);
            $this->postJson(route('ability.store'), [
                'Authorization' => "Bearer $token",
                'name' => 'whatever',
            ])
                ->assertForbidden();
        });
    });
    describe('succeed because', function () {
        it('persists the ability instance to database correctly', function () {
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);

            $this->postJson(route('ability.store'), [
                'Authorization' => "Bearer $token",
                'name' => 'whatever',
            ])
                ->assertCreated()
                ->assertHeader(
                    'Location',
                    route('ability.show', ['ability' => 4])
                );
        });
    });
});
