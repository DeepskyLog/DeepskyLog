<?php

use App\Models\DeepskyObject;
use App\Services\StarChartService;

it('joins the constellation figures into segments of every constellation', function () {
    $segments = app(StarChartService::class)->loadConstellationLines();

    expect($segments)->not->toBeEmpty();

    // The figures are drawn between neighbouring stars, so no segment should
    // run right across the sky; anything that does means two separate
    // figures have been joined end to end.
    foreach ($segments as $segment) {
        expect($segment['length_deg'])->toBeGreaterThan(0.0);
        expect($segment['length_deg'])->toBeLessThan(60.0);
    }
});

it('interpolates along the great circle between two positions', function () {
    $service = app(StarChartService::class);

    // Half way along the equator from 0h to 2h is 1h, exactly.
    [$ra, $dec] = $service->greatCirclePoint(0.0, 0.0, 30.0, 0.0, 0.5);
    expect($ra)->toBeGreaterThan(14.99)->toBeLessThan(15.01);
    expect(abs($dec))->toBeLessThan(0.01);

    // Half way from the equator to the pole along a meridian is 45 degrees,
    // which interpolating the coordinates themselves would also give; the
    // test that tells the two apart is a pair either side of the pole.
    [, $dec] = $service->greatCirclePoint(0.0, 80.0, 180.0, 80.0, 0.5);
    expect($dec)->toBeGreaterThan(89.9);

    // The ends come back where they started.
    [$ra, $dec] = $service->greatCirclePoint(12.0, -30.0, 55.0, 10.0, 0.0);
    expect(abs($ra - 12.0))->toBeLessThan(0.001);
    expect(abs($dec + 30.0))->toBeLessThan(0.001);

    [$ra, $dec] = $service->greatCirclePoint(12.0, -30.0, 55.0, 10.0, 1.0);
    expect(abs($ra - 55.0))->toBeLessThan(0.001);
    expect(abs($dec - 10.0))->toBeLessThan(0.001);
});

it('draws the constellation figure over the stars it joins', function () {
    $target = new DeepskyObject();
    $target->name = 'Big Dipper';
    $target->ra = 180.0;
    $target->decl = 55.0;

    $chart = app(StarChartService::class)
        ->build($target, 180.0, 55.0, 600.0, null, 10.0, 8.0, true, true, false, null, false);

    // The figure layer is one path, drawn in its own colour.
    expect($chart['svg'])->toContain('stroke="#4b5563" stroke-width="1.1"');
});

it('leaves the figures off a chart of a part of the sky that has none', function () {
    $service = app(StarChartService::class);

    $render = new ReflectionMethod($service, 'renderConstellationFigurePaths');
    $render->setAccessible(true);

    // A tenth of a degree of empty sky in southern Sculptor, far from any
    // figure: nothing to draw, and nothing projected to find that out.
    $path = $render->invoke(
        $service,
        $service->loadConstellationLines(),
        5.0,
        -33.0,
        0.05,
        0.05,
        56.0,
        70.0,
        1008.0,
        626.0
    );

    expect($path)->toBe('');
});
