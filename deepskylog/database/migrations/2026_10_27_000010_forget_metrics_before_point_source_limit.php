<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    /**
     * The contrast reserve is no longer above the one of a star of the
     * object's magnitude (ContrastReserveCalculator::pointSourceLimit()),
     * which lowers it for small objects near the limit of the instrument,
     * the most of the stored metrics. They are calculated again when needed.
     */
    public function up(): void
    {
        DB::table('user_object_metrics')->delete();
    }

    public function down(): void
    {
    }
};
