<?php

use Illuminate\Support\Facades\Blade;

// The object page itself uses MariaDB functions the SQLite test database does
// not have, so the partial is rendered on its own, with the stack the app
// layout prints in its <head>.
function renderCanonical(array $data): string
{
    return Blade::render("@include('object.partials.canonical')@stack('head')", $data);
}

it('points an alternative-name page at the main name', function () {
    $html = renderCanonical([
        'canonicalSlug' => 'ngc-891',
        'session' => (object) ['name' => 'UGC 1831', 'slug' => 'ugc-1831'],
    ]);

    expect($html)->toContain('<link rel="canonical" href="' . route('object.show', ['slug' => 'ngc-891']) . '" />');
});

it('falls back to the slug of the object, then its name', function () {
    expect(renderCanonical(['session' => (object) ['name' => 'Moon', 'slug' => 'moon']]))
        ->toContain('href="' . route('object.show', ['slug' => 'moon']) . '"')
        ->and(renderCanonical(['session' => (object) ['name' => 'NGC 7000']]))
        ->toContain('href="' . route('object.show', ['slug' => 'ngc-7000']) . '"');
});

it('prints nothing without a slug or name', function () {
    expect(renderCanonical(['session' => (object) []]))->not->toContain('canonical');
});
