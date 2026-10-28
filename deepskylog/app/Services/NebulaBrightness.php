<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Spatie\ResponseCache\Facades\ResponseCache;

/**
 * Estimated magnitudes of the bright nebulae without one, from the brightness
 * classes of Lynds' Catalogue of Bright Nebulae (1965, VizieR VII/9, classes 1
 * brightest to 6 faintest, database/lbn_brightness.csv) and the Sharpless
 * catalogue of H II regions (1959, VizieR VII/20, classes 1 faintest to 3
 * brightest, database/sharpless_brightness.csv).
 *
 * A class is a surface brightness, the same whatever part of the nebula an
 * object is, so the magnitude follows from that and the object's own size:
 * mag = SB − 2.5 log10(π · diam1 · diam2 / 4) (diameters in arcseconds, the
 * inverse of DeepskyObjectCreator::surfaceBrightness()). The surface
 * brightness of each class is the median of the nebulae that already have a
 * magnitude, about a magnitude per Lynds class; a Sharpless class spans two
 * Lynds classes (Sharpless 3 ≈ Lynds 1, 2 ≈ 3, 1 ≈ 5). The classes were judged
 * on red plates, so an estimate is rough: a nebula bright in H-alpha is
 * fainter to the eye than its class says.
 *
 * Only nebulae of NEBULA_TYPES without a magnitude, or with an earlier
 * estimate, get one; the size of the catalogue entry is used when the object
 * has none. An object with several Lynds or Sharpless names takes the
 * brightest class, Lynds before Sharpless. A name whose catalogue entry lies
 * further than MAX_OFFSET_ARCMIN of its catalogue (or the entry's size) from
 * the object is not used: a wrong position or alternative name, repaired by
 * objects:repair-nebula-positions. Lynds measured the centre of the area he
 * drew, which for a large nebula can lie more than a degree from where SIMBAD
 * puts it, so his entries get more room than the Sharpless ones.
 */
class NebulaBrightness
{
    /** Surface brightness (mag/arcsec²) of the Lynds classes 1 to 6. */
    public const LBN_SURFACE_BRIGHTNESS = [1 => 22.3, 2 => 23.3, 3 => 24.3, 4 => 25.3, 5 => 26.3, 6 => 27.3];

    /** Surface brightness (mag/arcsec²) of the Sharpless classes 3 (brightest) to 1. */
    public const SH2_SURFACE_BRIGHTNESS = [3 => 22.3, 2 => 24.3, 1 => 26.3];

    /** The object types that get an estimate; dark nebulae have no brightness to estimate. */
    public const NEBULA_TYPES = ['HII', 'EMINB', 'BRTNB', 'REFNB', 'SNREM', 'WRNEB', 'PLNNB', 'GACAN', 'GXADN'];

    /** The furthest a catalogue entry may lie from the object, unless the entry is larger. */
    public const MAX_OFFSET_ARCMIN = ['LBN' => 90.0, 'Sh' => 30.0];

    /**
     * The magnitude of a nebula of the given surface brightness and size
     * (arcseconds), rounded to a tenth.
     */
    public static function magnitude(float $surfaceBrightness, float $diam1, float $diam2): ?float
    {
        if ($diam1 <= 0) {
            return null;
        }
        $diam2 = $diam2 > 0 ? $diam2 : $diam1;

        return round($surfaceBrightness - 2.5 * log10(M_PI * $diam1 * $diam2 / 4), 1);
    }

    /**
     * The estimate of every nebula that can get one, keyed by object name:
     * name, type, the old magnitude, the new one, its source, the surface
     * brightness and the diameters (arcseconds) used, and whether those come
     * from the catalogue, and whether it differs from what is stored. The names whose catalogue entry is too far from the
     * object are under the key 'rejected'.
     *
     * @return array{estimates: array<string, array>, rejected: list<array>}
     */
    public static function plan(?string $lbnFile = null, ?string $sh2File = null): array
    {
        $catalogues = [
            'LBN' => self::readCatalogue($lbnFile ?? database_path('lbn_brightness.csv'), 'lbn'),
            'Sh'  => self::readCatalogue($sh2File ?? database_path('sharpless_brightness.csv'), 'sh2'),
        ];

        $objects = DB::table('objects')
            ->whereIn('type', self::NEBULA_TYPES)
            ->where(fn ($q) => $q->whereNull('mag')->orWhere('mag', '>=', 99)->orWhereNotNull('mag_source'))
            ->get(['name', 'type', 'ra', 'decl', 'mag', 'mag_source', 'diam1', 'diam2'])
            ->keyBy('name');

        $names = $objects->isEmpty() ? collect() : DB::table('objectnames')
            ->whereIn('catalog', array_keys($catalogues))
            ->whereIn('objectname', $objects->keys())
            ->get(['objectname', 'catalog', 'catindex']);

        $estimates = [];
        $rejected  = [];
        foreach ($names->groupBy('objectname') as $name => $rows) {
            $object = $objects[$name];
            $best   = null;
            foreach ($rows as $row) {
                $entry = self::entryFor($catalogues, $row->catalog, (string) $row->catindex);
                if ($entry === null) {
                    continue;
                }
                $offset = self::separationArcmin((float) $object->ra, (float) $object->decl, $entry['ra'], $entry['decl']);
                if ($offset > max(self::MAX_OFFSET_ARCMIN[$row->catalog], $entry['diam1'])) {
                    $rejected[] = ['name' => $name, 'source' => $entry['designation'], 'offset' => round($offset)];

                    continue;
                }
                // Lynds before Sharpless, then the brightest class
                $rank = [$row->catalog === 'LBN' ? 0 : 1, $entry['sb']];
                if ($best === null || $rank < $best['rank']) {
                    $best = $entry + ['rank' => $rank];
                }
            }
            if ($best === null) {
                continue;
            }

            $fromCatalogue = ! ((float) $object->diam1 > 0);
            $diam1         = $fromCatalogue ? $best['diam1'] * 60.0 : (float) $object->diam1;
            $diam2         = $fromCatalogue ? $best['diam2'] * 60.0 : (float) $object->diam2;
            $magnitude     = self::magnitude($best['sb'], $diam1, $diam2);
            if ($magnitude === null) {
                continue;
            }

            $source           = $best['designation'].', class '.$best['class'];
            $estimates[$name] = [
                'name'                => $name,
                'type'                => $object->type,
                'old'                 => $object->mag === null || (float) $object->mag >= 99 ? null : (float) $object->mag,
                'mag'                 => $magnitude,
                'source'              => $source,
                'sb'                  => $best['sb'],
                'diam1'               => $diam1,
                'diam2'               => $diam2 > 0 ? $diam2 : $diam1,
                'size_from_catalogue' => $fromCatalogue,
                'changed'             => $object->mag === null || (float) $object->mag !== $magnitude || $object->mag_source !== $source || $fromCatalogue,
            ];
        }
        ksort($estimates, SORT_NATURAL);

        return ['estimates' => $estimates, 'rejected' => $rejected];
    }

    /**
     * The catalogue entry of a name in objectnames: LBN with catindex "953",
     * Sh with catindex "2-276" (Sharpless' 1953 list, "1-89", is not in the
     * catalogue).
     */
    private static function entryFor(array $catalogues, string $catalog, string $catindex): ?array
    {
        if ($catalog === 'LBN' && preg_match('/^\d+$/', $catindex)) {
            $entry       = $catalogues['LBN'][(int) $catindex] ?? null;
            $designation = 'LBN '.(int) $catindex;
            $sb          = self::LBN_SURFACE_BRIGHTNESS[$entry['class'] ?? 0] ?? null;
        } elseif ($catalog === 'Sh' && preg_match('/^2-(\d+)$/', $catindex, $m)) {
            $entry       = $catalogues['Sh'][(int) $m[1]] ?? null;
            $designation = 'Sh 2-'.(int) $m[1];
            $sb          = self::SH2_SURFACE_BRIGHTNESS[$entry['class'] ?? 0] ?? null;
        } else {
            return null;
        }

        return $entry && $sb !== null ? $entry + ['designation' => $designation, 'sb' => $sb] : null;
    }

    /**
     * The rows of a catalogue file keyed by number: ra (hours), decl, diam1
     * and diam2 (arcminutes) and class.
     *
     * @return array<int, array{ra: float, decl: float, diam1: float, diam2: float, class: int}>
     */
    private static function readCatalogue(string $file, string $key): array
    {
        $handle  = fopen($file, 'r');
        $header  = fgetcsv($handle, escape: '');
        $entries = [];
        while (($row = fgetcsv($handle, escape: '')) !== false) {
            $row                       = array_combine($header, $row);
            $diam1                     = (float) ($row['diam1_arcmin'] ?? $row['diam_arcmin']);
            $diam2                     = (float) ($row['diam2_arcmin'] ?? $row['diam_arcmin']);
            $entries[(int) $row[$key]] = [
                'ra'    => (float) $row['ra_hours'],
                'decl'  => (float) $row['decl_deg'],
                'diam1' => max($diam1, $diam2),
                'diam2' => min($diam1, $diam2) ?: max($diam1, $diam2),
                'class' => (int) $row['brightness'],
            ];
        }
        fclose($handle);

        return $entries;
    }

    private static function separationArcmin(float $ra1, float $decl1, float $ra2, float $decl2): float
    {
        [$a1, $d1, $a2, $d2] = [deg2rad($ra1 * 15), deg2rad($decl1), deg2rad($ra2 * 15), deg2rad($decl2)];

        $cos = sin($d1) * sin($d2) + cos($d1) * cos($d2) * cos($a1 - $a2);

        return rad2deg(acos(max(-1.0, min(1.0, $cos)))) * 60;
    }

    /**
     * Writes the estimates: the magnitude, its source, SBObj and, when they
     * came from the catalogue, the diameters, and deletes the stored contrast
     * reserves of those objects, so they are calculated again. Estimates
     * that change nothing are skipped.
     */
    public static function apply(array $estimates): void
    {
        $estimates = array_values(array_filter($estimates, fn (array $estimate) => $estimate['changed']));
        foreach (array_chunk($estimates, 200) as $chunk) {
            DB::transaction(function () use ($chunk) {
                foreach ($chunk as $estimate) {
                    $columns = [
                        'mag'        => $estimate['mag'],
                        'mag_source' => $estimate['source'],
                        'SBObj'      => DeepskyObjectCreator::surfaceBrightness($estimate['mag'], $estimate['diam1'], $estimate['diam2']) ?? -999,
                    ];
                    if ($estimate['size_from_catalogue']) {
                        $columns['diam1'] = $estimate['diam1'];
                        $columns['diam2'] = $estimate['diam2'];
                    }
                    DB::table('objects')->where('name', $estimate['name'])->update($columns);
                }
                DB::table('user_object_metrics')->whereIn('object_name', array_column($chunk, 'name'))->delete();
            });
        }
        if ($estimates !== []) {
            ResponseCache::clear();
        }
    }
}
