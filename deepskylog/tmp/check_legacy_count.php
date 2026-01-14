<?php
chdir(__DIR__ . '/..');
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Boot the framework partially
// Boot the HTTP kernel to ensure database connections and facades are available
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/', 'GET', [], [], [], ['HTTP_HOST' => 'localhost']);
$kernel->handle($request);
try {
    $count = \App\Models\ObservationsOld::getObservationsCountForObject('Moon');
    echo "ObservationsOld::getObservationsCountForObject('Moon') = ";
    var_export($count);
    echo "\n";
} catch (\Throwable $e) {
    echo "Error: ", $e->getMessage(), "\n";
}
$kernel->terminate($request, new \Illuminate\Http\Response('OK'));
