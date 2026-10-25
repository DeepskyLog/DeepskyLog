<?php

namespace App\Services;

use App\Models\DeepskyObject;
use App\Models\ObjectName;
use deepskylog\AstronomyLibrary\Coordinates\EquatorialCoordinates;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Adds a deep-sky object to the objects table, with the columns derived from
 * its position and brightness, its objectnames entry and its search index
 * rows. Used by the object create form and the OAL observation import.
 */
class DeepskyObjectCreator
{
    /**
     * Columns the caller may give; the others get the legacy defaults.
     */
    private const COLUMNS = [
        'name', 'type', 'con', 'ra', 'decl', 'mag', 'subr', 'pa', 'diam1', 'diam2', 'SBObj', 'datasource',
        'urano', 'urano_new', 'sky', 'millenium', 'taki', 'psa', 'torresB', 'torresBC', 'torresC',
        'milleniumbase', 'DSLDL', 'DSLDP', 'DSLLL', 'DSLLP', 'DSLOL', 'DSLOP', 'DeepskyHunter', 'Interstellarum',
    ];

    /**
     * Empty/zero defaults for legacy NOT NULL columns that have no DB default.
     */
    private const DEFAULTS = [
        'ra' => 0.0,
        'decl' => 0.0,
        'type' => '',
        'con' => '',
        'mag' => 99.9,
        'subr' => 99.9,
        'pa' => 999,
        'diam1' => 0.0,
        'diam2' => 0.0,
        'urano' => 0,
        'urano_new' => 0,
        'sky' => 0,
        'millenium' => '',
        'taki' => '',
        'SBObj' => -999,
        'description' => '',
        'psa' => '',
        'torresB' => '',
        'torresBC' => '',
        'torresC' => '',
        'milleniumbase' => '',
        'DSLDL' => '',
        'DSLDP' => '',
        'DSLLL' => '',
        'DSLLP' => '',
        'DSLOL' => '',
        'DSLOP' => '',
        'DeepskyHunter' => '',
        'Interstellarum' => '',
    ];

    /**
     * Create the object. $values holds the name, the type code, the RA in
     * hours and the declination in degrees, and optionally mag, subr, pa,
     * datasource and the diameters in arcminutes (as the create form gives
     * them). The constellation, atlas pages and SBObj are calculated.
     *
     * @param  array<string, mixed>  $values
     */
    public function create(array $values): DeepskyObject
    {
        if (isset($values['ra'], $values['decl']) && $values['ra'] !== '' && $values['decl'] !== '') {
            $values = array_merge($values, self::coordinateDerivedFields((float) $values['ra'], (float) $values['decl'], 'object creation'));
        }
        $values = array_merge($values, self::measurementColumns($values));

        $objectData = array_merge(self::DEFAULTS, array_filter(
            array_intersect_key($values, array_flip(self::COLUMNS)),
            fn ($v) => $v !== null && $v !== ''
        ));
        $objectData['name'] = $values['name'];
        $objectData['slug'] = Str::slug($values['name'], '-');
        $objectData['timestamp'] = now();
        $object = DeepskyObject::create($objectData);

        $this->addObjectName($object);
        // STF 1 is also Σ 1 (#480)
        StruveSigmaNames::addFor([$object->name]);
        ObjectSearchIndex::syncObject($object->name);

        return $object;
    }

    /**
     * The constellation and the atlas pages of a position (RA in hours, declination
     * in degrees), keyed by their objects column. Empty when the calculation fails.
     */
    public static function coordinateDerivedFields(float $raHours, float $declDegrees, string $context): array
    {
        $fields = [];
        try {
            $coords = new EquatorialCoordinates($raHours, $declDegrees);
            $fields['con'] = $coords->getConstellation();

            foreach ([
                'urano',
                'urano_new',
                'sky',
                'taki',
                'psa',
                'torresB',
                'torresBC',
                'torresC',
                'milleniumbase',
                'DSLDL',
                'DSLDP',
                'DSLLL',
                'DSLLP',
                'DSLOL',
                'DSLOP',
                'DeepskyHunter',
                'Interstellarum'
            ] as $atlasKey) {
                $fields[$atlasKey] = $coords->calculateAtlasPage($atlasKey);
            }

            // Millenium Star Atlas full ref: page/volume (I=0-8h, II=8-16h, III=16-24h)
            $milVol = $raHours <= 8 ? 'I' : ($raHours <= 16 ? 'II' : 'III');
            $fields['millenium'] = $fields['milleniumbase'] . '/' . $milVol;
        } catch (\Throwable $e) {
            Log::warning('Constellation/atlas calculation failed during ' . $context, ['error' => $e->getMessage()]);

            return [];
        }

        return $fields;
    }

    /**
     * SBObj from the magnitude and the diameters in arcseconds: mag + 2.5 * log10(π * diam1 * diam2 / 4),
     * with diam2 = diam1 for a circular object. Null without a magnitude or a diameter.
     */
    public static function surfaceBrightness($mag, $diam1, $diam2): ?float
    {
        if ($mag === null || $mag == 99.9 || !($diam1 > 0)) {
            return null;
        }
        $effectiveDiam2 = ($diam2 > 0) ? $diam2 : $diam1;

        return $mag + 2.5 * log10(M_PI * $diam1 * $effectiveDiam2 / 4);
    }

    /**
     * The brightness and size columns of an object from the create or edit form, with the
     * sentinels of the legacy data for a missing value: mag and subr 99.9, pa 999, SBObj -999
     * and diameter 0. A magnitude or position angle of 0 is a real value. The form gives the
     * diameters in arcminutes, the objects table stores arcseconds.
     */
    public static function measurementColumns(array $validated): array
    {
        $given = fn(string $key) => isset($validated[$key]) && $validated[$key] !== '';

        $mag = $given('mag') ? (float) $validated['mag'] : 99.9;
        $diam1 = $given('diam1') ? $validated['diam1'] * 60.0 : 0;
        $diam2 = $given('diam2') ? $validated['diam2'] * 60.0 : 0;

        return [
            'mag' => $mag,
            'subr' => $given('subr') ? (float) $validated['subr'] : 99.9,
            'diam1' => $diam1,
            'diam2' => $diam2,
            'pa' => $given('pa') ? (int) $validated['pa'] : 999,
            'SBObj' => self::surfaceBrightness($mag, $diam1, $diam2) ?? -999,
        ];
    }

    /**
     * A primary entry in objectnames, so the catalog appears in the dropdown
     * on the create page and object lookups via objectnames.slug work.
     */
    private function addObjectName(DeepskyObject $object): void
    {
        try {
            // Catalog code and catindex from the name (e.g. "Wdm 1" → "Wdm", "1")
            [$catalogCode, $catindex] = ObjectName::splitCatalogDesignation($object->name);

            if (!DB::table('objectnames')->where('objectname', $object->name)->exists()) {
                DB::table('objectnames')->insert([
                    'objectname' => $object->name,
                    'catalog' => $catalogCode,
                    'catindex' => $catindex,
                    'altname' => $object->name,
                    'slug' => $object->slug,
                    'timestamp' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to insert objectnames entry for new object', [
                'error' => $e->getMessage(),
                'name' => $object->name,
            ]);
        }
    }
}
