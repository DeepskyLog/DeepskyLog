<?php

namespace App\Services\Planets;

/**
 * The disk of Mars or Venus of PlanetDisk::at() as an SVG, as the sky shows
 * it: the planet's north pole at its position angle P, the unlit part at the
 * bright limb's position angle; Mars with its polar caps (MarsFeatures::capEdge(),
 * schematic), its albedo features (MarsFeatures::FEATURES, named when large
 * and well inside the disk) and its central meridian dashed.
 *
 * The orientations are those of PlanetMoonsRenderer: 'north' (north up,
 * west right), 'south' (turned by 180°), 'mirror' (north up, west left).
 */
class PlanetDiskRenderer
{
    /** The points of the outline of a feature, a cap or the limb. */
    private const POINTS = 48;

    /** Features are named when at least this large (degrees) and their centre this far in front (Z, in radii). */
    private const LABEL_RADIUS = 7.0;

    private const LABEL_DEPTH = 0.45;

    private float $radius;

    private float $centre;

    /**
     * @param  array<string, mixed>  $disk  PlanetDisk::at()
     */
    public function __construct(
        private array $disk,
        private string $orientation = 'north',
        private int $size = 320,
        private bool $labels = true,
    ) {
        $this->orientation = in_array($orientation, PlanetMoonsRenderer::ORIENTATIONS, true) ? $orientation : 'north';
        $this->centre      = $size / 2;
        $this->radius      = $size / 2 - 22;
    }

    public function svg(): string
    {
        $mars      = $this->disk['planet'] === 'mars';
        $id        = $this->disk['planet'] . '-' . substr(md5(json_encode([$this->disk['at'], $this->orientation, $this->size])), 0, 8);
        [$cx, $cy] = [$this->centre, $this->centre];

        $svg = sprintf('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %1$d %1$d" class="h-auto w-full" role="img">', $this->size);
        $svg .= sprintf('<defs><clipPath id="%s-disk"><circle cx="%.1f" cy="%.1f" r="%.1f"/></clipPath></defs>', $id, $cx, $cy, $this->radius);
        $svg .= sprintf('<circle cx="%.1f" cy="%.1f" r="%.1f" fill="%s" stroke="#374151" stroke-width="1"/>', $cx, $cy, $this->radius, $mars ? '#d9825b' : '#f3eedb');

        $labels = '';
        if ($mars) {
            $svg .= sprintf('<g clip-path="url(#%s-disk)">', $id);
            foreach (MarsFeatures::FEATURES as $feature) {
                $outline = $this->outline($feature['lat'], $feature['lon'], $feature['radius']);
                if ($outline === null) {
                    continue;
                }
                $svg .= sprintf('<polygon points="%s" fill="%s" fill-opacity="%s"><title>%s</title></polygon>', $outline, $feature['bright'] ? '#f2c29b' : '#6b3f2a', $feature['bright'] ? '0.55' : '0.8', e($feature['name']));
                [$x, $y, $z] = $this->view($feature['lat'], $feature['lon']);
                if ($this->labels && $feature['radius'] >= self::LABEL_RADIUS && $z >= self::LABEL_DEPTH) {
                    [$sx, $sy] = $this->screen(...$this->rotate($x, $y));
                    // Names near the limb run towards the centre, so they stay on the disk.
                    $anchor = $sx > $cx + $this->radius * 0.35 ? 'end' : ($sx < $cx - $this->radius * 0.35 ? 'start' : 'middle');
                    $labels .= sprintf('<text x="%.1f" y="%.1f" font-size="10" font-family="sans-serif" fill="#f9fafb" fill-opacity="0.85" text-anchor="%s">%s</text>', $sx, $sy + 3, $anchor, e($feature['name']));
                }
            }
            foreach (['north' => 90.0, 'south' => -90.0] as $pole => $latitude) {
                $outline = $this->outline($latitude, 0.0, abs($latitude - MarsFeatures::capEdge($pole, $this->disk['ls'])));
                if ($outline !== null) {
                    $svg .= sprintf('<polygon points="%s" fill="#f8fafc"><title>%s</title></polygon>', $outline, e($pole === 'north' ? __('North polar cap') : __('South polar cap')));
                }
            }
            // The central meridian
            $points = [];
            for ($latitude = -90; $latitude <= 90; $latitude += 5) {
                [$x, $y, $z] = $this->view((float) $latitude, $this->disk['cm']);
                if ($z >= 0) {
                    $points[] = vsprintf('%.1f,%.1f', $this->screen(...$this->rotate($x, $y)));
                }
            }
            if (count($points) > 1) {
                $svg .= sprintf('<polyline points="%s" fill="none" stroke="#fde68a" stroke-opacity="0.6" stroke-width="1" stroke-dasharray="4 3"><title>%s</title></polyline>', implode(' ', $points), e(__('Central meridian')));
            }
            $svg .= '</g>';
        }

        $svg .= sprintf('<polygon points="%s" fill="#030712" fill-opacity="%s"/>', $this->nightSide(), $mars ? '0.85' : '1');
        $svg .= sprintf('<circle cx="%.1f" cy="%.1f" r="%.1f" fill="none" stroke="#374151" stroke-width="1"/>', $cx, $cy, $this->radius);
        $svg .= $labels;

        // The directions in the sky
        $west = $this->orientation === 'north' ? 'right' : 'left';
        $svg .= sprintf('<text x="%d" y="%d" font-size="11" font-family="sans-serif" fill="#9ca3af" text-anchor="%s">%s</text>', $west === 'right' ? $this->size - 4 : 4, $this->size / 2 + 4, $west === 'right' ? 'end' : 'start', e(__('W')));
        $svg .= sprintf('<text x="%d" y="%d" font-size="11" font-family="sans-serif" fill="#9ca3af" text-anchor="%s">%s</text>', $west === 'right' ? 4 : $this->size - 4, $this->size / 2 + 4, $west === 'right' ? 'start' : 'end', e(__('E')));
        $svg .= sprintf('<text x="%d" y="14" font-size="11" font-family="sans-serif" fill="#9ca3af" text-anchor="middle">%s</text>', $this->size / 2, $this->orientation === 'south' ? 'S' : 'N');

        return $svg . '</svg>';
    }

    /**
     * A point of Mars (areographic latitude, west longitude) in the frame of
     * the disk: X towards the planet's west, Y towards its north pole, Z
     * towards the Earth, in radii.
     *
     * @return array{0: float, 1: float, 2: float}
     */
    private function view(float $latitude, float $longitude): array
    {
        $phi = deg2rad($latitude);
        $d   = deg2rad($longitude - $this->disk['cm']);
        $de  = deg2rad($this->disk['de']);

        return [
            -cos($phi) * sin($d),
            sin($phi)  * cos($de) - cos($phi) * sin($de) * cos($d),
            sin($phi)  * sin($de) + cos($phi) * cos($de) * cos($d),
        ];
    }

    /**
     * The outline of a circle of $radius degrees on Mars around a point, as
     * SVG polygon points; the part behind the limb is laid on the limb.
     * Null when it is all on the far side.
     */
    private function outline(float $latitude, float $longitude, float $radius): ?string
    {
        $c = $this->view($latitude, $longitude);
        if ($c[2] < -sin(deg2rad($radius))) {
            return null;
        }
        // Two directions along the surface at the centre
        $pole = [0.0, cos(deg2rad($this->disk['de'])), sin(deg2rad($this->disk['de']))];
        $e1   = self::cross($c, $pole);
        if (sqrt(self::dot($e1, $e1)) < 1e-6) {
            $e1 = self::cross($c, [1.0, 0.0, 0.0]);
        }
        $length = sqrt(self::dot($e1, $e1));
        $e1     = [$e1[0] / $length, $e1[1] / $length, $e1[2] / $length];
        $e2     = self::cross($c, $e1);

        $r      = deg2rad($radius);
        $points = [];
        for ($i = 0; $i < self::POINTS; $i++) {
            $t = 2 * M_PI * $i / self::POINTS;
            $p = [];
            for ($k = 0; $k < 3; $k++) {
                $p[$k] = cos($r) * $c[$k] + sin($r) * (cos($t) * $e1[$k] + sin($t) * $e2[$k]);
            }
            [$x, $y] = [$p[0], $p[1]];
            if ($p[2] < 0) {
                $length  = sqrt($x * $x + $y * $y) ?: 1.0;
                [$x, $y] = [$x / $length, $y / $length];
            }
            $points[] = vsprintf('%.1f,%.1f', $this->screen(...$this->rotate($x, $y)));
        }

        return implode(' ', $points);
    }

    /**
     * The unlit part of the disk: the limb on the side away from the Sun and
     * the terminator, an ellipse of semi-minor axis cos(phase angle).
     */
    private function nightSide(): string
    {
        $chi  = deg2rad($this->disk['bright_limb']);
        $cosI = cos(deg2rad($this->disk['phase_angle']));
        // Towards the bright limb and across it, in the sky (west, north)
        $toSun  = [-sin($chi), cos($chi)];
        $across = [cos($chi), sin($chi)];
        $points = [];
        $add    = function (float $u, float $v) use (&$points, $toSun, $across) {
            $points[] = vsprintf('%.1f,%.1f', $this->screen($u * $toSun[0] + $v * $across[0], $u * $toSun[1] + $v * $across[1]));
        };
        for ($i = 0; $i <= self::POINTS / 2; $i++) {
            $a = M_PI / 2 + M_PI * $i / (self::POINTS / 2);
            $add(cos($a), sin($a));
        }
        for ($i = 0; $i <= self::POINTS / 2; $i++) {
            $a = -M_PI / 2 + M_PI * $i / (self::POINTS / 2);
            $add(-$cosI * cos($a), sin($a));
        }

        return implode(' ', $points);
    }

    /**
     * The frame of the disk (X west, Y north of the planet) to the sky (west,
     * north): turned by the position angle P of the planet's north pole.
     *
     * @return array{0: float, 1: float}
     */
    private function rotate(float $x, float $y): array
    {
        $p = deg2rad($this->disk['p'] ?? 0.0);

        return [$x * cos($p) - $y * sin($p), $x * sin($p) + $y * cos($p)];
    }

    /**
     * The sky (west, north, in radii of the disk) to the drawing.
     *
     * @return array{0: float, 1: float}
     */
    private function screen(float $west, float $north): array
    {
        $sx = $this->orientation === 'north' ? $west : -$west;
        $sy = $this->orientation === 'south' ? $north : -$north;

        return [$this->centre + $sx * $this->radius, $this->centre + $sy * $this->radius];
    }

    /**
     * @param  array{0: float, 1: float, 2: float}  $a
     * @param  array{0: float, 1: float, 2: float}  $b
     * @return array{0: float, 1: float, 2: float}
     */
    private static function cross(array $a, array $b): array
    {
        return [$a[1] * $b[2] - $a[2] * $b[1], $a[2] * $b[0] - $a[0] * $b[2], $a[0] * $b[1] - $a[1] * $b[0]];
    }

    /**
     * @param  array{0: float, 1: float, 2: float}  $a
     * @param  array{0: float, 1: float, 2: float}  $b
     */
    private static function dot(array $a, array $b): float
    {
        return $a[0] * $b[0] + $a[1] * $b[1] + $a[2] * $b[2];
    }
}
