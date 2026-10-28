<?php

use App\Models\Asteroid;
use App\Services\AsteroidCoordinates;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Pluto's number in the catalogue of the minor planets. */
    private const PLUTO = 134340;

    /**
     * Pluto is no planet: it is only known as the asteroid (134340) Pluto,
     * whose position and magnitude come from its orbital elements. Its
     * observations move to the asteroid, and the planet, its search index row
     * and the translations of its name (only used to find planets) go.
     */
    public function up(): void
    {
        $planet = DB::table('planets')->where('slug', 'pluto')->orWhere('name', 'Pluto')->first();
        if (! $planet) {
            return;
        }

        $keys = array_values(array_filter([(string) $planet->slug, (string) $planet->name, mb_strtolower((string) $planet->name), (string) $planet->id]));
        $observations = DB::table('observations')->where('target_table', 'planets')->whereIn('target_key', $keys);

        if ($observations->exists()) {
            $asteroid = Asteroid::where('number', self::PLUTO)->first();
            $elements = $asteroid ? null : AsteroidCoordinates::orbitalElements(self::PLUTO);
            if (! $asteroid && $elements) {
                $asteroid = Asteroid::addFromElements($elements);
            }
            if (! $asteroid) {
                // Without the orbital elements (astronomy:updateOrbitalElements) the
                // observations would lose their object: keep the planet for now
                return;
            }

            (clone $observations)->update([
                'target_type' => 'solarsystem',
                'target_table' => 'asteroids',
                'target_key' => $asteroid->slug,
                'target_name' => $asteroid->name,
            ]);
        }

        DB::table('search_index')->where('source_table', 'planets')->where('source_pk', (string) $planet->id)->delete();
        if (Schema::hasTable('object_name_translations')) {
            DB::table('object_name_translations')->where('objectname', $planet->name)->delete();
        }
        DB::table('planets')->where('id', $planet->id)->delete();
    }

    public function down(): void
    {
        // Pluto stays an asteroid.
    }
};
