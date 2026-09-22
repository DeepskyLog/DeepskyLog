<?php

use App\Services\StarChartService;

it('places increasing right ascension on the left side of the near-polar atlas plot', function () {
    $service = app(StarChartService::class);

    $atlasProjectionEnabled = new ReflectionProperty($service, 'atlasProjectionEnabled');
    $atlasProjectionEnabled->setAccessible(true);
    $atlasProjectionEnabled->setValue($service, true);

    $atlasProjectionContext = new ReflectionProperty($service, 'atlasProjectionContext');
    $atlasProjectionContext->setAccessible(true);
    $atlasProjectionContext->setValue($service, []);

    $method = new ReflectionMethod($service, 'projectToAtlasChartUnclipped');
    $method->setAccessible(true);

    $left = 56.0;
    $top = 70.0;
    $plotWidth = 1008.0;
    $plotHeight = 626.0;

    $east = $method->invoke($service, 3.33 * 15.0 + 2.0, 88.0, 3.33 * 15.0, 88.0, 6.0, 6.0, $left, $top, $plotWidth, $plotHeight);
    $west = $method->invoke($service, 3.33 * 15.0 - 2.0, 88.0, 3.33 * 15.0, 88.0, 6.0, 6.0, $left, $top, $plotWidth, $plotHeight);

    expect($east['x'])->toBeLessThan($west['x']);
});

it('covers exactly the requested angular field on an atlas page', function () {
    $service = app(StarChartService::class);

    $atlasProjectionEnabled = new ReflectionProperty($service, 'atlasProjectionEnabled');
    $atlasProjectionEnabled->setAccessible(true);
    $atlasProjectionEnabled->setValue($service, true);

    $atlasProjectionContext = new ReflectionProperty($service, 'atlasProjectionContext');
    $atlasProjectionContext->setAccessible(true);
    $atlasProjectionContext->setValue($service, []);

    $prepare = new ReflectionMethod($service, 'prepareAtlasProjectionContext');
    $prepare->setAccessible(true);
    $project = new ReflectionMethod($service, 'projectToAtlasChartUnclipped');
    $project->setAccessible(true);

    $centerRa = 345.0;
    $centerDec = 81.0;
    $xHalf = 6.8;
    $yHalf = 10.0;
    $left = 56.0;
    $top = 90.0;
    $plotWidth = 644.0;
    $plotHeight = 946.0;

    $prepare->invoke($service, $centerRa, $centerDec, $xHalf, $yHalf, $left, $top, $plotWidth, $plotHeight);

    // The page is centred on the requested coordinates ...
    $center = $project->invoke($service, $centerRa, $centerDec, $centerRa, $centerDec, $xHalf, $yHalf, $left, $top, $plotWidth, $plotHeight);
    expect($center['x'])->toBeGreaterThan($left + ($plotWidth / 2.0) - 1.0);
    expect($center['x'])->toBeLessThan($left + ($plotWidth / 2.0) + 1.0);
    expect($center['y'])->toBeGreaterThan($top + ($plotHeight / 2.0) - 1.0);
    expect($center['y'])->toBeLessThan($top + ($plotHeight / 2.0) + 1.0);

    // ... and reaches $yHalf degrees to the bottom edge, no further. This page
    // overshoots the pole by one degree, so the pole is drawn just inside the
    // top edge instead of being pushed down into the middle of the chart.
    $bottom = $project->invoke($service, $centerRa, $centerDec - $yHalf, $centerRa, $centerDec, $xHalf, $yHalf, $left, $top, $plotWidth, $plotHeight);
    expect($bottom['y'])->toBeGreaterThan($top + $plotHeight - 4.0);
    expect($bottom['y'])->toBeLessThan($top + $plotHeight + 1.0);

    $pole = $project->invoke($service, $centerRa, 90.0, $centerRa, $centerDec, $xHalf, $yHalf, $left, $top, $plotWidth, $plotHeight);
    $degreesPerPixel = (2.0 * $yHalf) / $plotHeight;
    expect(($pole['y'] - $top) * $degreesPerPixel)->toBeLessThan(1.5);
});

it('names the declination a graticule label is drawn next to', function () {
    $service = app(StarChartService::class);

    $atlasProjectionEnabled = new ReflectionProperty($service, 'atlasProjectionEnabled');
    $atlasProjectionEnabled->setAccessible(true);
    $atlasProjectionEnabled->setValue($service, true);

    $atlasProjectionContext = new ReflectionProperty($service, 'atlasProjectionContext');
    $atlasProjectionContext->setAccessible(true);
    $atlasProjectionContext->setValue($service, []);

    $grid = new ReflectionMethod($service, 'renderAtlasGrid');
    $grid->setAccessible(true);
    $project = new ReflectionMethod($service, 'projectToAtlasChartUnclipped');
    $project->setAccessible(true);

    $centerRa = 345.0;
    $centerDec = 81.0;
    $xHalf = 6.8;
    $yHalf = 10.0;
    $left = 56.0;
    $top = 90.0;
    $plotWidth = 644.0;
    $plotHeight = 946.0;

    $svg = $grid->invoke($service, $centerRa, $centerDec, $xHalf, $yHalf, $left, $top, $plotWidth, $plotHeight);

    // Declination labels sit against the right edge of the plot.
    $labelX = $left + $plotWidth + 6;
    preg_match_all(
        '/<text x="' . preg_quote(sprintf('%0.2f', $labelX), '/') . '" y="([0-9.]+)"[^>]*>\+(\d+)°(\d+)&#039;<\/text>/',
        $svg,
        $matches,
        PREG_SET_ORDER
    );

    expect($matches)->not->toBeEmpty();

    $context = $atlasProjectionContext->getValue($service);
    $unproject = new ReflectionMethod($service, 'atlasSphericalUnproject');
    $unproject->setAccessible(true);

    foreach ($matches as $match) {
        $labelDec = ((int) $match[2]) + (((int) $match[3]) / 60.0);

        // The label is drawn against the right edge of the plot, baseline
        // shifted 3px down. Read the sky back out of that pixel: it must be
        // the declination the label names.
        $projectedX = $context['max_x'] - ((($left + $plotWidth) - $left) / $plotWidth * ($context['max_x'] - $context['min_x']));
        $projectedY = $context['max_y'] - (((((float) $match[1]) - 3.0) - $top) / $plotHeight * ($context['max_y'] - $context['min_y']));

        [, $decAtLabel] = $unproject->invoke($service, $projectedX, $projectedY, $centerRa, $centerDec);

        expect(abs($decAtLabel - $labelDec))->toBeLessThan(0.2);
    }
});

it('draws the whole circle of meridians when a pole is on the page', function () {
    $service = app(StarChartService::class);

    $atlasProjectionEnabled = new ReflectionProperty($service, 'atlasProjectionEnabled');
    $atlasProjectionEnabled->setAccessible(true);
    $atlasProjectionEnabled->setValue($service, true);

    $atlasProjectionContext = new ReflectionProperty($service, 'atlasProjectionContext');
    $atlasProjectionContext->setAccessible(true);
    $atlasProjectionContext->setValue($service, []);

    $window = new ReflectionMethod($service, 'atlasSkyWindow');
    $window->setAccessible(true);
    $bounds = new ReflectionMethod($service, 'atlasProjectionBounds');
    $bounds->setAccessible(true);

    $polar = $window->invoke($service, 345.0, 81.0, $bounds->invoke($service, 6.8, 10.0, 644.0, 946.0));
    expect($polar['ra_window'])->toBe(180.0);
    expect($polar['dec_max'])->toBe(90.0);

    $equatorial = $window->invoke($service, 345.0, 9.0, $bounds->invoke($service, 6.8, 10.0, 644.0, 946.0));
    expect($equatorial['ra_window'])->toBeLessThan(10.0);
    expect($equatorial['dec_max'])->toBeGreaterThan(18.9);
    expect($equatorial['dec_min'])->toBeLessThan(-1.0);
});
