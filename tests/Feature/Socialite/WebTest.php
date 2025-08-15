<?php

use App\Library\Builders\Phrase;
use App\Library\Builders\UrlExternal;
use App\Library\Enums\PhraseKey;
use App\Models\Provider;
use App\Models\RegisterPermission;
use App\Models\User;
use App\Repositories\UserRepository;
use GuzzleHttp\{
    Psr7\Request,
    Psr7\Response,
    Exception\ClientException
};
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response as HttpResponse;
use Laravel\Socialite\{
    Facades\Socialite,
    Two\GoogleProvider,
};
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function makeClientException(string $uri)
{
    $request = new Request('POST', $uri);
    $response = new Response(404);
    return new ClientException('Client error', $request, $response);
}
function configSocialiteGoogleProvider(string $redirectUrl)
{
    $driver = Mockery::mock(GoogleProvider::class);
    $driver
        ->shouldReceive('redirect')
        ->andReturn(new RedirectResponse($redirectUrl));

    Socialite::shouldReceive('driver')->andReturn($driver);
}

describe('Socialite from web routes', function () {
    describe('fails because', function () {
        it('has invalid provider', function () {
            $providerName = 'whatever';
            $user = buildSocialite(password: NULL, email: 'someone@test.com');

            Provider::factory(count: 1)->create([
                'provider' => $providerName,
                'user_id' => $user->id
            ]);
            $this->get(route('oauth.redirect', ['provider' => $providerName, 'type' => 'login']));
            $this->get(route('oauth.callback', $providerName))
                ->assertStatus(HttpResponse::HTTP_FOUND)
                ->assertRedirect(
                    UrlExternal::build(query: [
                        'errormsg' => Phrase::pickSentence(PhraseKey::ProviderInvalid)->toString()
                    ])->value()
                );
        });
        it('has no email parameter stored into user database table', function () {
            buildSocialite(password: NULL, email: 'someone@test.com', createUsedDB: FALSE);
            $this->get(route('oauth.redirect', ['provider' => 'google', 'type' => 'login']));
            $this->get(route('oauth.callback', 'google'))
                ->assertStatus(HttpResponse::HTTP_FOUND)
                ->assertRedirect(
                    UrlExternal::build(query: [
                        'errormsg' => Phrase::pickSentence(PhraseKey::UserNullable)->toString()
                    ])->value()
                );
        });
        it('has email parameter with password stored into user database table', function () {
            buildSocialite(password: fake()->password(), email: 'someone@test.com');
            $this->get(route('oauth.redirect', ['provider' => 'google', 'type' => 'login']));
            $this->get(route('oauth.callback', 'google'))
                ->assertStatus(HttpResponse::HTTP_FOUND)
                ->assertRedirect(
                    UrlExternal::build(query: [
                        'errormsg' => Phrase::pickSentence(PhraseKey::PasswordNotNullable)->toString()
                    ])->value()
                );
        });
        it('throws the ClientException', function () {
            $route = route('oauth.callback', 'google');
            buildSocialite(
                password: 'whatever',
                exception: makeClientException(uri: $route)
            );
            $this->get(route('oauth.redirect', ['provider' => 'google', 'type' => 'login']));
            $this->get($route)
                ->assertStatus(HttpResponse::HTTP_FOUND)
                ->assertRedirect(
                    UrlExternal::build(query: [
                        'errormsg' => Phrase::pickSentence(PhraseKey::ProviderInvalid)->toString()
                    ])->value()
                );
        });
    });
    describe('receives successful because', function () {
        it("redirects to the correct Google's Provider sign in url", function () {
            $redirectUrl = 'https://redirect.url';
            configSocialiteGoogleProvider($redirectUrl);
            $this->get(route('oauth.redirect', ['google', '1']))
                ->assertStatus(HttpResponse::HTTP_FOUND)
                ->assertRedirect($redirectUrl);
        });
        it('authenticates the "login" using Google Provider', function () {
            buildSocialite();
            $this->get(route('oauth.redirect', ['provider' => 'google', 'type' => 'login']));
            $this->get(route('oauth.callback', 'google'))
                ->assertStatus(HttpResponse::HTTP_FOUND)
                ->assertRedirectContains(
                    UrlExternal::build(query: ['provided' => ''])->value()
                );
        });
        it('uses the "register" using Google Provider', function () {
            $registerPermission = RegisterPermission::factory()->createOne();
            buildSocialite(email: $registerPermission->email, name: fake()->name(), createUsedDB: false);

            $this->get(
                route(
                    'oauth.redirect',
                    [
                        'provider' => 'google',
                        'type' => 'register',
                        'token' => $registerPermission->token
                    ]
                )
            );
            $this->get(route('oauth.callback', 'google'))
                ->assertStatus(HttpResponse::HTTP_FOUND)
                ->assertRedirectContains(
                    UrlExternal::build(query: ['provided' => ''])->value()
                );

            $user = app(UserRepository::class)->findByEmail($registerPermission->email);

            $this->assertDatabaseHas('personal_access_tokens', [
                'tokenable_id' => $user->id,
            ]);
        });
    });
});
