<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Objects whose RA was stored in degrees; objects.ra is in decimal hours.
     * An edited object in the first 1.6 h of RA got a value below 24, which
     * cannot be told apart by range. These were found by comparing the stored
     * constellation with the one of the stored RA, and checked against SIMBAD
     * (Hickson 12 is at 1.4594 h). Keyed by name, with the degrees value, so
     * a row that was corrected in the meantime is left alone.
     */
    private const STORED_IN_DEGREES = [
        'Hickson 12' => 21.8916,
    ];

    /**
     * objects.ra is in decimal hours, but editing an object stored the RA in
     * degrees (M 65 as 169.731). A value above 24 can only be degrees, so it is
     * converted back to hours, in objects and in the search index, as are the
     * objects listed above. The constellation and atlas pages of those objects
     * were already calculated from the hours, so they are left alone.
     */
    public function up(): void
    {
        DB::table('objects')->where('ra', '>', 24)->update(['ra' => DB::raw('ra / 15')]);

        DB::table('search_index')
            ->where('source_table', 'objects')
            ->where('ra', '>', 24)
            ->update(['ra' => DB::raw('ra / 15')]);

        foreach (self::STORED_IN_DEGREES as $name => $raDegrees) {
            $slug = DB::table('objects')->where('name', $name)->whereBetween('ra', [$raDegrees - 0.0001, $raDegrees + 0.0001])->value('slug');
            if ($slug === null) {
                continue;
            }

            DB::table('objects')->where('name', $name)->update(['ra' => $raDegrees / 15]);
            DB::table('search_index')
                ->where('source_table', 'objects')
                ->where('source_pk', $slug)
                ->whereBetween('ra', [$raDegrees - 0.0001, $raDegrees + 0.0001])
                ->update(['ra' => $raDegrees / 15]);
        }
    }

    public function down(): void
    {
        // The degrees were never meant to be stored; there is nothing to restore.
    }
};
