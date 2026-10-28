<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * A one-degree declination zone for StarChartService::loadStars().
     *
     * On the (decl_degrees, ra_degrees) index a chart's declination range is
     * a range on the first column, so MariaDB could not use the right
     * ascension part: a detail chart read every star in its declination band
     * around the whole sky (millions of index entries, then the rows, to test
     * the magnitude) -- 4 to 6 seconds a chart. With the zone the query asks
     * for `decl_zone IN (...)`, an equality per zone, so the right ascension
     * range is used as well. With the magnitude and declination in the index
     * too, the chart query reads the index alone (InnoDB does not push
     * conditions on a virtual column down to the index, so a query that has
     * to read the rows would test them all).
     *
     * A virtual generated column: nothing that inserts stars has to fill it,
     * and on MariaDB adding it does not rebuild the 38 million row table --
     * only the index is built (and stores the value). SQLite (the tests)
     * indexes a virtual column as well.
     */
    public function up(): void
    {
        if (! Schema::hasTable('stars') || Schema::hasColumn('stars', 'decl_zone')) {
            return;
        }

        Schema::table('stars', function (Blueprint $table) {
            $table->smallInteger('decl_zone')->nullable()->virtualAs('floor(decl_degrees)');
        });

        Schema::table('stars', function (Blueprint $table) {
            $table->index(['decl_zone', 'ra_degrees', 'vmag', 'decl_degrees'], 'stars_decl_zone_ra_vmag_index');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('stars', 'decl_zone')) {
            return;
        }

        Schema::table('stars', function (Blueprint $table) {
            $table->dropIndex('stars_decl_zone_ra_vmag_index');
        });

        Schema::table('stars', function (Blueprint $table) {
            $table->dropColumn('decl_zone');
        });
    }
};
