<?php

use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
use App\Library\Enums\ColumnSize\RegisterRequestSize;
use App\Library\Converters\Phone as PhoneConverter;
use App\Models\RegisterPermission;
use App\Models\RegisterRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;

uses(RefreshDatabase::class);

describe('RegisterRequest store request', function () {
    describe('fails because', function () {
        it('has no email parameter', function () {
            $response = $this->postJson(route('register.request.store'));
            assertFailedResponse($response, 'email', Phrase::pickSentence(PhraseKey::ParameterRequired));
        });
        it('has invalid email parameter', function () {
            $response = $this->postJson(route('register.request.store', [
                'email' => 'whatever'
            ]));
            assertFailedResponse($response, 'email', Phrase::pickSentence(PhraseKey::EmailInvalid));
        });
        it('has email parameter overflowing the column size', function () {
            $maxColumnSize = RegisterRequestSize::EMAIL->get();
            $email = generateOverflowInvalidEmail($maxColumnSize);

            $response = $this->postJson(route('register.request.store', [
                'email' => $email->toString()
            ]));
            assertFailedResponse($response, 'email', Phrase::pickSentence(PhraseKey::MaxSizeInvalid, " ({$maxColumnSize})"));
        });
        it('has phone parameter overflowing the column size', function () {
            $response = $this->postJson(route('register.request.store', [
                'email' => fake()->email(),
                'phone' => 123456789012
            ]));
            $phoneMaxSixe = RegisterRequestSize::PHONE->get();
            assertFailedResponse($response, 'phone', Phrase::pickSentence(PhraseKey::MaxSizeInvalid, " ({$phoneMaxSixe})"));
        });
        it('has invalid phone parameter', function () {
            $response = $this->postJson(route('register.request.store', [
                'email' => fake()->email(),
                'phone' => 'whatever'
            ]));
            assertFailedResponse($response, 'phone', Phrase::pickSentence(PhraseKey::ParameterInvalid));
        });
    });
    describe('receives successful because', function () {
        it('has already a register request instance into database', function () {
            $registerRequest = RegisterRequest::factory(count: 1)->create()->first();
            $this->postJson(route('register.request.store', [
                'email' => $registerRequest->email,
                'phone' => $registerRequest->phone
            ]))->assertStatus(Response::HTTP_CREATED);
        });
        it('has already a register request instance into database (different phone)', function () {
            $registerRequest = RegisterRequest::factory(count: 1)->create()->first();
            $this->postJson(route('register.request.store', [
                'email' => $registerRequest->email,
                'phone' => '12345678901'
            ]))->assertStatus(Response::HTTP_CREATED);
        });
        it('has already a register permission instance into database', function () {
            $permission = RegisterPermission::factory(count: 1)->create([
                'expiration_data' => now()->subtract(DateInterval::createFromDateString('2 day'))
            ])->first();
            $this->postJson(route('register.request.store', [
                'email' => $permission->email,
                'phone' => $permission->phone
            ]))->assertStatus(Response::HTTP_CREATED);
            $this->assertDatabaseMissing('register_permissions', [
                'expiration_data' => $permission->expiration_data,
                'token' => $permission->token
            ]);
        });
        it('has complete parameters', function () {
            $email = fake()->email();
            $phone = '12 34567-8901';
            $this->postJson(route('register.request.store', [
                'email' => $email,
                'phone' => $phone
            ]))->assertStatus(Response::HTTP_CREATED);

            $this->assertDatabaseHas('register_requests', [
                'email' => $email,
                'phone' => PhoneConverter::clear($phone)
            ]);
        });
        it('has no phone parameter', function () {
            $email = fake()->email();
            $this->postJson(route('register.request.store', [
                'email' => $email,
            ]))->assertStatus(Response::HTTP_CREATED);

            $this->assertDatabaseHas('register_requests', [
                'email' => $email,
                'phone' => NULL
            ]);
        });
    });
});
