<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * The contrast reserves stored for double stars, calculated as if the
     * separation of the pair (diam1) were the size of a nebula. A double
     * star has none (ContrastReserveCalculator::appliesTo()); its metrics
     * are stored again, without a contrast reserve, when needed.
     */
    public function up(): void
    {
        DB::table('user_object_metrics')
            ->whereIn('object_name', DB::table('objects')->where('type', 'DS')->select('name'))
            ->delete();
    }

    public function down(): void
    {
    }
};
