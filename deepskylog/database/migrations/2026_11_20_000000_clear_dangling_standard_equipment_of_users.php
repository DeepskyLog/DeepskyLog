<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Clears the standard telescope, location, eyepiece and instrument set of the
 * observers whose row no longer exists (old data: 7 telescopes and 8 locations
 * on the server), which broke their observer page. Deleting equipment now
 * clears the default itself (DetachesOnDelete).
 */
return new class extends Migration
{
    private const COLUMNS = [
        'stdtelescope' => 'instruments',
        'stdlocation' => 'locations',
        'stdeyepiece' => 'eyepieces',
        'stdinstrumentset' => 'instrument_sets',
    ];

    public function up(): void
    {
        foreach (self::COLUMNS as $column => $table) {
            DB::table('users')
                ->whereNotNull($column)
                ->whereNotExists(fn ($query) => $query->select(DB::raw(1))->from($table)->whereColumn($table.'.id', 'users.'.$column))
                ->update([$column => null]);
        }
    }

    public function down(): void
    {
        // The deleted rows are gone; there is nothing to restore.
    }
};
