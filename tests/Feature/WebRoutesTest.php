<?php

use Illuminate\Http\Response;

describe('Web application routes', function () {
    it('returns 404 in route "/"', function () {
        $this->get('/')->assertStatus(Response::HTTP_NOT_FOUND);
    });
    it('returns 404 in whatever route', function () {
        $this->get('/any-route')->assertStatus(Response::HTTP_NOT_FOUND);
    });
});
