<?php

use App\Library\Builders\Phrase;
use App\Library\Enums\PhraseKey;
use App\Library\Enums\UserColumnSize;
use Illuminate\Support\Str;

function login($scope)
{
    $password = fake()->password();
    $user = createUserDB(password: $password, email: fake()->email());
    $responseLogin = $scope->postJson(route('auth.login'), [
        'email' => $user->email,
        'password' => $password
    ]);
    return Str::after($responseLogin->baseResponse->original['user']['token'], '|');
}

describe('RegisterRequest request', function () {
    it('fails the register request get method no authentication', function () {
        $response = $this->getJson(route('register.request.fetch'));
        $response->assertStatus(401);
    });
    it('fails the register request get method no page parameter', function () {
        $token = login($this);

        $responseRegReq = $this->getJson(route('register.request.fetch'), [
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json',
        ]);
        $errorMsg = Phrase::pickSentence(PhraseKey::ParameterRequired);
        $responseRegReq
            ->assertStatus(422)
            ->assertJson([
                'message' => $errorMsg,
                'errors' => [
                    'page' => [$errorMsg]
                ]
            ]);
    });
    it('fails the register request get method with invalid page parameter', function () {
        $token = login($this);
        $route = route('register.request.fetch');
        $responseRegReq = $this->getJson("{$route}?page=any", [
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json',
        ]);
        $errorMsg = Phrase::pickSentence(PhraseKey::ParameterInvalid);
        $responseRegReq
            ->assertStatus(422)
            ->assertJson([
                'message' => $errorMsg,
                'errors' => [
                    'page' => [$errorMsg]
                ]
            ]);
    });
    it('fails the register request get method with page parameter lower then minimal size', function () {
        $token = login($this);
        $route = route('register.request.fetch');
        $responseRegReq = $this->getJson("{$route}?page=0", [
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json',
        ]);
        $errorMsg = Str::of(Phrase::pickSentence(PhraseKey::MinSizeInvalid))->append(" (1)");
        $responseRegReq
            ->assertStatus(422)
            ->assertJson([
                'message' => $errorMsg,
                'errors' => [
                    'page' => [$errorMsg]
                ]
            ]);
    });
    it('fails the register request get method no group parameter', function () {
        $token = login($this);
        $route = route('register.request.fetch');
        $responseRegReq = $this->getJson("{$route}?page=1", [
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json',
        ]);
        $errorMsg = Phrase::pickSentence(PhraseKey::ParameterRequired);
        $responseRegReq
            ->assertStatus(422)
            ->assertJson([
                'message' => $errorMsg,
                'errors' => [
                    'group' => [$errorMsg]
                ]
            ]);
    });
    it('fails the register request get method with invalid group parameter', function () {
        $token = login($this);
        $route = route('register.request.fetch');
        $responseRegReq = $this->getJson("{$route}?page=1&group=any", [
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json',
        ]);
        $errorMsg = Phrase::pickSentence(PhraseKey::ParameterInvalid);
        $responseRegReq
            ->assertStatus(422)
            ->assertJson([
                'message' => $errorMsg,
                'errors' => [
                    'group' => [$errorMsg]
                ]
            ]);
    });
    it('fails the register request get method with group parameter lower then minimal size', function () {
        $token = login($this);
        $route = route('register.request.fetch');
        $responseRegReq = $this->getJson("{$route}?page=1&group=0", [
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json',
        ]);
        $errorMsg = Str::of(Phrase::pickSentence(PhraseKey::MinSizeInvalid))->append(" (1)");
        $responseRegReq
            ->assertStatus(422)
            ->assertJson([
                'message' => $errorMsg,
                'errors' => [
                    'group' => [$errorMsg]
                ]
            ]);
    });
    it('fails the register request get method with email parameter greater then maximun size', function () {
        $sufix = Str::of('@test.com');
        $size = UserColumnSize::EMAIL->value - $sufix->length() + 1;
        $email = $sufix->prepend(str_repeat('a', $size));

        $token = login($this);
        $route = route('register.request.fetch');
        $qs = http_build_query([
            'page' => 1,
            'group' => 1,
            'email' => $email->toString()
        ]);

        $responseRegReq = $this->getJson("{$route}?{$qs}", [
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json',
        ]);
        $emailColumnSize = UserColumnSize::EMAIL->value;
        $errorMsg = Str::of(Phrase::pickSentence(PhraseKey::MaxSizeInvalid))->append(" ({$emailColumnSize})");
        $responseRegReq
            ->assertStatus(422)
            ->assertJson([
                'message' => $errorMsg,
                'errors' => [
                    'email' => [$errorMsg]
                ]
            ]);
    });
    it('receives successful register request output with complete parameters', function () {
        $token = login($this);
        $route = route('register.request.fetch');
        $qs = http_build_query([
            'page' => 1,
            'group' => 1,
            'email' => fake()->email()
        ]);
        $this->getJson("{$route}?{$qs}", [
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json',
        ])->assertStatus(200);
    });
    it('receives successful register request output no email parameter', function () {
        $token = login($this);
        $route = route('register.request.fetch');
        $qs = http_build_query([
            'page' => 1,
            'group' => 1,
        ]);
        $this->getJson("{$route}?{$qs}", [
            'Authorization' => "Bearer {$token}",
            'Accept' => 'application/json',
        ])->assertStatus(200);
    });
});
