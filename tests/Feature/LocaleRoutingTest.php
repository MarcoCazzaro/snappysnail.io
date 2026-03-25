<?php

it('redirects / to the default locale', function () {
    $this->get('/')->assertRedirect('/'.config('app.locale'));
});

it('redirects /dear-googlebot to the default locale', function () {
    $this->get('/dear-googlebot')->assertRedirect('/'.config('app.locale').'/dear-googlebot');
});

it('serves the Italian home page', function () {
    $this->get('/it')->assertOk()->assertSee('Snappysnail');
});

it('serves the English home page', function () {
    $this->get('/en')->assertOk()->assertSee('Snappysnail');
});

it('sets Italian locale and shows Italian text', function () {
    $this->get('/it')
        ->assertOk()
        ->assertSee('Sviluppo web');
});

it('sets English locale and shows English text', function () {
    $this->get('/en')
        ->assertOk()
        ->assertSee('Web development');
});

it('serves Italian locale pages', function () {
    $this->get('/it/curriculum')->assertOk();
    $this->get('/it/services')->assertOk();
    $this->get('/it/contact')->assertOk();
});

it('serves English locale pages', function () {
    $this->get('/en/curriculum')->assertOk();
    $this->get('/en/services')->assertOk();
    $this->get('/en/contact')->assertOk();
});

it('returns 404 for unknown locale prefix', function () {
    $this->get('/fr/services')->assertNotFound();
});

it('serves the Italian dear-googlebot page', function () {
    $this->get('/it/dear-googlebot')->assertOk();
});

it('sets the html lang attribute to the active locale', function () {
    $this->get('/it')->assertOk()->assertSee('lang="it"', false);
    $this->get('/en')->assertOk()->assertSee('lang="en"', false);
});
