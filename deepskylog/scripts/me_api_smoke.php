<?php
// Calls the /api/v1/me endpoints in-process as a given observer and prints
// the size and timing of each response: php scripts/me_api_smoke.php [username]
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();
$user = App\Models\User::where('username', $argv[1] ?? 'wim')->firstOrFail();
// A temporary personal access token, deleted again at the end.
$token = $user->createToken('me_api_smoke', ['read']);
foreach (['', '/locations', '/instruments', '/instrument-sets', '/observations?per_page=500', '/sessions', '/observing-lists?include=subscribed'] as $path) {
    $t = microtime(true);
    $response = $kernel->handle(Illuminate\Http\Request::create('/api/v1/me'.$path, 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json', 'HTTP_AUTHORIZATION' => 'Bearer '.$token->plainTextToken]));
    $json = json_decode($response->getContent(), true);
    printf("%-40s %d %6.0f ms %s\n", $path ?: '/', $response->getStatusCode(), (microtime(true) - $t) * 1000,
        isset($json['total']) ? 'total='.$json['total'].' rows='.count($json['data']) : (array_is_list($json ?? []) ? 'rows='.count($json) : substr($response->getContent(), 0, 120)));
}
$token->accessToken->delete();
