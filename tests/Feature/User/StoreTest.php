<?php

use App\Library\Builders\Phrase;
use App\Library\Enums\PasswordRules;
use App\Library\Enums\PhraseKey;
use App\Library\Enums\ColumnSize\RegisterPermissionSize;
use App\Library\Enums\ColumnSize\UserSize;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\RegisterPermission;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Uri;

uses(RefreshDatabase::class);

describe('User store request', function () {
    describe('fails because', function () {
        it('has no name parameter', function () {
            $response = $this->postJson(route('users.store'));
            assertFailedResponse($response, 'name', Phrase::pickSentence(PhraseKey::ParameterRequired));
        });
        it('has name parameter overflowing the column size', function () {
            $maxSize = UserSize::NAME->get();
            $response = $this->postJson(route('users.store'), [
                'name' => generateWordBySize($maxSize + 1)
            ]);
            assertFailedResponse($response, 'name', Phrase::pickSentence(PhraseKey::MaxSizeInvalid, " ({$maxSize})"));
        });
        it('has no email parameter', function () {
            $response = $this->postJson(route('users.store'), [
                'name' => fake()->name()
            ]);
            assertFailedResponse($response, 'email', Phrase::pickSentence(PhraseKey::ParameterRequired));
        });
        it('has invalid email parameter', function () {
            $response = $this->postJson(route('users.store'), [
                'name' => fake()->name(),
                'email' => 'whatever'
            ]);
            assertFailedResponse($response, 'email', Phrase::pickSentence(PhraseKey::EmailInvalid));
        });
        it('has email parameter overflowing the column size', function () {
            $maxSize = UserSize::EMAIL->get();
            $response = $this->postJson(route('users.store'), [
                'name' => fake()->name(),
                'email' => generateOverflowInvalidEmail($maxSize + 1)
            ]);
            assertFailedResponse($response, 'email', Phrase::pickSentence(PhraseKey::MaxSizeInvalid, " ({$maxSize})"));
        });
        it('has email parameter already included into database table users', function () {
            $email = fake()->email();
            createUserDB(email: $email);
            $response = $this->postJson(route('users.store'), [
                'name' => fake()->name(),
                'email' => $email
            ]);
            assertFailedResponse($response, 'email', Phrase::pickSentence(PhraseKey::EmailInvalid));
        });
        it('has email parameter not included into database table register_permissions', function () {
            $response = $this->postJson(route('users.store'), [
                'name' => fake()->name(),
                'email' => fake()->email()
            ]);
            assertFailedResponse($response, 'email', Phrase::pickSentence(PhraseKey::EmailInvalid));
        });
        it('has no password parameter', function () {
            $permission = RegisterPermission::factory(count: 1)->create()->first();
            $response = $this->postJson(route('users.store'), [
                'name' => fake()->name(),
                'email' => $permission->email
            ]);
            assertFailedResponse($response, 'password', Phrase::pickSentence(PhraseKey::ParameterRequired));
        });
        it('has no password_confirmation parameter', function () {
            $permission = RegisterPermission::factory(count: 1)->create()->first();
            $response = $this->postJson(route('users.store'), [
                'name' => fake()->name(),
                'email' => $permission->email,
                'password' => fake()->password()
            ]);
            assertFailedResponse($response, 'password', Phrase::pickSentence(PhraseKey::PassConfirmInvalid));
        });
        it('has invalid password_confirmation parameter', function () {
            $permission = RegisterPermission::factory(count: 1)->create()->first();
            $response = $this->postJson(route('users.store'), [
                'name' => fake()->name(),
                'email' => $permission->email,
                'password' => fake()->password(),
                'password_confirmation' => 'whatever'
            ]);
            assertFailedResponse($response, 'password', Phrase::pickSentence(PhraseKey::PassConfirmInvalid));
        });
        it('has password with mininum size invalid', function () {
            $permission = RegisterPermission::factory(count: 1)->create()->first();
            $password = 'abc';
            $response = $this->postJson(route('users.store'), [
                'name' => fake()->name(),
                'email' => $permission->email,
                'password' => $password,
                'password_confirmation' => $password
            ]);
            assertFailedResponse($response, 'password', Phrase::pickSentence(PhraseKey::MinSizeInvalid));
        });
        it('has password with maximum size invalid', function () {
            $permission = RegisterPermission::factory(count: 1)->create()->first();
            $initial = 'Aa1!';
            $password = $initial . generateWordBySize((UserSize::PASSWORD->get() - mb_strlen($initial)) + 1);
            $response = $this->postJson(route('users.store'), [
                'name' => fake()->name(),
                'email' => $permission->email,
                'password' => $password,
                'password_confirmation' => $password
            ]);
            assertFailedResponse($response, 'password', Phrase::pickSentence(PhraseKey::MaxSizeInvalid));
        });
        it('has password no letters', function () {
            $permission = RegisterPermission::factory(count: 1)->create()->first();
            $password = '12345678';
            $response = $this->postJson(route('users.store'), [
                'name' => fake()->name(),
                'email' => $permission->email,
                'password' => $password,
                'password_confirmation' => $password
            ]);
            $minQtyLetters = PasswordRules::QtyLetters->get();
            assertFailedResponse($response, 'password', Phrase::pickSentence(PhraseKey::MinLettersInvalid, ": ($minQtyLetters)"));
        });
        it('has password no uppercase letters', function () {
            $permission = RegisterPermission::factory(count: 1)->create()->first();
            $password = 'abcdefgh';
            $response = $this->postJson(route('users.store'), [
                'name' => fake()->name(),
                'email' => $permission->email,
                'password' => $password,
                'password_confirmation' => $password
            ]);
            $minQtyUppercase = PasswordRules::QtyUppercase->get();
            assertFailedResponse($response, 'password', Phrase::pickSentence(PhraseKey::MinUppercaseInvalid, ": ($minQtyUppercase)"));
        });
        it('has password no lowercase letters', function () {
            $permission = RegisterPermission::factory(count: 1)->create()->first();
            $password = 'ABCDEFGH';
            $response = $this->postJson(route('users.store'), [
                'name' => fake()->name(),
                'email' => $permission->email,
                'password' => $password,
                'password_confirmation' => $password
            ]);
            $minQtyLowercase = PasswordRules::QtyLowercase->get();
            assertFailedResponse($response, 'password', Phrase::pickSentence(PhraseKey::MinLowercaseInvalid, ": ($minQtyLowercase)"));
        });
        it('has password no digits', function () {
            $permission = RegisterPermission::factory(count: 1)->create()->first();
            $password = 'ABCDefgh';
            $response = $this->postJson(route('users.store'), [
                'name' => fake()->name(),
                'email' => $permission->email,
                'password' => $password,
                'password_confirmation' => $password
            ]);
            $minQtyDigits = PasswordRules::QtyDigits->get();
            assertFailedResponse($response, 'password', Phrase::pickSentence(PhraseKey::MinDigitsInvalid, ": ($minQtyDigits)"));
        });
        it('has password no specialchars', function () {
            $permission = RegisterPermission::factory(count: 1)->create()->first();
            $password = 'ABCDef12';
            $response = $this->postJson(route('users.store'), [
                'name' => fake()->name(),
                'email' => $permission->email,
                'password' => $password,
                'password_confirmation' => $password
            ]);
            $minQtySpecialChars = PasswordRules::QtySpecialChars->get();
            assertFailedResponse($response, 'password', Phrase::pickSentence(PhraseKey::MinSpecialCharsInvalid, ": ($minQtySpecialChars)"));
        });
        it('has invalid phone parameter', function () {
            $permission = RegisterPermission::factory(count: 1)->create()->first();
            $password = 'ABCDef12@';
            $response = $this->postJson(route('users.store'), [
                'name' => fake()->name(),
                'email' => $permission->email,
                'phone' => '12345a78901',
                'password' => $password,
                'password_confirmation' => $password
            ]);
            assertFailedResponse($response, 'phone', Phrase::pickSentence(PhraseKey::ParameterInvalid));
        });
        it('has phone parameter overflowing the column size', function () {
            $permission = RegisterPermission::factory(count: 1)->create()->first();
            $password = 'ABCDef12@';
            $phoneMaxSize = UserSize::PHONE->get();
            $phone = generateWordBySize(size: $phoneMaxSize + 1, letter: '1');
            $response = $this->postJson(route('users.store'), [
                'name' => fake()->name(),
                'email' => $permission->email,
                'phone' => $phone,
                'password' => $password,
                'password_confirmation' => $password
            ]);
            assertFailedResponse($response, 'phone', Phrase::pickSentence(PhraseKey::MaxSizeInvalid, " ($phoneMaxSize)"));
        });
        it('has no token parameter', function () {
            $permission = RegisterPermission::factory(count: 1)->create()->first();
            $password = 'ABCDef12@';
            $response = $this->postJson(route('users.store'), [
                'name' => fake()->name(),
                'email' => $permission->email,
                'phone' => '12345678901',
                'password' => $password,
                'password_confirmation' => $password
            ]);
            assertFailedResponse($response, 'token', Phrase::pickSentence(PhraseKey::ParameterRequired));
        });
        it('has token parameter overflowing the column size', function () {
            $tokenMaxSize = RegisterPermissionSize::TOKEN->get();
            $permission = RegisterPermission::factory(count: 1)->create()->first();
            $password = 'ABCDef12@';
            $response = $this->postJson(route('users.store'), [
                'name' => fake()->name(),
                'email' => $permission->email,
                'phone' => '12345678901',
                'password' => $password,
                'password_confirmation' => $password,
                'token' => generateWordBySize(size: $tokenMaxSize + 1)
            ]);
            assertFailedResponse($response, 'token', Phrase::pickSentence(PhraseKey::MaxSizeInvalid, " ({$tokenMaxSize})"));
        });
        it('is from register permission instance with expiration_data already expired', function () {
            $permission = RegisterPermission::factory(count: 1)->create([
                'expiration_data' => now()->subtract(DateInterval::createFromDateString('2 day'))
            ])->first();
            $password = 'ABCDef12@';
            $response = $this->postJson(route('users.store'), [
                'name' => fake()->name(),
                'email' => $permission->email,
                'phone' => '12345678901',
                'password' => $password,
                'password_confirmation' => $password,
                'token' => $permission->token
            ]);
            assertFailedResponse($response, 'token', Phrase::pickSentence(PhraseKey::RegistrationExpired));
        });
        it('has invalid token parameter', function () {
            $permission = RegisterPermission::factory(count: 1)->create()->first();
            $password = 'ABCDef12@';
            $uri = Uri::of(route('users.store'))->withQuery([
                'token' => 'whatever'
            ]);
            $response = $this->postJson($uri->value(), [
                'name' => fake()->name(),
                'email' => $permission->email,
                'phone' => '12345678901',
                'password' => $password,
                'password_confirmation' => $password,
                'token' => 'whatever'
            ]);
            assertFailedResponse($response, 'token', Phrase::pickSentence(PhraseKey::ParameterInvalid));
        });
    });
    describe('receives successful because', function () {
        it('has complete parameters', function () {
            Mail::fake();
            $permission = RegisterPermission::factory(count: 1)->create()->first();
            $password = 'ABCDef12@';
            $uri = Uri::of(route('users.store'))->withQuery([
                'token' => $permission->token
            ]);
            $userName = fake()->name();
            $this->postJson($uri->value(), [
                'name' => $userName,
                'email' => $permission->email,
                'phone' => $permission->phone,
                'password' => $password,
                'password_confirmation' => $password,
                'token' => $permission->token
            ])->assertStatus(Response::HTTP_OK);

            $this->assertDatabaseHas('users', [
                'name' => $userName,
                'email' => $permission->email,
            ]);
        });
    });
});
