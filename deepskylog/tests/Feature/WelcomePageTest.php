<?php

it('links the register and privacy policy texts in the guest welcome message', function () {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee("<a href='" . route('register') . "'>register</a> to DeepskyLog", false);
    $response->assertSee("in accordance with our <a href='" . route('privacy') . "'>privacy policy</a>.", false);
});

it('links the translated texts in the guest welcome message', function () {
    app()->setLocale('nl');

    $text = __('To start recording your observations and share your observations with other observers, you are kindly requested to :register_linkregister:close_link to DeepskyLog. Your personal data will be handled in accordance with our :privacy_linkprivacy policy:close_link.', [
        'register_link' => '<a href="/register">',
        'privacy_link' => '<a href="/privacy">',
        'close_link' => '</a>',
    ]);

    expect($text)
        ->toContain('<a href="/register">registreren</a>')
        ->toContain('<a href="/privacy">privacybeleid</a>');
});
