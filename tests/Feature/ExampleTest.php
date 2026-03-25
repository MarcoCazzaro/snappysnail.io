<?php

it('redirects root to Italian locale', function () {
    $response = $this->get('/');

    $response->assertRedirect('/it');
});

it('returns a successful response on the Italian home', function () {
    $response = $this->get('/it');

    $response->assertOk();
});
