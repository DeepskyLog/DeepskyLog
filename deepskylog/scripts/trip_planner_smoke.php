<?php

// Times the trip planner for one observer: php scripts/trip_planner_smoke.php <username> <lat> <lon> <from> <until> [sqm]
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

[$script, $username, $lat, $lon, $from, $until] = array_pad($argv, 6, null);
$user = App\Models\User::where('username', $username)->firstOrFail();
$trip = ['latitude' => (float) $lat, 'longitude' => (float) $lon, 'from' => $from, 'until' => $until, 'sqm' => $argv[6] ?? null];
$trip['timezone'] = App\Services\TripPlanner::guessTimezone((float) $lat, (float) $lon);
$planner = App\Services\TripPlanner::for($user, $trip);
echo 'Time zone: '.$planner->trip['timezone'].', unavailable: '.($planner->unavailable() ?? '-')."\n";

$start  = microtime(true);
$result = $planner->compute();
printf("Calculated %d objects in %.2f s (%d MB)\n", count($result['objects']), microtime(true) - $start, memory_get_peak_usage() / 1048576);
$shown = $planner->objects($result);
printf("Not seen: %d; low at home: %d, too faint: %d\n", $shown['total'],
    $planner->objects($result, App\Services\TripPlanner::LOW)['total'], $planner->objects($result, App\Services\TripPlanner::FAINTER)['total']);
foreach (array_slice($shown['objects'], 0, 15) as $object) {
    printf("  %-20s %-8s %-15s %s\n", $object['name'], $object['reason'], $object['category'], implode(',', $object['nights']));
}
$start = microtime(true);
$plan  = $planner->plan($result);
printf("Planned in %.2f s: %s; not planned %d\n", microtime(true) - $start,
    implode(', ', array_map(fn ($night, $objects) => $night.': '.count($objects), array_keys($plan['nights']), $plan['nights'])), count($plan['unplanned']));
