<?php

describe('Web application routes', function () {
    it('return 404 in route "/"', function () {
        $response = $this->get('/');
        $response->assertStatus(404);
    });
    it('return 404 in whatever route', function () {
        $response = $this->get('/any-route');
        $response->assertStatus(404);
    });
});
