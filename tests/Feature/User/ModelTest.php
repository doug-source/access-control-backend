<?php

use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('UserModel', function () {
    describe('initialize correctly because', function () {
        it('stores into database', function () {
            $user = User::factory(count: 1)->create()->first();
            $this->assertDatabaseHas('users', [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'password' => $user->password
            ]);
        });
        it('persists an user with created_at_formatted property on pt_BR locale correctly', function () {
            App::setLocale('pt_BR');
            $date = new DateTimeImmutable();
            $user = User::factory(count: 1)->create()->first();
            expect($user->created_at_formatted)->toBe($date->format('d/m/Y'));
        });
        it('persists an user with created_at_formatted property on en_US locale correctly', function () {
            App::setLocale('en_US');
            $date = new DateTimeImmutable();
            $user = User::factory(count: 1)->create()->first();
            expect($user->created_at_formatted)->toBe($date->format('m/d/Y'));
        });
        it('persists an user with updated_at_formatted property on pt_BR locale correctly', function () {
            App::setLocale('pt_BR');
            $date = new DateTimeImmutable();
            $user = User::factory(count: 1)->create()->first();
            expect($user->updated_at_formatted)->toBe($date->format('d/m/Y'));
        });
        it('persists an user with updated_at_formatted property on en_US locale correctly', function () {
            App::setLocale('en_US');
            $date = new DateTimeImmutable();
            $user = User::factory(count: 1)->create()->first();
            expect($user->updated_at_formatted)->toBe($date->format('m/d/Y'));
        });
    });
});
