<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The old site (/var/www/DeepskyLog.old) reads the focal ratio `fd` of an
 * instrument and gets '' for NULL, which PHP 8 refuses in round() and in
 * `fd * diameter` (its instrument list, OAL export and ICQ export). Since
 * 2026_11_20_000010 an unknown focal length is NULL instead of 1 mm, so `fd`
 * gives 0 for it, as it did for the 1 mm placeholder. MySQL/MariaDB only, like
 * the legacy views: the test database does not serve the old site.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::statement('ALTER TABLE instruments MODIFY fd DOUBLE GENERATED ALWAYS AS (COALESCE(ROUND(focal_length_mm / NULLIF(aperture_mm, 0), 1), 0)) VIRTUAL');
    }

    public function down(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::statement('ALTER TABLE instruments MODIFY fd DOUBLE GENERATED ALWAYS AS (ROUND(focal_length_mm / aperture_mm, 1)) VIRTUAL');
    }
};
