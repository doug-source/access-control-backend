<?php

use App\Library\Builders\Phrase;
use App\Library\Builders\UrlExternal;
use App\Library\Enums\PhraseKey;
use App\Models\Provider;
use App\Models\RegisterPermission;
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
use App\Library\Builders\Token as TokenBuilder;
use App\Library\Enums\ColumnSize\RegisterPermissionSize;
use Illuminate\Support\Facades\Exceptions;

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
        it('has invalid type during provider request', function () {
            Exceptions::fake();

            $providerName = 'whatever';
            $user = buildSocialite(password: NULL, email: 'someone@test.com');

            Provider::factory(count: 1)->create([
                'provider' => $providerName,
                'user_id' => $user->id
            ]);
            $this->get(route('oauth.redirect', ['provider' => $providerName, 'type' => 'whatever']));
            $this->get(route('oauth.callback', $providerName));

            Exceptions::assertReported(function (\Exception $e) {
                $msgOk = $e->getMessage() === 'Execution flow not implemented';
                $codeOk = $e->getCode() === 1;
                return $msgOk && $codeOk;
            });
        });
        it('has invalid provider during provider login', function () {
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
        it('has no email parameter stored into user database table during provider login', function () {
            buildSocialite(password: NULL, email: fake()->email(), createUsedDB: FALSE);
            $this->get(route('oauth.redirect', ['provider' => 'google', 'type' => 'login']));
            $this->get(route('oauth.callback', 'google'))
                ->assertStatus(HttpResponse::HTTP_FOUND)
                ->assertRedirect(
                    UrlExternal::build(query: [
                        'errormsg' => Phrase::pickSentence(PhraseKey::UserNullable)->toString()
                    ])->value()
                );
        });
        it('has email parameter with password stored into user database table during provider login', function () {
            buildSocialite(password: fake()->password(), email: fake()->email());
            $this->get(route('oauth.redirect', ['provider' => 'google', 'type' => 'login']));
            $this->get(route('oauth.callback', 'google'))
                ->assertStatus(HttpResponse::HTTP_FOUND)
                ->assertRedirect(
                    UrlExternal::build(query: [
                        'errormsg' => Phrase::pickSentence(PhraseKey::PasswordNotNullable)->toString()
                    ])->value()
                );
        });
        it('throws the ClientException during provider login', function () {
            Exceptions::fake();

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
                        'errormsg' => Phrase::pickSentence(PhraseKey::ProviderCredentialsInvalid)->toString()
                    ])->value()
                );
        });
        it('throws the ClientException during provider register', function () {
            Exceptions::fake();

            $route = route('oauth.callback', 'google');
            buildSocialite(
                password: 'whatever',
                exception: makeClientException(uri: $route)
            );
            $this->get(route('oauth.redirect', [
                'provider' => 'google',
                'type' => 'register',
                'token' => TokenBuilder::build()
            ]));
            $this->get($route)
                ->assertStatus(HttpResponse::HTTP_FOUND)
                ->assertRedirect(
                    UrlExternal::build(query: [
                        'errormsg' => Phrase::pickSentence(PhraseKey::ProviderCredentialsInvalid)->toString()
                    ])->value()
                );
        });
        it('has invalid email parameter during provider register', function () {
            $route = route('oauth.callback', 'google');
            buildSocialite(
                password: 'whatever',
                createUsedDB: FALSE,
                email: 'whatever',
            );
            $this->get(route('oauth.redirect', [
                'provider' => 'google',
                'type' => 'register',
                'token' => TokenBuilder::build()
            ]));
            $this->get($route)
                ->assertStatus(HttpResponse::HTTP_FOUND)
                ->assertRedirect(
                    UrlExternal::build(query: [
                        'errormsg' => Phrase::pickSentence(PhraseKey::EmailInvalid)->toString()
                    ])->value()
                );
        });
        it('has email parameter already stored during provider register', function () {
            $route = route('oauth.callback', 'google');
            buildSocialite(
                password: 'whatever',
                email: fake()->email(),
            );
            $this->get(route('oauth.redirect', [
                'provider' => 'google',
                'type' => 'register',
                'token' => TokenBuilder::build()
            ]));
            $this->get($route)
                ->assertStatus(HttpResponse::HTTP_FOUND)
                ->assertRedirect(
                    UrlExternal::build(query: [
                        'errormsg' => Phrase::pickSentence(PhraseKey::EmailInvalid)->toString()
                    ])->value()
                );
        });
        it('has no token parameter during provider register', function () {
            $route = route('oauth.callback', 'google');
            buildSocialite(
                password: 'whatever',
                createUsedDB: FALSE,
                email: fake()->email(),
            );
            $this->get(route('oauth.redirect', [
                'provider' => 'google',
                'type' => 'register',
                'token' => ''
            ]));
            $this->get($route)
                ->assertStatus(HttpResponse::HTTP_FOUND)
                ->assertRedirect(
                    UrlExternal::build(query: [
                        'errormsg' => Phrase::pickSentence(PhraseKey::ParameterRequired)->toString()
                    ])->value()
                );
        });
        it('has token parameter overflowing the column size during provider register', function () {
            $route = route('oauth.callback', 'google');
            buildSocialite(
                password: 'whatever',
                createUsedDB: FALSE,
                email: fake()->email(),
            );
            $tokenMaxSize = RegisterPermissionSize::TOKEN->get();
            $this->get(route('oauth.redirect', [
                'provider' => 'google',
                'type' => 'register',
                'token' => generateWordBySize(size: $tokenMaxSize + 1)
            ]));
            $this->get($route)
                ->assertStatus(HttpResponse::HTTP_FOUND)
                ->assertRedirect(
                    UrlExternal::build(query: [
                        'errormsg' => Phrase::pickSentence(PhraseKey::MaxSizeInvalid, " ({$tokenMaxSize})")->toString()
                    ])->value()
                );
        });
        it('has token parameter not equal to token stored during provider register', function () {
            $route = route('oauth.callback', 'google');
            buildSocialite(
                password: 'whatever',
                createUsedDB: FALSE,
                email: fake()->email(),
            );
            $this->get(route('oauth.redirect', [
                'provider' => 'google',
                'type' => 'register',
                'token' => TokenBuilder::build()
            ]));
            $this->get($route)
                ->assertStatus(HttpResponse::HTTP_FOUND)
                ->assertRedirect(
                    UrlExternal::build(query: [
                        'errormsg' => Phrase::pickSentence(PhraseKey::ParameterInvalid)->toString()
                    ])->value()
                );
        });
        it('has token parameter expired during provider register', function () {
            $registerPermission = RegisterPermission::factory()->createOne([
                'expiration_data' => now()->subtract(DateInterval::createFromDateString('2 day'))
            ]);
            $route = route('oauth.callback', 'google');
            buildSocialite(
                password: 'whatever',
                createUsedDB: FALSE,
                email: $registerPermission->email,
            );
            $this->get(route('oauth.redirect', [
                'provider' => 'google',
                'type' => 'register',
                'token' => $registerPermission->token,
            ]));
            $this->get($route)
                ->assertStatus(HttpResponse::HTTP_FOUND)
                ->assertRedirect(
                    UrlExternal::build(query: [
                        'errormsg' => Phrase::pickSentence(PhraseKey::RegistrationExpired)->toString()
                    ])->value()
                );
        });
        it('throws the ClientException during provider request', function () {
            Exceptions::fake();

            $route = route('oauth.callback', 'google');
            buildSocialite(
                password: 'whatever',
                exception: makeClientException(uri: $route)
            );
            $this->get(route('oauth.redirect', [
                'provider' => 'google',
                'type' => 'request',
            ]));
            $path = config('app.frontend.uri.register.request');
            $this->get($route)
                ->assertStatus(HttpResponse::HTTP_FOUND)
                ->assertRedirect(
                    UrlExternal::build(path: $path, query: [
                        'errormsg' => Phrase::pickSentence(PhraseKey::ProviderCredentialsInvalid)->toString()
                    ])->value()
                );
        });
        it('has invalid email parameter during provider request', function () {
            $route = route('oauth.callback', 'google');
            buildSocialite(
                password: 'whatever',
                createUsedDB: FALSE,
                email: 'whatever',
            );
            $this->get(route('oauth.redirect', [
                'provider' => 'google',
                'type' => 'request',
            ]));
            $path = config('app.frontend.uri.register.request');
            $this->get($route)
                ->assertStatus(HttpResponse::HTTP_FOUND)
                ->assertRedirect(
                    UrlExternal::build(path: $path, query: [
                        'errormsg' => Phrase::pickSentence(PhraseKey::EmailInvalid)->toString()
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
        it('uses the "request" using Google Provider', function () {
            buildSocialite(email: fake()->email(), name: fake()->name(), createUsedDB: false);

            $this->get(
                route(
                    'oauth.redirect',
                    [
                        'provider' => 'google',
                        'type' => 'request',
                    ]
                )
            );
            $path = config('app.frontend.uri.register.request');
            $this->get(route('oauth.callback', 'google'))
                ->assertStatus(HttpResponse::HTTP_FOUND)
                ->assertRedirect(
                    UrlExternal::build(
                        path: $path,
                        query: ['successmsg' => Phrase::pickSentence(key: PhraseKey::Registered, remain: '!')->toString()]
                    )->value()
                );
        });
    });
});
