<?php

it('includes the statistics page in the sitemap', function () {
    $response = $this->get('/sitemap.xml');

    $response->assertOk();
    $response->assertSee('/statistics', false);
});