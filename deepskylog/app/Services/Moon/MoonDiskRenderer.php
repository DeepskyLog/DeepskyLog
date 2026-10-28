<?php

namespace App\Services\Moon;

/**
 * The disk of the Moon as an SVG at a moment of LunarEphemeris::at(): the lit
 * part with the terminator, the maria (as circles of their diameter, from the
 * gazetteer), the libration and the position angle of the axis, and numbered
 * markers for the features of the terminator list.
 *
 * The orientations are those of the eye: ORIENTATIONS 'north' is celestial
 * north up and east left (naked eye, binoculars), 'south' turned by 180°
 * (a telescope without diagonal), 'mirror' north up and east right (a
 * refractor or SCT with a star diagonal).
 */
class MoonDiskRenderer
{
    public const ORIENTATIONS = ['north', 'south', 'mirror'];

    /** The radius of the Moon in km. */
    private const MOON_RADIUS_KM = 1737.4;

    /** The maria smaller than this (km) are not drawn. */
    private const MIN_MARE_DIAMETER = 150.0;

    /** The points of the outline of a mare or crater. */
    private const OUTLINE_POINTS = 36;

    private float $centre;

    private float $radius;

    public function __construct(private array $ephemeris, private string $orientation = 'north', private int $size = 420)
    {
        $this->orientation = in_array($orientation, self::ORIENTATIONS, true) ? $orientation : 'north';
        $this->centre      = $size / 2;
        $this->radius      = $size / 2 - 18;
    }

    /**
     * @param  list<array{name: string, lat: float, lon: float, diameter: float}>  $markers  numbered from 1 in this order
     */
    public function svg(array $markers = []): string
    {
        $c     = $this->centre;
        $r     = $this->radius;
        $id    = 'moon-'.substr(md5(json_encode([$this->ephemeris['timestamp'], $this->orientation, count($markers)])), 0, 8);
        $lit   = $this->points($this->litOutline());
        $maria = '';
        foreach (LunarTerminator::catalogue() as $feature) {
            if ($feature['type'] === 'mare' && $feature['diameter'] >= self::MIN_MARE_DIAMETER) {
                $maria .= '<polygon points="'.$this->points($this->outline($feature)).'"/>';
            }
        }

        $svg = sprintf('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %1$d %1$d" class="h-auto w-full max-w-md" role="img">', $this->size);
        $svg .= sprintf('<defs><clipPath id="%s-disk"><circle cx="%.1f" cy="%.1f" r="%.1f"/></clipPath><clipPath id="%s-lit"><polygon points="%s"/></clipPath></defs>', $id, $c, $c, $r, $id, $lit);
        $svg .= sprintf('<circle cx="%.1f" cy="%.1f" r="%.1f" fill="#111827" stroke="#4b5563" stroke-width="1"/>', $c, $c, $r);
        // the night side in earthshine
        $svg .= sprintf('<g clip-path="url(#%s-disk)" fill="#1f2937">%s</g>', $id, $maria);
        $svg .= sprintf('<g clip-path="url(#%1$s-disk)"><g clip-path="url(#%1$s-lit)"><circle cx="%2$.1f" cy="%2$.1f" r="%3$.1f" fill="#d1d5db"/><g fill="#9ca3af">%4$s</g></g></g>', $id, $c, $r, $maria);
        $svg .= sprintf('<polyline points="%s" fill="none" stroke="#f59e0b" stroke-width="1.5" stroke-opacity="0.8"/>', $this->points($this->terminator()));

        foreach (array_values($markers) as $index => $feature) {
            $point = LunarEphemeris::project($this->ephemeris, $feature['lat'], $feature['lon']);
            if (! $point['visible']) {
                continue;
            }
            [$x, $y] = $this->screen($point['x'], $point['y']);
            $svg .= sprintf(
                '<g><title>%s</title><polygon points="%s" fill="none" stroke="#2563eb" stroke-width="1.2"/><circle cx="%.1f" cy="%.1f" r="7" fill="#1d4ed8" fill-opacity="0.85"/><text x="%.1f" y="%.1f" font-size="9" font-family="sans-serif" fill="#fff" text-anchor="middle" dominant-baseline="central">%d</text></g>',
                e($feature['name']),
                $this->points($this->outline($feature)),
                $x,
                $y,
                $x,
                $y,
                $index + 1
            );
        }

        // celestial north
        [$nx, $ny] = $this->screen(0.0, 1.0 + 12 / $r);
        $svg .= sprintf('<text x="%.1f" y="%.1f" font-size="11" font-family="sans-serif" fill="#9ca3af" text-anchor="middle" dominant-baseline="central">N</text>', $nx, $ny);

        return $svg.'</svg>';
    }

    /**
     * The lit part of the disk: the limb on the side of the Sun and the
     * terminator back, in disk radii.
     *
     * @return list<array{0: float, 1: float}>
     */
    private function litOutline(): array
    {
        [$eu, $ev, $sz] = $this->sunFrame();
        $points         = [];
        for ($a = -90; $a <= 90; $a += 3) {
            $points[] = $this->along($eu, $ev, cos(deg2rad($a)), sin(deg2rad($a)));
        }
        for ($a = 90; $a >= -90; $a -= 3) {
            $points[] = $this->along($eu, $ev, -$sz * cos(deg2rad($a)), sin(deg2rad($a)));
        }

        return $points;
    }

    /**
     * The terminator: the half of the circle 90° from the subsolar point that
     * faces the Earth, which projects as half an ellipse.
     *
     * @return list<array{0: float, 1: float}>
     */
    private function terminator(): array
    {
        [$eu, $ev, $sz] = $this->sunFrame();
        $points         = [];
        for ($a = 90; $a >= -90; $a -= 3) {
            $points[] = $this->along($eu, $ev, -$sz * cos(deg2rad($a)), sin(deg2rad($a)));
        }

        return $points;
    }

    /**
     * The direction of the Sun on the disk (unit vector), the perpendicular
     * one and the component of the Sun towards the Earth.
     *
     * @return array{0: array{0: float, 1: float}, 1: array{0: float, 1: float}, 2: float}
     */
    private function sunFrame(): array
    {
        $sun    = LunarEphemeris::sunDirection($this->ephemeris);
        $length = hypot($sun['x'], $sun['y']);
        $eu     = $length < 1e-9 ? [1.0, 0.0] : [$sun['x'] / $length, $sun['y'] / $length];

        return [$eu, [-$eu[1], $eu[0]], $sun['z']];
    }

    private function along(array $eu, array $ev, float $u, float $v): array
    {
        return [$u * $eu[0] + $v * $ev[0], $u * $eu[1] + $v * $ev[1]];
    }

    /**
     * The outline of a feature as the circle of its diameter on the sphere,
     * points behind the limb put on it.
     *
     * @return list<array{0: float, 1: float}>
     */
    private function outline(array $feature): array
    {
        $angular = $feature['diameter'] / 2 / self::MOON_RADIUS_KM;
        $lat     = deg2rad($feature['lat']);
        $lon     = deg2rad($feature['lon']);
        $points  = [];
        for ($i = 0; $i < self::OUTLINE_POINTS; $i++) {
            $bearing = 2 * M_PI * $i / self::OUTLINE_POINTS;
            $pLat    = asin(sin($lat) * cos($angular) + cos($lat) * sin($angular) * cos($bearing));
            $pLon    = $lon + atan2(sin($bearing) * sin($angular) * cos($lat), cos($angular) - sin($lat) * sin($pLat));
            $point   = LunarEphemeris::project($this->ephemeris, rad2deg($pLat), rad2deg($pLon));
            if (! $point['visible']) {
                $length = max(1e-9, hypot($point['x'], $point['y']));
                $point  = ['x' => $point['x'] / $length, 'y' => $point['y'] / $length];
            }
            $points[] = [$point['x'], $point['y']];
        }

        return $points;
    }

    /**
     * SVG coordinates of a point on the disk (radii, x right, y up, north up
     * and east left).
     *
     * @return array{0: float, 1: float}
     */
    private function screen(float $x, float $y): array
    {
        [$x, $y] = match ($this->orientation) {
            'south'  => [-$x, -$y],
            'mirror' => [-$x, $y],
            default  => [$x, $y],
        };

        return [$this->centre + $x * $this->radius, $this->centre - $y * $this->radius];
    }

    /**
     * @param  list<array{0: float, 1: float}>  $points
     */
    private function points(array $points): string
    {
        return implode(' ', array_map(function (array $point) {
            [$x, $y] = $this->screen($point[0], $point[1]);

            return sprintf('%.1f,%.1f', $x, $y);
        }, $points));
    }
}
