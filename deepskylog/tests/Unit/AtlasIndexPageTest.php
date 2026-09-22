<?php

use App\Services\AtlasIndexPageService;
use App\Services\AtlasLayout;

/**
 * Every page number of an atlas, as the index maps print them.
 *
 * @return array<int, int>
 */
function indexMapPageNumbers(array $maps): array
{
    $numbers = [];

    foreach ($maps as $svg) {
        preg_match_all('/>(\d+)<\/text>/', $svg, $matches);
        foreach ($matches[1] as $number) {
            $numbers[(int) $number] = true;
        }
    }

    return array_keys($numbers);
}

it('follows the title page with two index maps per hemisphere', function () {
    $maps = app(AtlasIndexPageService::class)->build('overview', 'A4', 'landscape');

    expect($maps)->toHaveCount(4);
    expect($maps[0])->toContain('Northern sky')->toContain('Index map 1 of 4');
    expect($maps[1])->toContain('Northern sky');
    expect($maps[2])->toContain('Southern sky');
    expect($maps[3])->toContain('Southern sky')->toContain('Index map 4 of 4');
});

it('draws the index maps on an atlas page canvas', function () {
    $service = app(AtlasIndexPageService::class);

    expect($service->build('overview', 'A4', 'portrait')[0])->toContain('viewBox="0 0 756 1120"');
    expect($service->build('overview', 'A4', 'landscape')[0])->toContain('viewBox="0 0 1120 780"');
});

it('accounts for every page of the atlas across the index maps', function () {
    $maps = app(AtlasIndexPageService::class)->build('overview', 'A4', 'landscape');
    $printed = indexMapPageNumbers($maps);

    $total = AtlasLayout::totalPages(AtlasLayout::variant('overview', 'landscape'));

    foreach (range(1, $total) as $page) {
        expect($printed)->toContain($page);
    }
});

it('splits a finer atlas over more index maps', function () {
    $service = app(AtlasIndexPageService::class);

    // The detail atlas has more than ten times the pages of the overview
    // one, and squeezing them onto four maps would make the numbers
    // unreadable.
    expect(count($service->build('detail', 'A4', 'portrait')))
        ->toBeGreaterThan(count($service->build('overview', 'A4', 'portrait')));
});

it('names the constellations on the index maps', function () {
    $maps = app(AtlasIndexPageService::class)->build('overview', 'A4', 'landscape');
    $all = implode('', $maps);

    foreach (['ORION', 'URSA MAJOR', 'CRUX', 'CASSIOPEIA'] as $constellation) {
        expect($all)->toContain($constellation);
    }
});

it('draws the constellation figures on the index maps', function () {
    $maps = app(AtlasIndexPageService::class)->build('overview', 'A4', 'landscape');

    foreach ($maps as $svg) {
        expect($svg)->toContain('stroke="#4b5563"');
    }
});

it('overlaps the hemispheres and the sectors so nothing falls between maps', function () {
    $maps = app(AtlasIndexPageService::class)->build('overview', 'A4', 'landscape');

    // Both hemispheres reach past the equator ...
    expect($maps[0])->toContain('-6°');
    expect($maps[2])->toContain('+6°');

    // ... and each hemisphere is covered by sectors wider than half the sky,
    // so the two maps of a hemisphere share a strip of right ascension.
    expect($maps[0])->toContain('23h to 13h');
    expect($maps[1])->toContain('11h to 1h');
});
