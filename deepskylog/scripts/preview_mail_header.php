<?php

// Minimal script to bootstrap Laravel and render the vendor mail header for preview
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$status = $kernel->bootstrap();

echo view('vendor.mail.html.header', ['url' => config('app.url'), 'slot' => 'DeepskyLog'])->render();
