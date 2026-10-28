<?php
// Times /observations and /observations?following=1 through the HTTP kernel
// for a logged-in user: php scripts/bench_following.php [username]
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

$username = $argv[1] ?? 'wim';
foreach (array_slice($argv, 2) ?: ['/observations', '/observations?following=1'] as $uri) {
    $user = User::where('username', $username)->firstOrFail();
    auth()->guard('web')->setUser($user);
    DB::flushQueryLog();
    DB::enableQueryLog();
    $t = microtime(true);
    $response = $kernel->handle(Illuminate\Http\Request::create($uri));
    $elapsed = microtime(true) - $t;
    $queries = DB::getQueryLog();
    printf("%-30s %d  %.2f s  %d queries, %.2f s in SQL\n", $uri, $response->getStatusCode(), $elapsed, count($queries), array_sum(array_column($queries, 'time')) / 1000);
    foreach ($queries as $q) {
        if ($q['time'] > 100) printf("   %8.1f ms  %s\n", $q['time'], substr($q['query'], 0, 200));
    }
}
