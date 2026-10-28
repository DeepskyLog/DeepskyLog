<?php

namespace App\Services\Moon;

use App\Models\SearchIndex;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * The named lunar features of the IAU Gazetteer of Planetary Nomenclature
 * (https://planetarynames.wr.usgs.gov, MOON_nomenclature_center_pts), in
 * database/lunar_features_iau.csv: the features adopted by the IAU without the
 * satellite craters (Copernicus A), the names of the Apollo landing sites and
 * the stations, with their centre (selenographic latitude, longitude east
 * positive in [-180, 180]) and diameter in km.
 *
 * plan() compares them with the lunar_features table and apply() writes the
 * differences: the positions and diameters, the names the old seed cut at
 * their apostrophe (RENAMES) and the features that are missing.
 */
class LunarGazetteer
{
    /**
     * The names the static seed list cut off at an apostrophe or misspelled,
     * with the IAU name. The slug stays, as links and observations use it.
     */
    public const RENAMES = [
        'Bel'         => "Bel'kovich",
        'd'           => "d'Alembert",
        'Idel'        => "Idel'son",
        'Mons Blanc'  => 'Mont Blanc',
        'Yangel'      => "Yangel'",
        'Rima Yangel' => "Rima Yangel'",
    ];

    /** The feature type of each IAU descriptor code. */
    public const TYPES = [
        'AA' => 'crater',
        'ME' => 'mare',
        'OC' => 'mare',
        'LC' => 'mare',
        'SI' => 'mare',
        'PA' => 'mare',
        'MO' => 'mountain',
        'VA' => 'valley',
    ];

    /** A position or diameter closer than this to the stored one is kept. */
    private const TOLERANCE = 0.01;

    /**
     * The features of the gazetteer, keyed by the name in lower case, as the
     * table compares names case-insensitively ("de la Rue" is "De La Rue").
     *
     * @return array<string, array{name: string, type: string, lat: float, lon: float, diameter: float}>
     */
    public static function features(): array
    {
        $handle   = fopen(database_path('lunar_features_iau.csv'), 'r');
        $header   = fgetcsv($handle);
        $features = [];
        while (($row = fgetcsv($handle)) !== false) {
            $row                                   = array_combine($header, $row);
            $features[mb_strtolower($row['name'])] = [
                'name'     => $row['name'],
                'type'     => self::TYPES[$row['type']] ?? 'other',
                'lat'      => (float) $row['lat'],
                'lon'      => (float) $row['lon'],
                'diameter' => (float) $row['diameter'],
            ];
        }
        fclose($handle);

        return $features;
    }

    /**
     * What apply() would change: the features to rename, to update (with the
     * new values), to add, and those of the table the gazetteer does not have.
     *
     * @return array{renamed: list<array{id: int, from: string, to: string}>, updated: list<array{id: int, name: string, values: array<string, float>}>, added: list<array<string, mixed>>, unknown: list<string>}
     */
    public static function plan(): array
    {
        $gazetteer = self::features();
        $plan      = ['renamed' => [], 'updated' => [], 'added' => [], 'unknown' => []];
        $present   = [];

        foreach (DB::table('lunar_features')->orderBy('id')->get(['id', 'name', 'lat', 'lon', 'diameter']) as $row) {
            $name = $row->name;
            if (isset(self::RENAMES[$name]) && ! DB::table('lunar_features')->where('name', self::RENAMES[$name])->exists()) {
                $plan['renamed'][] = ['id' => $row->id, 'from' => $name, 'to' => self::RENAMES[$name]];
                $name              = self::RENAMES[$name];
            }
            $feature = $gazetteer[mb_strtolower($name)] ?? null;
            if ($feature === null) {
                $plan['unknown'][] = $row->name;

                continue;
            }
            $present[mb_strtolower($name)] = true;

            $values = [];
            foreach (['lat', 'lon', 'diameter'] as $column) {
                if ($row->{$column} === null || abs((float) $row->{$column} - $feature[$column]) > self::TOLERANCE) {
                    $values[$column] = $feature[$column];
                }
            }
            if ($values !== []) {
                $plan['updated'][] = ['id' => $row->id, 'name' => $name, 'values' => $values];
            }
        }

        foreach ($gazetteer as $key => $feature) {
            if (! isset($present[$key])) {
                $plan['added'][] = $feature;
            }
        }

        return $plan;
    }

    /**
     * Write a plan: rename, update and add the features, the added ones with a
     * slug and a row in the search index.
     */
    public static function apply(array $plan): void
    {
        DB::transaction(function () use ($plan) {
            foreach ($plan['renamed'] as $rename) {
                DB::table('lunar_features')->where('id', $rename['id'])->update(['name' => $rename['to'], 'updated_at' => now()]);
                DB::table('search_index')
                    ->where('source_table', 'lunar_features')
                    ->where('source_pk', (string) $rename['id'])
                    ->update(['name' => $rename['to'], 'name_normalized' => mb_strtolower($rename['to']), 'display_name' => $rename['to'], 'updated_at' => now()]);
            }

            foreach ($plan['updated'] as $update) {
                DB::table('lunar_features')->where('id', $update['id'])->update($update['values'] + ['updated_at' => now()]);
            }

            $moonId = DB::table('moons')->where('name', 'Moon')->value('id');
            foreach ($plan['added'] as $feature) {
                $id = DB::table('lunar_features')->insertGetId([
                    'name'         => $feature['name'],
                    'slug'         => self::uniqueSlug($feature['name']),
                    'feature_type' => $feature['type'],
                    'moon_id'      => $moonId,
                    'lat'          => $feature['lat'],
                    'lon'          => $feature['lon'],
                    'diameter'     => $feature['diameter'],
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
                DB::table('search_index')->insert([
                    'name'            => $feature['name'],
                    'name_normalized' => mb_strtolower($feature['name']),
                    'source_table'    => 'lunar_features',
                    'source_pk'       => (string) $id,
                    'display_name'    => $feature['name'],
                    'source_type'     => $feature['type'],
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
        });

        LunarTerminator::forgetCatalogue();

        $names = array_merge(array_column($plan['renamed'], 'from'), array_column($plan['renamed'], 'to'), array_column($plan['added'], 'name'));
        if ($names !== []) {
            SearchIndex::markUniqueNames($names);
        }
    }

    /**
     * A slug no lunar feature has yet, as BackfillLunarFeatureSlugsSeeder
     * makes them.
     */
    private static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name, '-') ?: 'lunar-feature';
        $slug = $base;
        for ($i = 1; DB::table('lunar_features')->where('slug', $slug)->exists(); $i++) {
            $slug = $base.'-'.$i;
        }

        return $slug;
    }
}
