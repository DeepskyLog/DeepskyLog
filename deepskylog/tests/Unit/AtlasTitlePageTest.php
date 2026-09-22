<?php

use App\Services\StarChartService;

it('names the atlas on the front page', function () {
    $svg = app(StarChartService::class)->buildTitlePage('detail', 'A4', 'portrait');

    expect($svg)->toContain('Deepskylog Atlas')
        ->toContain('Detail atlas - A4 portrait');
});

it('draws every object type symbol on the front page', function () {
    $service = app(StarChartService::class);
    $svg = $service->buildTitlePage('overview', 'A3', 'landscape');

    $entries = new ReflectionMethod($service, 'titlePageLegendEntries');
    $entries->setAccessible(true);

    foreach ($entries->invoke($service) as $label) {
        expect($svg)->toContain($label);
    }

    // One drawing per row, and none of them empty.
    expect(substr_count($svg, '<g transform='))->toBe(count($entries->invoke($service)));
});

it('sizes the front page like the atlas pages it precedes', function () {
    $service = app(StarChartService::class);

    expect($service->buildTitlePage('detail', 'A4', 'portrait'))->toContain('viewBox="0 0 756 1120"');
    expect($service->buildTitlePage('detail', 'A4', 'landscape'))->toContain('viewBox="0 0 1120 780"');
});
