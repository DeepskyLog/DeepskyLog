<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Default connection: " . config('database.default') . PHP_EOL;
$connections = array_keys(config('database.connections') ?? []);
foreach ($connections as $c) {
    try {
        $has = $app->make('db')->connection($c)->getSchemaBuilder()->hasTable('cache') ? 'yes' : 'no';
        $conf = config('database.connections.' . $c) ?? [];
        $host = $conf['host'] ?? ($conf['read']['host'][0] ?? '<none>');
        $dbName = $conf['database'] ?? '<none>';
        echo "$c: $has (host: $host, database: $dbName)\n";
    } catch (Throwable $e) {
        echo "$c: error: " . $e->getMessage() . "\n";
    }
}

// Also show actual database names for mysql connections
$mysqlConfig = config('database.connections.mysql') ?? null;
if ($mysqlConfig) {
    echo "mysql.database: " . ($mysqlConfig['database'] ?? '<none>') . PHP_EOL;
    echo "mysql.host: " . ($mysqlConfig['host'] ?? '<none>') . PHP_EOL;
    echo "mysql.port: " . ($mysqlConfig['port'] ?? '<none>') . PHP_EOL;
}

exit(0);
