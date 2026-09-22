<?php
require __DIR__ . '/../vendor/autoload.php';
putenv('LOG_CHANNEL=errorlog');

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Http\Request;

$params = [
    'type' => 'overview',
    'paper' => 'A4',
    'orientation' => 'landscape',
    'scope' => 'single',
    'ra' => 330.0,
    'dec' => 84.0,
    'title' => 'Atlas page 1 test',
];

$request = Request::create('/', 'POST', $params);

try {
    $controller = $app->make(\App\Http\Controllers\AtlasController::class);
    $response = $controller->generate($request, $app->make(\App\Services\StarChartService::class));

    ob_start();
    $response->send();
    $content = ob_get_clean();

    $out = __DIR__ . '/../tmp/dsl_atlas_page1.pdf';
    if (file_put_contents($out, $content) === false) {
        fwrite(STDERR, "Failed to write $out\n");
        exit(1);
    }

    echo "Wrote: $out\n";
} catch (\Throwable $e) {
    fwrite(STDERR, "Exception: " . $e->getMessage() . PHP_EOL);
    exit(1);
}
