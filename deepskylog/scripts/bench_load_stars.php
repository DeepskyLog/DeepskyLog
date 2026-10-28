<?php

/**
 * Time StarChartService::loadStars() for a set of atlas charts and record the
 * stars each one returns, so a change to the query can be checked for both
 * speed and identical results.
 *
 *   php scripts/bench_load_stars.php [out.json] [--compare=before.json] [--repeat=2]
 *
 * With --compare the star lists are compared chart by chart with an earlier
 * run (same stars, same order).
 */

require __DIR__ . '/../vendor/autoload.php';
putenv('LOG_CHANNEL=errorlog');

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\StarChartService;

$out = null;
$compare = null;
$repeat = 2;
foreach (array_slice($argv, 1) as $arg) {
    if (str_starts_with($arg, '--compare=')) {
        $compare = substr($arg, 10);
    } elseif (str_starts_with($arg, '--repeat=')) {
        $repeat = max(1, (int) substr($arg, 9));
    } else {
        $out = $arg;
    }
}

// [label, ra deg, dec deg, radius arcmin, star magnitude, atlas mode, portrait]
$cases = [
    ['detail M42', 83.82, -5.39, 180, 15.0, true, false],
    ['detail M31', 10.68, 41.27, 180, 15.0, true, false],
    ['detail RA wrap 0h +20', 0.5, 20.0, 180, 15.0, true, false],
    ['detail Cygnus +40 portrait', 310.0, 40.0, 180, 15.0, true, true],
    ['detail +80', 120.0, 80.0, 180, 15.0, true, false],
    ['detail north pole', 0.0, 89.0, 180, 15.0, true, false],
    ['detail south pole', 200.0, -88.5, 180, 15.0, true, false],
    ['lookup Sgr -25', 270.0, -25.0, 360, 12.0, true, false],
    ['overview Ori 0', 90.0, 0.0, 600, 10.0, true, false],
    ['overview +84', 330.0, 84.0, 600, 10.0, true, false],
    ['flat 30\' M51', 202.47, 47.2, 15, 15.0, false, false],
    ['flat 2deg wrap', 359.5, 10.0, 120, 13.0, false, false],
    ['flat 1deg pole', 45.0, 89.6, 60, 14.0, false, false],
];

$service = $app->make(StarChartService::class);
$ref = new ReflectionClass($service);
$loadStars = $ref->getMethod('loadStars');
$resolveLayout = $ref->getMethod('resolveAtlasLayout');

$results = [];
$total = 0.0;
foreach ($cases as [$label, $ra, $dec, $radiusArcMin, $mag, $atlas, $portrait]) {
    $radiusDeg = max(0.1, $radiusArcMin / 60);
    $layout = $resolveLayout->invoke($service, $atlas, $portrait);
    $aspect = ((float) $layout['plot_width']) / max(1.0, (float) $layout['plot_height']);

    $times = [];
    $stars = null;
    for ($i = 0; $i < $repeat; $i++) {
        $t = microtime(true);
        $stars = $loadStars->invoke($service, (float) $ra, (float) $dec, $radiusDeg, $mag, true, $atlas, $aspect, $layout);
        $times[] = (microtime(true) - $t) * 1000;
    }

    $rows = $stars->map(fn(array $s) => [
        $s['name'],
        round($s['ra_degrees'], 9),
        round($s['decl_degrees'], 9),
        $s['vmag'],
    ])->all();
    $results[$label] = $rows;

    $best = min($times);
    $total += $best;
    printf("%-30s %7d stars  %s ms\n", $label, count($rows), implode(' / ', array_map(fn($t) => sprintf('%8.1f', $t), $times)));
}
printf("%-30s %13s  %8.1f ms (best of each)\n", 'total', '', $total);

if ($out !== null) {
    file_put_contents($out, json_encode($results));
    echo "Wrote $out\n";
}

if ($compare !== null) {
    $before = json_decode(file_get_contents($compare), true);
    $same = 0;
    foreach ($results as $label => $rows) {
        $old = $before[$label] ?? null;
        if ($old === null) {
            echo "  [missing] $label\n";
            continue;
        }
        $rows = json_decode(json_encode($rows), true);
        // Only the stars that can be labelled (4.6 and brighter) need a name.
        $old = array_map(fn($r) => $r[3] !== null && $r[3] > 4.6 ? [null, $r[1], $r[2], $r[3]] : $r, $old);
        $sortKey = fn($r) => json_encode($r);
        $asSet = fn($rs) => array_map($sortKey, $rs);
        $oldSet = $asSet($old);
        $newSet = $asSet($rows);
        sort($oldSet);
        sort($newSet);
        if ($old === $rows) {
            $same++;
        } elseif ($oldSet === $newSet) {
            echo "  [same set, different order] $label\n";
        } else {
            printf("  [DIFFERENT] %s: %d before, %d after, %d only before, %d only after\n", $label, count($old), count($rows),
                count(array_diff($oldSet, $newSet)), count(array_diff($newSet, $oldSet)));
        }
    }
    printf("%d/%d charts identical\n", $same, count($results));
}
