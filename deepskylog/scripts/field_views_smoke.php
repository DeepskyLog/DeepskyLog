<?php
// Renders the field views of an object for an observer and writes the SVGs
// (and PNGs, when rsvg-convert is installed) to the given directory.
// Usage: php scripts/field_views_smoke.php <username> "<object>" <dir> ['{"datetime":"2026-10-06T22:00","instrument":1}']
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

[$_, $username, $objectName, $dir] = $argv + [null, null, 'M 13', sys_get_temp_dir()];
$user = App\Models\User::where('username', $username)->firstOrFail();
$object = App\Models\DeepskyObject::where('name', $objectName)->firstOrFail();
$t = microtime(true);
$result = app(App\Services\FieldViews\FieldViewBuilder::class)->build($user, $object, json_decode($argv[4] ?? '{}', true) ?: [], $argv[5] ?? 'dark');
printf("%.2fs  location=%s instrument=%s eyepiece=%s finder=%s nelm=%s alt=%s\n", microtime(true) - $t,
    $result['location']?->name, $result['instrument']?->name, $result['eyepiece']?->name, $result['finder']?->name, $result['nelm'], $result['altitude']);
foreach ($result['warnings'] as $w) echo "warning: $w\n";
foreach ($result['views'] as $key => $view) {
    if (isset($view['svg'])) {
        file_put_contents("$dir/$key.svg", $view['svg']);
        @exec('rsvg-convert '.escapeshellarg("$dir/$key.svg").' -o '.escapeshellarg("$dir/$key.png"));
        printf("%s: fov=%s limit=%s %s\n", $key, $view['fov'], $view['limit'], $view['subtitle'] ?? '');
    } else {
        printf("%s: %s\n", $key, $view['message']);
    }
}
