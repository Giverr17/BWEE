<?php

beforeEach(function () {
    app()->setLocale('en');
});

it('switches locale via locale route', function () {
    $response = $this->get(route('locale.set', ['locale' => 'fr']));
    $response->assertSessionHas('locale', 'fr');
});

it('renders all public pages in English', function (string $route) {
    $response = $this->withSession(['locale' => 'en'])->get($route);
    $response->assertStatus(200);
})->with([
    '/',
    '/about',
    '/founders',
    '/programs',
    '/events',
    '/gallery',
    '/partners',
    '/get-involved',
    '/contact',
]);

it('renders all public pages in French with French text', function (string $route, string $expectedFrench) {
    $response = $this->withSession(['locale' => 'fr'])->get($route);
    $response->assertStatus(200);
    $response->assertSee($expectedFrench, false);
})->with([
    ['/', 'ÉCHANGE ENTREPRENEURIAL DES FEMMES NOIRES'],
    ['/about', 'L’ÉQUITÉ EN ACTION'],
    ['/founders', 'Rencontrez les cofondatrices de BWEE'],
    ['/programs', 'NOS AXES STRATÉGIQUES'],
    ['/events', 'SOMMETS ET SESSIONS'],
    ['/gallery', 'MÉDIAS ET ARCHIVES'],
    ['/partners', 'ALLIÉS INSTITUTIONNELS'],
    ['/get-involved', 'REJOINDRE LE MOUVEMENT'],
    ['/contact', 'Nous joindre'],
]);
