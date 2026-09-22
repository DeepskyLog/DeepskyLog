<?php
require __DIR__ . '/../vendor/autoload.php';
putenv('LOG_CHANNEL=errorlog');

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\DeepskyObject;
use App\Services\StarChartService;

// Create a transient target centered at RA=22h (330 deg), Dec=+84°
$target = new DeepskyObject();
$target->name = 'TEST-22h+84';
$target->ra = 330.0; // degrees or hours accepted by build
$target->decl = 84.0;

$centerRaDeg = 330.0;
$centerDecDeg = 84.0;
// Use an overview-like FoV: full-field approx 21 x 14 deg => radius ~ 7 deg => arcmin 420
$radiusArcMin = 630; // try 10.5° radius (630') to match 21° x14° in example

$svc = $app->make(StarChartService::class);
$chart = $svc->build($target, $centerRaDeg, $centerDecDeg, $radiusArcMin, null, 10.0, 10.0, false, true);

$svgOut = __DIR__ . '/../tmp/test_polar_chart.svg';
file_put_contents($svgOut, $chart['svg']);

echo "Wrote: $svgOut\n";
