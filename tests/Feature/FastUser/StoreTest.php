<?php

use App\Library\Builders\Phrase;
use App\Library\Enums\PasswordRules;
use App\Library\Enums\PhraseKey;
use App\Library\Enums\ColumnSize\UserSize;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;

uses(RefreshDatabase::class);

describe('Fast User store request', function () {
    describe('fails because', function () {
        it('has no authentication', function () {
            $response = $this->postJson(route('user.fast.store'));
            $response->assertUnauthorized();
        });
        it('has user no super-admin role authenticated', function () {
            $password = '1234Abcd!';
            ['token' => $token] = authenticate(scope: $this);
            $response = $this->postJson(route('user.fast.store'), [
                'Authorization' => "Bearer $token",
                'name' => fake()->name(),
                'email' => 'test@test.com',
                'password' => $password,
                'password_confirmation' => $password,
            ]);
            $response->assertForbidden();
        });
        it('has no name parameter', function () {
            ['token' => $token] = authenticate(scope: $this);
            assertFailedResponse(
                response: $this->postJson(route('user.fast.store'), [
                    'Authorization' => "Bearer $token"
                ]),
                errorKey: 'name',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterRequired)
            );
        });
        it('has name parameter overflowing the column size', function () {
            ['token' => $token] = authenticate(scope: $this);
            $maxSize = UserSize::NAME->get();
            assertFailedResponse(
                response: $this->postJson(route('user.fast.store'), [
                    'Authorization' => "Bearer $token",
                    'name' => generateWordBySize($maxSize + 1)
                ]),
                errorKey: 'name',
                errorMsg: Phrase::pickSentence(PhraseKey::MaxSizeInvalid, " ({$maxSize})")
            );
        });
        it('has no email parameter', function () {
            ['token' => $token] = authenticate(scope: $this);
            assertFailedResponse(
                response: $this->postJson(route('user.fast.store'), [
                    'Authorization' => "Bearer $token",
                    'name' => fake()->name()
                ]),
                errorKey: 'email',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterRequired)
            );
        });
        it('has invalid email parameter', function () {
            ['token' => $token] = authenticate(scope: $this);
            assertFailedResponse(
                response: $this->postJson(route('user.fast.store'), [
                    'Authorization' => "Bearer $token",
                    'name' => fake()->name(),
                    'email' => 'whatever'
                ]),
                errorKey: 'email',
                errorMsg: Phrase::pickSentence(PhraseKey::EmailInvalid)
            );
        });
        it('has email parameter overflowing the column size', function () {
            ['token' => $token] = authenticate(scope: $this);
            $maxSize = UserSize::EMAIL->get();
            assertFailedResponse(
                response: $this->postJson(route('user.fast.store'), [
                    'Authorization' => "Bearer $token",
                    'name' => fake()->name(),
                    'email' => generateOverflowInvalidEmail($maxSize + 1)
                ]),
                errorKey: 'email',
                errorMsg: Phrase::pickSentence(PhraseKey::MaxSizeInvalid, " ({$maxSize})")
            );
        });
        it('has email parameter already included into database table users', function () {
            ['token' => $token] = authenticate(scope: $this);
            $email = fake()->email();
            createUserDB(email: $email);
            assertFailedResponse(
                response: $this->postJson(route('user.fast.store'), [
                    'Authorization' => "Bearer $token",
                    'name' => fake()->name(),
                    'email' => $email
                ]),
                errorKey: 'email',
                errorMsg: Phrase::pickSentence(PhraseKey::EmailInvalid)
            );
        });
        it('has no password parameter', function () {
            ['token' => $token] = authenticate(scope: $this);
            assertFailedResponse(
                response: $this->postJson(route('user.fast.store'), [
                    'Authorization' => "Bearer $token",
                    'name' => fake()->name(),
                    'email' => fake()->email()
                ]),
                errorKey: 'password',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterRequired)
            );
        });
        it('has no password_confirmation parameter', function () {
            ['token' => $token] = authenticate(scope: $this);
            assertFailedResponse(
                response: $this->postJson(route('user.fast.store'), [
                    'Authorization' => "Bearer $token",
                    'name' => fake()->name(),
                    'email' => fake()->email(),
                    'password' => fake()->password()
                ]),
                errorKey: 'password',
                errorMsg: Phrase::pickSentence(PhraseKey::PassConfirmInvalid)
            );
        });
        it('has invalid password_confirmation parameter', function () {
            ['token' => $token] = authenticate(scope: $this);
            assertFailedResponse(
                response: $this->postJson(route('user.fast.store'), [
                    'Authorization' => "Bearer $token",
                    'name' => fake()->name(),
                    'email' => fake()->email(),
                    'password' => fake()->password(),
                    'password_confirmation' => 'whatever'
                ]),
                errorKey: 'password',
                errorMsg: Phrase::pickSentence(PhraseKey::PassConfirmInvalid)
            );
        });
        it('has password with mininum size invalid', function () {
            ['token' => $token] = authenticate(scope: $this);
            $password = 'abc';
            assertFailedResponse(
                response: $this->postJson(route('user.fast.store'), [
                    'Authorization' => "Bearer $token",
                    'name' => fake()->name(),
                    'email' => fake()->email(),
                    'password' => $password,
                    'password_confirmation' => $password
                ]),
                errorKey: 'password',
                errorMsg: Phrase::pickSentence(PhraseKey::MinSizeInvalid)
            );
        });
        it('has password with maximum size invalid', function () {
            ['token' => $token] = authenticate(scope: $this);
            $initial = 'Aa1!';
            $password = $initial . generateWordBySize((UserSize::PASSWORD->get() - mb_strlen($initial)) + 1);
            assertFailedResponse(
                response: $this->postJson(route('user.fast.store'), [
                    'Authorization' => "Bearer $token",
                    'name' => fake()->name(),
                    'email' => fake()->email(),
                    'password' => $password,
                    'password_confirmation' => $password
                ]),
                errorKey: 'password',
                errorMsg: Phrase::pickSentence(PhraseKey::MaxSizeInvalid)
            );
        });
        it('has password no letters', function () {
            ['token' => $token] = authenticate(scope: $this);
            $password = '12345678';
            $minQtyLetters = PasswordRules::QtyLetters->get();
            assertFailedResponse(
                response: $this->postJson(route('user.fast.store'), [
                    'Authorization' => "Bearer $token",
                    'name' => fake()->name(),
                    'email' => fake()->email(),
                    'password' => $password,
                    'password_confirmation' => $password
                ]),
                errorKey: 'password',
                errorMsg: Phrase::pickSentence(PhraseKey::MinLettersInvalid, ": ($minQtyLetters)")
            );
        });
        it('has password no uppercase letters', function () {
            ['token' => $token] = authenticate(scope: $this);
            $password = 'abcdefgh';
            $minQtyUppercase = PasswordRules::QtyUppercase->get();
            assertFailedResponse(
                response: $this->postJson(route('user.fast.store'), [
                    'Authorization' => "Bearer $token",
                    'name' => fake()->name(),
                    'email' => fake()->email(),
                    'password' => $password,
                    'password_confirmation' => $password
                ]),
                errorKey: 'password',
                errorMsg: Phrase::pickSentence(PhraseKey::MinUppercaseInvalid, ": ($minQtyUppercase)")
            );
        });
        it('has password no lowercase letters', function () {
            ['token' => $token] = authenticate(scope: $this);
            $password = 'ABCDEFGH';
            $minQtyLowercase = PasswordRules::QtyLowercase->get();
            assertFailedResponse(
                response: $this->postJson(route('user.fast.store'), [
                    'Authorization' => "Bearer $token",
                    'name' => fake()->name(),
                    'email' => fake()->email(),
                    'password' => $password,
                    'password_confirmation' => $password
                ]),
                errorKey: 'password',
                errorMsg: Phrase::pickSentence(PhraseKey::MinLowercaseInvalid, ": ($minQtyLowercase)")
            );
        });
        it('has password no digits', function () {
            ['token' => $token] = authenticate(scope: $this);
            $password = 'ABCDefgh';
            $minQtyDigits = PasswordRules::QtyDigits->get();
            assertFailedResponse(
                response: $this->postJson(route('user.fast.store'), [
                    'Authorization' => "Bearer $token",
                    'name' => fake()->name(),
                    'email' => fake()->email(),
                    'password' => $password,
                    'password_confirmation' => $password
                ]),
                errorKey: 'password',
                errorMsg: Phrase::pickSentence(PhraseKey::MinDigitsInvalid, ": ($minQtyDigits)")
            );
        });
        it('has password no specialchars', function () {
            ['token' => $token] = authenticate(scope: $this);
            $password = 'ABCDef12';
            $minQtySpecialChars = PasswordRules::QtySpecialChars->get();
            assertFailedResponse(
                response: $this->postJson(route('user.fast.store'), [
                    'Authorization' => "Bearer $token",
                    'name' => fake()->name(),
                    'email' => fake()->email(),
                    'password' => $password,
                    'password_confirmation' => $password
                ]),
                errorKey: 'password',
                errorMsg: Phrase::pickSentence(PhraseKey::MinSpecialCharsInvalid, ": ($minQtySpecialChars)")
            );
        });
        it('has invalid phone parameter', function () {
            ['token' => $token] = authenticate(scope: $this);
            $password = 'ABCDef12@';
            assertFailedResponse(
                response: $this->postJson(route('user.fast.store'), [
                    'Authorization' => "Bearer $token",
                    'name' => fake()->name(),
                    'email' => fake()->email(),
                    'phone' => '12345a78901',
                    'password' => $password,
                    'password_confirmation' => $password
                ]),
                errorKey: 'phone',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterInvalid)
            );
        });
        it('has phone parameter overflowing the column size', function () {
            ['token' => $token] = authenticate(scope: $this);
            $password = 'ABCDef12@';
            $phoneMaxSize = UserSize::PHONE->get();
            $phone = generateWordBySize(size: $phoneMaxSize + 1, letter: '1');
            assertFailedResponse(
                response: $this->postJson(route('user.fast.store'), [
                    'Authorization' => "Bearer $token",
                    'name' => fake()->name(),
                    'email' => fake()->email(),
                    'phone' => $phone,
                    'password' => $password,
                    'password_confirmation' => $password
                ]),
                errorKey: 'phone',
                errorMsg: Phrase::pickSentence(PhraseKey::MaxSizeInvalid, " ($phoneMaxSize)")
            );
        });
    });
    describe('receives successful because', function () {
        it('has complete parameters', function () {
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);

            $password = 'ABCDef12@';
            $userName = fake()->name();
            $userEmail = fake()->email();
            $phone = '12345678901';
            $this->postJson(route('user.fast.store'), [
                'Authorization' => "Bearer $token",
                'name' => $userName,
                'email' => $userEmail,
                'phone' => $phone,
                'password' => $password,
                'password_confirmation' => $password,
            ])->assertStatus(Response::HTTP_CREATED);

            $this->assertDatabaseHas('users', [
                'name' => $userName,
                'email' => $userEmail,
            ]);
        });
    });
});
