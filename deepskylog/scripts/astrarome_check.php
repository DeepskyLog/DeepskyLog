<?php

// Why does a place get 7Timer's seeing instead of AstrArome's?
// php scripts/astrarome_check.php [lat] [lon]   (default Leuven)

use App\Services\Weather\SeeingForecast;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$lat = (float) ($argv[1] ?? 50.88);
$lon = (float) ($argv[2] ?? 4.70);

echo 'config services.astrarome: '.json_encode(config('services.astrarome'))."\n";
echo 'config cached: '.(app()->configurationIsCached() ? 'yes' : 'no')."\n";
echo 'cache store: '.config('cache.default')."\n";
echo 'paused: '.json_encode(Cache::get('seeing_forecast:astrarome:paused'))."\n";
$run = Cache::get('seeing_forecast:astrarome:run');
echo 'cached run: '.($run ? gmdate('Y-m-d H:i', $run).'Z' : 'none')."\n";

$cellLat = round(round($lat / 0.05) * 0.05, 2);
$cellLon = round(round($lon / 0.05) * 0.05, 2);
$cell    = Cache::get('seeing_forecast:astrarome:points:'.$cellLat.':'.$cellLon);
echo "cell {$cellLat}:{$cellLon}: ";
if (is_array($cell) && isset($cell['run'])) {
    echo 'run '.gmdate('Y-m-d H:i', $cell['run']).'Z, '.count($cell['time']).' points';
    if ($cell['time']) {
        echo ' from '.gmdate('Y-m-d H:i', $cell['time'][0]).'Z to '.gmdate('Y-m-d H:i', end($cell['time'])).'Z';
    }
} else {
    echo json_encode($cell);
}
echo "\n";

echo "\nDirect request (outside the service, so no pause or lock):\n";
try {
    $base     = rtrim((string) config('services.astrarome.url'), '/');
    $response = Http::timeout(10)->withUserAgent(SeeingForecast::USER_AGENT)->acceptJson()->get($base.'/api/runs/latest');
    echo '  '.$response->status().' '.substr($response->body(), 0, 200)."\n";
} catch (Throwable $e) {
    echo '  error: '.$e->getMessage()."\n";
}

echo "\nThrough the service (fetch: true):\n";
$tz    = 'Europe/Brussels';
$night = now($tz)->hour < 12 ? now($tz)->subDay()->toDateString() : now($tz)->toDateString();
$t     = microtime(true);
$seen  = SeeingForecast::forNight($lat, $lon, $night, $tz, fetch: true);
printf("  night %s: %s (%.1f s)\n", $night, json_encode($seen), microtime(true) - $t);
echo '  paused now: '.json_encode(Cache::get('seeing_forecast:astrarome:paused'))."\n";

echo "\nWhat the code has:\n";
$grid = (new ReflectionClassConstant(SeeingForecast::class, 'AROME_GRID'))->getValue();
echo '  AROME_GRID in the code: '.$grid."\n";
echo '  git commit: '.trim((string) shell_exec('git -C '.escapeshellarg(base_path()).' log -1 --oneline 2>&1'))."\n";
$points = SeeingForecast::points($lat, $lon, false);
$arome  = $points['astrarome'] ?? null;
if ($arome === null) {
    echo "  astrarome points: none\n";
} else {
    echo '  astrarome run '.gmdate('Y-m-d H:i', $arome['run']).'Z, '.count($arome['time']).' points';
    if ($arome['time']) {
        echo ' from '.gmdate('Y-m-d H:i', $arome['time'][0]).'Z to '.gmdate('Y-m-d H:i', end($arome['time'])).'Z';
    }
    echo "\n";
}
$window = App\Services\NightWindow::for($lat, $lon, $night, $tz);
echo '  dark hours: '.($window ? gmdate('Y-m-d H:i', $window->start).'Z to '.gmdate('Y-m-d H:i', $window->end).'Z' : 'none')."\n";

echo "\nAstrArome lines in today's log:\n";
$log = storage_path('logs/laravel-'.now()->toDateString().'.log');
if (is_file($log)) {
    $lines = array_filter(file($log), fn ($l) => str_contains($l, 'AstrArome'));
    echo $lines ? implode('', array_slice($lines, -10)) : "  none\n";
} else {
    echo "  no log file\n";
}
