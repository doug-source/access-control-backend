<?php

use App\Library\Builders\Phrase;
use App\Library\Builders\UrlExternal;
use App\Library\Enums\PhraseKey;
use App\Library\Enums\ColumnSize\RegisterPermissionSize;
use App\Models\RegisterPermission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;

uses(RefreshDatabase::class);

describe('User form create request', function () {
    describe('fails because', function () {
        it('has no token parameter', function () {
            $url = URL::temporarySignedRoute(
                name: 'users.create',
                expiration: now()->addMinutes(15),
            );
            $response = $this->getJson($url);
            assertFailedResponse($response, 'token', Phrase::pickSentence(PhraseKey::ParameterRequired));
        });
        it('has token parameter overflowing the column size', function () {
            $maxSize = RegisterPermissionSize::TOKEN->get();
            $url = URL::temporarySignedRoute(
                name: 'users.create',
                expiration: now()->addMinutes(15),
                parameters: ['token' => generateWordBySize($maxSize + 1)]
            );
            $response = $this->getJson($url);
            assertFailedResponse($response, 'token', Phrase::pickSentence(PhraseKey::MaxSizeInvalid, " ({$maxSize})"));
        });
        it('has token parameter nonexistent into database', function () {
            $url = URL::temporarySignedRoute(
                name: 'users.create',
                expiration: now()->addMinutes(15),
                parameters: ['token' => fake()->password()]
            );
            $response = $this->getJson($url);
            assertFailedResponse($response, 'token', Phrase::pickSentence(PhraseKey::ParameterInvalid));
        });
    });
    describe('receives successful because', function () {
        it('has complete parameters', function () {
            $permission = RegisterPermission::factory(count: 1)->create()->first();
            $url = URL::temporarySignedRoute(
                name: 'users.create',
                expiration: now()->addMinutes(15),
                parameters: ['token' => $permission->token]
            );

            $this->getJson($url)
                ->assertStatus(Response::HTTP_FOUND)
                ->assertRedirect(
                    UrlExternal::build(
                        path: config('app.frontend.uri.register.form'),
                        query: ['token' => $permission->token]
                    )->value()
                );
        });
    });
});
