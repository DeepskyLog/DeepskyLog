<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The number of an asteroid links it to its orbital elements (see
     * App\Services\AsteroidCoordinates). The orbital elements of the numbered
     * asteroids (about 900,000) are searched on number and name when an
     * observer adds an asteroid; astronomy:updateOrbitalElements replaces
     * their rows, not the table, so the indexes stay.
     */
    public function up(): void
    {
        Schema::table('asteroids', function (Blueprint $table) {
            $table->unsignedInteger('number')->nullable()->unique()->after('designation');
        });

        if (Schema::hasTable('asteroids_orbital_elements')) {
            Schema::table('asteroids_orbital_elements', function (Blueprint $table) {
                $table->index('number');
                $table->index('name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('asteroids_orbital_elements')) {
            Schema::table('asteroids_orbital_elements', function (Blueprint $table) {
                $table->dropIndex(['number']);
                $table->dropIndex(['name']);
            });
        }

        Schema::table('asteroids', function (Blueprint $table) {
            $table->dropUnique(['number']);
            $table->dropColumn('number');
        });
    }
};
