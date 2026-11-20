<?php

// Calls every tool of the MCP server (/mcp) in-process as a given observer,
// through the HTTP kernel with a temporary personal access token, and prints
// the status, time and size of each answer (with -v the answers themselves):
// php scripts/mcp_smoke.php [username] [-v]
require __DIR__.'/../vendor/autoload.php';
$app    = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$args    = array_values(array_filter(array_slice($argv, 1), fn ($arg) => $arg !== '-v'));
$verbose = in_array('-v', $argv, true);
$user    = App\Models\User::where('username', $args[0] ?? 'wim')->firstOrFail();
// A temporary token that may write, deleted again at the end (with the list it makes).
$token = $user->createToken('mcp_smoke', ['read', 'write']);

$id   = 0;
$call = function (string $method, array $params = []) use ($kernel, $token, &$id) {
    $request = Illuminate\Http\Request::create('/mcp', 'POST', [], [], [], [
        'CONTENT_TYPE'       => 'application/json',
        'HTTP_ACCEPT'        => 'application/json, text/event-stream',
        'HTTP_AUTHORIZATION' => 'Bearer '.$token->plainTextToken,
    ], json_encode(['jsonrpc' => '2.0', 'id' => ++$id, 'method' => $method, 'params' => $params]));
    $t        = microtime(true);
    $response = $kernel->handle($request);

    return [$response->getStatusCode(), (microtime(true) - $t) * 1000, $response->getContent()];
};

[$status, $ms, $body] = $call('tools/list');
$tools                = array_column(json_decode($body, true)['result']['tools'] ?? [], 'name');
printf("%-26s %d %6.0f ms %s\n", 'tools/list', $status, $ms, implode(', ', $tools));

$object      = App\Models\Observation::where('observer_username', $user->username)->where('target_type', 'deepsky')->latest('observed_on')->value('target_name') ?? 'M 31';
$observation = App\Models\Observation::where('observer_username', $user->username)->latest('observed_on')->value('id');
$list        = App\Models\ObservingList::where('owner_user_id', $user->id)->latest('updated_at')->value('id');

$calls = [
    ['get-my-profile', []],
    ['list-my-instrument-sets', []],
    ['search-objects', ['query' => 'andromeda']],
    ['get-object', ['name' => $object]],
    ['get-object', ['name' => 'STF 2382']],
    ['list-my-observations', ['limit' => 25]],
    ['get-observation', ['id' => $observation]],
    ['get-my-statistics', []],
    ['get-my-statistics', ['year' => (int) date('Y') - 1]],
    ['get-tonights-best', []],
    ['get-night-forecast', []],
    ['list-my-observing-lists', []],
    ['get-observing-list', ['id' => $list]],
    ['get-catalog-progress', ['catalog' => 'H400', 'limit' => 20]],
    ['list-my-goals', []],
    ['create-observing-list', ['name' => 'mcp_smoke', 'objects' => ['M 31', 'M 32', 'Nothing 1']]],
];
foreach ($calls as [$tool, $arguments]) {
    [$status, $ms, $body] = $call('tools/call', ['name' => $tool, 'arguments' => $arguments]);
    $json                 = json_decode($body, true);
    $error                = $json['error']['message'] ?? (($json['result']['isError'] ?? false) ? ($json['result']['content'][0]['text'] ?? 'error') : null);
    printf("%-26s %d %6.0f ms %6d bytes %s\n", $tool, $status, $ms, strlen($body), $error !== null ? 'ERROR: '.$error : '');
    if ($verbose) {
        echo json_encode($json['result']['structuredContent'] ?? $json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), "\n";
    }
}

App\Models\ObservingList::where('owner_user_id', $user->id)->where('name', 'like', 'mcp_smoke%')->each(fn ($list) => $list->delete());
$token->accessToken->delete();
