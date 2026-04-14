<?php

// Drop cache table script for migrations conflict resolution
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$db = $app->make('db');
try {
    $db->statement('DROP TABLE IF EXISTS `cache`');
    echo "Dropped `cache` table if it existed.\n";
} catch (Throwable $e) {
    echo "Error dropping `cache` table: " . $e->getMessage() . "\n";
    exit(1);
}

exit(0);
