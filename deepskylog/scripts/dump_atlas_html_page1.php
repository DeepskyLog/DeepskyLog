<?php
require __DIR__ . '/../vendor/autoload.php';
putenv('LOG_CHANNEL=errorlog');

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Http\Request;
use App\Models\DeepskyObject;

$type = 'overview';
$paper = 'A4';
$orientation = 'landscape';
$scope = 'single';
$ra = 330.0;
$dec = 84.0;
$title = 'Atlas page 1 test';

$user = auth()->user() ?? App\Models\User::find(1);

$orientationIndex = $orientation === 'landscape' ? 3 : 0;
$typeIndex = match ($type) {
    'overview' => 0,
    'lookup' => 1,
    default => 2,
};
$atlasIndex = $typeIndex + $orientationIndex;

if ($type === 'overview') {
    $fov = 840;
    $objectMag = is_numeric($user->overviewdsos) ? (float) $user->overviewdsos : 10.0;
    $starMag = is_numeric($user->overviewstars) ? (float) $user->overviewstars : 10.0;
} elseif ($type === 'lookup') {
    $fov = 560;
    $objectMag = is_numeric($user->lookupdsos) ? (float) $user->lookupdsos : 12.0;
    $starMag = is_numeric($user->lookupstars) ? (float) $user->lookupstars : 12.0;
} else {
    $fov = 360;
    $objectMag = is_numeric($user->detaildsos) ? (float) $user->detaildsos : 15.0;
    $starMag = is_numeric($user->detailstars) ? (float) $user->detailstars : 14.0;
}

$radiusArcMin = max(5, (int) round(((float) $fov) / 2.0));

$target = new DeepskyObject();
$target->name = 'Atlas page 1 test';
$target->ra = $ra;
$target->decl = $dec;
$target->atlasVariant = $atlasIndex;

$centerRaDeg = $ra;
$centerDecDeg = $dec;

/** @var \App\Services\StarChartService $starChartService */
$starChartService = $app->make(\App\Services\StarChartService::class);
$chart = $starChartService->build($target, $centerRaDeg, $centerDecDeg, $radiusArcMin, null, $objectMag, $starMag, false, true);

$pw = 297; $ph = 210; // landscape
$charts = [[
    'title' => $title,
    'svg' => $chart['svg'],
]];

$html = view('pdf.star_chart_bundle', ['charts' => $charts, 'pageWidthMm' => $pw, 'pageHeightMm' => $ph])->render();
$out = __DIR__ . '/../tmp/dsl_atlas_page1.html';
file_put_contents($out, $html);
echo "Wrote: $out\n";
