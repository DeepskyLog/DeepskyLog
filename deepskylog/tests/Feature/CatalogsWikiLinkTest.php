<?php

// The catalogs page and the Help menu link to the wiki page on the deep-sky
// catalogs (#418).

it('links the catalogs page and the Help menu to the wiki on the deep-sky catalogs', function () {
    $response = $this->get(route('catalogs.index'));

    $response->assertOk();
    expect(substr_count($response->getContent(), 'https://github.com/DeepskyLog/DeepskyLog/wiki/Deep-sky-Catalogues'))
        ->toBeGreaterThanOrEqual(3);
});
