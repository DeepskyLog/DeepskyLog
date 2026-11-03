<?php

namespace App\Services;

use App\Models\Location;
use App\Models\Star;
use App\Services\News\SkyPositions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Measuring the naked-eye limiting magnitude by counting stars, as the
 * meteor observers of the IMO do: the observer counts the stars they see
 * inside a figure of bright stars (the bowl of the Big Dipper, the square of
 * Pegasus, Crux); the faintest star that count reaches is the limiting
 * magnitude there, which is far more reliable than a guess.
 *
 * The figures are AREAS, the stars of each are read from the `stars` table:
 * the stars inside the figure (not its corner stars), brightest first, rows
 * closer than SAME_STAR_DEG kept once (the table holds a TYC row next to a
 * named star; the eye does not split them either), down to CATALOGUE_LIMIT.
 * Seeing n stars puts the limit between the n-th and the n+1-th star. The
 * figures are not the IMO's numbered areas: those come with tables of their
 * own; these are counted from DeepskyLog's own star catalogue.
 *
 * The count gives the limit at the altitude of the figure; the extinction
 * there (AtmosphericExtinction) is added to give the limit at the zenith,
 * which is what DeepskyLog stores as a NELM. Figures below MIN_ALTITUDE are
 * not offered, as the extinction near the horizon is too uncertain.
 */
class StarCountAreas
{
    /**
     * The figures: corner stars as [Bayer, constellation] (the brightest row
     * of that Bayer letter when it has several suffixes), in any order (the
     * figure is their convex hull), and the label as a translation key.
     */
    public const AREAS = [
        'ursa-major' => ['label' => 'Bowl of the Big Dipper (Ursa Major)', 'corners' => [['Alp', 'UMa'], ['Bet', 'UMa'], ['Gam', 'UMa'], ['Del', 'UMa']]],
        'cassiopeia' => ['label' => 'The W of Cassiopeia', 'corners' => [['Bet', 'Cas'], ['Alp', 'Cas'], ['Gam', 'Cas'], ['Del', 'Cas'], ['Eps', 'Cas']]],
        'pegasus' => ['label' => 'Square of Pegasus', 'corners' => [['Alp', 'Peg'], ['Bet', 'Peg'], ['Gam', 'Peg'], ['Alp', 'And']]],
        'auriga' => ['label' => 'Pentagon of Auriga', 'corners' => [['Alp', 'Aur'], ['Bet', 'Aur'], ['The', 'Aur'], ['Bet', 'Tau'], ['Iot', 'Aur']]],
        'orion' => ['label' => 'Body of Orion', 'corners' => [['Alp', 'Ori'], ['Gam', 'Ori'], ['Kap', 'Ori'], ['Bet', 'Ori']]],
        'gemini' => ['label' => 'Gemini (Castor, Pollux, Alhena, μ Gem)', 'corners' => [['Alp', 'Gem'], ['Bet', 'Gem'], ['Gam', 'Gem'], ['Mu', 'Gem']]],
        'leo' => ['label' => 'Body of Leo (Regulus, Algieba, Zosma, Denebola)', 'corners' => [['Alp', 'Leo'], ['Gam', 'Leo'], ['Del', 'Leo'], ['Bet', 'Leo']]],
        'bootes' => ['label' => 'Head of Boötes (β, γ, δ, ε Boo)', 'corners' => [['Bet', 'Boo'], ['Gam', 'Boo'], ['Del', 'Boo'], ['Eps', 'Boo']]],
        'hercules' => ['label' => 'Keystone of Hercules', 'corners' => [['Eps', 'Her'], ['Zet', 'Her'], ['Eta', 'Her'], ['Pi', 'Her']]],
        'cygnus' => ['label' => 'Cygnus (Deneb, Sadr, δ Cyg)', 'corners' => [['Alp', 'Cyg'], ['Gam', 'Cyg'], ['Del', 'Cyg']]],
        'aquila' => ['label' => 'Aquila (Altair, δ, ζ Aql)', 'corners' => [['Alp', 'Aql'], ['Del', 'Aql'], ['Zet', 'Aql']]],
        'sagittarius' => ['label' => 'Teapot of Sagittarius', 'corners' => [['Del', 'Sgr'], ['Eps', 'Sgr'], ['Zet', 'Sgr'], ['Phi', 'Sgr']]],
        'canis-major' => ['label' => 'Body of Canis Major (Sirius, Mirzam, Adhara, Wezen)', 'corners' => [['Alp', 'CMa'], ['Bet', 'CMa'], ['Eps', 'CMa'], ['Del', 'CMa']]],
        'crux' => ['label' => 'Southern Cross', 'corners' => [['Alp', 'Cru'], ['Bet', 'Cru'], ['Gam', 'Cru'], ['Del', 'Cru']]],
        'false-cross' => ['label' => 'False Cross (Carina and Vela)', 'corners' => [['Eps', 'Car'], ['Iot', 'Car'], ['Kap', 'Vel'], ['Del', 'Vel']]],
        'triangulum-australe' => ['label' => 'Triangulum Australe', 'corners' => [['Alp', 'TrA'], ['Bet', 'TrA'], ['Gam', 'TrA']]],
        'grus' => ['label' => 'Grus and Fomalhaut (α, β Gru, α PsA)', 'corners' => [['Alp', 'Gru'], ['Bet', 'Gru'], ['Alp', 'PsA']]],
        'hydrus' => ['label' => 'Hydrus (α, β, γ Hyi)', 'corners' => [['Alp', 'Hyi'], ['Bet', 'Hyi'], ['Gam', 'Hyi']]],
    ];

    /** Figures lower than this (degrees) are not offered. */
    public const MIN_ALTITUDE = 35.0;

    /** Below this altitude the result is marked as less reliable. */
    public const GOOD_ALTITUDE = 50.0;

    /** The faintest stars read from the table. */
    public const CATALOGUE_LIMIT = 8.0;

    /** Rows closer than this are one star. */
    private const SAME_STAR_DEG = 1.0 / 60.0;

    /** A star this close to a corner is that corner star, not counted. */
    private const CORNER_DEG = 0.1;

    /** Stars at least this bright around the figure are drawn on the chart, to find it. */
    public const CHART_MAGNITUDE = 4.5;

    /** The chart shows this many times the radius of the figure around it. */
    private const CHART_MARGIN = 1.8;

    /** Bump to recompute the cached figures. */
    private const CACHE_VERSION = 2;

    /**
     * A figure with its corner stars and the magnitudes of the stars inside,
     * brightest first; null when a corner star is missing from the table.
     *
     * @return array{key: string, label: string, ra: float, dec: float, radius: float, corners: list<array{ra: float, dec: float, mag: float, label: string}>, magnitudes: list<float>}|null
     */
    public static function area(string $key): ?array
    {
        if (! isset(self::AREAS[$key])) {
            return null;
        }

        return Cache::rememberForever('star_count_area:v' . self::CACHE_VERSION . ':' . $key, fn () => self::compute($key));
    }

    /**
     * The figures above MIN_ALTITUDE at the location at the moment, highest
     * first, with their altitude.
     *
     * @return list<array{key: string, label: string, altitude: float}>
     */
    public static function visible(Location $location, int $timestamp): array
    {
        $visible = [];
        foreach (array_keys(self::AREAS) as $key) {
            $area = self::area($key);
            if (! $area) {
                continue;
            }
            $altitude = SkyPositions::altitude($area['ra'], $area['dec'], (float) $location->latitude, (float) $location->longitude, $timestamp);
            if ($altitude >= self::MIN_ALTITUDE) {
                $visible[] = ['key' => $key, 'label' => $area['label'], 'altitude' => round($altitude, 1)];
            }
        }
        usort($visible, fn ($a, $b) => $b['altitude'] <=> $a['altitude']);

        return $visible;
    }

    /**
     * The limiting magnitude in the figure for $count stars seen: halfway
     * between the faintest star seen and the next one. Null for none seen
     * (clouds, twilight, or a figure that is not visible), and the faintest
     * star of the table plus a little when all were seen.
     */
    public static function limitingMagnitude(array $area, int $count): ?float
    {
        $magnitudes = $area['magnitudes'];
        if ($count <= 0 || $magnitudes === []) {
            return null;
        }
        if ($count >= count($magnitudes)) {
            return round(end($magnitudes) + 0.1, 2);
        }

        return round(($magnitudes[$count - 1] + $magnitudes[$count]) / 2, 2);
    }

    /**
     * The result of a count: the limit in the figure, the altitude, the
     * extinction there and the limit at the zenith (the NELM), null without
     * a limit.
     *
     * @return array{area_magnitude: float, altitude: float, extinction: float, nelm: float, reliable: bool}|null
     */
    public static function measure(array $area, int $count, Location $location, int $timestamp): ?array
    {
        $limit = self::limitingMagnitude($area, $count);
        if ($limit === null) {
            return null;
        }
        $altitude = SkyPositions::altitude($area['ra'], $area['dec'], (float) $location->latitude, (float) $location->longitude, $timestamp);
        if ($altitude <= 0) {
            return null;
        }
        $extinction = AtmosphericExtinction::magnitudes($altitude);

        return [
            'area_magnitude' => $limit,
            'altitude' => round($altitude, 1),
            'extinction' => round($extinction, 2),
            'nelm' => round(min(LocationSkyHistory::NELM_RANGE[1], $limit + $extinction), 1),
            'reliable' => $altitude >= self::GOOD_ALTITUDE,
        ];
    }

    /**
     * The figure as the observer sees it at the moment, as an SVG: zenith up,
     * the outline, its corner stars with their Greek letter and the bright
     * stars around it to find it. The stars inside are not drawn, so the
     * chart does not tell what to look for.
     */
    public static function chart(array $area, Location $location, int $timestamp, int $size = 360): string
    {
        $lat = (float) $location->latitude;
        $lst = SkyPositions::siderealTime($timestamp) + (float) $location->longitude;
        $center = self::horizontal($area['ra'], $area['dec'], $lat, $lst);
        // the directions of the chart: up towards the zenith, right towards growing azimuth
        $zenith = [0.0, 0.0, 1.0];
        if (abs($center[2]) > 0.999) {
            $zenith = [0.0, 1.0, 0.0];
        }
        $up = self::normalize(self::subtract($zenith, self::scale($center, self::dot($zenith, $center))));
        $right = self::cross($center, $up);

        $scale = $size / 2 / tan(deg2rad(min(60.0, $area['radius'] * self::CHART_MARGIN)));
        $project = function (float $ra, float $dec) use ($lat, $lst, $center, $up, $right, $scale, $size): ?array {
            $p = self::horizontal($ra, $dec, $lat, $lst);
            $d = self::dot($p, $center);
            if ($d <= 0.2) {
                return null;
            }

            return [$size / 2 + $scale * self::dot($p, $right) / $d, $size / 2 - $scale * self::dot($p, $up) / $d];
        };
        $radius = fn (float $mag) => max(1.2, 4.5 - 0.7 * $mag);

        $corners = array_map(fn ($c) => ($xy = $project($c['ra'], $c['dec'])) ? $c + ['x' => $xy[0], 'y' => $xy[1]] : null, $area['corners']);
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $size . ' ' . $size . '" class="h-auto w-full" role="img">'
            . '<rect width="' . $size . '" height="' . $size . '" fill="#0b1020"/>';

        $points = implode(' ', array_map(fn ($c) => round($c['x'], 1) . ',' . round($c['y'], 1), array_filter(array_map(fn ($i) => $corners[$i], $area['outline']))));
        $svg .= '<polygon points="' . $points . '" fill="rgba(59,130,246,0.08)" stroke="#60a5fa" stroke-width="1" stroke-dasharray="4 3"/>';

        foreach (self::brightStarsAround($area) as $star) {
            if (($xy = $project($star->ra_degrees, $star->decl_degrees)) && $xy[0] >= 0 && $xy[0] <= $size && $xy[1] >= 0 && $xy[1] <= $size) {
                $svg .= '<circle cx="' . round($xy[0], 1) . '" cy="' . round($xy[1], 1) . '" r="' . round($radius((float) $star->vmag), 1) . '" fill="#cbd5e1"/>';
            }
        }
        foreach (array_filter($corners) as $corner) {
            $svg .= '<circle cx="' . round($corner['x'], 1) . '" cy="' . round($corner['y'], 1) . '" r="' . round($radius($corner['mag']), 1) . '" fill="#ffffff"/>'
                . '<text x="' . round($corner['x'] + 6, 1) . '" y="' . round($corner['y'] - 6, 1) . '" fill="#fde68a" font-size="13" font-family="sans-serif">' . e($corner['label']) . '</text>';
        }
        $svg .= '<text x="' . ($size / 2) . '" y="14" fill="#94a3b8" font-size="11" font-family="sans-serif" text-anchor="middle">' . e(__('Zenith')) . ' ↑</text>';

        return $svg . '</svg>';
    }

    private static function compute(string $key): ?array
    {
        $definition = self::AREAS[$key];
        $corners = [];
        foreach ($definition['corners'] as [$bayer, $constellation]) {
            $star = DB::table('stars')
                ->where('bayer', $bayer)
                ->where('constellation_code', $constellation)
                ->whereNotNull('vmag')
                ->orderBy('vmag')
                ->first(['ra_degrees', 'decl_degrees', 'vmag', 'bayer', 'bayer_suffix', 'constellation_code', 'flamsteed', 'name']);
            if (! $star) {
                return null;
            }
            $corners[] = [
                'ra' => (float) $star->ra_degrees,
                'dec' => (float) $star->decl_degrees,
                'mag' => (float) $star->vmag,
                'label' => (string) Star::displayNameFor((object) ['bayer' => self::greek($bayer), 'bayer_suffix' => $star->bayer_suffix, 'constellation_code' => $constellation]),
            ];
        }

        $vectors = array_map(fn ($c) => self::vector($c['ra'], $c['dec']), $corners);
        $center = self::normalize(array_reduce($vectors, fn ($sum, $v) => self::add($sum, $v), [0.0, 0.0, 0.0]));
        [$ra, $dec] = self::equatorial($center);
        $radius = max(array_map(fn ($v) => rad2deg(acos(max(-1.0, min(1.0, self::dot($v, $center))))), $vectors));

        // the outline: the convex hull of the corners in the plane tangent at the center,
        // counterclockwise; a corner inside it (γ Cas in the W) is a corner star all the same
        $basis = self::tangentBasis($center);
        $outline = self::convexHull(array_map(fn ($v) => self::onPlane($v, $center, $basis), $vectors));
        $hull = array_map(fn ($i) => self::onPlane($vectors[$i], $center, $basis), $outline);

        $stars = [];
        foreach (self::starsWithin($ra, $dec, $radius, self::CATALOGUE_LIMIT) as $row) {
            $v = self::vector((float) $row->ra_degrees, (float) $row->decl_degrees);
            if (self::dot($v, $center) <= 0) {
                continue;
            }
            foreach ($vectors as $corner) {
                if (self::separation($v, $corner) < self::CORNER_DEG) {
                    continue 2;
                }
            }
            if (! self::inside(self::onPlane($v, $center, $basis), $hull)) {
                continue;
            }
            foreach ($stars as $kept) {
                if (self::separation($v, $kept['v']) < self::SAME_STAR_DEG) {
                    continue 2;
                }
            }
            $stars[] = ['v' => $v, 'mag' => (float) $row->vmag];
        }

        return [
            'key' => $key,
            'label' => $definition['label'],
            'ra' => $ra,
            'dec' => $dec,
            'radius' => $radius,
            'corners' => $corners,
            'outline' => $outline,
            'magnitudes' => array_column($stars, 'mag'),
        ];
    }

    /** The stars of the table within $radius of the point, brightest first. */
    private static function starsWithin(float $ra, float $dec, float $radius, float $limit): \Illuminate\Support\Collection
    {
        $query = DB::table('stars')
            ->whereNotNull('vmag')
            ->where('vmag', '<=', $limit)
            ->whereBetween('decl_degrees', [$dec - $radius, $dec + $radius]);

        $poleDistance = 90.0 - abs($dec);
        if ($poleDistance > $radius) {
            $halfWidth = rad2deg(asin(min(1.0, sin(deg2rad($radius)) / cos(deg2rad($dec)))));
            $from = fmod($ra - $halfWidth + 360.0, 360.0);
            $to = fmod($ra + $halfWidth, 360.0);
            $query->where(fn ($q) => $from <= $to
                ? $q->whereBetween('ra_degrees', [$from, $to])
                : $q->where('ra_degrees', '>=', $from)->orWhere('ra_degrees', '<=', $to));
        }

        return $query->orderBy('vmag')->get(['ra_degrees', 'decl_degrees', 'vmag']);
    }

    /** The bright stars on the chart around a figure, without those inside it. */
    private static function brightStarsAround(array $area): array
    {
        $center = self::vector($area['ra'], $area['dec']);
        $basis = self::tangentBasis($center);
        $hull = array_map(fn ($i) => self::onPlane(self::vector($area['corners'][$i]['ra'], $area['corners'][$i]['dec']), $center, $basis), $area['outline']);

        return Cache::rememberForever('star_count_area:v' . self::CACHE_VERSION . ':around:' . $area['key'], function () use ($area, $center, $basis, $hull) {
            $around = [];
            foreach (self::starsWithin($area['ra'], $area['dec'], min(60.0, $area['radius'] * self::CHART_MARGIN * 1.5), self::CHART_MAGNITUDE) as $row) {
                $v = self::vector((float) $row->ra_degrees, (float) $row->decl_degrees);
                if (self::dot($v, $center) > 0 && self::inside(self::onPlane($v, $center, $basis), $hull)) {
                    continue;
                }
                foreach ($area['corners'] as $corner) {
                    if (self::separation($v, self::vector($corner['ra'], $corner['dec'])) < self::CORNER_DEG) {
                        continue 2;
                    }
                }
                $around[] = (object) ['ra_degrees' => (float) $row->ra_degrees, 'decl_degrees' => (float) $row->decl_degrees, 'vmag' => (float) $row->vmag];
            }

            return $around;
        });
    }

    /**
     * The indices of the points on their convex hull, counterclockwise
     * (Andrew's monotone chain).
     *
     * @param  list<array{0: float, 1: float}>  $points
     * @return list<int>
     */
    private static function convexHull(array $points): array
    {
        $order = array_keys($points);
        usort($order, fn ($a, $b) => [$points[$a][0], $points[$a][1]] <=> [$points[$b][0], $points[$b][1]]);
        $turn = fn ($o, $a, $b) => ($points[$a][0] - $points[$o][0]) * ($points[$b][1] - $points[$o][1])
            - ($points[$a][1] - $points[$o][1]) * ($points[$b][0] - $points[$o][0]);

        $chain = function (array $order) use ($turn): array {
            $hull = [];
            foreach ($order as $i) {
                while (count($hull) >= 2 && $turn($hull[count($hull) - 2], $hull[count($hull) - 1], $i) <= 0) {
                    array_pop($hull);
                }
                $hull[] = $i;
            }
            array_pop($hull);

            return $hull;
        };

        return array_merge($chain($order), $chain(array_reverse($order)));
    }

    /** Whether the point lies inside the convex polygon (counterclockwise). */
    private static function inside(array $point, array $hull): bool
    {
        $count = count($hull);
        for ($i = 0; $i < $count; $i++) {
            $a = $hull[$i];
            $b = $hull[($i + 1) % $count];
            if (($b[0] - $a[0]) * ($point[1] - $a[1]) - ($b[1] - $a[1]) * ($point[0] - $a[0]) < 0) {
                return false;
            }
        }

        return true;
    }

    private static function greek(string $bayer): string
    {
        return [
            'Alp' => 'α', 'Bet' => 'β', 'Gam' => 'γ', 'Del' => 'δ', 'Eps' => 'ε', 'Zet' => 'ζ', 'Eta' => 'η', 'The' => 'θ',
            'Iot' => 'ι', 'Kap' => 'κ', 'Lam' => 'λ', 'Mu' => 'μ', 'Nu' => 'ν', 'Xi' => 'ξ', 'Omi' => 'ο', 'Pi' => 'π',
            'Rho' => 'ρ', 'Sig' => 'σ', 'Tau' => 'τ', 'Ups' => 'υ', 'Phi' => 'φ', 'Chi' => 'χ', 'Psi' => 'ψ', 'Ome' => 'ω',
        ][$bayer] ?? $bayer;
    }

    /** The unit vector of a direction in the horizontal frame: x east, y north, z up. */
    private static function horizontal(float $ra, float $dec, float $lat, float $lstDeg): array
    {
        $h = deg2rad($lstDeg - $ra);
        $d = deg2rad($dec);
        $phi = deg2rad($lat);
        $east = -cos($d) * sin($h);
        $north = sin($d) * cos($phi) - cos($d) * cos($h) * sin($phi);
        $up = sin($d) * sin($phi) + cos($d) * cos($h) * cos($phi);

        return [$east, $north, $up];
    }

    private static function vector(float $ra, float $dec): array
    {
        $a = deg2rad($ra);
        $d = deg2rad($dec);

        return [cos($d) * cos($a), cos($d) * sin($a), sin($d)];
    }

    private static function equatorial(array $v): array
    {
        return [fmod(rad2deg(atan2($v[1], $v[0])) + 360.0, 360.0), rad2deg(asin(max(-1.0, min(1.0, $v[2]))))];
    }

    private static function tangentBasis(array $center): array
    {
        $pole = abs($center[2]) > 0.999 ? [1.0, 0.0, 0.0] : [0.0, 0.0, 1.0];
        $north = self::normalize(self::subtract($pole, self::scale($center, self::dot($pole, $center))));

        return [self::cross($north, $center), $north];
    }

    /** The gnomonic projection of a direction on the plane tangent at $center (great circles become lines). */
    private static function onPlane(array $v, array $center, array $basis): array
    {
        $d = self::dot($v, $center);

        return [self::dot($v, $basis[0]) / $d, self::dot($v, $basis[1]) / $d];
    }

    private static function separation(array $a, array $b): float
    {
        return rad2deg(acos(max(-1.0, min(1.0, self::dot($a, $b)))));
    }

    private static function dot(array $a, array $b): float
    {
        return $a[0] * $b[0] + $a[1] * $b[1] + $a[2] * $b[2];
    }

    private static function cross(array $a, array $b): array
    {
        return [$a[1] * $b[2] - $a[2] * $b[1], $a[2] * $b[0] - $a[0] * $b[2], $a[0] * $b[1] - $a[1] * $b[0]];
    }

    private static function add(array $a, array $b): array
    {
        return [$a[0] + $b[0], $a[1] + $b[1], $a[2] + $b[2]];
    }

    private static function subtract(array $a, array $b): array
    {
        return [$a[0] - $b[0], $a[1] - $b[1], $a[2] - $b[2]];
    }

    private static function scale(array $a, float $f): array
    {
        return [$a[0] * $f, $a[1] * $f, $a[2] * $f];
    }

    private static function normalize(array $a): array
    {
        $length = sqrt(self::dot($a, $a));

        return $length > 0 ? self::scale($a, 1 / $length) : $a;
    }
}
