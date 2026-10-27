<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * The contrast reserves stored for binoculars were those of one eye; with
     * both eyes they are higher (ContrastReserveCalculator::BINOCULAR_GAIN)
     * and binoculars without a magnification get one of their own. They are
     * calculated again when needed.
     */
    public function up(): void
    {
        DB::table('user_object_metrics')
            ->whereIn('instrument_id', DB::table('instruments')
                ->join('instrument_types', 'instrument_types.id', '=', 'instruments.instrument_type_id')
                ->where('instrument_types.name', 'Binoculars')
                ->select('instruments.id'))
            ->delete();
    }

    public function down(): void
    {
    }
};
