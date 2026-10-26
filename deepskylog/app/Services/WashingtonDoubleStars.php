<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Spatie\ResponseCache\Facades\ResponseCache;

/**
 * Complete and correct DeepskyLog's double stars (type DS, see
 * DoubleStarResolvability) from the Washington Double Star Catalog (WDS):
 * the magnitude of the companion (objects.subr), and the separation, position
 * angle and magnitude of the primary where they are missing or belong to
 * another pair, and the position where it is off.
 *
 * database/wds_double_stars.txt holds the lines of the WDS summary file
 * (wdsweb_summ2.txt, fixed columns) the double stars need; a full summary
 * file can be read instead, for double stars added later.
 *
 * A double star is found
 * - by its discoverer designation (STF 2382, BU 1004, HJ 1735 AD), at most
 *   NAME_RADIUS_ARCMIN from the object, else anywhere when the designation
 *   is one system whose primary is as bright as the object's (within
 *   PRIMARY_TOLERANCE): the object's position is wrong;
 * - by its Bayer or Flamsteed name (Alpha CMa, Nu-1 CMa, 30 Eri) through the
 *   star of that name in the stars table: the pairs within
 *   POSITION_RADIUS_ARCMIN of the star;
 * - else by position: the pairs within POSITION_RADIUS_ARCMIN of the object
 *   as wide as the object's (SEPARATION_TOLERANCE, SEPARATION_MIN_ARCSEC)
 *   and with a primary as bright.
 *
 * Of the pairs of a system (AB, AC, …) the one as wide as the object's and
 * with a primary as bright is taken, else the main pair (AB, or the one
 * without components; choosePair()), whose separation and position angle
 * then replace the object's. A pair that agrees in neither separation nor
 * primary is probably another one: it is only listed. A primary that
 * differs more than PRIMARY_TOLERANCE from the object's magnitude (often a
 * combined or photographic one) is replaced too, so the magnitude difference
 * is that of the pair. An object more than MOVE_ARCMIN from the pair is
 * moved to the WDS position. Known values that agree are never touched.
 */
class WashingtonDoubleStars
{
    public const FILE = 'wds_double_stars.txt';

    public const NAME_RADIUS_ARCMIN = 15.0;

    public const POSITION_RADIUS_ARCMIN = 3.0;

    /** The pairs of one system lie within this of each other. */
    public const SYSTEM_RADIUS_ARCMIN = 1.0;

    public const MOVE_ARCMIN = 2.0;

    public const SEPARATION_TOLERANCE = 0.5;

    public const SEPARATION_MIN_ARCSEC = 2.0;

    public const PRIMARY_TOLERANCE = 1.5;

    /** The main pair of a system. */
    private const MAIN_COMPONENTS = ['AB', ''];

    /** The Bayer letters as the stars table stores them. */
    private const GREEK = [
        'alpha' => 'Alp', 'beta' => 'Bet', 'gamma' => 'Gam', 'delta' => 'Del', 'epsilon' => 'Eps', 'zeta' => 'Zet',
        'eta'   => 'Eta', 'theta' => 'The', 'iota' => 'Iot', 'kappa' => 'Kap', 'lambda' => 'Lam', 'mu' => 'Mu',
        'nu'    => 'Nu', 'xi' => 'Xi', 'omicron' => 'Omi', 'pi' => 'Pi', 'rho' => 'Rho', 'sigma' => 'Sig',
        'tau'   => 'Tau', 'upsilon' => 'Ups', 'phi' => 'Phi', 'chi' => 'Chi', 'psi' => 'Psi', 'omega' => 'Ome',
    ];

    /**
     * The pairs of a WDS summary file with a position.
     *
     * @return list<array{wds: string, code: ?string, number: ?int, components: string, pa: ?int, separation: ?float, primary: ?float, companion: ?float, ra: float, decl: float}>
     */
    public static function read(?string $path = null): array
    {
        $handle = fopen($path ?? database_path(self::FILE), 'r');
        if ($handle === false) {
            throw new \RuntimeException('Cannot read '.($path ?? database_path(self::FILE)));
        }

        $number = fn (string $value) => is_numeric(trim($value)) ? (float) trim($value) : null;
        $pairs  = [];
        while (($line = fgets($handle)) !== false) {
            if (strlen($line) < 130 || ! ctype_digit($line[0])) {
                continue;
            }
            // The precise coordinates: hhmmss.ss±ddmmss.s
            $precise = substr($line, 112, 18);
            if (! preg_match('/^(\d{2})(\d{2})(\d{2}\.\d+)([+-])(\d{2})(\d{2})(\d{2}(?:\.\d+)?)$/', trim($precise), $c)) {
                continue;
            }
            $designation = preg_match('/^(.+?)\s*(\d+)$/', trim(substr($line, 10, 7)), $d) ? $d : null;
            $pa          = $number(substr($line, 41, 4));

            $pairs[] = [
                'wds'        => substr($line, 0, 10),
                'code'       => $designation ? strtoupper(preg_replace('/\s+/', ' ', $designation[1])) : null,
                'number'     => $designation ? (int) $designation[2] : null,
                'components' => trim(substr($line, 17, 5)),
                'pa'         => $pa !== null && $pa >= 0 ? (int) $pa : null,
                'separation' => $number(substr($line, 52, 5)),
                'primary'    => $number(substr($line, 58, 5)),
                'companion'  => $number(substr($line, 64, 5)),
                'ra'         => ((int) $c[1] + (int) $c[2] / 60 + (float) $c[3] / 3600) * 15.0,
                'decl'       => ($c[4] === '-' ? -1 : 1) * ((int) $c[5] + (int) $c[6] / 60 + (float) $c[7] / 3600),
            ];
        }
        fclose($handle);

        return $pairs;
    }

    /**
     * The discoverer code, number and components of a designation (STF 2382,
     * STTA 89, H 5 79, HJ 1735 AD), or null for another name (Alpha CMa,
     * 17 Dra).
     *
     * @return array{0: string, 1: int, 2: ?string}|null
     */
    public static function designation(string $name): ?array
    {
        if (! preg_match('/^([A-Za-z]+(?: (?:[IVX]+|N|\d)(?= ))?)\s*(\d+)\s*([A-Z]{1,3})?$/', trim($name), $m)) {
            return null;
        }

        return [strtoupper($m[1]), (int) $m[2], ($m[3] ?? '') !== '' ? $m[3] : null];
    }

    /**
     * The position (RA and declination in degrees) of the star of a Bayer or
     * Flamsteed name (Alpha CMa, Nu-1 CMa, Rho1 Cnc, 30 Eri) in the stars
     * table, or null.
     *
     * @return array{0: float, 1: float, 2: ?float}|null  RA, declination and magnitude
     */
    public static function starPosition(string $name): ?array
    {
        $query = DB::table('stars')->whereNotNull('ra_degrees')->whereNotNull('decl_degrees');
        if (preg_match('/^(\d+)\s+([A-Za-z]{3})$/', trim($name), $m)) {
            $query->where('flamsteed', $m[1])->where('constellation_code', $m[2]);
        } elseif (preg_match('/^([A-Za-z]+)[- ]?(\d)?\s+([A-Za-z]{3})$/', trim($name), $m) && isset(self::GREEK[strtolower($m[1])])) {
            $query->where('bayer', self::GREEK[strtolower($m[1])])->where('constellation_code', $m[3]);
            ($m[2] ?? '') !== '' ? $query->where('bayer_suffix', $m[2]) : $query->whereNull('bayer_suffix');
        } else {
            return null;
        }
        $stars = $query->orderBy('vmag')->limit(5)->get(['ra_degrees', 'decl_degrees', 'vmag']);
        if ($stars->isEmpty()) {
            return null;
        }
        // A name of two stars apart is ambiguous; the components of one star (Porrima) are not
        $first = $stars[0];
        foreach ($stars as $star) {
            if (self::distanceArcmin((float) $first->ra_degrees, (float) $first->decl_degrees, (float) $star->ra_degrees, (float) $star->decl_degrees) > self::SYSTEM_RADIUS_ARCMIN) {
                return null;
            }
        }

        return [(float) $first->ra_degrees, (float) $first->decl_degrees, is_numeric($first->vmag) ? (float) $first->vmag : null];
    }

    /**
     * The changes for the double stars: what is missing, and what belongs to
     * another pair or lies at the wrong position.
     *
     * @return array{matches: list<array<string, mixed>>, rejected: list<array<string, mixed>>, unmatched: list<string>}
     */
    public static function plan(?string $path = null): array
    {
        $pairs  = self::read($path);
        $byName = [];
        $byZone = [];
        foreach ($pairs as $i => $pair) {
            if ($pair['code'] !== null) {
                $byName[$pair['code'].' '.$pair['number']][] = $i;
            }
            $byZone[(int) floor($pair['decl'] * 2)][] = $i;
        }
        $near = function (float $ra, float $decl, float $radius) use ($pairs, $byZone): array {
            $zone  = (int) floor($decl * 2);
            $found = [];
            foreach ([$zone - 1, $zone, $zone + 1] as $z) {
                foreach ($byZone[$z] ?? [] as $i) {
                    if (self::distanceArcmin($ra, $decl, $pairs[$i]['ra'], $pairs[$i]['decl']) <= $radius) {
                        $found[] = $pairs[$i];
                    }
                }
            }

            return $found;
        };

        $unknown = fn (mixed $value) => ! is_numeric($value) || (float) $value == 0.0 || (float) $value >= 90;
        $objects = DB::table('objects')->where('type', 'DS')->orderBy('name')->get(['name', 'ra', 'decl', 'diam1', 'pa', 'mag', 'subr']);

        $matches   = [];
        $rejected  = [];
        $unmatched = [];
        foreach ($objects as $object) {
            if (! is_numeric($object->ra) || ! is_numeric($object->decl)) {
                $unmatched[] = $object->name;

                continue;
            }
            $ra         = (float) $object->ra * 15.0;
            $decl       = (float) $object->decl;
            $separation = (float) $object->diam1 > 0 ? (float) $object->diam1 : null;
            $primary    = $unknown($object->mag) ? null : (float) $object->mag;
            $distance   = fn (array $pair) => self::distanceArcmin($ra, $decl, $pair['ra'], $pair['decl']);
            $withMags   = fn (array $list) => array_values(array_filter($list, fn (array $pair) => $pair['companion'] !== null));

            $how         = null;
            $candidates  = [];
            $designation = self::designation($object->name);
            if ($designation) {
                // By the discoverer designation, near the object or else anywhere
                $system = array_map(fn (int $i) => $pairs[$i], $byName[$designation[0].' '.$designation[1]] ?? []);
                if ($designation[2] !== null) {
                    $system = array_values(array_filter($system, fn (array $pair) => $pair['components'] === $designation[2])) ?: $system;
                }
                $candidates = $withMags(array_filter($system, fn (array $pair) => $distance($pair) <= self::NAME_RADIUS_ARCMIN));
                $how        = 'name';
                if ($candidates === [] && ($system = $withMags($system)) !== []) {
                    $how = 'name, far';
                    if (! self::oneSystem($system) || ($primary !== null && ! self::primaryAgrees($primary, self::mainPair($system)['primary']))) {
                        $rejected[] = ['name' => $object->name, 'how' => $how, 'wds' => self::label(self::mainPair($system)), 'offset' => round($distance(self::mainPair($system)), 1),
                            'reason' => self::oneSystem($system) ? 'primary' : 'several systems', 'old' => self::old($object, $primary, $separation), 'wds_pair' => self::values(self::mainPair($system))];

                        continue;
                    }
                    $candidates = $system;
                }
            } elseif ($star = self::starPosition($object->name)) {
                // By the Bayer or Flamsteed name: the pairs of which the star is the primary
                $how        = 'star';
                $reference  = $primary ?? $star[2];
                $candidates = $withMags(array_filter($near($star[0], $star[1], self::POSITION_RADIUS_ARCMIN),
                    fn (array $pair) => $reference === null || self::primaryAgrees($reference, $pair['primary'])));
            }
            if ($candidates === [] && $separation !== null) {
                // By position: a pair as wide and a primary as bright
                $how        = 'position';
                $candidates = $withMags(array_filter($near($ra, $decl, self::POSITION_RADIUS_ARCMIN), fn (array $pair) => self::separationAgrees($separation, $pair['separation'])
                    && ($primary === null || self::primaryAgrees($primary, $pair['primary']))));
            }
            if ($candidates === []) {
                $unmatched[] = $object->name;

                continue;
            }

            $pair        = self::choosePair($candidates, $separation, $primary, $distance);
            $samePair    = $separation !== null && self::separationAgrees($separation, $pair['separation']);
            $samePrimary = $primary === null || $pair['primary'] === null || self::primaryAgrees($primary, $pair['primary']);
            if ($separation !== null && $primary !== null && ! $samePair && ! $samePrimary) {
                // Neither the separation nor the primary: probably another pair
                $rejected[] = ['name' => $object->name, 'how' => $how, 'wds' => self::label($pair), 'offset' => round($distance($pair), 1),
                    'reason' => 'separation and primary', 'old' => self::old($object, $primary, $separation), 'wds_pair' => self::values($pair)];

                continue;
            }

            $changes = [];
            if (! $samePair && $pair['separation'] !== null && $pair['separation'] > 0) {
                $changes['diam1'] = $pair['separation'];
                if ($pair['pa'] !== null) {
                    $changes['pa'] = $pair['pa'];
                }
            } elseif ($object->pa === null && $pair['pa'] !== null) {
                $changes['pa'] = $pair['pa'];
            }
            if ($pair['primary'] !== null && ($primary === null || ! $samePrimary)) {
                $changes['mag'] = $pair['primary'];
            }
            // The companion of another pair, or of another primary, is replaced
            if ($unknown($object->subr) || ! $samePair || ! $samePrimary) {
                $changes['subr'] = $pair['companion'];
            }
            if ($distance($pair) > self::MOVE_ARCMIN) {
                $changes['ra']   = round($pair['ra'] / 15.0, 6);
                $changes['decl'] = round($pair['decl'], 6);
            }
            // Only real changes
            $old     = self::old($object, $primary, $separation) + ['ra' => (float) $object->ra, 'decl' => $decl];
            $changes = array_filter($changes, fn ($value, $field) => $old[$field] === null || abs((float) $old[$field] - (float) $value) > 1e-6, ARRAY_FILTER_USE_BOTH);
            if ($changes !== []) {
                $matches[] = [
                    'name'     => $object->name,
                    'how'      => $how,
                    'wds'      => self::label($pair),
                    'offset'   => round($distance($pair), 1),
                    'old'      => $old,
                    'wds_pair' => self::values($pair),
                    'changes'  => $changes,
                ];
            }
        }

        return ['matches' => $matches, 'rejected' => $rejected, 'unmatched' => $unmatched];
    }

    /**
     * Write the changes of plan(). A moved object gets the constellation and
     * atlas pages of its new position, its search index entry is updated
     * and its stored metrics are deleted.
     *
     * @param  list<array{name: string, changes: array<string, mixed>}>  $matches
     */
    public static function apply(array $matches): void
    {
        $moved = [];
        DB::transaction(function () use ($matches, &$moved) {
            foreach ($matches as $match) {
                $changes = $match['changes'];
                if (isset($changes['ra'], $changes['decl'])) {
                    $changes += DeepskyObjectCreator::coordinateDerivedFields((float) $changes['ra'], (float) $changes['decl'], 'Washington Double Star Catalog');
                    $changes['timestamp'] = now();
                    DB::table('user_object_metrics')->where('object_name', $match['name'])->delete();
                    $moved[] = $match['name'];
                }
                DB::table('objects')->where('name', $match['name'])->update($changes);
            }
        });

        foreach ($moved as $name) {
            ObjectSearchIndex::syncObject($name);
        }
        if ($matches !== []) {
            ResponseCache::clear();
        }
    }

    /**
     * The pair of the candidates that is the object: the first of
     * - as wide as the object's, with a primary as bright (the closest);
     * - the main pair, with a primary as bright;
     * - a primary as bright (the closest in separation);
     * - as wide as the object's;
     * - the main pair.
     *
     * @param  non-empty-list<array<string, mixed>>  $candidates
     * @return array<string, mixed>
     */
    private static function choosePair(array $candidates, ?float $separation, ?float $primary, callable $distance): array
    {
        $wide   = fn (array $pair) => $separation !== null && self::separationAgrees($separation, $pair['separation']);
        $bright = fn (array $pair) => $primary === null || self::primaryAgrees($primary, $pair['primary']);
        $main   = fn (array $pair) => in_array($pair['components'], self::MAIN_COMPONENTS, true);
        $closest = function (array $pairs) use ($separation, $distance): array {
            usort($pairs, fn (array $a, array $b) => [$separation === null ? 0 : abs(($a['separation'] ?? INF) - $separation), $distance($a)]
                <=> [$separation === null ? 0 : abs(($b['separation'] ?? INF) - $separation), $distance($b)]);

            return $pairs[0];
        };

        foreach ([
            fn (array $pair) => $wide($pair) && $bright($pair),
            fn (array $pair) => $main($pair) && $bright($pair),
            $bright,
            $wide,
        ] as $test) {
            $found = array_values(array_filter($candidates, $test));
            if ($found !== []) {
                return $closest($found);
            }
        }

        return self::mainPair($candidates);
    }

    /**
     * The main pair of a system: AB or the pair without components, else the
     * first one.
     *
     * @param  non-empty-list<array<string, mixed>>  $system
     * @return array<string, mixed>
     */
    private static function mainPair(array $system): array
    {
        usort($system, fn (array $a, array $b) => [! in_array($a['components'], self::MAIN_COMPONENTS, true), $a['components']] <=> [! in_array($b['components'], self::MAIN_COMPONENTS, true), $b['components']]);

        return $system[0];
    }

    /**
     * Whether the pairs lie together, as one system.
     *
     * @param  list<array<string, mixed>>  $pairs
     */
    private static function oneSystem(array $pairs): bool
    {
        foreach ($pairs as $pair) {
            if (self::distanceArcmin($pairs[0]['ra'], $pairs[0]['decl'], $pair['ra'], $pair['decl']) > self::SYSTEM_RADIUS_ARCMIN) {
                return false;
            }
        }

        return true;
    }

    private static function label(array $pair): string
    {
        return $pair['wds'].' '.$pair['code'].' '.$pair['number'].($pair['components'] !== '' ? ' '.$pair['components'] : '');
    }

    /** @return array{mag: ?float, subr: ?float, diam1: ?float, pa: ?int} */
    private static function values(array $pair): array
    {
        return ['mag' => $pair['primary'], 'subr' => $pair['companion'], 'diam1' => $pair['separation'], 'pa' => $pair['pa']];
    }

    /** @return array{mag: ?float, subr: ?float, diam1: ?float, pa: mixed} */
    private static function old(object $object, ?float $primary, ?float $separation): array
    {
        $subr = is_numeric($object->subr) && (float) $object->subr != 0.0 && (float) $object->subr < 90 ? (float) $object->subr : null;

        return ['mag' => $primary, 'subr' => $subr, 'diam1' => $separation, 'pa' => $object->pa];
    }

    private static function separationAgrees(float $separation, ?float $wds): bool
    {
        return $wds !== null && abs($wds - $separation) <= max(self::SEPARATION_MIN_ARCSEC, self::SEPARATION_TOLERANCE * $separation);
    }

    private static function primaryAgrees(float $primary, ?float $wds): bool
    {
        return $wds !== null && abs($wds - $primary) <= self::PRIMARY_TOLERANCE;
    }

    private static function distanceArcmin(float $ra1, float $decl1, float $ra2, float $decl2): float
    {
        $d1  = deg2rad($decl1);
        $d2  = deg2rad($decl2);
        $cos = sin($d1) * sin($d2) + cos($d1) * cos($d2) * cos(deg2rad($ra1 - $ra2));

        return rad2deg(acos(max(-1.0, min(1.0, $cos)))) * 60.0;
    }
}
