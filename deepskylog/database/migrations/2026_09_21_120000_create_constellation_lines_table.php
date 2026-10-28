<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The constellation figures -- the "stick figures" joining the bright stars of
 * a constellation.
 *
 * These are a drawing convention rather than anything the IAU defines, so the
 * coordinates come from a published set (d3-celestial, BSD 3-Clause, see the
 * header of database/constellation_figures.csv) instead of being derived from
 * the star catalogue. Each figure is one or more open polylines; a row is one
 * point of one polyline, ordered by point_index.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('constellation_lines')) {
            return;
        }

        Schema::create('constellation_lines', function (Blueprint $table) {
            $table->string('con', 3);
            $table->unsignedSmallInteger('line_index');
            $table->unsignedSmallInteger('point_index');
            $table->double('ra_degrees');
            $table->double('decl_degrees');

            $table->index(['con', 'line_index', 'point_index']);
        });

        foreach (array_chunk($this->points(), 500) as $chunk) {
            DB::table('constellation_lines')->insert($chunk);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('constellation_lines');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function points(): array
    {
        $path = base_path('database/constellation_figures.csv');
        if (! is_readable($path)) {
            return [];
        }

        $handle = fopen($path, 'r');
        if ($handle === false) {
            return [];
        }

        $points = [];
        while (($row = fgetcsv($handle, 0, ';', '"', '')) !== false) {
            // Provenance and column notes are carried in the file itself.
            if ($row === [null] || count($row) < 5 || str_starts_with((string) $row[0], '#')) {
                continue;
            }

            $points[] = [
                'con' => strtoupper(trim((string) $row[0])),
                'line_index' => (int) $row[1],
                'point_index' => (int) $row[2],
                'ra_degrees' => (float) $row[3],
                'decl_degrees' => (float) $row[4],
            ];
        }
        fclose($handle);

        return $points;
    }
};
