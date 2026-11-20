<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The old site stored a focal length of 1 mm (the form 0 mm, and the OAL
 * import of the old site 7 mm for the naked eye) for instruments with a fixed
 * magnification: binoculars, finders and the naked eye. They have no focal
 * length to speak of, and the pages showed "1 mm". An unknown focal length is
 * null; `Instrument::focalLengthMm()` reads what the old site still writes the
 * same way.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('instruments')
            ->where('focal_length_mm', '<=', 1)
            ->update(['focal_length_mm' => null]);

        $nakedEye = DB::table('instrument_types')->where('name', 'Naked Eye')->pluck('id');
        if ($nakedEye->isNotEmpty()) {
            DB::table('instruments')
                ->whereIn('instrument_type_id', $nakedEye)
                ->whereNotNull('focal_length_mm')
                ->update(['focal_length_mm' => null]);
        }
    }

    public function down(): void
    {
        // The placeholders carried no information.
    }
};
