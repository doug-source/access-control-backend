<?php

declare(strict_types=1);

use App\Library\Builders\Phrase;
use App\Library\Enums\ColumnSize\UserSize;
use App\Library\Enums\PhraseKey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\User\Strategy\Patch\Plain as PatchPlain;
use App\Library\Converters\Phone as PhoneConverter;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

describe('User update request', function () {
    $patchPlain = new PatchPlain();
    describe('fails because', function () use ($patchPlain) {
        it('has no authentication', function () {
            $this->patchJson(route('user.update'))->assertUnauthorized();
        });
        it('has no name parameter', function () {
            ['token' => $token] = authenticate(scope: $this);
            assertFailedResponse(
                response: $this->patchJson(route('user.update'), [
                    'Authorization' => "Bearer $token"
                ]),
                errorKey: 'name',
                errorMsg: Phrase::pickSentence(PhraseKey::ParameterRequired)
            );
        });
        it('has name parameter overflowing the column size', function () {
            $maxColumnSize = UserSize::NAME->get();
            $name = generateWordBySize(size: $maxColumnSize + 1);
            ['token' => $token] = authenticate(scope: $this);
            assertFailedResponse(
                response: $this->patchJson(route('user.update'), [
                    'Authorization' => "Bearer $token",
                    'name' => $name
                ]),
                errorKey: 'name',
                errorMsg: Phrase::pickSentence(PhraseKey::MaxSizeInvalid, " ({$maxColumnSize})")
            );
        });
        it('has phone parameter overflowing the column size', function () {
            ['token' => $token] = authenticate(scope: $this);
            $response = $this->patchJson(route('user.update', [
                'Authorization' => "Bearer $token",
                'name' => fake()->word(),
                'phone' => 123456789012
            ]));
            $phoneMaxSixe = UserSize::PHONE->get();
            assertFailedResponse($response, 'phone', Phrase::pickSentence(PhraseKey::MaxSizeInvalid, " ({$phoneMaxSixe})"));
        });
        it('has invalid phone parameter', function () {
            ['token' => $token] = authenticate(scope: $this);
            $response = $this->patchJson(route('user.update', [
                'Authorization' => "Bearer $token",
                'name' => fake()->word(),
                'phone' => fake()->word()
            ]));
            assertFailedResponse($response, 'phone', Phrase::pickSentence(PhraseKey::ParameterInvalid));
        });
        it("has invalid photo's mime type parameter", function () use ($patchPlain) {
            Storage::fake('public');
            $invalidFile = UploadedFile::fake()->create(fake()->word() . '.pdf', 500, 'application/pdf');

            ['token' => $token] = authenticate(scope: $this);
            $response = $this->patchJson(route('user.update'), [
                'Authorization' => "Bearer $token",
                'name' => fake()->word(),
                'photo' => $invalidFile
            ]);
            assertFailedResponse($response, 'photo', $patchPlain->buildMimeErrorMessage());
        });
        it("has invalid photo's max size parameter", function () use ($patchPlain) {
            Storage::fake('local');
            $size = 1024 * (1024 * 10);
            $file = UploadedFile::fake()->image('avatar.png')->size($size)->mimeType('image/png');

            ['token' => $token] = authenticate(scope: $this);
            $response = $this->patchJson(route('user.update'), [
                'Authorization' => "Bearer $token",
                'name' => fake()->word(),
                'photo' => $file,
            ]);
            assertFailedResponse($response, 'photo', $patchPlain->buildPhotoMaxErrorMessage());
        });
    });
    describe('succeed because', function () {
        it('update name and phone from user', function () {
            // Storage::fake('local');
            // $size = 1024 * 1024 * 0.5;
            // $file = UploadedFile::fake()->image('avatar.png')->size($size)->mimeType('image/png');
            ['token' => $token, 'user' => $admin] = authenticate(scope: $this);
            $oldName = $admin->name;
            $oldPhone = $admin->phone;
            $this->assertDatabaseHas('users', [
                'id' => $admin->id,
                'name' => $oldName,
                'phone' => $oldPhone,
            ]);
            $newName = fake()->name();
            $newPhone = PhoneConverter::clear(fake()->phoneNumber());
            $this->patchJson(route('user.update'), [
                'Authorization' => "Bearer $token",
                'name' => $newName,
                'phone' => $newPhone,
            ])->assertOk();
            $this->assertDatabaseMissing('users', [
                'id' => $admin->id,
                'name' => $oldName,
                'phone' => $oldPhone,
            ]);
            $this->assertDatabaseHas('users', [
                'id' => $admin->id,
                'name' => $newName,
                'phone' => $newPhone,
            ]);
        });
        it('update photo from user', function () {
            Storage::fake('local');
            $size = 1024;
            $file = UploadedFile::fake()->image('avatar.png')->size($size)->mimeType('image/png');
            ['token' => $token, 'user' => $admin] = authenticate(scope: $this);

            $this->assertDatabaseHas('users', [
                'id' => $admin->id,
                'photo' => NULL,
            ]);
            $this->patchJson(route('user.update'), [
                'Authorization' => "Bearer $token",
                'name' => $admin->name,
                'photo' => $file,
            ])->assertOk();
            $this->assertDatabaseMissing('users', [
                'id' => $admin->id,
                'photo' => NULL,
            ]);
            $this->assertFileExists(
                Storage::disk('local')->path('user-photos/' . $file->hashName())
            );
        });
    });
});
