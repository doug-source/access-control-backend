<?php

declare(strict_types=1);

use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
use App\Mail\RegisterPermission as RegisterPermissionMail;
use App\Models\RegisterRequest;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('RegisterRequest approval request', function () {
    describe('fails because', function () {
        it('has no authentication', function () {
            $response = $this->deleteJson(route('register.request.approval', ['registerRequestID' => 1]));
            $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        });
        it('has invalid registerRequest parameter', function () {
            $token = login($this);
            $response = $this->deleteJson(route('register.request.approval', ['registerRequestID' => 'whatever']), [
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/json',
            ]);
            assertFailedResponse($response, 'registerRequestID', Phrase::pickSentence(PhraseKey::ParameterInvalid));
        });
        it('has registerRequest parameter lower then minimal size', function () {
            $token = login($this);
            $response = $this->deleteJson(route('register.request.approval', ['registerRequestID' => 0]), [
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/json',
            ]);
            assertFailedResponse($response, 'registerRequestID', Phrase::pickSentence(PhraseKey::ParameterInvalid));
        });
        it('has registerRequest parameter non-existent', function () {
            $this->assertDatabaseMissing('register_requests', [
                'id' => 1,
            ]);
            $token = login($this);
            $response = $this->deleteJson(route('register.request.approval', ['registerRequestID' => 1]), [
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

            $this->deleteJson(route('register.request.approval', ['registerRequestID' => $registerRequest->id]), [
                'Authorization' => "Bearer {$token}",
            ])
                ->assertForbidden()
                ->assertJson([
                    'message' => 'This action is unauthorized.'
                ]);
        });
    });
    describe('receives successful because', function () {
        it('moves the register request to register permission into database', function () {
            $registerRequest = RegisterRequest::factory(count: 1)->createOne([
                'email' => fake()->email(),
                'phone' => '12345678901',
            ]);
            $email = fake()->email();
            $token = login(scope: $this, email: $email);
            createSuperAdminRelationship(
                findUserFromDB(email: $email)
            );
            $this->assertDatabaseHas('register_requests', [
                'id' => $registerRequest->id,
                'email' => $registerRequest->email,
                'phone' => $registerRequest->phone,
            ]);
            $this->deleteJson(route('register.request.approval', ['registerRequestID' => $registerRequest->id]), [
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/json',
            ])->assertStatus(Response::HTTP_OK);
            $this->assertDatabaseMissing('register_requests', [
                'id' => $registerRequest->id,
                'email' => $registerRequest->email,
                'phone' => $registerRequest->phone,
            ]);
            $this->assertDatabaseHas('register_permissions', [
                'email' => $registerRequest->email,
                'phone' => $registerRequest->phone,
            ]);
        });
        it('send email after register permission was created', function () {
            $registerRequest = RegisterRequest::factory(count: 1)->create()->first();
            Mail::fake();
            $email = fake()->email();
            $token = login(scope: $this, email: $email);
            createSuperAdminRelationship(
                findUserFromDB(email: $email)
            );

            $this->deleteJson(route('register.request.approval', ['registerRequestID' => $registerRequest->id]), [
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/json',
            ]);
            Mail::assertSent(RegisterPermissionMail::class, function ($mailSent) use ($registerRequest) {
                return $mailSent->hasTo($registerRequest->email) && $mailSent->hasFrom(
                    config('mail.from.address'),
                    config('app.name')
                ) && $mailSent->hasSubject(Phrase::pickSentence(PhraseKey::RegisterApproval));
            });
            Mail::assertSent(RegisterPermissionMail::class);
        });
    });
});
