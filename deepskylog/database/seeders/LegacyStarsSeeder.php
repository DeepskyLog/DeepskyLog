<?php

namespace Database\Seeders;

use App\Support\LegacyStarCatalog;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PDO;

class LegacyStarsSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (! Schema::hasTable('stars')) {
            return;
        }

        $legacy = DB::connection('mysqlOld');
        $tables = LegacyStarCatalog::discoverTables($legacy);

        foreach ($tables as $table) {
            $columns = $legacy->getSchemaBuilder()->getColumnListing($table);

            $selectColumns = array_values(array_intersect([
                'type',
                'nameCon',
                'nameBayer',
                'nameBayer2',
                'nameFlamSteed',
                'name',
                'RA2000mas',
                'DE2000mas',
                'RA2000',
                'DE2000',
                'vMag',
                'spType',
            ], $columns));

            if (! in_array('RA2000', $selectColumns, true) || ! in_array('DE2000', $selectColumns, true)) {
                continue;
            }

            // A table imported completely before is skipped, so a run that stopped can be started again.
            // Stars with the same name, position and magnitude are one row in `stars`, so a table with
            // such duplicates is never complete by this count and is read again (insertOrIgnore).
            $legacyCount = $legacy->table($table)->count();
            $imported = DB::table('stars')->where('source_table', $table)->count();
            if ($imported >= $legacyCount) {
                $this->command?->info(sprintf('%s: all %d stars imported already.', $table, $legacyCount));

                continue;
            }

            $this->command?->info(sprintf('Importing %d legacy stars from %s (%d already)...', $legacyCount, $table, $imported));

            $this->importTable($legacy, $table, $selectColumns);
        }
    }

    /**
     * Reads the table in one pass, without an order: paging with LIMIT/OFFSET rereads every earlier
     * row for each page (the tables have up to 13 million rows and no primary key) and skips rows of
     * the same RA at a page boundary. The query is unbuffered, so the rows are not held in memory.
     */
    private function importTable(ConnectionInterface $legacy, string $table, array $selectColumns): void
    {
        $legacy->statement('SET SESSION net_write_timeout = 3600');
        $pdo = $legacy->getPdo();
        $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);

        try {
            $batch = [];
            $read = 0;
            foreach ($legacy->table($table)->select($selectColumns)->cursor() as $row) {
                $batch[] = LegacyStarCatalog::normalizeRow($table, $row);
                if (count($batch) === 1000) {
                    DB::table('stars')->insertOrIgnore($batch);
                    $batch = [];
                }
                if (++$read % 1000000 === 0) {
                    $this->command?->info(sprintf('  %s: %d read', $table, $read));
                }
            }
            if ($batch !== []) {
                DB::table('stars')->insertOrIgnore($batch);
            }
        } finally {
            $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
        }
    }
}
