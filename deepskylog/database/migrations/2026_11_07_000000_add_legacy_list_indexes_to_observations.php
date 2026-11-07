<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Indexes for the queries the old site runs on the view legacy_observations
 * (WHERE target_type = 'deepsky'): the count of every observation list
 * (language, date: the view's date is computed from observed_on, which the
 * index covers), the drawing lists and statistics (has_drawing, grouped by
 * target_name), and the hourly observations:check-suspect (updated_at).
 * Without them each of these read 100,000 to 200,000 rows (2-4 s on the
 * server under load). The drawing lists of a catalog join objectnames on
 * objectname and filter on altname (LIKE "NGC %"), which the objectnames index
 * covers.
 */
return new class extends Migration
{
    private const INDEXES = [
        'objectnames' => [
            'idx_objectnames_objectname_altname' => ['objectname', 'altname'],
        ],
        'observations' => [
            'idx_obs_legacy_language' => ['target_type', 'language', 'observed_on'],
            'idx_obs_legacy_drawing' => ['target_type', 'has_drawing', 'target_name'],
            'idx_obs_updated_at' => ['updated_at'],
        ],
    ];

    public function up(): void
    {
        foreach (self::INDEXES as $tableName => $indexes) {
            foreach ($indexes as $name => $columns) {
                if (! Schema::hasIndex($tableName, $name)) {
                    Schema::table($tableName, function (Blueprint $table) use ($name, $columns) {
                        $table->index($columns, $name);
                    });
                }
            }
        }
    }

    public function down(): void
    {
        foreach (self::INDEXES as $tableName => $indexes) {
            foreach (array_keys($indexes) as $name) {
                if (Schema::hasIndex($tableName, $name)) {
                    Schema::table($tableName, function (Blueprint $table) use ($name) {
                        $table->dropIndex($name);
                    });
                }
            }
        }
    }
};
