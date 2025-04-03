<?php

use App\Models\Enterprise;
use App\Models\User;
use Illuminate\Support\Facades\App;

describe('UserModel', function () {
    it('initialize correctly', function () {
        $enterpriseList = Enterprise::factory(count: 1)->create();
        $userList = User::factory(count: 1)->create(['enterprises_id' => $enterpriseList->first()->id]);
        expect($userList->isEmpty())->toBe(FALSE);
    });
    it('initialize with id equal 1 correctly', function () {
        $enterpriseList = Enterprise::factory(count: 1)->create();
        $userList = User::factory(count: 1)->create(['enterprises_id' => $enterpriseList->first()->id]);
        expect($userList->first()->id)->toBe(1);
    });
    it('initialize with name property correctly', function () {
        $enterpriseList = Enterprise::factory(count: 1)->create();
        $userList = User::factory(count: 1)->create(['enterprises_id' => $enterpriseList->first()->id]);
        expect($userList->first()->name)->not()->toBe(NULL);
    });
    it('initialize with email property correctly', function () {
        $enterpriseList = Enterprise::factory(count: 1)->create();
        $userList = User::factory(count: 1)->create(['enterprises_id' => $enterpriseList->first()->id]);
        expect($userList->first()->email)->not()->toBe(NULL);
    });
    it('initialize with password property correctly', function () {
        $enterpriseList = Enterprise::factory(count: 1)->create();
        $userList = User::factory(count: 1)->create(['enterprises_id' => $enterpriseList->first()->id]);
        expect($userList->first()->password)->not()->toBe(NULL);
    });
    it('initialize with email_verified_at_formatted property on pt_BR locale correctly', function () {
        App::setLocale('pt_BR');
        $date = new DateTimeImmutable();
        $enterpriseList = Enterprise::factory(count: 1)->create();
        $userList = User::factory(count: 1)->create(['enterprises_id' => $enterpriseList->first()->id]);
        expect($userList->first()->email_verified_at_formatted)->toBe($date->format('d/m/Y'));
    });
    it('initialize with email_verified_at_formatted property on en_US locale correctly', function () {
        App::setLocale('en_US');
        $date = new DateTimeImmutable();
        $enterpriseList = Enterprise::factory(count: 1)->create();
        $userList = User::factory(count: 1)->create(['enterprises_id' => $enterpriseList->first()->id]);
        expect($userList->first()->email_verified_at_formatted)->toBe($date->format('m/d/Y'));
    });
    it('initialize with created_at_formatted property on pt_BR locale correctly', function () {
        App::setLocale('pt_BR');
        $date = new DateTimeImmutable();
        $enterpriseList = Enterprise::factory(count: 1)->create();
        $userList = User::factory(count: 1)->create(['enterprises_id' => $enterpriseList->first()->id]);
        expect($userList->first()->created_at_formatted)->toBe($date->format('d/m/Y'));
    });
    it('initialize with created_at_formatted property on en_US locale correctly', function () {
        App::setLocale('en_US');
        $date = new DateTimeImmutable();
        $enterpriseList = Enterprise::factory(count: 1)->create();
        $userList = User::factory(count: 1)->create(['enterprises_id' => $enterpriseList->first()->id]);
        expect($userList->first()->created_at_formatted)->toBe($date->format('m/d/Y'));
    });
    it('initialize with updated_at_formatted property on pt_BR locale correctly', function () {
        App::setLocale('pt_BR');
        $date = new DateTimeImmutable();
        $enterpriseList = Enterprise::factory(count: 1)->create();
        $userList = User::factory(count: 1)->create(['enterprises_id' => $enterpriseList->first()->id]);
        expect($userList->first()->updated_at_formatted)->toBe($date->format('d/m/Y'));
    });
    it('initialize with updated_at_formatted property on en_US locale correctly', function () {
        App::setLocale('en_US');
        $date = new DateTimeImmutable();
        $enterpriseList = Enterprise::factory(count: 1)->create();
        $userList = User::factory(count: 1)->create(['enterprises_id' => $enterpriseList->first()->id]);
        expect($userList->first()->updated_at_formatted)->toBe($date->format('m/d/Y'));
    });
});
