<?php

declare(strict_types=1);

use App\Library\Builders\Phrase;
use App\Library\Enums\ColumnSize\RegisterPermissionSize;
use App\Library\Enums\PhraseKey;
use App\Models\RegisterPermission;
use Illuminate\Http\Response;
use Illuminate\Support\Uri;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('RegisterPermission index request', function () {
    describe('fails because', function () {
        it('has no authentication', function () {
            $response = $this->getJson(route('register.permission.index'));
            $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        });
        it('has no page parameter', function () {
            ['token' => $token] = authenticate(scope: $this);
            assertFailedResponse(
                response: $this->getJson(route('register.permission.index'), [
                    'Authorization' => "Bearer {$token}",
                ]),
                errorKey: 'page',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterRequired)
            );
        });
        it('has invalid page parameter', function () {
            ['token' => $token] = authenticate(scope: $this);
            $uri = Uri::of(route('register.permission.index'))->withQuery(['page' => 'whatever'])->value();
            assertFailedResponse(
                response: $this->getJson($uri, [
                    'Authorization' => "Bearer {$token}",
                ]),
                errorKey: 'page',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterInvalid)
            );
        });
        it('has page parameter lower then minimal size', function () {
            ['token' => $token] = authenticate(scope: $this);
            $uri = Uri::of(route('register.permission.index'))->withQuery(['page' => '0'])->value();
            assertFailedResponse(
                response: $this->getJson($uri, [
                    'Authorization' => "Bearer {$token}",
                ]),
                errorKey: 'page',
                errorMsg: Phrase::pickSentence(PhraseKey::MinSizeInvalid, ' (1)')
            );
        });
        it('has no group parameter', function () {
            ['token' => $token] = authenticate(scope: $this);
            $uri = Uri::of(route('register.permission.index'))->withQuery(['page' => '1'])->value();
            assertFailedResponse(
                response: $this->getJson($uri, [
                    'Authorization' => "Bearer {$token}",
                ]),
                errorKey: 'group',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterRequired)
            );
        });
        it('has invalid group parameter', function () {
            ['token' => $token] = authenticate(scope: $this);
            $uri = Uri::of(route('register.permission.index'))->withQuery(['page' => '1', 'group' => 'whatever'])->value();
            assertFailedResponse(
                response: $this->getJson($uri, [
                    'Authorization' => "Bearer {$token}",
                ]),
                errorKey: 'group',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterInvalid)
            );
        });
        it('has group parameter lower then minimal size', function () {
            ['token' => $token] = authenticate(scope: $this);
            $uri = Uri::of(route('register.permission.index'))->withQuery(['page' => '1', 'group' => '0'])->value();
            assertFailedResponse(
                response: $this->getJson($uri, [
                    'Authorization' => "Bearer {$token}",
                ]),
                errorKey: 'group',
                errorMsg: Phrase::pickSentence(PhraseKey::MinSizeInvalid, ' (1)')
            );
        });
        it('has email parameter greater then maximun size', function () {
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $maxColumnSize = RegisterPermissionSize::EMAIL->get();
            $emailOverflowed = generateOverflowInvalidEmail($maxColumnSize)->toString();
            $uri = Uri::of(route('register.permission.index'))->withQuery(['page' => '1', 'group' => '1', 'email' => $emailOverflowed])->value();
            assertFailedResponse(
                response: $this->getJson($uri, [
                    'Authorization' => "Bearer {$token}",
                ]),
                errorKey: 'email',
                errorMsg: Phrase::pickSentence(PhraseKey::MaxSizeInvalid, " ($maxColumnSize)")
            );
        });
        it('executes by user no super-admin role', function () {
            RegisterPermission::factory(count: 1)->createOne([
                'email' => fake()->email(),
                'phone' => '12345678901'
            ]);
            ['token' => $token] = authenticate(scope: $this, password: 'Test123!');
            $uri = Uri::of(route('register.permission.index'))->withQuery([
                'page' => '1',
                'group' => '1',
            ])->value();
            $this->getJson($uri, [
                'Authorization' => "Bearer $token"
            ])
                ->assertForbidden()
                ->assertJson([
                    'message' => 'This action is unauthorized.'
                ]);
        });
    });
    describe('succeed because', function () {
        it('has complete parameters', function () {
            $registerPermission = RegisterPermission::factory(count: 1)->createOne();
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $uri = Uri::of(route('register.permission.index'))->withQuery(['page' => '1', 'group' => '1', 'email' => $registerPermission->email])->value();
            $this->getJson($uri, [
                'Authorization' => "Bearer {$token}",
            ])
                ->assertOk()
                ->assertJson([
                    'data' => [
                        [
                            'id' => $registerPermission->id,
                            'email' => $registerPermission->email,
                            'phone' => $registerPermission->phone
                        ]
                    ]
                ]);
        });
    });
});
