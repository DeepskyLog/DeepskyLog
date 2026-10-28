<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * The diameter (km, the length of a rille or valley) of a lunar feature,
     * from the IAU Gazetteer of Planetary Nomenclature, which decides how high
     * the Sun may stand for the feature to be well placed at the terminator
     * (App\Services\Moon\LunarTerminator). Filled by
     * lunar-features:fill-from-gazetteer.
     */
    public function up(): void
    {
        Schema::table('lunar_features', function (Blueprint $table) {
            $table->decimal('diameter', 8, 2)->nullable()->after('lon');
        });
    }

    public function down(): void
    {
        Schema::table('lunar_features', function (Blueprint $table) {
            $table->dropColumn('diameter');
        });
    }
};
