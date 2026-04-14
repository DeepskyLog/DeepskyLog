<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$connections = ['mysql', 'mariadb', 'mysqlOld'];
foreach ($connections as $c) {
    try {
        $conn = $app->make('db')->connection($c);
        $conn->statement('DROP TABLE IF EXISTS `cache`');
        $conn->statement('DROP TABLE IF EXISTS `cache_locks`');
        echo "Dropped `cache` and `cache_locks` on connection $c\n";
    } catch (Throwable $e) {
        echo "Failed to drop on $c: " . $e->getMessage() . "\n";
    }
}

exit(0);
