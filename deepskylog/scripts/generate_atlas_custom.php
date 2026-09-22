<?php
// CLI helper: generate a single atlas page PDF for testing with custom RA/Dec

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Avoid file-based logging permission issues in CLI by using the errorlog channel
putenv('LOG_CHANNEL=errorlog');

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

use Illuminate\Http\Request;
// Bootstrap the application (register providers, facades, config)
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$ra = $argv[1] ?? '3.33';
$dec = $argv[2] ?? '88';

$params = [
    'type' => 'detail',
    'paper' => 'A4',
    'orientation' => 'landscape',
    'scope' => 'single',
    'ra' => $ra,
    'dec' => $dec,
    'title' => "Custom center $ra / $dec",
];

$request = Request::create('/', 'POST', $params);

try {
    $controller = $app->make(\App\Http\Controllers\AtlasController::class);
    $starChartService = $app->make(\App\Services\StarChartService::class);
    $response = $controller->generate($request, $starChartService);

    ob_start();
    $response->send();
    $content = ob_get_clean();

    $out = __DIR__ . '/../tmp/dsl_atlas_custom.pdf';
    if (file_put_contents($out, $content) === false) {
        fwrite(STDERR, "Failed to write $out\n");
        exit(1);
    }

    echo "Wrote: $out\n";
} catch (\Throwable $e) {
    fwrite(STDERR, "Exception: " . $e->getMessage() . PHP_EOL);
    exit(1);
}
