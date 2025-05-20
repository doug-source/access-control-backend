<?php

declare(strict_types=1);

use App\Library\Builders\DatetimeFormat as DatetimeFormatBuilder;
use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
use App\Models\RegisterPermission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;

uses(RefreshDatabase::class);

describe('RegisterPermission index request', function () {
    describe('fails because', function () {
        it('has user not authenticated', function () {
            $route = route('register.permission.show', [
                'registerPermissionID' => 'whatever',
            ]);
            $this->getJson($route)
                ->assertStatus(Response::HTTP_UNAUTHORIZED)
                ->assertExactJson([
                    'message' => 'Unauthenticated.'
                ]);
        });
        it('has route parameter not integer', function () {
            ['token' => $token] = authenticate(scope: $this);
            $route = route('register.permission.show', [
                'registerPermissionID' => 'whatever',
            ]);
            assertFailedResponse(
                response: $this->getJson($route, [
                    'Authorization' => "Bearer {$token}",
                ]),
                errorKey: 'registerPermissionID',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterInvalid)
            );
        });
        it("has route parameter's value lower then minimal size", function () {
            ['token' => $token] = authenticate(scope: $this);
            $route = route('register.permission.show', [
                'registerPermissionID' => '0',
            ]);
            assertFailedResponse(
                response: $this->getJson($route, [
                    'Authorization' => "Bearer {$token}",
                ]),
                errorKey: 'registerPermissionID',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterInvalid)
            );
        });
        it("has route parameter's value not included into database", function () {
            ['token' => $token] = authenticate(scope: $this);
            $route = route('register.permission.show', [
                'registerPermissionID' => '1',
            ]);
            assertFailedResponse(
                response: $this->getJson($route, [
                    'Authorization' => "Bearer {$token}",
                ]),
                errorKey: 'registerPermissionID',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterInvalid)
            );
        });
        it('executes by user no super-admin role', function () {
            ['token' => $token] = authenticate(scope: $this, password: 'Test123!');
            $registerPermission = RegisterPermission::factory(count: 1)->createOne([
                'email' => fake()->email(),
                'phone' => '12345678901'
            ]);
            $route = route('register.permission.show', [
                'registerPermissionID' => $registerPermission->id,
            ]);
            $this->getJson($route, [
                'Authorization' => "Bearer $token"
            ])
                ->assertForbidden()
                ->assertJson([
                    'message' => 'This action is unauthorized.'
                ]);
        });
    });
    describe('succeed because', function () {
        it('has complete parameters in right way', function () {
            ['token' => $token, 'user' => $user] = authenticate(scope: $this, password: 'Test123!');
            createSuperAdminRelationship(user: $user);

            $registerPermission = RegisterPermission::factory(count: 1)->createOne([
                'email' => fake()->email(),
                'phone' => '12345678901'
            ]);
            $route = route('register.permission.show', [
                'registerPermissionID' => $registerPermission->id,
            ]);
            $this->getJson($route, [
                'Authorization' => "Bearer $token"
            ])
                ->assertStatus(Response::HTTP_OK)
                ->assertExactJson([
                    'id' => 1,
                    'email' => $registerPermission->email,
                    'phone' => $registerPermission->phone,
                    'createdAt' => DatetimeFormatBuilder::formatToDate(
                        $registerPermission->created_at
                    ),
                    'updatedAt' => DatetimeFormatBuilder::formatToDate(
                        $registerPermission->updated_at
                    ),
                    'expirationData' => DatetimeFormatBuilder::formatToDate(
                        $registerPermission->expiration_data
                    )
                ]);
        });
    });
});
