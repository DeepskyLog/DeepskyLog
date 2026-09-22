<?php
require __DIR__ . '/../vendor/autoload.php';
putenv('LOG_CHANNEL=errorlog');

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\DeepskyObject;
use App\Services\StarChartService;

$target = DeepskyObject::where('slug', 'm-31')->orWhere('name', 'm-31')->first();
if (!$target) {
    $target = DeepskyObject::where('name', 'M 31')->first();
}
if (!$target) {
    fwrite(STDERR, "Target m-31 not found\n");
    exit(1);
}

$centerRaDeg = floatval($target->ra ?? 0.0);
if ($centerRaDeg <= 24.0) $centerRaDeg *= 15.0;
$centerDecDeg = floatval($target->decl ?? 0.0);

$svc = $app->make(StarChartService::class);
$chart = $svc->build($target, $centerRaDeg, $centerDecDeg, 15, null, 15.0, 15.0);

$svgOut = __DIR__ . '/../tmp/test_chart.svg';
$htmlOut = __DIR__ . '/../tmp/test_chart.html';
file_put_contents($svgOut, $chart['svg']);

$html = view('pdf.star_chart_bundle', ['charts' => [[
    'title' => 'Test atlas',
    'svg_data_uri' => 'data:image/svg+xml;base64,' . base64_encode($chart['svg']),
]], 'pageWidthMm' => 297, 'pageHeightMm' => 210])->render();
file_put_contents($htmlOut, $html);

echo "Wrote: $svgOut and $htmlOut\n";
