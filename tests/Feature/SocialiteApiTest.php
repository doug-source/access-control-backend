<?php

use App\Models\Provider;
use Illuminate\Support\Str;

describe('Socialite from api namespace', function () {
    it('fails the token release when user is not logged', function () {
        $response = $this->postJson(route('release.token'));
        $response->assertStatus(401);
    });
    it('releases token successfully when user sends the valid token', function () {
        $user = buildSocialite(password: NULL, email: 'someone@test.com');
        Provider::factory(count: 1)->create([
            'user_id' => $user->id
        ])->first();
        $responseRedirect = $this->get(route('oauth.callback', 'google'));
        $token = Str::replaceMatches(
            pattern: '|^\d+\||',
            replace: '',
            subject: urldecode(
                Str::after(
                    parse_url($responseRedirect->getTargetUrl(), PHP_URL_QUERY),
                    'provided='
                )
            )
        );
        $response = $this->postJson(route('release.token'), [], [
            'Authorization' => "Bearer {$token}"
        ]);
        $response->assertStatus(200);
    });
});
