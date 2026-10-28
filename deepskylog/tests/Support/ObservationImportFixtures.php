<?php

namespace Tests\Support;

use App\Models\Eyepiece;
use App\Models\Filter;
use App\Models\Instrument;
use App\Models\Lens;
use App\Models\Location;
use App\Models\User;
use App\Services\Import\CsvObservationImporter;
use App\Services\Import\ImportResult;
use App\Services\Import\OalObservationImporter;
use Illuminate\Support\Facades\DB;

/**
 * The example files of tests/Fixtures/ObservationImport (see its README.md):
 * the objects they name, an observer with the equipment the CSV files name,
 * and running them through the importers.
 */
class ObservationImportFixtures
{
    public static function content(string $path): string
    {
        return file_get_contents(base_path('tests/Fixtures/ObservationImport/'.$path));
    }

    /**
     * The paths of all example files, as "csv/…" and "oal/…".
     *
     * @return list<string>
     */
    public static function files(): array
    {
        return collect(glob(base_path('tests/Fixtures/ObservationImport/*/*.{csv,xml}'), GLOB_BRACE))
            ->map(fn (string $path) => basename(dirname($path)).'/'.basename($path))
            ->sort()->values()->all();
    }

    /**
     * The deep-sky objects and Solar System targets the files name.
     */
    public static function seedTargets(): void
    {
        self::object('M 31', 'm-31', 'GALXY', 0.7123, 41.2692);
        self::object('M 13', 'm-13', 'GLOCL', 16.6949, 36.4613);
        self::object('M 42', 'm-42', 'BRTNB', 5.5881, -5.3911);
        self::object('M 45', 'm-45', 'OPNCL', 3.7833, 24.1167);
        self::object('M 81', 'm-81', 'GALXY', 9.9259, 69.0653);
        self::object('NGC 104', 'ngc-104', 'GLOCL', 0.4014, -72.0814);
        self::solarSystem();
    }

    /**
     * An observer with the location and equipment the CSV files name.
     */
    public static function createObserver(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $owner = ['user_id' => $user->id, 'observer' => $user->username, 'active' => true];
        $slug = fn (string $name) => $user->username.'-'.$name;

        Location::query()->create($owner + [
            'name' => 'Example Site', 'slug' => $slug('example-site'), 'latitude' => 50.88, 'longitude' => 4.70, 'elevation' => 30,
            'timezone' => 'Europe/Brussels', 'limitingMagnitude' => -999, 'skyBackground' => -999, 'country' => 'Belgium',
            'observations' => 0,
        ]);
        Instrument::query()->create($owner + [
            'name' => 'Example Dobson 250', 'make_id' => DB::table('instrument_makes')->value('id'), 'aperture_mm' => 250,
            'focal_length_mm' => 1200, 'instrument_type_id' => 4, 'mount_type_id' => DB::table('mount_types')->value('id'),
        ]);
        Instrument::query()->create($owner + [
            'name' => 'Naked eye', 'make_id' => DB::table('instrument_makes')->value('id'), 'aperture_mm' => 7,
            'focal_length_mm' => 1, 'fixedMagnification' => 1, 'instrument_type_id' => 1,
            'mount_type_id' => DB::table('mount_types')->value('id'),
        ]);
        Eyepiece::query()->create($owner + [
            'name' => 'Example 10mm', 'slug' => $slug('example-10mm'), 'make_id' => DB::table('eyepiece_makes')->value('id'),
            'type_id' => DB::table('eyepiece_types')->value('id'), 'focal_length_mm' => 10, 'apparentFOV' => 82,
            'observations' => 0,
        ]);
        Filter::query()->create($owner + [
            'name' => 'Example UHC', 'make_id' => DB::table('filter_makes')->value('id'),
            'type_id' => DB::table('filter_types')->value('id'), 'color_id' => DB::table('filter_colors')->value('id'),
        ]);
        Lens::query()->create($owner + [
            'name' => 'Example Barlow 2x', 'slug' => $slug('example-barlow-2x'), 'make_id' => DB::table('lens_makes')->value('id'),
            'factor' => 2.0, 'observations' => 0,
        ]);

        return $user;
    }

    /**
     * Import a file (csv/… or oal/…), or the content given, as the user.
     */
    public static function import(User $user, string $file, bool $belowHorizon = false, ?string $content = null): ImportResult
    {
        $importer = str_starts_with($file, 'csv') ? new CsvObservationImporter($user) : new OalObservationImporter($user);

        return $importer->importBelowHorizon($belowHorizon)->import($content ?? self::content($file));
    }

    /**
     * @return list<string>
     */
    public static function messages(ImportResult $result, string $type): array
    {
        return array_values(array_map(
            fn (array $entry) => $entry['message'],
            array_filter($result->log, fn (array $entry) => $entry['type'] === $type),
        ));
    }

    private static function object(string $name, string $slug, string $type, float $ra, float $decl): void
    {
        DB::table('objects')->insert([
            'name' => $name, 'slug' => $slug, 'type' => $type, 'con' => 'AND', 'ra' => $ra, 'decl' => $decl,
            'mag' => 5.0, 'subr' => 12.0, 'pa' => 0, 'urano' => 0, 'urano_new' => 0, 'sky' => 0, 'millenium' => '',
            'diam1' => 60.0, 'diam2' => 60.0, 'datasource' => null, 'taki' => '', 'SBObj' => 0.0, 'description' => '',
            'psa' => '', 'torresB' => '', 'torresBC' => '', 'torresC' => '', 'milleniumbase' => '0', 'DSLDL' => '0',
            'DSLDP' => '0', 'DSLLL' => '0', 'DSLLP' => '0', 'DSLOL' => '0', 'DSLOP' => '0', 'DeepskyHunter' => '0',
            'Interstellarum' => '0', 'timestamp' => now(),
        ]);
    }

    /**
     * The Solar System targets of the solar-system and all-types files, with the
     * orbital elements of 12P/Pons-Brooks (MPC), so its horizon check is real.
     */
    private static function solarSystem(): void
    {
        $now = now();
        foreach (['Sun' => 'star', 'Earth' => 'planet', 'Jupiter' => 'planet'] as $planet => $type) {
            DB::table('planets')->insert(['name' => $planet, 'slug' => strtolower($planet), 'body_type' => $type, 'created_at' => $now, 'updated_at' => $now]);
        }
        DB::table('asteroids')->insert(['name' => 'Vesta', 'slug' => 'vesta', 'designation' => '4', 'body_type' => 'asteroid', 'created_at' => $now, 'updated_at' => $now]);
        $planetIds = DB::table('planets')->pluck('id', 'name');
        DB::table('moons')->insert([
            ['name' => 'Moon', 'slug' => 'moon', 'planet_id' => $planetIds['Earth'], 'body_type' => 'moon', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Io', 'slug' => 'io', 'planet_id' => $planetIds['Jupiter'], 'body_type' => 'moon', 'created_at' => $now, 'updated_at' => $now],
        ]);
        foreach (['Copernicus', 'Tycho'] as $feature) {
            DB::table('lunar_features')->insert(['name' => $feature, 'slug' => strtolower($feature), 'feature_type' => 'crater', 'created_at' => $now, 'updated_at' => $now]);
        }
        DB::table('cometobjects')->insert(['name' => '12P/Pons-Brooks', 'slug' => '12ppons-brooks', 'icqname' => '', 'timestamp' => $now]);
        DB::table('comets_orbital_elements')->insert([
            'name' => '12P/Pons-Brooks', 'epoch' => 2461307.5, 'q' => 0.781885, 'e' => 0.954746, 'i' => 74.1494,
            'w' => 199.0345, 'node' => 255.8673, 'Tp' => 20240421.2023, 'H' => 5, 'n' => 13.5, 'ref' => 'MPC191593',
        ]);
    }
}
