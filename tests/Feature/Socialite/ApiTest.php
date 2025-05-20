<?php

use App\Models\Provider;
use App\Services\User\Contracts\AbilityServiceInterface;
use Illuminate\Support\Str;
use Illuminate\Support\Uri;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Socialite from api routes', function () {
    describe('fails because', function () {
        it('tries token release but user is not authenticated', function () {
            $response = $this->postJson(route('release.token'));
            $response->assertStatus(401);
        });
    });
    describe('receives successful because', function () {
        it('tries token release with user authenticated and email verified', function () {
            $userModel = buildSocialite(
                password: NULL,
                email: 'someone@test.com',
            );
            Provider::factory(count: 1)->create([
                'user_id' => $userModel->id
            ]);
            $this->get(route('oauth.redirect', ['provider' => 'google', 'type' => 'login']));
            $responseRedirect = $this->get(route('oauth.callback', 'google'));

            $uri = Uri::of($responseRedirect->getTargetUrl());
            $token = Str::of($uri->query()->get('provided'))->after('|')->toString();

            $this->postJson(route('release.token'), [], [
                'Authorization' => "Bearer {$token}"
            ])
                ->assertOk()
                ->assertJson(function (AssertableJson $json) use ($userModel, $token) {
                    $json
                        ->has('user', function (AssertableJson $json) use ($userModel, $token) {
                            $json
                                ->where('id', $userModel->id)
                                ->where('name', $userModel->name)
                                ->has('token')
                                ->whereNot('token', $token)
                                ->where('email', $userModel->email)
                                ->where('emailVerified', TRUE)
                                ->etc();
                        });
                });
        });
        it('tries token release with user authenticated and email not verified', function () {
            $userModel = buildSocialite(
                password: NULL,
                email: 'someone@test.com',
                emailVerified: FALSE
            );
            createSuperAdminRelationship($userModel);
            Provider::factory(count: 1)->create([
                'user_id' => $userModel->id
            ]);
            $this->get(route('oauth.redirect', ['provider' => 'google', 'type' => 'login']));
            $responseRedirect = $this->get(route('oauth.callback', 'google'));

            $uri = Uri::of($responseRedirect->getTargetUrl());
            $token = Str::of($uri->query()->get('provided'))->after('|')->toString();

            $this->postJson(route('release.token'), [], [
                'Authorization' => "Bearer {$token}"
            ])
                ->assertOk()
                ->assertJson(function (AssertableJson $json) use ($userModel, $token) {
                    $json
                        ->has('user', function (AssertableJson $json) use ($userModel, $token) {
                            $abilities = app(AbilityServiceInterface::class)->abilitiesFromUser($userModel)->pluck('name')->all();
                            $json
                                ->where('id', $userModel->id)
                                ->where('name', $userModel->name)
                                ->has('token')
                                ->whereNot('token', $token)
                                ->where('email', $userModel->email)
                                ->where('emailVerified', FALSE)
                                ->where('abilities', $abilities);
                        });
                });
        });
    });
});
