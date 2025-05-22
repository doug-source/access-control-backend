<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('User Index from api routes', function () {
    describe('fails because', function () {
        it('has no authentication', function () {
            $this->getJson(route('user.index'))->assertUnauthorized();
        });
        it('has user no super-admin role authenticated', function () {
            ['token' => $token] = authenticate(scope: $this);
            $this->getJson(route('user.index'), [
                'Authorization' => "Bearer $token"
            ])
                ->assertForbidden();
        });
    });
    describe('succeed because', function () {
        it('has user super-admin authenticated', function () {
            ['token' => $token, 'user' => $user] = authenticate(scope: $this);
            createSuperAdminRelationship($user);
            $userList = [
                $user,
                User::factory(count: 1)->createOne()
            ];

            $this->getJson(route('user.index'), [
                'Authorization' => "Bearer $token"
            ])
                ->assertJson(function (AssertableJson $json) use (&$userList) {
                    $json = $json->has('data', sizeof($userList));
                    foreach ($userList as $i => $user) {
                        $json = $json
                            ->has("data.{$i}", function (AssertableJson $json) use ($user) {
                                $data = $user->ui;
                                $json
                                    ->where('id', $data['id'])
                                    ->where('name', $data['name'])
                                    ->where('email', $data['email'])
                                    ->where('phone', $data['phone'])
                                    ->where('emailVerifiedAt', $data['emailVerifiedAt'])
                                    ->where('createdAt', $data['createdAt'])
                                    ->where('updatedAt', $data['updatedAt'])
                                    ->etc();
                            });
                    }
                    $json->etc();
                });
        });
    });
});
