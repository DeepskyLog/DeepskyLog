<?php

// Lists the lunar occultations AstronomicalEvents finds between two dates,
// with the local circumstances at a place:
// php scripts/lunar_occultations_smoke.php 2026-01-01 2027-01-01 [latitude] [longitude]

use App\Services\News\AstronomicalEvents;
use App\Services\News\LocalCircumstances;

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$from      = strtotime(($argv[1] ?? 'today') . ' UTC');
$to        = strtotime(($argv[2] ?? '+1 year') . ' UTC');
$latitude  = (float) ($argv[3] ?? 50.85);
$longitude = (float) ($argv[4] ?? 4.35);

$start  = microtime(true);
$events = array_filter((new AstronomicalEvents())->between($from, $to), fn ($e) => $e['kind'] === 'lunar_occultation');
printf("%d occultations in %.1f s\n", count($events), microtime(true) - $start);

foreach ($events as $event) {
    $local = LocalCircumstances::compute($event['kind'], $event['at'], $event['data'], $latitude, $longitude);
    $occ   = $local['occultation'] ?? null;
    $text  = $occ === null ? sprintf('not covered here (closest %.2f°)', $local['closest']['separation'] ?? -1) : implode('  ', array_map(
        fn ($c) => sprintf('%s %s limb Moon %d° Sun %d°', gmdate('H:i:s', $c['at']), $c['limb'], $c['moon_altitude'], $c['sun_altitude']),
        array_filter([$occ['disappears'], $occ['reappears']]),
    ));
    printf("%s  %-28s %5.2f°  %3d%%  %s\n", gmdate('Y-m-d H:i', $event['at']), $event['data']['star']['name'] ?? $event['data']['target'], $event['data']['separation'], 100 * $event['data']['moon_illumination'], $text);
}
