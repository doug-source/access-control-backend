<?php

declare(strict_types=1);

use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
use App\Models\RegisterRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;

uses(RefreshDatabase::class);

describe('RegisterRequest destroy request', function () {
    describe('fails because', function () {
        it('has no authentication', function () {
            $response = $this->deleteJson(route('register.request.destroy', ['registerRequestID' => 1]));
            $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        });
        it('has invalid registerRequest parameter', function () {
            $token = login($this);
            $response = $this->deleteJson(route('register.request.destroy', ['registerRequestID' => 'whatever']), [
                'Authorization' => "Bearer {$token}",
            ]);
            assertFailedResponse($response, 'registerRequestID', Phrase::pickSentence(PhraseKey::ParameterInvalid));
        });
        it('has registerRequest parameter lower then minimal size', function () {
            $token = login($this);
            $response = $this->deleteJson(route('register.request.destroy', ['registerRequestID' => 0]), [
                'Authorization' => "Bearer {$token}",
            ]);
            assertFailedResponse($response, 'registerRequestID', Phrase::pickSentence(PhraseKey::ParameterInvalid));
        });
        it('has registerRequest parameter nonexistent', function () {
            $this->assertDatabaseMissing('register_requests', [
                'id' => 1,
            ]);
            $token = login($this);
            $response = $this->deleteJson(route('register.request.destroy', ['registerRequestID' => 1]), [
                'Authorization' => "Bearer {$token}",
            ]);
            assertFailedResponse($response, 'registerRequestID', Phrase::pickSentence(PhraseKey::ParameterInvalid));
        });
        it('executes by user no super-admin role', function () {
            $registerRequest = RegisterRequest::factory(count: 1)->createOne([
                'email' => fake()->email(),
                'phone' => '12345678901'
            ]);
            $email = fake()->email();
            $password = 'Test123!';
            $token = login(scope: $this, email: $email, password: $password);

            $this->deleteJson(route('register.request.destroy', ['registerRequestID' => $registerRequest->id]), [
                'Authorization' => "Bearer {$token}",
            ])
                ->assertForbidden()
                ->assertJson([
                    'message' => 'This action is unauthorized.'
                ]);
        });
    });
    describe('receives successful because', function () {
        it('removes the register request instance from database', function () {
            $email = fake()->email();
            $phone = '12345678901';
            $registerRequest = RegisterRequest::factory(count: 1)->create([
                'email' => $email,
                'phone' => $phone,
            ])->first();
            $token = login(scope: $this, email: $email);
            $userModel = findUserFromDB(email: $email);
            createSuperAdminRelationship($userModel);

            $this->assertDatabaseHas('register_requests', [
                'id' => $registerRequest->id,
                'email' => $email,
                'phone' => $phone
            ]);
            $this->deleteJson(route('register.request.destroy', ['registerRequestID' => $registerRequest->id]), [
                'Authorization' => "Bearer {$token}",
            ]);
            $this->assertDatabaseMissing('register_requests', [
                'id' => $registerRequest->id,
                'email' => $email,
                'phone' => $phone
            ]);
        });
    });
});
