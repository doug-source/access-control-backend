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
            assertFailedResponse(
                response: $this->postJson(route('users.store')),
                errorKey: 'name',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterRequired)
            );
        });
        it('has name parameter overflowing the column size', function () {
            $maxSize = UserSize::NAME->get();
            assertFailedResponse(
                response: $this->postJson(route('users.store'), [
                    'name' => generateWordBySize($maxSize + 1)
                ]),
                errorKey: 'name',
                errorMsg: Phrase::pickSentence(PhraseKey::MaxSizeInvalid, " ({$maxSize})")
            );
        });
        it('has no email parameter', function () {
            assertFailedResponse(
                response: $this->postJson(route('users.store'), [
                    'name' => fake()->name()
                ]),
                errorKey: 'email',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterRequired)
            );
        });
        it('has invalid email parameter', function () {
            assertFailedResponse(
                response: $this->postJson(route('users.store'), [
                    'name' => fake()->name(),
                    'email' => 'whatever'
                ]),
                errorKey: 'email',
                errorMsg: Phrase::pickSentence(PhraseKey::EmailInvalid)
            );
        });
        it('has email parameter overflowing the column size', function () {
            $maxSize = UserSize::EMAIL->get();
            assertFailedResponse(
                response: $this->postJson(route('users.store'), [
                    'name' => fake()->name(),
                    'email' => generateOverflowInvalidEmail($maxSize + 1)
                ]),
                errorKey: 'email',
                errorMsg: Phrase::pickSentence(PhraseKey::MaxSizeInvalid, " ({$maxSize})")
            );
        });
        it('has email parameter already included into database table users', function () {
            $email = fake()->email();
            createUserDB(email: $email);
            assertFailedResponse(
                response: $this->postJson(route('users.store'), [
                    'name' => fake()->name(),
                    'email' => $email
                ]),
                errorKey: 'email',
                errorMsg: Phrase::pickSentence(PhraseKey::EmailInvalid)
            );
        });
        it('has email parameter not included into database table register_permissions', function () {
            assertFailedResponse(
                response: $this->postJson(route('users.store'), [
                    'name' => fake()->name(),
                    'email' => fake()->email()
                ]),
                errorKey: 'email',
                errorMsg: Phrase::pickSentence(PhraseKey::EmailInvalid)
            );
        });
        it('has no password parameter', function () {
            $permission = RegisterPermission::factory(count: 1)->create()->first();
            assertFailedResponse(
                response: $this->postJson(route('users.store'), [
                    'name' => fake()->name(),
                    'email' => $permission->email
                ]),
                errorKey: 'password',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterRequired)
            );
        });
        it('has no password_confirmation parameter', function () {
            $permission = RegisterPermission::factory(count: 1)->create()->first();
            assertFailedResponse(
                response: $this->postJson(route('users.store'), [
                    'name' => fake()->name(),
                    'email' => $permission->email,
                    'password' => fake()->password()
                ]),
                errorKey: 'password',
                errorMsg: Phrase::pickSentence(PhraseKey::PassConfirmInvalid)
            );
        });
        it('has invalid password_confirmation parameter', function () {
            $permission = RegisterPermission::factory(count: 1)->create()->first();
            assertFailedResponse(
                response: $this->postJson(route('users.store'), [
                    'name' => fake()->name(),
                    'email' => $permission->email,
                    'password' => fake()->password(),
                    'password_confirmation' => 'whatever'
                ]),
                errorKey: 'password',
                errorMsg: Phrase::pickSentence(PhraseKey::PassConfirmInvalid)
            );
        });
        it('has password with mininum size invalid', function () {
            $permission = RegisterPermission::factory(count: 1)->create()->first();
            $password = 'abc';
            assertFailedResponse(
                response: $this->postJson(route('users.store'), [
                    'name' => fake()->name(),
                    'email' => $permission->email,
                    'password' => $password,
                    'password_confirmation' => $password
                ]),
                errorKey: 'password',
                errorMsg: Phrase::pickSentence(PhraseKey::MinSizeInvalid)
            );
        });
        it('has password with maximum size invalid', function () {
            $permission = RegisterPermission::factory(count: 1)->create()->first();
            $initial = 'Aa1!';
            $password = $initial . generateWordBySize((UserSize::PASSWORD->get() - mb_strlen($initial)) + 1);
            assertFailedResponse(
                response: $this->postJson(route('users.store'), [
                    'name' => fake()->name(),
                    'email' => $permission->email,
                    'password' => $password,
                    'password_confirmation' => $password
                ]),
                errorKey: 'password',
                errorMsg: Phrase::pickSentence(PhraseKey::MaxSizeInvalid)
            );
        });
        it('has password no letters', function () {
            $permission = RegisterPermission::factory(count: 1)->create()->first();
            $password = '12345678';
            $minQtyLetters = PasswordRules::QtyLetters->get();
            assertFailedResponse(
                response: $this->postJson(route('users.store'), [
                    'name' => fake()->name(),
                    'email' => $permission->email,
                    'password' => $password,
                    'password_confirmation' => $password
                ]),
                errorKey: 'password',
                errorMsg: Phrase::pickSentence(PhraseKey::MinLettersInvalid, ": ($minQtyLetters)")
            );
        });
        it('has password no uppercase letters', function () {
            $permission = RegisterPermission::factory(count: 1)->create()->first();
            $password = 'abcdefgh';
            $minQtyUppercase = PasswordRules::QtyUppercase->get();
            assertFailedResponse(
                response: $this->postJson(route('users.store'), [
                    'name' => fake()->name(),
                    'email' => $permission->email,
                    'password' => $password,
                    'password_confirmation' => $password
                ]),
                errorKey: 'password',
                errorMsg: Phrase::pickSentence(PhraseKey::MinUppercaseInvalid, ": ($minQtyUppercase)")
            );
        });
        it('has password no lowercase letters', function () {
            $permission = RegisterPermission::factory(count: 1)->create()->first();
            $password = 'ABCDEFGH';
            $minQtyLowercase = PasswordRules::QtyLowercase->get();
            assertFailedResponse(
                response: $this->postJson(route('users.store'), [
                    'name' => fake()->name(),
                    'email' => $permission->email,
                    'password' => $password,
                    'password_confirmation' => $password
                ]),
                errorKey: 'password',
                errorMsg: Phrase::pickSentence(PhraseKey::MinLowercaseInvalid, ": ($minQtyLowercase)")
            );
        });
        it('has password no digits', function () {
            $permission = RegisterPermission::factory(count: 1)->create()->first();
            $password = 'ABCDefgh';
            $minQtyDigits = PasswordRules::QtyDigits->get();
            assertFailedResponse(
                response: $this->postJson(route('users.store'), [
                    'name' => fake()->name(),
                    'email' => $permission->email,
                    'password' => $password,
                    'password_confirmation' => $password
                ]),
                errorKey: 'password',
                errorMsg: Phrase::pickSentence(PhraseKey::MinDigitsInvalid, ": ($minQtyDigits)")
            );
        });
        it('has password no specialchars', function () {
            $permission = RegisterPermission::factory(count: 1)->create()->first();
            $password = 'ABCDef12';
            $minQtySpecialChars = PasswordRules::QtySpecialChars->get();
            assertFailedResponse(
                response: $this->postJson(route('users.store'), [
                    'name' => fake()->name(),
                    'email' => $permission->email,
                    'password' => $password,
                    'password_confirmation' => $password
                ]),
                errorKey: 'password',
                errorMsg: Phrase::pickSentence(PhraseKey::MinSpecialCharsInvalid, ": ($minQtySpecialChars)")
            );
        });
        it('has invalid phone parameter', function () {
            $permission = RegisterPermission::factory(count: 1)->create()->first();
            $password = 'ABCDef12@';
            assertFailedResponse(
                response: $this->postJson(route('users.store'), [
                    'name' => fake()->name(),
                    'email' => $permission->email,
                    'phone' => '12345a78901',
                    'password' => $password,
                    'password_confirmation' => $password
                ]),
                errorKey: 'phone',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterInvalid)
            );
        });
        it('has phone parameter overflowing the column size', function () {
            $permission = RegisterPermission::factory(count: 1)->create()->first();
            $password = 'ABCDef12@';
            $phoneMaxSize = UserSize::PHONE->get();
            $phone = generateWordBySize(size: $phoneMaxSize + 1, letter: '1');
            assertFailedResponse(
                response: $this->postJson(route('users.store'), [
                    'name' => fake()->name(),
                    'email' => $permission->email,
                    'phone' => $phone,
                    'password' => $password,
                    'password_confirmation' => $password
                ]),
                errorKey: 'phone',
                errorMsg: Phrase::pickSentence(PhraseKey::MaxSizeInvalid, " ($phoneMaxSize)")
            );
        });
        it('has no token parameter', function () {
            $permission = RegisterPermission::factory(count: 1)->create()->first();
            $password = 'ABCDef12@';
            assertFailedResponse(
                response: $this->postJson(route('users.store'), [
                    'name' => fake()->name(),
                    'email' => $permission->email,
                    'phone' => '12345678901',
                    'password' => $password,
                    'password_confirmation' => $password
                ]),
                errorKey: 'token',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterRequired)
            );
        });
        it('has token parameter overflowing the column size', function () {
            $tokenMaxSize = RegisterPermissionSize::TOKEN->get();
            $permission = RegisterPermission::factory(count: 1)->create()->first();
            $password = 'ABCDef12@';
            assertFailedResponse(
                response: $this->postJson(route('users.store'), [
                    'name' => fake()->name(),
                    'email' => $permission->email,
                    'phone' => '12345678901',
                    'password' => $password,
                    'password_confirmation' => $password,
                    'token' => generateWordBySize(size: $tokenMaxSize + 1)
                ]),
                errorKey: 'token',
                errorMsg: Phrase::pickSentence(PhraseKey::MaxSizeInvalid, " ({$tokenMaxSize})")
            );
        });
        it('is from register permission instance with expiration_data already expired', function () {
            $permission = RegisterPermission::factory(count: 1)->create([
                'expiration_data' => now()->subtract(DateInterval::createFromDateString('2 day'))
            ])->first();
            $password = 'ABCDef12@';
            assertFailedResponse(
                response: $this->postJson(route('users.store'), [
                    'name' => fake()->name(),
                    'email' => $permission->email,
                    'phone' => '12345678901',
                    'password' => $password,
                    'password_confirmation' => $password,
                    'token' => $permission->token
                ]),
                errorKey: 'token',
                errorMsg: Phrase::pickSentence(PhraseKey::RegistrationExpired)
            );
        });
        it('has invalid token parameter', function () {
            $permission = RegisterPermission::factory(count: 1)->create()->first();
            $password = 'ABCDef12@';
            $uri = Uri::of(route('users.store'))->withQuery([
                'token' => 'whatever'
            ]);
            assertFailedResponse(
                response: $this->postJson($uri->value(), [
                    'name' => fake()->name(),
                    'email' => $permission->email,
                    'phone' => '12345678901',
                    'password' => $password,
                    'password_confirmation' => $password,
                    'token' => 'whatever'
                ]),
                errorKey: 'token',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterInvalid)
            );
        });
    });
    describe('receives successful because', function () {
        it('has complete parameters', function () {
            Mail::fake();
            $permission = RegisterPermission::factory(count: 1)->create()->first();
            $password = 'ABCDef12@';
            $uri = Uri::of(route('users.store'))->withQuery([
                'token' => $permission->token
            ])->value();
            $userName = fake()->name();
            $this->postJson($uri, [
                'name' => $userName,
                'email' => $permission->email,
                'phone' => $permission->phone,
                'password' => $password,
                'password_confirmation' => $password,
                'token' => $permission->token
            ])->assertStatus(Response::HTTP_CREATED);

            $this->assertDatabaseHas('users', [
                'name' => $userName,
                'email' => $permission->email,
            ]);
        });
    });
});
