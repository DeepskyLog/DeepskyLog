<?php
// Checks the responses of the /api/v1/me endpoints of a given observer against
// their OpenAPI description (App\Support\Api\MeApiSpec), with real data:
// php scripts/me_api_spec_check.php [username]
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();
// url() in the description needs a request
$app->instance('request', Illuminate\Http\Request::create(config('app.url')));
$spec = App\Support\Api\MeApiSpec::spec();
$user = App\Models\User::where('username', $argv[1] ?? 'wim')->firstOrFail();
// A temporary personal access token, deleted again at the end.
$token = $user->createToken('me_api_spec_check', ['read']);
$get = fn (string $uri) => $kernel->handle(Illuminate\Http\Request::create($uri, 'GET', ['per_page' => 500], [], [], ['HTTP_ACCEPT' => 'application/json', 'HTTP_AUTHORIZATION' => 'Bearer '.$token->plainTextToken]));
try {
    foreach (array_keys($spec['paths']) as $path) {
        $uri = $path;
        if (str_contains($path, '{id}')) {
            // the first of the list
            $list = json_decode($get(str_replace('/{id}', '', $path))->getContent(), true);
            $first = $list['data'][0] ?? $list[0] ?? null;
            if ($first === null) {
                printf("%-35s none to check\n", $path);
                continue;
            }
            $uri = str_replace('{id}', $first['id'], $path);
        }
        $response = $get($uri);
        $problems = App\Support\Api\OpenApiValidator::problems(json_decode($response->getContent(), true),
            $spec['paths'][$path]['get']['responses']['200']['content']['application/json']['schema'], $spec);
        // one line per kind of problem, not per row
        $problems = array_values(array_unique(array_map(fn ($p) => preg_replace('/\[\d+\]/', '[]', $p), $problems)));
        printf("%-35s %d %s\n", $path, $response->getStatusCode(), $problems === [] ? 'ok' : implode("\n    ", ['', ...$problems]));
    }
} finally {
    $token->accessToken->delete();
}
