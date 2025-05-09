<?php

use App\Library\Builders\Phrase;
use App\Library\Builders\UrlExternal;
use App\Library\Enums\PhraseKey;
use GuzzleHttp\{
    Psr7\Request,
    Psr7\Response,
    Exception\ClientException
};
use Illuminate\{
    Http\RedirectResponse,
    Support\Str
};
use Laravel\Socialite\{
    Facades\Socialite,
    Two\GoogleProvider,
};

function makeRedirectErrorUrl($errormsg)
{
    return UrlExternal::build(
        query: [
            'errormsg' => $errormsg
        ]
    )->value();
}
function makeClientException(string $uri)
{
    $request = new Request('POST', $uri);
    $response = new Response(404);
    return new ClientException('Client error', $request, $response);
}

describe('Web application routes', function () {
    it('returns 404 in route "/"', function () {
        $response = $this->get('/');
        $response->assertStatus(404);
    });
    it('returns 404 in whatever route', function () {
        $response = $this->get('/any-route');
        $response->assertStatus(404);
    });
    it("redirects to the correct Google's Provider sign in url", function () {
        $driver = Mockery::mock(GoogleProvider::class);
        $driver->shouldReceive('redirect')
            ->andReturn(new RedirectResponse('https://redirect.url'));

        Socialite::shouldReceive('driver')->andReturn($driver);

        $this->get(route('oauth.redirect', ['google', '1']))
            ->assertStatus(302)
            ->assertRedirect('https://redirect.url');
    });
    it('signs in with Google Provider', function () {
        buildSocialite();

        $response = $this->get(route('oauth.callback', 'google'));
        $response->assertStatus(302);

        $url = preg_replace(['|[-]|', '|[.]|'], ['\\-', '\\.'], config('app.frontend.uri.host'));
        expect($response->getTargetUrl())->toMatch("|^{$url}\?provided=.+$|");
    });
    it('fails the sign in with invalid provider', function () {
        buildSocialite();

        $response = $this->get(route('oauth.callback', 'foo'));
        $response->assertStatus(302);

        $url = makeRedirectErrorUrl(
            Phrase::pickSentence(PhraseKey::ProviderInvalid)->toString()
        );
        expect($response->getTargetUrl())->toBe($url);
    });
    it('fails the sign in with user passworded with provider', function () {
        buildSocialite(password: true);

        $response = $this->get(route('oauth.callback', 'google'));
        $response->assertStatus(302);

        $url = makeRedirectErrorUrl(
            Phrase::pickSentence(PhraseKey::PasswordNotNullable)->toString()
        );
        expect($response->getTargetUrl())->toBe($url);
    });
    it('fails the sign in with provider when user not is registered', function () {
        buildSocialite(password: NULL, email: 'dude@mail.com', createUsedDB: FALSE);
        $responseRedirect = $this->get(route('oauth.callback', 'google'));
        $responseRedirect->assertStatus(302);
        $errorMsg = urldecode(
            Str::after(
                parse_url($responseRedirect->getTargetUrl(), PHP_URL_QUERY),
                'errormsg='
            )
        );

        expect($errorMsg)->toBe(Phrase::pickSentence(PhraseKey::UserNullable)->toString());
    });
    it('fails the sign in with provider when user is registered with password', function () {
        buildSocialite(password: 'whatever', email: 'dude@mail.com', createUsedDB: TRUE);
        $responseRedirect = $this->get(route('oauth.callback', 'google'));
        $responseRedirect->assertStatus(302);
        $errorMsg = urldecode(
            Str::after(
                parse_url($responseRedirect->getTargetUrl(), PHP_URL_QUERY),
                'errormsg='
            )
        );
        expect($errorMsg)->toBe(Phrase::pickSentence(PhraseKey::PasswordNotNullable)->toString());
    });
    it('fails the sign in with provider when provider throws the ClientException', function () {
        $route = route('oauth.callback', 'google');
        $exception = makeClientException(uri: $route);

        buildSocialite(
            password: 'whatever',
            exception: $exception
        );
        $responseRedirect = $this->get($route);
        $responseRedirect->assertStatus(302);
        $errorMsg = urldecode(
            Str::after(
                parse_url($responseRedirect->getTargetUrl(), PHP_URL_QUERY),
                'errormsg='
            )
        );
        expect($errorMsg)->toBe(Phrase::pickSentence(PhraseKey::ProviderInvalid)->toString());
    });
});
