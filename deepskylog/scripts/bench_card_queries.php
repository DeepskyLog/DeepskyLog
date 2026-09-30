<?php

// Counts the queries (and time) of the pages that render observation/sketch
// cards, as a guest and as a logged-in user. Usage: php scripts/bench_card_queries.php [username]

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

config(['responsecache.enabled' => false]);

$session = DB::table('sessionObservations')->select('sessionid', DB::raw('count(*) c'))
    ->groupBy('sessionid')->orderByDesc('c')->first();
$sessionRow = DB::table('observation_sessions')->where('id', $session->sessionid)->first();
$sessionOwner = DB::table('users')->where('username', $sessionRow->observerid)->value('slug');
$drawer = DB::table('observations')->where('has_drawing', 1)->select('observer_username', DB::raw('count(*) c'))
    ->groupBy('observer_username')->orderByDesc('c')->value('observer_username');
$drawerSlug = DB::table('users')->where('username', $drawer)->value('slug');

$urls = [
    '/',
    '/observations',
    '/drawings',
    '/cometdrawings',
    '/drawings/'.$drawerSlug,
    '/session/'.$sessionOwner.'/'.$sessionRow->slug,
    '/observers/'.$drawerSlug,
];

$login = $argv[1] ?? null;
$user = $login ? App\Models\User::where('username', $login)->first() : null;

foreach ($urls as $url) {
    $request = Illuminate\Http\Request::create($url);
    app()->instance('request', $request);
    if ($user) {
        auth()->guard('web')->setUser($user);
        $request->setUserResolver(fn () => $user);
    }
    DB::flushQueryLog();
    DB::enableQueryLog();
    $t = microtime(true);
    $response = $kernel->handle($request);
    $ms = (microtime(true) - $t) * 1000;
    $log = DB::getQueryLog();
    DB::disableQueryLog();
    $dbMs = array_sum(array_column($log, 'time'));
    printf("%-55s %3d  %4d queries  %6.0f ms db  %6.0f ms total  %s\n", $url, $response->getStatusCode(), count($log), $dbMs, $ms, md5(preg_replace('/(csrf|_token|nonce|wire:snapshot|wire:id|wire:effects)[^\s>]*/', '', (string) $response->getContent())));
    if ($dir = getenv('BENCH_DUMP')) {
        @mkdir($dir, 0777, true);
        file_put_contents($dir.'/'.trim(str_replace('/', '_', $url), '_').($login ? '-'.$login : '').'.html', (string) $response->getContent());
    }
    $kernel->terminate($request, $response);
    app('view')->flushState();
}
