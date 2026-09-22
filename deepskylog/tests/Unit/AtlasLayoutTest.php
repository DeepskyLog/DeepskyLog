<?php

use App\Services\AtlasLayout;

it('numbers pages band by band, from the first page of the northernmost band', function () {
    // Overview portrait: bands of 12, 20, 26, ... pages.
    expect(AtlasLayout::pageNumber(0, 0, 0))->toBe(1);
    expect(AtlasLayout::pageNumber(0, 0, 11))->toBe(12);
    expect(AtlasLayout::pageNumber(0, 1, 0))->toBe(13);
    expect(AtlasLayout::pageNumber(0, 2, 0))->toBe(33);
    expect(AtlasLayout::pageNumber(0, 9, 11))->toBe(AtlasLayout::totalPages(0));
});

it('keeps the published page counts of every atlas variant', function () {
    expect(array_map(
        fn(int $variant): int => AtlasLayout::totalPages($variant),
        range(0, 5)
    ))->toBe([240, 642, 2612, 231, 678, 2516]);
});

it('centres a page in the middle of its band and column', function () {
    // Overview portrait has ten 18 degree bands; the first covers +72 to +90.
    [$raHours, $decDeg] = AtlasLayout::pageCenter(0, 0, 0);

    expect($decDeg)->toBe(81.0);
    expect($raHours)->toBe(23.0);

    [, $lastBandDec] = AtlasLayout::pageCenter(0, 9, 0);
    expect($lastBandDec)->toBe(-81.0);
});

it('finds the page a position on the sky falls on', function () {
    [$raHours, $decDeg] = AtlasLayout::pageCenter(0, 3, 5);

    expect(AtlasLayout::pageAt(0, $raHours, $decDeg))->toBe(AtlasLayout::pageNumber(0, 3, 5));

    // Both poles land on a page rather than falling through the bands. Which
    // column of the polar band they land on still follows right ascension.
    expect(AtlasLayout::pageAt(0, 6.0, 90.0))->toBe(AtlasLayout::pageNumber(0, 0, AtlasLayout::columnAt(0, 0, 6.0)));
    expect(AtlasLayout::pageAt(0, 6.0, -90.0))->toBe(AtlasLayout::pageNumber(0, 9, AtlasLayout::columnAt(0, 9, 6.0)));

    expect(AtlasLayout::pageAt(99, 6.0, 0.0))->toBe(0);
});

it('names the charts adjoining a page', function () {
    $neighbours = AtlasLayout::neighbours(0, 3, 5);

    // Right ascension increases to the left, so the left neighbour is the
    // previous column and the right neighbour the next one.
    expect($neighbours['left'])->toBe(AtlasLayout::pageNumber(0, 3, 4));
    expect($neighbours['right'])->toBe(AtlasLayout::pageNumber(0, 3, 6));

    // Bands above and below hold a different number of pages, so the
    // neighbour is whichever page covers this page's own right ascension.
    [$raHours] = AtlasLayout::pageCenter(0, 3, 5);
    expect($neighbours['top'])->toBe(AtlasLayout::pageNumber(0, 2, AtlasLayout::columnAt(0, 2, $raHours)));
    expect($neighbours['bottom'])->toBe(AtlasLayout::pageNumber(0, 4, AtlasLayout::columnAt(0, 4, $raHours)));
});

it('wraps neighbours around the sky but not past a pole', function () {
    $first = AtlasLayout::neighbours(0, 0, 0);

    expect($first['left'])->toBe(12);   // wraps to the far end of the band
    expect($first['right'])->toBe(2);
    expect($first['top'])->toBeNull();  // beyond the first band lies the pole
    expect($first['bottom'])->toBe(13);

    $last = AtlasLayout::neighbours(0, 9, 11);

    expect($last['left'])->toBe(239);
    expect($last['right'])->toBe(229);  // wraps to the start of the band
    expect($last['top'])->toBe(228);
    expect($last['bottom'])->toBeNull();
});

it('every page names neighbours that point back at it', function () {
    foreach (range(0, 5) as $variant) {
        $bands = AtlasLayout::bandCount($variant);

        for ($band = 0; $band < $bands; $band++) {
            $columns = AtlasLayout::columnCount($variant, $band);

            for ($column = 0; $column < $columns; $column++) {
                $page = AtlasLayout::pageNumber($variant, $band, $column);
                $neighbours = AtlasLayout::neighbours($variant, $band, $column);

                // Walking left then right comes back to where it started.
                $left = AtlasLayout::neighbours($variant, $band, ($column - 1 + $columns) % $columns);
                expect($left['right'])->toBe($page);

                foreach ($neighbours as $side => $neighbour) {
                    if ($neighbour === null) {
                        continue;
                    }

                    expect($neighbour)->toBeGreaterThanOrEqual(1);
                    expect($neighbour)->toBeLessThanOrEqual(AtlasLayout::totalPages($variant));
                    expect($neighbour)->not->toBe($page, "variant {$variant} page {$page} is its own {$side} neighbour");
                }
            }
        }
    }
});

it('maps atlas type and orientation onto a variant', function () {
    expect(AtlasLayout::variant('overview', 'portrait'))->toBe(0);
    expect(AtlasLayout::variant('lookup', 'portrait'))->toBe(1);
    expect(AtlasLayout::variant('detail', 'portrait'))->toBe(2);
    expect(AtlasLayout::variant('overview', 'landscape'))->toBe(3);
    expect(AtlasLayout::variant('DETAIL', ' Landscape '))->toBe(5);
});
