<?php

declare(strict_types=1);

use App\Library\Builders\DatetimeFormat as DatetimeFormatBuilder;
use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
use App\Models\RegisterRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;

uses(RefreshDatabase::class);

describe('RegisterPermission index request', function () {
    describe('fails because', function () {
        it('has user not authenticated', function () {
            $route = route('register.request.show', [
                'registerRequestID' => 'whatever',
            ]);
            $this->getJson($route)
                ->assertStatus(Response::HTTP_UNAUTHORIZED)
                ->assertExactJson([
                    'message' => 'Unauthenticated.'
                ]);
        });
        it('has route parameter not integer', function () {
            $email = fake()->email();
            $password = 'Test123!';
            $token = login(scope: $this, email: $email, password: $password);
            $route = route('register.request.show', [
                'registerRequestID' => 'whatever',
            ]);
            assertFailedResponse(
                response: $this->getJson($route, [
                    'Authorization' => "Bearer $token"
                ]),
                errorKey: 'registerRequestID',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterInvalid),
            );
        });
        it("has route parameter's value lower then minimal size", function () {
            $email = fake()->email();
            $password = 'Test123!';
            $token = login(scope: $this, email: $email, password: $password);
            $route = route('register.request.show', [
                'registerRequestID' => '0',
            ]);
            assertFailedResponse(
                response: $this->getJson($route, [
                    'Authorization' => "Bearer $token"
                ]),
                errorKey: 'registerRequestID',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterInvalid),
            );
        });
        it("has route parameter's value not included into database", function () {
            $email = fake()->email();
            $password = 'Test123!';
            $token = login(scope: $this, email: $email, password: $password);
            $route = route('register.request.show', [
                'registerRequestID' => '1',
            ]);
            assertFailedResponse(
                response: $this->getJson($route, [
                    'Authorization' => "Bearer $token"
                ]),
                errorKey: 'registerRequestID',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterInvalid),
            );
        });
        it('executes by user no super-admin role', function () {
            $registerRequest = RegisterRequest::factory(count: 1)->createOne([
                'email' => fake()->email(),
                'phone' => '12345678901'
            ]);
            $email = fake()->email();
            $password = 'Test123!';
            $token = login(scope: $this, email: $email, password: $password);

            $route = route('register.request.show', [
                'registerRequestID' => $registerRequest->id,
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
            $registerRequest = RegisterRequest::factory(count: 1)->createOne([
                'email' => fake()->email(),
                'phone' => '12345678901'
            ]);
            $email = fake()->email();
            $password = 'Test123!';
            $token = login(scope: $this, email: $email, password: $password);
            createSuperAdminRelationship(user: findUserFromDB($email));

            $route = route('register.request.show', [
                'registerRequestID' => $registerRequest->id,
            ]);
            $this->getJson($route, [
                'Authorization' => "Bearer $token"
            ])
                ->assertStatus(Response::HTTP_OK)
                ->assertExactJson([
                    "id" => 1,
                    "email" => $registerRequest->email,
                    "phone" => $registerRequest->phone,
                    "createdAt" => DatetimeFormatBuilder::formatToDate(
                        $registerRequest->created_at
                    ),
                    "updatedAt" => DatetimeFormatBuilder::formatToDate(
                        $registerRequest->updated_at
                    )
                ]);
        });
    });
});
