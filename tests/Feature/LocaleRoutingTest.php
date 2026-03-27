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
        ->assertSee('Web development');
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

it('redirects bare paths to the default locale', function () {
    $this->get('/portfolio')->assertRedirect('/'.config('app.locale').'/portfolio');
});

it('redirects bare paths to the session locale when set', function () {
    $this->withSession(['locale' => 'it'])
        ->get('/portfolio')
        ->assertRedirect('/it/portfolio');
});

it('does not redirect bare locale names via catch-all', function () {
    $this->get('/en')->assertOk();
    $this->get('/it')->assertOk();
});

it('serves the Italian dear-googlebot page', function () {
    $this->get('/it/dear-googlebot')->assertOk();
});

it('sets the html lang attribute to the active locale', function () {
    $this->get('/it')->assertOk()->assertSee('lang="it"', false);
    $this->get('/en')->assertOk()->assertSee('lang="en"', false);
});

it('outputs hreflang tags on the Italian home page', function () {
    $response = $this->get('/it')->assertOk();

    $response->assertSee('hreflang="it"', false);
    $response->assertSee('hreflang="en"', false);
    $response->assertSee('hreflang="x-default"', false);
});

it('outputs hreflang tags on the English home page', function () {
    $response = $this->get('/en')->assertOk();

    $response->assertSee('hreflang="it"', false);
    $response->assertSee('hreflang="en"', false);
    $response->assertSee('hreflang="x-default"', false);
});

it('hreflang alternate URLs point to correct locale on root pages', function () {
    $response = $this->get('/it')->assertOk();

    $response->assertSee('hreflang="it" href="' . url('it') . '"', false);
    $response->assertSee('hreflang="en" href="' . url('en') . '"', false);
});

it('hreflang alternate URLs swap locale prefix on subpages', function (string $locale, string $page) {
    $response = $this->get("/{$locale}/{$page}")->assertOk();

    $response->assertSee('hreflang="it" href="' . url("it/{$page}") . '"', false);
    $response->assertSee('hreflang="en" href="' . url("en/{$page}") . '"', false);
})->with([
    'it curriculum' => ['it', 'curriculum'],
    'en curriculum' => ['en', 'curriculum'],
    'it services'   => ['it', 'services'],
    'en services'   => ['en', 'services'],
    'it contact'    => ['it', 'contact'],
    'en contact'    => ['en', 'contact'],
]);

it('x-default hreflang always points to the Italian URL', function (string $locale) {
    $response = $this->get("/{$locale}/curriculum")->assertOk();

    $response->assertSee('hreflang="x-default" href="' . url('it/curriculum') . '"', false);
})->with(['it', 'en']);
