<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * The local horizon of a location (App\Services\LocalHorizon): the
     * altitude of the trees, houses and hills around it every 15° of
     * azimuth, null for an open horizon.
     */
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->json('horizon')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn('horizon');
        });
    }
};
