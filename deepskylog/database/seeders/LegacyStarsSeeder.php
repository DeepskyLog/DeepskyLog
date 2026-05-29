<?php

namespace Database\Seeders;

use App\Support\LegacyStarCatalog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LegacyStarsSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (!Schema::hasTable('stars')) {
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

            if (!in_array('RA2000', $selectColumns, true) || !in_array('DE2000', $selectColumns, true)) {
                continue;
            }

            if ($this->command) {
                $this->command->info(sprintf('Importing legacy stars from %s...', $table));
            }

            $legacy->table($table)
                ->select($selectColumns)
                ->orderBy('RA2000')
                ->chunk(5000, function ($rows) use ($table) {
                    $inserts = [];

                    foreach ($rows as $row) {
                        $inserts[] = LegacyStarCatalog::normalizeRow($table, $row);
                    }

                    foreach (array_chunk($inserts, 1000) as $batch) {
                        DB::table('stars')->insertOrIgnore($batch);
                    }
                });
        }
    }
}