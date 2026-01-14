<?php
// Render /object/m-31 using the application HTTP kernel and print the HTML
chdir(__DIR__ . '/..');
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

use Illuminate\Http\Request;

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Request::create('/object/m-31', 'GET', [], [], [], ['HTTP_HOST' => 'localhost']);
$response = $kernel->handle($request);
file_put_contents('/tmp/render_object_m31_headers.txt', json_encode($response->headers->all(), JSON_PRETTY_PRINT));
echo $response->getContent();
$kernel->terminate($request, $response);
