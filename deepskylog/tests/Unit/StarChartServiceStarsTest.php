<?php

use App\Models\Star;
use App\Services\StarChartService;
use Illuminate\Support\Facades\DB;

/**
 * StarChartService::loadStars() selects on the decl_zone column, a generated
 * floor(decl_degrees): these check that the zones pick up every star the
 * declination and right ascension window takes in, across the zone edges,
 * the 0h/24h wrap and the poles, and that a flat chart keeps its brightest
 * stars when it is cut to its maximum.
 */
function insertStars(array $stars): void
{
    static $n = 0;

    $rows = array_map(function (array $star) use (&$n) {
        $n++;

        return [
            'source_key' => sha1('zone-star-' . $n),
            'source_table' => 'stars8',
            'name' => $star['name'] ?? null,
            'constellation_code' => $star['con'] ?? null,
            'bayer' => $star['bayer'] ?? null,
            'ra_hours' => $star['ra'] / 15.0,
            'ra_degrees' => $star['ra'],
            'decl_degrees' => $star['dec'],
            'vmag_int' => (int) round($star['mag'] * 100),
            'vmag' => $star['mag'],
        ];
    }, $stars);

    foreach (array_chunk($rows, 500) as $chunk) {
        DB::table('stars')->insert($chunk);
    }
}

function loadStars(float $ra, float $dec, float $radiusArcMin, float $mag, bool $atlas): \Illuminate\Support\Collection
{
    $service = app(StarChartService::class);
    $ref = new ReflectionClass($service);
    $layout = $ref->getMethod('resolveAtlasLayout')->invoke($service, $atlas, false);
    $aspect = $layout['plot_width'] / $layout['plot_height'];

    return $ref->getMethod('loadStars')->invoke($service, $ra, $dec, max(0.1, $radiusArcMin / 60), $mag, true, $atlas, $aspect, $layout);
}

it('fills decl_zone with the floor of the declination', function () {
    insertStars([
        ['name' => 'a', 'ra' => 10.0, 'dec' => 41.28, 'mag' => 5.0],
        ['name' => 'b', 'ra' => 10.0, 'dec' => -0.5, 'mag' => 5.0],
        ['name' => 'c', 'ra' => 10.0, 'dec' => -1.0, 'mag' => 5.0],
        ['name' => 'd', 'ra' => 10.0, 'dec' => 90.0, 'mag' => 5.0],
        ['name' => 'e', 'ra' => 10.0, 'dec' => -89.99, 'mag' => 5.0],
    ]);

    expect(DB::table('stars')->orderBy('name')->pluck('decl_zone', 'name')->map(fn($z) => (int) $z)->all())
        ->toBe(['a' => 41, 'b' => -1, 'c' => -1, 'd' => 90, 'e' => -90]);
});

it('finds the stars on both sides of a zone edge and of 0h', function () {
    insertStars([
        ['name' => 'Wrap East', 'ra' => 359.6, 'dec' => -0.3, 'mag' => 4.0],
        ['name' => 'Wrap West', 'ra' => 0.4, 'dec' => 0.3, 'mag' => 3.0],
        ['name' => 'Centre', 'ra' => 0.0, 'dec' => 0.0, 'mag' => 4.5],
        ['name' => 'Too faint', 'ra' => 0.1, 'dec' => 0.1, 'mag' => 12.0],
        ['name' => 'Too far south', 'ra' => 0.0, 'dec' => -5.0, 'mag' => 4.0],
        ['name' => 'Opposite', 'ra' => 180.0, 'dec' => 0.0, 'mag' => 4.0],
        // Drawn, but too faint to be labelled, so its name is not read.
        ['name' => 'Faint', 'ra' => 0.2, 'dec' => -0.2, 'mag' => 8.0],
    ]);

    foreach ([true, false] as $atlas) {
        $names = loadStars(0.0, 0.0, 60, 10.0, $atlas)->pluck('name')->all();

        // Brightest first.
        expect($names)->toBe(['Wrap West', 'Wrap East', 'Centre', null]);
    }
});

it('covers every right ascension around a pole', function () {
    $stars = [];
    foreach (range(0, 330, 30) as $i => $ra) {
        $stars[] = ['name' => 'N' . $ra, 'ra' => (float) $ra, 'dec' => 89.5, 'mag' => 3.0 + ($i / 100)];
        $stars[] = ['name' => 'S' . $ra, 'ra' => (float) $ra, 'dec' => -89.5, 'mag' => 3.0 + ($i / 100)];
    }
    insertStars($stars);

    $north = loadStars(0.0, 89.0, 180, 10.0, true)->pluck('name')->all();
    $south = loadStars(200.0, -88.5, 180, 10.0, true)->pluck('name')->all();

    expect($north)->toBe(array_map(fn($ra) => 'N' . $ra, range(0, 330, 30)))
        ->and($south)->toBe(array_map(fn($ra) => 'S' . $ra, range(0, 330, 30)));
});

it('keeps the brightest stars when a flat chart is cut to its maximum', function () {
    // A 0.4 degree chart draws at most 5000 stars.
    $stars = [];
    for ($i = 0; $i < 5200; $i++) {
        $stars[] = [
            'ra' => 100.0 + ((($i * 37) % 200) - 100) / 1000.0,
            'dec' => 20.0 + ((($i * 53) % 200) - 100) / 1000.0,
            'mag' => 8.0 + (($i * 7919) % 5200) / 1000.0,
        ];
    }
    // Bright, but just outside the chart: it must not take a place.
    $stars[] = ['name' => 'Outside', 'ra' => 100.0, 'dec' => 20.5, 'mag' => 1.0];
    insertStars($stars);

    $loaded = loadStars(100.0, 20.0, 24, 15.0, false);
    $mags = $loaded->pluck('vmag')->all();

    expect($loaded)->toHaveCount(5000)
        ->and($loaded->pluck('name')->filter()->all())->toBe([])
        ->and(max($mags))->toBeLessThan(8.0 + 5000 / 1000.0)
        ->and($mags)->toBe(collect($mags)->sort()->values()->all());
});

it('names a plain row the way the model does', function () {
    $row = (object) ['flamsteed' => '58', 'bayer' => 'alp', 'bayer_suffix' => null, 'constellation_code' => 'ORI', 'name' => 'SAO 113271'];
    $catalogue = (object) ['flamsteed' => null, 'bayer' => null, 'bayer_suffix' => null, 'constellation_code' => null, 'name' => 'SOA308'];

    expect(Star::displayNameFor($row))->toBe('58 alp ORI')
        ->and(Star::displayNameFor($catalogue))->toBeNull()
        ->and((new Star((array) $row))->displayName())->toBe('58 alp ORI');
});
