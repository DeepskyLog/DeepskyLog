<?php

namespace App\Services\FieldViews;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Draws one circular field of view as an SVG: the sky around a point as the
 * observer sees it, with the naked eye, in a finder or in a telescope.
 *
 * The sky is drawn in a stereographic projection centred on the field. That
 * projection does not depend on the coordinate system it is computed in, so
 * the view in horizontal coordinates is the equatorial one turned until the
 * zenith points up. The image is then mirrored the way the instrument
 * mirrors it, and the horizon, which a stereographic projection draws as a
 * circle, is filled in as ground.
 *
 * The view is described by an array:
 * - ra, dec: centre of the field, in degrees
 * - radius_deg: half the field of view
 * - star_limit: faintest star drawn (at the zenith when extinction is on)
 * - object_limit: faintest deep-sky object drawn; null draws only the target
 * - target: ['name', 'ra', 'dec' (degrees), 'type', 'diam1', 'diam2' (arcseconds), 'pa']
 * - lst, latitude: local sidereal time and latitude, in degrees; null when
 *   the time and place are unknown
 * - up: 'zenith' or 'north'
 * - flip (top-bottom), flop (left-right): mirroring of the image
 * - horizon: whether to draw the ground and the compass points on it
 * - extinction: whether stars fade towards the horizon
 * - constellations, star_names, object_labels: what to draw besides the stars
 * - overlays: [['radius_deg' => float, 'label' => string]], circles around the centre
 * - theme: 'dark' (screen) or 'light' (print)
 * - size: width and height in pixels
 */
class FieldViewRenderer
{
    /** Atmospheric extinction in the visual, magnitudes per airmass. */
    public const EXTINCTION_COEFFICIENT = 0.25;

    /** Stars brighter than this get their Bayer letter in views with star names. */
    private const STAR_NAME_MAGNITUDE = 3.0;

    private const GREEK_LETTERS = [
        'Alp' => 'α', 'Bet' => 'β', 'Gam' => 'γ', 'Del' => 'δ', 'Eps' => 'ε', 'Zet' => 'ζ', 'Eta' => 'η', 'The' => 'θ',
        'Iot' => 'ι', 'Kap' => 'κ', 'Lam' => 'λ', 'Mu' => 'μ', 'Nu' => 'ν', 'Xi' => 'ξ', 'Omi' => 'ο', 'Pi' => 'π',
        'Rho' => 'ρ', 'Sig' => 'σ', 'Tau' => 'τ', 'Ups' => 'υ', 'Phi' => 'φ', 'Chi' => 'χ', 'Psi' => 'ψ', 'Ome' => 'ω',
    ];

    /** Fields wider than this read the stars by magnitude, not by declination zone. */
    private const WIDE_FIELD_DEG = 10.0;

    /** Most deep-sky objects drawn in one view. */
    private const MAX_OBJECTS = 400;

    /** Most stars drawn in one view; the brightest are kept. */
    private const MAX_STARS = 30000;

    private const GALAXY_TYPES = ['GALXY', 'GALAXY', 'G', 'GACAN', 'GXADN', 'GXAGC', 'QUASR', 'GALCL'];

    private const THEMES = [
        'dark' => [
            'background'    => '#111827',
            'sky'           => '#03050b',
            'star'          => '#ffffff',
            'ground'        => '#1c2414',
            'horizon'       => '#7c9a3d',
            'lines'         => '#30507a',
            'label'         => '#9ca3af',
            'constellation' => '#5b7bb0',
            'target'        => '#f87171',
            'overlay'       => '#facc15',
            'object'        => '#7dd3fc',
            'rim'           => '#4b5563',
        ],
        'light' => [
            'background'    => '#ffffff',
            'sky'           => '#ffffff',
            'star'          => '#000000',
            'ground'        => '#e5e7eb',
            'horizon'       => '#4b5563',
            'lines'         => '#9ca3af',
            'label'         => '#374151',
            'constellation' => '#6b7280',
            'target'        => '#dc2626',
            'overlay'       => '#b45309',
            'object'        => '#1d4ed8',
            'rim'           => '#111827',
        ],
    ];

    private array $view;

    private array $colors;

    private float $centerX;

    private float $centerY;

    private float $plotRadius;

    /** Pixels per unit of the stereographic projection. */
    private float $scale;

    /** Rotation that brings "up" (north or the zenith) to the top, in radians. */
    private float $rotation = 0.0;

    /**
     * @return string the SVG document
     */
    public function render(array $view): string
    {
        $this->view = $view + [
            'object_limit'   => null,
            'lst'            => null,
            'latitude'       => null,
            'up'             => 'north',
            'flip'           => false,
            'flop'           => false,
            'horizon'        => false,
            'extinction'     => false,
            'constellations' => false,
            'star_names'     => false,
            'object_labels'  => false,
            'overlays'       => [],
            'theme'          => 'dark',
            'size'           => 600,
        ];
        $this->colors = self::THEMES[$this->view['theme']] ?? self::THEMES['dark'];

        $size             = (float) $this->view['size'];
        $this->centerX    = $size / 2;
        $this->centerY    = $size / 2;
        $this->plotRadius = $size / 2 - $size * 0.06;
        $radius           = max(0.01, (float) $this->view['radius_deg']);
        $this->scale      = $this->plotRadius / (2.0 * tan(deg2rad($radius) / 2.0));
        $this->rotation   = $this->resolveRotation();

        $s = $size / 600;

        $svg   = [];
        $svg[] = sprintf('<svg xmlns="http://www.w3.org/2000/svg" width="%1$d" height="%1$d" viewBox="0 0 %1$d %1$d" font-family="Helvetica, Arial, sans-serif">', (int) $size);
        $svg[] = sprintf('<rect x="0" y="0" width="%1$d" height="%1$d" fill="%2$s"/>', (int) $size, $this->colors['background']);
        $svg[] = sprintf('<circle cx="%s" cy="%s" r="%s" fill="%s"/>', $this->f($this->centerX), $this->f($this->centerY), $this->f($this->plotRadius), $this->colors['sky']);

        if ($this->view['constellations']) {
            $svg[] = $this->constellationLines($s);
        }
        $svg[] = $this->objects($s);
        $svg[] = $this->stars($s);
        if ($this->view['horizon'] && $this->hasTime()) {
            $svg[] = $this->ground($s);
        }
        $svg[] = $this->overlays($s);
        $svg[] = $this->targetMarker($s);
        // What lies outside the field is painted over rather than clipped:
        // not every SVG renderer (ImageMagick, dompdf) supports clip paths.
        $svg[] = sprintf('<path d="M0 0H%1$dV%1$dH0Z%2$s" fill="%3$s" fill-rule="evenodd"/>', (int) $size, $this->circlePath($this->centerX, $this->centerY, $this->plotRadius), $this->colors['background']);

        $svg[] = sprintf('<circle cx="%s" cy="%s" r="%s" fill="none" stroke="%s" stroke-width="%s"/>', $this->f($this->centerX), $this->f($this->centerY), $this->f($this->plotRadius), $this->colors['rim'], $this->f(1.5 * $s));
        if ($this->view['horizon'] && $this->hasTime()) {
            $svg[] = $this->compassPoints($s);
        } else {
            $svg[] = $this->compass($s);
        }
        $svg[] = '</svg>';

        return implode("\n", array_filter($svg));
    }

    /**
     * Screen position of a point of the sky, or null when it lies (nearly)
     * opposite the centre of the field.
     *
     * @return array{0: float, 1: float}|null
     */
    public function project(float $raDeg, float $decDeg): ?array
    {
        $ra0  = deg2rad((float) $this->view['ra']);
        $dec0 = deg2rad((float) $this->view['dec']);
        $ra   = deg2rad($raDeg);
        $dec  = deg2rad($decDeg);
        $dRa  = $ra - $ra0;

        $cosC = sin($dec0) * sin($dec) + cos($dec0) * cos($dec) * cos($dRa);
        if ($cosC <= -0.98) {
            return null;
        }

        $k = 2.0 / (1.0 + $cosC);
        // East to the left, north up, as on the sky.
        $x = -$k * cos($dec) * sin($dRa);
        $y = $k  * (cos($dec0) * sin($dec) - sin($dec0) * cos($dec) * cos($dRa));

        $cos = cos($this->rotation);
        $sin = sin($this->rotation);
        $xr  = $x * $cos - $y * $sin;
        $yr  = $x * $sin + $y * $cos;

        if ($this->view['flop']) {
            $xr = -$xr;
        }
        if ($this->view['flip']) {
            $yr = -$yr;
        }

        return [$this->centerX + $xr * $this->scale, $this->centerY - $yr * $this->scale];
    }

    /**
     * Equatorial coordinates (degrees) of a point at the given altitude and
     * azimuth (from the north through the east).
     *
     * @return array{0: float, 1: float}
     */
    public static function horizontalToEquatorial(float $altDeg, float $azDeg, float $lstDeg, float $latDeg): array
    {
        $alt = deg2rad($altDeg);
        $az  = deg2rad($azDeg);
        $lat = deg2rad($latDeg);

        $sinDec    = sin($lat) * sin($alt) + cos($lat) * cos($alt) * cos($az);
        $dec       = asin(max(-1.0, min(1.0, $sinDec)));
        $hourAngle = atan2(-sin($az) * cos($alt), cos($lat) * sin($alt) - sin($lat) * cos($alt) * cos($az));

        return [fmod(fmod($lstDeg - rad2deg($hourAngle), 360.0) + 360.0, 360.0), rad2deg($dec)];
    }

    /**
     * Altitude (degrees) of a point of the sky.
     */
    public static function altitude(float $raDeg, float $decDeg, float $lstDeg, float $latDeg): float
    {
        $hourAngle = deg2rad($lstDeg - $raDeg);
        $dec       = deg2rad($decDeg);
        $lat       = deg2rad($latDeg);

        return rad2deg(asin(max(-1.0, min(1.0, sin($lat) * sin($dec) + cos($lat) * cos($dec) * cos($hourAngle)))));
    }

    /**
     * Magnitudes a star loses at this altitude, against the zenith (Kasten
     * and Young's air mass). Below the horizon a star is never seen.
     */
    public static function extinction(float $altDeg): float
    {
        if ($altDeg <= 0) {
            return INF;
        }

        $airmass = 1.0 / (sin(deg2rad($altDeg)) + 0.50572 * pow($altDeg + 6.07995, -1.6364));

        return self::EXTINCTION_COEFFICIENT * ($airmass - 1.0);
    }

    private function hasTime(): bool
    {
        return $this->view['lst'] !== null && $this->view['latitude'] !== null;
    }

    /**
     * The angle that turns the zenith to the top of the view, from the
     * direction in which the zenith projects.
     */
    private function resolveRotation(): float
    {
        if ($this->view['up'] !== 'zenith' || ! $this->hasTime()) {
            return 0.0;
        }

        $this->rotation     = 0.0;
        $flip               = $this->view['flip'];
        $flop               = $this->view['flop'];
        $this->view['flip'] = $this->view['flop'] = false;

        $zenith = $this->project((float) $this->view['lst'], (float) $this->view['latitude']);

        $this->view['flip'] = $flip;
        $this->view['flop'] = $flop;

        if ($zenith === null) {
            return 0.0;
        }

        $dx = $zenith[0]     - $this->centerX;
        $dy = $this->centerY - $zenith[1];
        if (hypot($dx, $dy) < 1.0e-6) {
            return 0.0;
        }

        return M_PI / 2 - atan2($dy, $dx);
    }

    private function inField(array $point, float $margin = 0.0): bool
    {
        return hypot($point[0] - $this->centerX, $point[1] - $this->centerY) <= $this->plotRadius + $margin;
    }

    private function starRadius(float $magnitude, float $limit, float $s): float
    {
        return max(0.6, min(7.0, 0.7 + 0.5 * ($limit - $magnitude))) * $s;
    }

    private function stars(float $s): string
    {
        $limit      = (float) $this->view['star_limit'];
        $extinction = $this->view['extinction'] && $this->hasTime();
        $radius     = (float) $this->view['radius_deg'];

        $out   = [];
        $named = [];
        foreach ($this->loadStars($radius * 1.02, $limit) as $star) {
            $magnitude = (float) $star->vmag;
            if ($extinction) {
                $magnitude += self::extinction(self::altitude((float) $star->ra_degrees, (float) $star->decl_degrees, (float) $this->view['lst'], (float) $this->view['latitude']));
                if ($magnitude > $limit) {
                    continue;
                }
            }

            $point = $this->project((float) $star->ra_degrees, (float) $star->decl_degrees);
            if ($point === null || ! $this->inField($point, 2)) {
                continue;
            }

            $out[] = sprintf('<circle cx="%s" cy="%s" r="%s"/>', $this->f($point[0]), $this->f($point[1]), $this->f($this->starRadius($magnitude, $limit, $s)));

            if ($this->view['star_names'] && (float) $star->vmag <= self::STAR_NAME_MAGNITUDE) {
                $named[$star->id] = $point;
            }
        }

        $labels = [];
        if ($named !== []) {
            $rows = DB::table('stars')->select(['id', 'bayer', 'bayer_suffix'])->whereIn('id', array_keys($named))->whereNotNull('bayer')->get();
            foreach ($rows as $row) {
                $letter = self::GREEK_LETTERS[ucfirst(strtolower((string) $row->bayer))] ?? null;
                if ($letter === null) {
                    continue;
                }
                [$x, $y]  = $named[$row->id];
                $labels[] = sprintf('<text x="%s" y="%s" font-size="%s" fill="%s">%s</text>', $this->f($x + 5 * $s), $this->f($y - 3 * $s), $this->f(16 * $s), $this->colors['label'], e($letter.($row->bayer_suffix ?? '')));
            }
        }

        return '<g fill="'.$this->colors['star'].'">'.implode('', $out).'</g>'.implode('', $labels);
    }

    /**
     * The stars within the radius, brightest first. A wide field reads them
     * through the magnitude index (only a few thousand stars are bright
     * enough), a narrow one through the declination zones.
     */
    private function loadStars(float $radiusDeg, float $limit): iterable
    {
        $ra     = (float) $this->view['ra'];
        $dec    = (float) $this->view['dec'];
        $decMin = max(-90.0, $dec - $radiusDeg);
        $decMax = min(90.0, $dec + $radiusDeg);

        $query = DB::table('stars')
            ->select(['id', 'ra_degrees', 'decl_degrees', 'vmag'])
            ->whereNotNull('vmag')
            ->where('vmag', '<=', $limit)
            ->whereBetween('decl_degrees', [$decMin, $decMax]);

        $wide = $radiusDeg > self::WIDE_FIELD_DEG;
        if ($wide) {
            $query->orderBy('vmag')->limit(self::MAX_STARS);
        } else {
            // No ORDER BY: with one, MariaDB reads the magnitude index, a
            // million stars for a finder, instead of the zones.
            $query->whereIn('decl_zone', range((int) floor($decMin), (int) floor($decMax)));
        }

        $this->whereRaWindow($query, 'ra_degrees', 1.0, $ra, $dec, $radiusDeg);

        $stars = $query->get();
        if (! $wide) {
            $stars = $stars->sortBy('vmag')->take(self::MAX_STARS);
        }

        foreach ($stars as $star) {
            if ($this->separation($ra, $dec, (float) $star->ra_degrees, (float) $star->decl_degrees) <= $radiusDeg) {
                yield $star;
            }
        }
    }

    /**
     * Limits the right ascension to the window around the centre that holds
     * every point within the radius; none when the field holds a pole.
     */
    private function whereRaWindow($query, string $column, float $unitDeg, float $ra, float $dec, float $radiusDeg): void
    {
        if (abs($dec) + $radiusDeg >= 89.0) {
            return;
        }

        $half = rad2deg(asin(min(1.0, sin(deg2rad($radiusDeg)) / cos(deg2rad($dec)))));
        if ($half >= 179.0) {
            return;
        }

        $full = 360.0         / $unitDeg;
        $min  = ($ra - $half) / $unitDeg;
        $max  = ($ra + $half) / $unitDeg;

        if ($min < 0) {
            $query->where(fn ($q) => $q->where($column, '>=', $min + $full)->orWhere($column, '<=', $max));
        } elseif ($max >= $full) {
            $query->where(fn ($q) => $q->where($column, '>=', $min)->orWhere($column, '<=', $max - $full));
        } else {
            $query->whereBetween($column, [$min, $max]);
        }
    }

    private function separation(float $ra0, float $dec0, float $ra1, float $dec1): float
    {
        $cos = sin(deg2rad($dec0)) * sin(deg2rad($dec1)) + cos(deg2rad($dec0)) * cos(deg2rad($dec1)) * cos(deg2rad($ra1 - $ra0));

        return rad2deg(acos(max(-1.0, min(1.0, $cos))));
    }

    private function objects(float $s): string
    {
        if ($this->view['object_limit'] === null) {
            return '';
        }

        $ra         = (float) $this->view['ra'];
        $dec        = (float) $this->view['dec'];
        $radius     = (float) $this->view['radius_deg'];
        $limit      = (float) $this->view['object_limit'];
        $targetName = strtoupper((string) ($this->view['target']['name'] ?? ''));

        $query = DB::table('objects')
            ->select(['name', 'type', 'ra', 'decl', 'mag', 'diam1', 'diam2', 'pa'])
            ->whereBetween('decl', [max(-90.0, $dec - $radius), min(90.0, $dec + $radius)])
            ->whereNotIn('type', ['DS', 'AA1STAR', 'NONEX', ''])
            ->where(fn ($q) => $q->where('mag', '<=', $limit)
                // Objects without a magnitude only in a telescope's field, where they are large enough to see.
                ->orWhere(fn ($q) => $q->where('mag', '>=', 99)->where('diam1', '>=', $radius * 3600 * 0.05)))
            ->orderBy('mag')
            ->limit(self::MAX_OBJECTS * 2);
        $this->whereRaWindow($query, 'ra', 15.0, $ra, $dec, $radius);

        [$north, $east] = $this->localAxes();

        $out    = [];
        $labels = [];
        $count  = 0;
        foreach ($query->get() as $object) {
            if (strtoupper($object->name) === $targetName) {
                continue;
            }
            $objectRa = (float) $object->ra * 15.0;
            if ($this->separation($ra, $dec, $objectRa, (float) $object->decl) > $radius * 1.1) {
                continue;
            }
            $point = $this->project($objectRa, (float) $object->decl);
            if ($point === null || ! $this->inField($point, 10)) {
                continue;
            }

            $out[] = $this->objectSymbol($point, (string) $object->type, (float) $object->diam1, (float) $object->diam2, (float) $object->pa, $north, $east, $s);
            if ($this->view['object_labels']) {
                $labels[] = sprintf('<text x="%s" y="%s" font-size="%s" fill="%s">%s</text>', $this->f($point[0] + 7 * $s), $this->f($point[1] - 6 * $s), $this->f(18 * $s), $this->colors['object'], e($object->name));
            }

            if (++$count >= self::MAX_OBJECTS) {
                break;
            }
        }

        return '<g fill="none" stroke="'.$this->colors['object'].'" stroke-width="'.$this->f(1.2 * $s).'">'.implode('', $out).'</g>'.implode('', $labels);
    }

    /**
     * Unit vectors on the screen pointing north and east at the centre.
     *
     * @return array{0: array{0: float, 1: float}, 1: array{0: float, 1: float}}
     */
    private function localAxes(): array
    {
        $ra   = (float) $this->view['ra'];
        $dec  = (float) $this->view['dec'];
        $step = min(0.5, (float) $this->view['radius_deg'] / 10);

        $center     = $this->project($ra, $dec);
        $northPoint = $dec + $step <= 90 ? $this->project($ra, $dec + $step) : $this->project($ra + 180, 180 - $dec - $step);
        $eastPoint  = $this->project($ra + $step / max(0.01, cos(deg2rad($dec))), $dec);

        $unit = function (?array $p) use ($center): array {
            if ($p === null) {
                return [0.0, -1.0];
            }
            $dx  = $p[0] - $center[0];
            $dy  = $p[1] - $center[1];
            $len = hypot($dx, $dy) ?: 1.0;

            return [$dx / $len, $dy / $len];
        };

        return [$unit($northPoint), $unit($eastPoint)];
    }

    private function objectSymbol(array $point, string $type, float $diam1, float $diam2, float $pa, array $north, array $east, float $s): string
    {
        [$x, $y]         = $point;
        $pixelsPerArcsec = $this->scale * deg2rad(1.0 / 3600.0);
        $major           = max(4.0 * $s, $diam1 > 0 ? $diam1 * $pixelsPerArcsec / 2 : 0);
        $minor           = max(2.5 * $s, $diam2 > 0 ? $diam2 * $pixelsPerArcsec / 2 : $major);

        if ($type === 'OUTLINE') {
            return sprintf('<circle cx="%s" cy="%s" r="%s"/>', $this->f($x), $this->f($y), $this->f($major));
        }

        if (in_array($type, self::GALAXY_TYPES, true)) {
            $angle = 0.0;
            if ($pa >= 0 && $pa < 360) {
                $axis  = [cos(deg2rad($pa)) * $north[0] + sin(deg2rad($pa)) * $east[0], cos(deg2rad($pa)) * $north[1] + sin(deg2rad($pa)) * $east[1]];
                $angle = rad2deg(atan2($axis[1], $axis[0]));
            } else {
                $minor = $major;
            }

            return sprintf('<ellipse cx="%1$s" cy="%2$s" rx="%3$s" ry="%4$s" transform="rotate(%5$s %1$s %2$s)"/>', $this->f($x), $this->f($y), $this->f($major), $this->f(min($major, $minor)), $this->f($angle));
        }

        if (in_array($type, ['OPNCL', 'ASTER', 'CLANB'], true)) {
            return sprintf('<circle cx="%s" cy="%s" r="%s" stroke-dasharray="%s"/>', $this->f($x), $this->f($y), $this->f($major), $this->f(3 * $s));
        }

        if ($type === 'GLOCL') {
            return sprintf('<circle cx="%1$s" cy="%2$s" r="%3$s"/><path d="M%4$s %2$sH%5$sM%1$s %6$sV%7$s"/>', $this->f($x), $this->f($y), $this->f($major), $this->f($x - $major), $this->f($x + $major), $this->f($y - $major), $this->f($y + $major));
        }

        if ($type === 'PLNNB') {
            $r = max(3.0 * $s, $major);

            return sprintf('<circle cx="%1$s" cy="%2$s" r="%3$s"/><path d="M%1$s %4$sV%5$sM%1$s %6$sV%7$sM%8$s %2$sH%9$sM%10$s %2$sH%11$s"/>', $this->f($x), $this->f($y), $this->f($r), $this->f($y - $r), $this->f($y - 2 * $r), $this->f($y + $r), $this->f($y + 2 * $r), $this->f($x - $r), $this->f($x - 2 * $r), $this->f($x + $r), $this->f($x + 2 * $r));
        }

        // Nebulae and the rest: a rounded square of the size of the object.
        return sprintf('<rect x="%s" y="%s" width="%s" height="%s" rx="%s"/>', $this->f($x - $major), $this->f($y - $major), $this->f(2 * $major), $this->f(2 * $major), $this->f($major / 3));
    }

    private function constellationLines(float $s): string
    {
        $segments = self::constellationSegments();
        if ($segments === []) {
            return '';
        }

        $ra    = (float) $this->view['ra'];
        $dec   = (float) $this->view['dec'];
        $reach = (float) $this->view['radius_deg'] + 30.0;

        $paths     = [];
        $centroids = [];
        foreach ($segments as $segment) {
            [$con, $ra0, $dec0, $ra1, $dec1] = $segment;
            if ($this->separation($ra, $dec, $ra0, $dec0) > $reach && $this->separation($ra, $dec, $ra1, $dec1) > $reach) {
                continue;
            }
            $p0 = $this->project($ra0, $dec0);
            $p1 = $this->project($ra1, $dec1);
            if ($p0 === null || $p1 === null) {
                continue;
            }
            $paths[]           = sprintf('M%s %sL%s %s', $this->f($p0[0]), $this->f($p0[1]), $this->f($p1[0]), $this->f($p1[1]));
            $centroids[$con][] = $p0;
            $centroids[$con][] = $p1;
        }

        $out = '<path d="'.implode('', $paths).'" fill="none" stroke="'.$this->colors['lines'].'" stroke-width="'.$this->f(1.0 * $s).'"/>';

        // The names, only in a field large enough to hold whole constellations.
        if ((float) $this->view['radius_deg'] >= 15.0) {
            $names = self::constellationNames();
            foreach ($centroids as $con => $points) {
                $x = array_sum(array_column($points, 0)) / count($points);
                $y = array_sum(array_column($points, 1)) / count($points);
                if (! $this->inField([$x, $y], -20 * $s)) {
                    continue;
                }
                $out .= sprintf('<text x="%s" y="%s" font-size="%s" fill="%s" text-anchor="middle" font-style="italic">%s</text>', $this->f($x), $this->f($y), $this->f(19 * $s), $this->colors['constellation'], e(__($names[$con] ?? $con)));
            }
        }

        return $out;
    }

    /**
     * @return list<array{0: string, 1: float, 2: float, 3: float, 4: float}>
     */
    private static function constellationSegments(): array
    {
        static $segments = null;
        if ($segments !== null) {
            return $segments;
        }
        if (! Schema::hasTable('constellation_lines')) {
            return $segments = [];
        }

        $segments = [];
        $previous = null;
        $rows     = DB::table('constellation_lines')
            ->select(['con', 'line_index', 'ra_degrees', 'decl_degrees'])
            ->orderBy('con')->orderBy('line_index')->orderBy('point_index')
            ->get();
        foreach ($rows as $row) {
            $key = $row->con.'|'.$row->line_index;
            if ($previous !== null && $previous[0] === $key) {
                $segments[] = [(string) $row->con, $previous[1], $previous[2], (float) $row->ra_degrees, (float) $row->decl_degrees];
            }
            $previous = [$key, (float) $row->ra_degrees, (float) $row->decl_degrees];
        }

        return $segments;
    }

    /**
     * @return array<string, string>
     */
    private static function constellationNames(): array
    {
        static $names = null;
        if ($names === null) {
            $names = Schema::hasTable('constellations')
                ? DB::table('constellations')->pluck('name', 'id')->map(fn ($name) => (string) $name)->all()
                : [];
        }

        return $names;
    }

    /**
     * The ground: the part of the field below the horizon, which the
     * projection draws as a circle (a straight line when the centre is on
     * the horizon).
     */
    private function ground(float $s): string
    {
        $lst       = (float) $this->view['lst'];
        $lat       = (float) $this->view['latitude'];
        $centerAlt = self::altitude((float) $this->view['ra'], (float) $this->view['dec'], $lst, $lat);

        $points = [];
        for ($az = 0; $az < 360; $az += 10) {
            [$ra, $dec] = self::horizontalToEquatorial(0.0, (float) $az, $lst, $lat);
            $point      = $this->project($ra, $dec);
            if ($point !== null) {
                $points[] = $point;
            }
        }
        if (count($points) < 3) {
            return '';
        }

        $circle      = $this->circleThrough($points[0], $points[intdiv(count($points), 3)], $points[intdiv(2 * count($points), 3)]);
        $groundColor = $this->colors['ground'];
        $lineColor   = $this->colors['horizon'];
        $width       = $this->f(1.5 * $s);

        if ($circle === null || $circle[2] > 1.0e5) {
            // The horizon runs through the centre: a straight line.
            [$a, $b]   = [$points[0], $points[intdiv(count($points), 2)]];
            $dx        = $b[0] - $a[0];
            $dy        = $b[1] - $a[1];
            $len       = hypot($dx, $dy) ?: 1.0;
            [$dx, $dy] = [$dx / $len * 4000, $dy / $len * 4000];
            // The ground is on the side of the nadir.
            [$nadirRa, $nadirDec] = self::horizontalToEquatorial(-30.0, 0.0, $lst, $lat);
            $nadir                = $this->project($nadirRa, $nadirDec) ?? [$this->centerX, $this->centerY + 1];
            $side                 = ($nadir[0] - $a[0]) * (-$dy) + ($nadir[1] - $a[1]) * $dx >= 0 ? 1 : -1;
            [$nx, $ny]            = [-$dy * $side, $dx * $side];
            $polygon              = sprintf('M%s %sL%s %sL%s %sL%s %sZ', $this->f($a[0] - $dx), $this->f($a[1] - $dy), $this->f($a[0] + $dx), $this->f($a[1] + $dy), $this->f($a[0] + $dx + $nx), $this->f($a[1] + $dy + $ny), $this->f($a[0] - $dx + $nx), $this->f($a[1] - $dy + $ny));

            return sprintf('<path d="%s" fill="%s" fill-opacity="0.92"/><path d="M%s %sL%s %s" stroke="%s" stroke-width="%s"/>', $polygon, $groundColor, $this->f($a[0] - $dx), $this->f($a[1] - $dy), $this->f($a[0] + $dx), $this->f($a[1] + $dy), $lineColor, $width);
        }

        [$cx, $cy, $r] = $circle;
        $centerInside  = hypot($this->centerX - $cx, $this->centerY - $cy) < $r;
        // The centre is in the sky when it is above the horizon: the ground
        // is then the side of the circle the centre is not on.
        $groundInside = ($centerAlt >= 0) !== $centerInside;

        $circlePath = $this->circlePath($cx, $cy, $r);
        if ($groundInside) {
            $fill = sprintf('<path d="%s" fill="%s" fill-opacity="0.92"/>', $circlePath, $groundColor);
        } else {
            $disc = $this->circlePath($this->centerX, $this->centerY, $this->plotRadius + 5);
            $fill = sprintf('<path d="%s%s" fill="%s" fill-opacity="0.92" fill-rule="evenodd"/>', $disc, $circlePath, $groundColor);
        }

        return $fill.sprintf('<circle cx="%s" cy="%s" r="%s" fill="none" stroke="%s" stroke-width="%s"/>', $this->f($cx), $this->f($cy), $this->f($r), $lineColor, $width);
    }

    private function circlePath(float $cx, float $cy, float $r): string
    {
        return sprintf('M%1$s %2$sa%3$s %3$s 0 1 0 %4$s 0a%3$s %3$s 0 1 0 %5$s 0Z', $this->f($cx - $r), $this->f($cy), $this->f($r), $this->f(2 * $r), $this->f(-2 * $r));
    }

    /**
     * @return array{0: float, 1: float, 2: float}|null centre and radius
     */
    private function circleThrough(array $a, array $b, array $c): ?array
    {
        $d = 2 * ($a[0] * ($b[1] - $c[1]) + $b[0] * ($c[1] - $a[1]) + $c[0] * ($a[1] - $b[1]));
        if (abs($d) < 1.0e-9) {
            return null;
        }

        $a2 = $a[0] ** 2 + $a[1] ** 2;
        $b2 = $b[0] ** 2 + $b[1] ** 2;
        $c2 = $c[0] ** 2 + $c[1] ** 2;
        $x  = ($a2 * ($b[1] - $c[1]) + $b2 * ($c[1] - $a[1]) + $c2 * ($a[1] - $b[1])) / $d;
        $y  = ($a2 * ($c[0] - $b[0]) + $b2 * ($a[0] - $c[0]) + $c2 * ($b[0] - $a[0])) / $d;

        return [$x, $y, hypot($a[0] - $x, $a[1] - $y)];
    }

    /**
     * The compass points on the horizon that fall in the field.
     */
    private function compassPoints(float $s): string
    {
        $labels = [0 => __('N'), 45 => __('NE'), 90 => __('E'), 135 => __('SE'), 180 => __('S'), 225 => __('SW'), 270 => __('W'), 315 => __('NW')];
        $out    = '';
        foreach ($labels as $az => $label) {
            [$ra, $dec] = self::horizontalToEquatorial(0.0, (float) $az, (float) $this->view['lst'], (float) $this->view['latitude']);
            $point      = $this->project($ra, $dec);
            if ($point === null || ! $this->inField($point, -12 * $s)) {
                continue;
            }
            $out .= sprintf('<text x="%s" y="%s" font-size="%s" font-weight="bold" fill="%s" text-anchor="middle">%s</text>', $this->f($point[0]), $this->f($point[1] + 24 * $s), $this->f(22 * $s), $this->colors['horizon'], e($label));
        }

        return $out;
    }

    /**
     * Arrows to the north and the east in the lower left corner, which show
     * how the image is turned and mirrored.
     */
    private function compass(float $s): string
    {
        [$north, $east] = $this->localAxes();
        $x0             = 44                               * $s;
        $y0             = (float) $this->view['size'] - 44 * $s;
        $length         = 30                               * $s;
        $out            = '';
        foreach ([[$north, __('N')], [$east, __('E')]] as [$axis, $label]) {
            $x1 = $x0 + $axis[0] * $length;
            $y1 = $y0 + $axis[1] * $length;
            $out .= sprintf('<path d="M%s %sL%s %s" stroke="%s" stroke-width="%s"/>', $this->f($x0), $this->f($y0), $this->f($x1), $this->f($y1), $this->colors['label'], $this->f(1.5 * $s));
            $out .= sprintf('<text x="%s" y="%s" font-size="%s" fill="%s" text-anchor="middle" dominant-baseline="middle">%s</text>', $this->f($x0 + $axis[0] * ($length + 10 * $s)), $this->f($y0 + $axis[1] * ($length + 10 * $s)), $this->f(19 * $s), $this->colors['label'], e($label));
        }

        return $out;
    }

    private function overlays(float $s): string
    {
        $out = '';
        foreach ($this->view['overlays'] as $overlay) {
            $radius = (float) ($overlay['radius_deg'] ?? 0);
            if ($radius <= 0 || $radius >= (float) $this->view['radius_deg']) {
                continue;
            }
            $r = 2.0 * tan(deg2rad($radius) / 2.0) * $this->scale;
            $out .= sprintf('<circle cx="%s" cy="%s" r="%s" fill="none" stroke="%s" stroke-width="%s" stroke-dasharray="%s"/>', $this->f($this->centerX), $this->f($this->centerY), $this->f($r), $this->colors['overlay'], $this->f(1.4 * $s), $this->f(6 * $s));
            if (! empty($overlay['label'])) {
                $out .= sprintf('<text x="%s" y="%s" font-size="%s" fill="%s" text-anchor="middle">%s</text>', $this->f($this->centerX), $this->f($this->centerY - $r - 5 * $s), $this->f(18 * $s), $this->colors['overlay'], e($overlay['label']));
            }
        }

        return $out;
    }

    private function targetMarker(float $s): string
    {
        $target = $this->view['target'] ?? null;
        if (! is_array($target)) {
            return '';
        }

        $point = $this->project((float) $target['ra'], (float) $target['dec']);
        if ($point === null) {
            return '';
        }
        [$x, $y] = $point;
        $color   = $this->colors['target'];

        // The outline of the object where it is large enough, else a reticle.
        $pixelsPerArcsec = $this->scale                    * deg2rad(1.0 / 3600.0);
        $size            = (float) ($target['diam1'] ?? 0) * $pixelsPerArcsec / 2;
        if ($size >= 6 * $s) {
            // An ellipse for a galaxy with a position angle, else a circle.
            $isGalaxy       = in_array((string) ($target['type'] ?? ''), self::GALAXY_TYPES, true);
            $type           = $isGalaxy ? 'GALXY' : 'OUTLINE';
            [$north, $east] = $this->localAxes();

            return '<g fill="none" stroke="'.$color.'" stroke-width="'.$this->f(1.6 * $s).'">'
                .$this->objectSymbol($point, $type, (float) $target['diam1'], (float) ($target['diam2'] ?? 0), (float) ($target['pa'] ?? 999), $north, $east, $s)
                .'</g>';
        }

        $inner = 6  * $s;
        $outer = 16 * $s;

        return sprintf(
            '<g stroke="%s" stroke-width="%s" fill="none"><path d="M%s %sH%sM%s %sH%sM%s %sV%sM%s %sV%s"/></g>',
            $color,
            $this->f(2 * $s),
            $this->f($x - $outer),
            $this->f($y),
            $this->f($x - $inner),
            $this->f($x + $inner),
            $this->f($y),
            $this->f($x + $outer),
            $this->f($x),
            $this->f($y - $outer),
            $this->f($y - $inner),
            $this->f($x),
            $this->f($y + $inner),
            $this->f($y + $outer),
        );
    }

    private function f(float $value): string
    {
        $text = rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');

        return $text === '' || $text === '-' || $text === '-0' ? '0' : $text;
    }
}
