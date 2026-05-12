<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Add stored generated columns + indexes to allow fast double-star lookups
     * without correlated EXISTS subqueries.
     *
     * Before: WHERE EXISTS (SELECT 1 FROM objects WHERE LOWER(REPLACE(objects.name,' ','')) = LOWER(REPLACE(observations.target_name,' ','')) AND objects.type='DS')
     * After:  WHERE target_name_normalized IN (SELECT name_normalized FROM objects WHERE type='DS')
     *         — both columns are stored+indexed so MySQL can use index scans.
     */
    public function up(): void
    {
        // Stored generated column on objects: LOWER(REPLACE(name,' ',''))
        DB::statement("ALTER TABLE objects ADD COLUMN name_normalized VARCHAR(200) GENERATED ALWAYS AS (LOWER(REPLACE(name, ' ', ''))) STORED");
        DB::statement('CREATE INDEX idx_objects_name_normalized ON objects (name_normalized)');

        // Stored generated column on observations: LOWER(REPLACE(target_name,' ',''))
        DB::statement("ALTER TABLE observations ADD COLUMN target_name_normalized VARCHAR(200) GENERATED ALWAYS AS (LOWER(REPLACE(target_name, ' ', ''))) STORED");
        DB::statement('CREATE INDEX idx_obs_target_name_normalized ON observations (target_name_normalized)');

        // Composite index for the common query pattern: target_table='objects' AND target_name_normalized IN (...)
        DB::statement('CREATE INDEX idx_obs_table_name_normalized ON observations (target_table, target_name_normalized)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX idx_obs_table_name_normalized ON observations');
        DB::statement('DROP INDEX idx_obs_target_name_normalized ON observations');
        DB::statement('ALTER TABLE observations DROP COLUMN target_name_normalized');

        DB::statement('DROP INDEX idx_objects_name_normalized ON objects');
        DB::statement('ALTER TABLE objects DROP COLUMN name_normalized');
    }
};
