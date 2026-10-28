<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Store the orbital elements of comets and asteroids as doubles.
     *
     * The tables were created with $table->float('q', 12, 8). Since Laravel 11
     * the second argument is the precision in bits, so the columns became
     * single precision floats with about 7 significant digits: a time of
     * perihelion of 20240421.13116 was stored as 20240422, which moves a comet
     * near perihelion by degrees. The epoch was an integer, which drops the .5
     * of the julian day. Comet positions are now calculated from these
     * elements (App\Services\CometCoordinates) instead of being asked to JPL
     * Horizons.
     *
     * Run `php artisan astronomy:updateOrbitalElements` afterwards: the values
     * already in the tables stay rounded.
     */
    private const COLUMNS = [
        'comets_orbital_elements' => ['epoch', 'q', 'e', 'i', 'w', 'node', 'Tp'],
        'asteroids_orbital_elements' => ['epoch', 'a', 'e', 'i', 'w', 'node', 'M'],
    ];

    public function up(): void
    {
        foreach (self::COLUMNS as $tableName => $columns) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }
            Schema::table($tableName, function (Blueprint $table) use ($columns) {
                foreach ($columns as $column) {
                    $table->double($column)->change();
                }
            });
        }
    }

    public function down(): void
    {
        foreach (self::COLUMNS as $tableName => $columns) {
            if (! Schema::hasTable($tableName)) {
                continue;
            }
            Schema::table($tableName, function (Blueprint $table) use ($columns) {
                foreach ($columns as $column) {
                    if ($column === 'epoch') {
                        $table->integer($column)->change();
                    } else {
                        $table->float($column, 12)->change();
                    }
                }
            });
        }
    }
};
