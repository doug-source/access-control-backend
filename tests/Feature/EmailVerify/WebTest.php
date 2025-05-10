<?php

use App\Library\Builders\UrlExternal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;

uses(RefreshDatabase::class);

describe('Email Verification from web routes', function () {
    describe('receives successful because', function () {
        it('calls the route returning redirecting response', function () {
            $this->get(route('login'))
                ->assertStatus(Response::HTTP_FOUND)
                ->assertRedirect(
                    UrlExternal::build()->value()
                );
        });
    });
});
