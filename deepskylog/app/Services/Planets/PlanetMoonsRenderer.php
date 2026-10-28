<?php

namespace App\Services\Planets;

/**
 * Jupiter or Saturn with its moons as an SVG at a moment of PlanetMoons:
 * the flattened disk (Saturn with its rings at their tilt, Jupiter with its
 * main belts and the Great Red Spot when it is on the side facing us), the
 * moons with their names, the shadows on the disk, and a moon in the
 * planet's shadow greyed out. The planet's equator is horizontal.
 *
 * The orientations are those of MoonDiskRenderer: 'north' (north up, west
 * right: naked eye, binoculars), 'south' (turned by 180°: a telescope
 * without diagonal), 'mirror' (north up, west left: with a star diagonal).
 */
class PlanetMoonsRenderer
{
    public const ORIENTATIONS = ['north', 'south', 'mirror'];

    /** The planetographic latitude of the Great Red Spot (degrees). */
    private const GRS_LATITUDE = -22.0;

    /** The inner edge of the B ring in Saturn radii. */
    private const RING_INNER_RADIUS = 1.53;

    private float $scale;

    /** The names next to the moons; the close-up has them only as a tooltip. */
    private bool $labels = true;

    /**
     * @param  array<string, mixed>  $positions  PlanetMoons::jupiter() or saturn()
     * @param  ?float  $extent  the planet radii from the centre to the edge: null for all moons, small for a close-up of the disk
     */
    public function __construct(
        private string $planet,
        private array $positions,
        private string $orientation = 'north',
        private ?float $grsLongitude = null,
        ?float $extent = null,
        private int $width = 640,
        private int $height = 200,
    ) {
        $this->orientation = in_array($orientation, self::ORIENTATIONS, true) ? $orientation : 'north';
        if ($extent === null) {
            $extent = $planet === 'jupiter' ? 28.0 : 22.0;
            foreach ($positions['earth'] as $moon) {
                $extent = max($extent, abs($moon['x']) + 3.0);
            }
        }
        $this->scale = ($width / 2 - 10) / $extent;
    }

    /**
     * A close-up of the disk (and Saturn's rings) with the shadows, the moons
     * in front of it and the Great Red Spot.
     *
     * @param  array<string, mixed>  $positions
     */
    public static function closeUp(string $planet, array $positions, string $orientation = 'north', ?float $grsLongitude = null): self
    {
        $closeUp         = new self($planet, $positions, $orientation, $grsLongitude, $planet === 'jupiter' ? 1.5 : PlanetMoons::RING_RADIUS + 0.3, 320, 200);
        $closeUp->labels = false;

        return $closeUp;
    }

    public function svg(): string
    {
        $flattening = PlanetMoons::FLATTENING[$this->planet];
        $s          = $this->scale;
        [$cx, $cy]  = $this->screen(0.0, 0.0);
        $id         = $this->planet . '-' . substr(md5(json_encode([$this->positions['earth'], $this->orientation])), 0, 8);

        $svg = sprintf('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %d %d" class="h-auto w-full" role="img">', $this->width, $this->height);
        $svg .= sprintf('<defs><clipPath id="%s-disk"><ellipse cx="%.1f" cy="%.1f" rx="%.1f" ry="%.1f"/></clipPath></defs>', $id, $cx, $cy, $s, $s * $flattening);

        $ring = '';
        if ($this->planet === 'saturn') {
            $tilt = abs(sin(deg2rad($this->positions['ring_tilt'])));
            // The half of the rings nearer to us: south of the centre when their northern face is turned to us
            $nearSouth = $this->positions['ring_tilt'] >= 0;
            $nearDown  = $nearSouth === ($this->orientation !== 'south');
            // Half of the ring between the inner edge of the B ring and the outer edge of the A ring
            $half = function (bool $down) use ($cx, $cy, $s, $tilt): string {
                [$outer, $inner] = [PlanetMoons::RING_RADIUS * $s, self::RING_INNER_RADIUS * $s];
                $sweep           = $down ? 0 : 1;

                return sprintf(
                    'M %.1f %.1f A %.1f %.1f 0 0 %d %.1f %.1f L %.1f %.1f A %.1f %.1f 0 0 %d %.1f %.1f Z',
                    $cx - $outer,
                    $cy,
                    $outer,
                    max(0.3, $outer * $tilt),
                    $sweep,
                    $cx + $outer,
                    $cy,
                    $cx + $inner,
                    $cy,
                    $inner,
                    max(0.2, $inner * $tilt),
                    1   - $sweep,
                    $cx - $inner,
                    $cy
                );
            };
            $svg .= sprintf('<path d="%s" fill="#a8a29e" stroke="#a8a29e" stroke-width="0.6"/>', $half(! $nearDown));
            $ring = sprintf('<path d="%s" fill="#d6d3d1" stroke="#d6d3d1" stroke-width="0.6"/>', $half($nearDown));
        }

        $svg .= sprintf('<ellipse cx="%.1f" cy="%.1f" rx="%.1f" ry="%.1f" fill="%s"/>', $cx, $cy, $s, $s * $flattening, $this->planet === 'jupiter' ? '#e7d8bf' : '#e8d9a8');
        $svg .= sprintf('<g clip-path="url(#%s-disk)">', $id);
        if ($this->planet === 'jupiter') {
            // The north and south equatorial belts
            foreach ([0.27, -0.2] as $belt) {
                [, $y] = $this->screen(0.0, $belt * $flattening);
                $svg .= sprintf('<rect x="%.1f" y="%.1f" width="%.1f" height="%.1f" fill="#b08d6a" fill-opacity="0.7"/>', $cx - $s, $y - $s * 0.07, 2 * $s, $s * 0.14);
            }
            if ($this->grsLongitude !== null) {
                $angle = deg2rad($this->grsLongitude - $this->positions['cm2']);
                if (cos($angle) > 0) {
                    // Spots that have not reached the central meridian yet are on the east side.
                    $latitude = deg2rad(self::GRS_LATITUDE);
                    [$x, $y]  = $this->screen(-sin($angle) * cos($latitude), sin($latitude) * $flattening);
                    $svg .= sprintf('<ellipse cx="%.1f" cy="%.1f" rx="%.1f" ry="%.1f" fill="#b4533a"><title>%s</title></ellipse>', $x, $y, $s * 0.17 * cos($angle), $s * 0.09, e(__('Great Red Spot')));
                }
            }
        }
        foreach ($this->positions['sun'] as $moon => $p) {
            if ($p['z'] < 0 && PlanetMoons::disk($this->planet, $p['x'], $p['y']) < 0) {
                [$x, $y] = $this->screen($p['x'], $p['y']);
                $svg .= sprintf('<circle cx="%.1f" cy="%.1f" r="%.1f" fill="#000"><title>%s</title></circle>', $x, $y, max(2.0, $s * 0.07), e(__('The shadow of :moon', ['moon' => PlanetMoonEvents::moonName($moon)])));
            }
        }
        $svg .= '</g>' . $ring;

        $labels = [];
        foreach ($this->positions['earth'] as $moon => $p) {
            $behind = $p['z'] > 0 && PlanetMoons::disk($this->planet, $p['x'], $p['y']) < 0;
            if ($behind) {
                continue;
            }
            $sun      = $this->positions['sun'][$moon];
            $eclipsed = $sun['z'] > 0 && PlanetMoons::disk($this->planet, $sun['x'], $sun['y']) < 0;
            [$x, $y]  = $this->screen($p['x'], $p['y']);
            $svg .= sprintf('<circle cx="%.1f" cy="%.1f" r="%.1f" fill="%s" stroke="#111827" stroke-width="0.5"><title>%s</title></circle>', $x, $y, max(3.0, $s * 0.06), $eclipsed ? '#4b5563' : '#f9fafb', e(PlanetMoonEvents::moonName($moon)));
            if (! $this->labels) {
                continue;
            }
            // Labels alternate above and below, so close moons stay readable.
            $above    = count($labels) % 2 === 0;
            $labels[] = sprintf(
                '<text x="%.1f" y="%.1f" font-size="11" font-family="sans-serif" fill="%s" text-anchor="middle">%s</text>',
                $x,
                $above ? $y - 9 : $y + 17,
                $eclipsed ? '#6b7280' : '#d1d5db',
                e(PlanetMoonEvents::moonName($moon))
            );
        }
        $svg .= implode('', $labels);

        // The directions
        $west = $this->orientation === 'north' ? 'right' : 'left';
        $svg .= sprintf('<text x="%d" y="%d" font-size="11" font-family="sans-serif" fill="#9ca3af" text-anchor="%s">%s</text>', $west === 'right' ? $this->width - 4 : 4, $this->height - 6, $west === 'right' ? 'end' : 'start', e(__('W')));
        $svg .= sprintf('<text x="%d" y="%d" font-size="11" font-family="sans-serif" fill="#9ca3af" text-anchor="%s">%s</text>', $west === 'right' ? 4 : $this->width - 4, $this->height - 6, $west === 'right' ? 'start' : 'end', e(__('E')));
        $svg .= sprintf('<text x="%d" y="14" font-size="11" font-family="sans-serif" fill="#9ca3af" text-anchor="middle">%s</text>', $this->width / 2, $this->orientation === 'south' ? 'S' : 'N');

        return $svg . '</svg>';
    }

    /**
     * @return array{0: float, 1: float}
     */
    private function screen(float $x, float $y): array
    {
        $sx = $this->orientation === 'north' ? $x : -$x;
        $sy = $this->orientation === 'south' ? $y : -$y;

        return [$this->width / 2 + $sx * $this->scale, $this->height / 2 + $sy * $this->scale];
    }
}
