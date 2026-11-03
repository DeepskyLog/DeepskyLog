<?php

use App\Models\User;

it('renders every menu item, with a log out in the menu for small screens', function () {
    $observer = User::factory()->create();

    $html = $this->actingAs($observer)->get('/')->assertOk()->getContent();
    $responsive = substr($html, strpos($html, 'Responsive Navigation Menu'));

    // A component tag Blade did not compile (for example because of nested quotes in an attribute) shows nothing in the browser
    expect($html)->not->toMatch('/<x-[\w.-]+/')
        ->and($responsive)->toContain('href="' . route('logout') . '"')
        ->and($responsive)->toContain('Log Out');
});
