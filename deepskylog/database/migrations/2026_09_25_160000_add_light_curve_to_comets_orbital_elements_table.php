<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * The light curve of aerith.net in parts, each valid for a range of days
     * around the perihelion: [{"from", "to", "H", "K", "shift"}, ...].
     * Filled by `php artisan astronomy:updateCometPhotometry` (astronomy
     * library 6.8.3 and later) and used by App\Services\CometCoordinates.
     */
    public function up(): void
    {
        if (Schema::hasTable('comets_orbital_elements') && ! Schema::hasColumn('comets_orbital_elements', 'light_curve')) {
            Schema::table('comets_orbital_elements', function (Blueprint $table) {
                $table->json('light_curve')->nullable()->after('n_post');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('comets_orbital_elements') && Schema::hasColumn('comets_orbital_elements', 'light_curve')) {
            Schema::table('comets_orbital_elements', function (Blueprint $table) {
                $table->dropColumn('light_curve');
            });
        }
    }
};
