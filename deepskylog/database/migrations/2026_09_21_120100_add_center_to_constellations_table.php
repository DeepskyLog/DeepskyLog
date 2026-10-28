<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Where to write a constellation's name on a map.
 *
 * Not the centroid of its boundary: that falls outside the figure for the
 * sprawling constellations (Eridanus, Hydra, Serpens), so the positions are
 * the curated label points from d3-celestial -- see the header of
 * database/constellation_centers.csv.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('constellations')) {
            return;
        }

        if (! Schema::hasColumn('constellations', 'ra_degrees')) {
            Schema::table('constellations', function (Blueprint $table) {
                $table->double('ra_degrees')->nullable();
                $table->double('decl_degrees')->nullable();
            });
        }

        foreach ($this->centers() as $code => [$ra, $dec]) {
            DB::table('constellations')
                ->where('id', $code)
                ->update(['ra_degrees' => $ra, 'decl_degrees' => $dec]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('constellations', 'ra_degrees')) {
            Schema::table('constellations', function (Blueprint $table) {
                $table->dropColumn(['ra_degrees', 'decl_degrees']);
            });
        }
    }

    /**
     * @return array<string, array{0: float, 1: float}>
     */
    private function centers(): array
    {
        $path = base_path('database/constellation_centers.csv');
        if (! is_readable($path)) {
            return [];
        }

        $handle = fopen($path, 'r');
        if ($handle === false) {
            return [];
        }

        $centers = [];
        while (($row = fgetcsv($handle, 0, ';', '"', '')) !== false) {
            if ($row === [null] || count($row) < 3 || str_starts_with((string) $row[0], '#')) {
                continue;
            }

            $centers[strtoupper(trim((string) $row[0]))] = [(float) $row[1], (float) $row[2]];
        }
        fclose($handle);

        return $centers;
    }
};
