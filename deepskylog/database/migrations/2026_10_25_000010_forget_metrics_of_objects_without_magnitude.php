<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * The contrast reserves stored for objects with a magnitude of 0, which
     * means unknown but was calculated as magnitude 0, so Tonight's best
     * proposed AND IV as very easy to see. They are calculated again,
     * without the magnitude, when needed.
     */
    public function up(): void
    {
        DB::table('user_object_metrics')
            ->whereIn('object_name', DB::table('objects')->where('mag', 0)->select('name'))
            ->delete();
    }

    public function down(): void
    {
    }
};
