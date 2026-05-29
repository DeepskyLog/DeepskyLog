<?php

namespace App\Services;

use App\Models\DeepskyObject;
use App\Models\Star;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StarChartService
{
    private const CHART_WIDTH = 1120;
    private const CHART_HEIGHT = 780;
    private const CHART_LEFT = 56;
    private const CHART_RIGHT = 56;
    private const CHART_TOP = 70;
    private const CHART_BOTTOM = 84;

    public function build(DeepskyObject $target, float $centerRaDeg, float $centerDecDeg, int $radiusArcMin, ?float $magnitudeLimit = null): array
    {
        $radiusDeg = max(0.1, $radiusArcMin / 60);
        $magnitudeLimit = $this->resolveMagnitudeLimit($radiusArcMin, $magnitudeLimit);

        $stars = $this->loadStars($centerRaDeg, $centerDecDeg, $radiusDeg, $magnitudeLimit);
        $objects = $this->loadObjects($centerRaDeg, $centerDecDeg, $radiusDeg, $target);
        $boundaries = $this->loadConstellationBoundaries();

        return [
            'target' => $target,
            'radius_arc_min' => $radiusArcMin,
            'radius_deg' => $radiusDeg,
            'magnitude_limit' => $magnitudeLimit,
            'stars' => $stars,
            'objects' => $objects,
            'svg' => $this->renderSvg($centerRaDeg, $centerDecDeg, $radiusDeg, $magnitudeLimit, $target, $stars, $objects, $boundaries),
        ];
    }

    public function angularDistanceDeg(float $ra0Deg, float $dec0Deg, float $ra1Deg, float $dec1Deg): float
    {
        $ra0 = deg2rad($ra0Deg);
        $dec0 = deg2rad($dec0Deg);
        $ra1 = deg2rad($ra1Deg);
        $dec1 = deg2rad($dec1Deg);

        $cosDistance = sin($dec0) * sin($dec1)
            + cos($dec0) * cos($dec1) * cos($ra0 - $ra1);

        $cosDistance = max(-1.0, min(1.0, $cosDistance));

        return rad2deg(acos($cosDistance));
    }

    /**
     * @return array{x: float, y: float}|null
     */
    public function projectToChart(
        float $raDeg,
        float $decDeg,
        float $centerRaDeg,
        float $centerDecDeg,
        float $radiusDeg,
        float $plotRadius,
        float $centerX,
        float $centerY
    ): ?array {
        $deltaRa = $this->normalizeRaDelta($raDeg - $centerRaDeg);
        $xDegrees = $deltaRa * cos(deg2rad($centerDecDeg));
        $yDegrees = $decDeg - $centerDecDeg;

        $distance = sqrt(($xDegrees * $xDegrees) + ($yDegrees * $yDegrees));
        if ($distance > $radiusDeg) {
            return null;
        }

        return [
            'x' => $centerX - (($xDegrees / $radiusDeg) * $plotRadius),
            'y' => $centerY - (($yDegrees / $radiusDeg) * $plotRadius),
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function loadStars(float $centerRaDeg, float $centerDecDeg, float $radiusDeg, float $magnitudeLimit): Collection
    {
        $xHalfDeg = $this->xHalfDegrees($radiusDeg);
        $declMin = max(-90.0, $centerDecDeg - $radiusDeg);
        $declMax = min(90.0, $centerDecDeg + $radiusDeg);
        $raWindow = $this->raWindowDegreesFromXHalf($centerDecDeg, $xHalfDeg);

        $query = Star::query()
            ->select(['name', 'constellation_code', 'bayer', 'bayer_suffix', 'flamsteed', 'ra_degrees', 'decl_degrees', 'vmag'])
            ->whereNotNull('ra_degrees')
            ->whereNotNull('decl_degrees')
            ->whereNotNull('vmag')
            ->where('vmag', '<=', $magnitudeLimit)
            ->whereBetween('decl_degrees', [$declMin, $declMax]);

        $this->applyRaWindow($query, $centerRaDeg, $raWindow);

        $stars = $query->get()->map(function (Star $star) use ($centerRaDeg, $centerDecDeg) {
            $xDegrees = $this->normalizeRaDelta((float) $star->ra_degrees - $centerRaDeg) * cos(deg2rad($centerDecDeg));
            $yDegrees = (float) $star->decl_degrees - $centerDecDeg;

            return [
                'name' => $star->displayName(),
                'ra_degrees' => (float) $star->ra_degrees,
                'decl_degrees' => (float) $star->decl_degrees,
                'vmag' => $star->vmag === null ? null : (float) $star->vmag,
                'x_degrees' => $xDegrees,
                'y_degrees' => $yDegrees,
            ];
        })->filter(function (array $star) use ($xHalfDeg, $radiusDeg) {
            return abs($star['x_degrees']) <= $xHalfDeg && abs($star['y_degrees']) <= $radiusDeg;
        })->sortBy(fn(array $star) => $star['vmag'] ?? 99)->values();

        $maxStars = $this->maxStarCount($magnitudeLimit, $radiusDeg);
        if ($stars->count() > $maxStars) {
            $stars = $stars->take($maxStars)->values();
        }

        return $stars;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function loadObjects(float $centerRaDeg, float $centerDecDeg, float $radiusDeg, DeepskyObject $target): Collection
    {
        $xHalfDeg = $this->xHalfDegrees($radiusDeg);
        $raWindow = $this->raWindowDegreesFromXHalf($centerDecDeg, $xHalfDeg);
        $centerRaHours = $centerRaDeg / 15.0;
        $raWindowHours = $raWindow / 15.0;

        $raMinHours = $centerRaHours - $raWindowHours;
        $raMaxHours = $centerRaHours + $raWindowHours;
        $raMinDeg = $centerRaDeg - $raWindow;
        $raMaxDeg = $centerRaDeg + $raWindow;

        return DeepskyObject::query()
            ->select(['name', 'slug', 'type', 'ra', 'decl', 'mag', 'diam1', 'diam2', 'pa'])
            ->whereBetween('decl', [max(-90.0, $centerDecDeg - $radiusDeg), min(90.0, $centerDecDeg + $radiusDeg)])
            ->where(function (Builder $builder) use ($raMinHours, $raMaxHours, $raMinDeg, $raMaxDeg) {
                $builder->where(function (Builder $hoursQuery) use ($raMinHours, $raMaxHours) {
                    $hoursQuery->where('ra', '<=', 24.0);
                    $this->applyLegacyRaRange($hoursQuery, $raMinHours, $raMaxHours, 24.0);
                })->orWhere(function (Builder $degQuery) use ($raMinDeg, $raMaxDeg) {
                    $degQuery->where('ra', '>', 24.0);
                    $this->applyLegacyRaRange($degQuery, $raMinDeg, $raMaxDeg, 360.0, 24.000001);
                });
            })
            ->limit(200)
            ->get()
            ->map(function (DeepskyObject $object) use ($target) {
                $raDegrees = DeepskyObject::raToDecimal($object->ra);

                return [
                    'name' => $object->name,
                    'slug' => $object->slug,
                    'type' => $object->type,
                    'mag' => $object->mag === null ? null : (float) $object->mag,
                    'diam1' => $object->diam1 === null ? null : (float) $object->diam1,
                    'diam2' => $object->diam2 === null ? null : (float) $object->diam2,
                    'pa' => $object->pa === null || (float) $object->pa > 360.0 ? null : (float) $object->pa,
                    'ra_degrees' => (float) ($raDegrees ?? 0.0),
                    'decl_degrees' => (float) $object->decl,
                    'is_target' => $object->name === $target->name,
                ];
            })->filter(function (array $object) use ($centerRaDeg, $centerDecDeg, $radiusDeg, $xHalfDeg) {
                $xDegrees = $this->normalizeRaDelta($object['ra_degrees'] - $centerRaDeg) * cos(deg2rad($centerDecDeg));
                $yDegrees = $object['decl_degrees'] - $centerDecDeg;

                return abs($xDegrees) <= $xHalfDeg && abs($yDegrees) <= $radiusDeg;
            })->sortBy(fn(array $object) => $object['is_target'] ? -999 : ($object['mag'] ?? 999))->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function loadConstellationBoundaries(): Collection
    {
        return DB::table('constellation_boundaries')
            ->select(['con0', 'con0pos', 'con1', 'con1pos', 'ra0', 'decl0', 'ra1', 'decl1'])
            ->get()
            ->map(function ($row) {
                $label0 = strtoupper(trim((string) $row->con0));
                $label1 = strtoupper(trim((string) $row->con1));

                return [
                    'label0' => $label0 !== '' ? $label0 : null,
                    'label0_pos' => strtoupper(trim((string) $row->con0pos)),
                    'label1' => $label1 !== '' ? $label1 : null,
                    'label1_pos' => strtoupper(trim((string) $row->con1pos)),
                    'ra0_degrees' => (float) $row->ra0 * 15.0,
                    'decl0_degrees' => (float) $row->decl0,
                    'ra1_degrees' => (float) $row->ra1 * 15.0,
                    'decl1_degrees' => (float) $row->decl1,
                ];
            })->values();
    }

    private function renderSvg(
        float $centerRaDeg,
        float $centerDecDeg,
        float $radiusDeg,
        float $magnitudeLimit,
        DeepskyObject $target,
        Collection $stars,
        Collection $objects,
        Collection $boundaries
    ): string {
        $width = self::CHART_WIDTH;
        $height = self::CHART_HEIGHT;
        $left = self::CHART_LEFT;
        $right = self::CHART_RIGHT;
        $top = self::CHART_TOP;
        $bottom = self::CHART_BOTTOM;
        $plotWidth = $width - $left - $right;
        $plotHeight = $height - $top - $bottom;
        $xHalfDeg = $this->xHalfDegrees($radiusDeg);
        $yHalfDeg = $radiusDeg;
        $cosDec = max(0.01, abs(cos(deg2rad($centerDecDeg))));
        $degPerPx = (2 * $yHalfDeg) / max(1.0, $plotHeight);
        $arcMinToPx = $degPerPx > 0.0 ? (1.0 / (60.0 * $degPerPx)) : 1.0;

        $svg = [];
        $svg[] = sprintf('<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d" viewBox="0 0 %d %d">', $width, $height, $width, $height);
        $svg[] = '<rect width="100%" height="100%" fill="#f5f5f5" />';
        $svg[] = sprintf('<rect x="%0.2f" y="14" width="%0.2f" height="34" fill="#f2f2f2" stroke="#888" stroke-width="0.8" />', $left, $plotWidth);
        $svg[] = $this->renderTopLegend($left + 8, 33);
        $svg[] = sprintf(
            '<text x="%0.2f" y="27" font-size="10" text-anchor="end" fill="#4b5563">%s</text>',
            $left + $plotWidth - 8,
            htmlspecialchars($target->name . '  -  ' . now()->format('d M Y'), ENT_QUOTES, 'UTF-8')
        );
        $svg[] = sprintf('<rect x="%0.2f" y="%0.2f" width="%0.2f" height="%0.2f" fill="#f8f8f8" stroke="#111827" stroke-width="2" />', $left, $top, $plotWidth, $plotHeight);

        $polarMode = abs($centerDecDeg) >= 85.0;
        if ($polarMode) {
            $svg[] = $this->renderPolarGrid($centerRaDeg, $centerDecDeg, $xHalfDeg, $yHalfDeg, $left, $top, $plotWidth, $plotHeight);
        } else {
            $gridColumns = 10;
            for ($i = 0; $i <= $gridColumns; $i++) {
                $x = $left + ($plotWidth * $i / $gridColumns);
                $svg[] = sprintf('<line x1="%0.2f" y1="%0.2f" x2="%0.2f" y2="%0.2f" stroke="#d9d9d9" stroke-width="0.6" />', $x, $top, $x, $top + $plotHeight);

                $xDeg = $xHalfDeg - (($i / $gridColumns) * (2 * $xHalfDeg));
                $raDeg = $centerRaDeg + ($xDeg / $cosDec);
                $svg[] = sprintf('<text x="%0.2f" y="%0.2f" font-size="9" text-anchor="middle" fill="#6b7280">%s</text>', $x, $top + 12, htmlspecialchars($this->formatRaHours($raDeg), ENT_QUOTES, 'UTF-8'));
            }

            $gridRows = 8;
            for ($j = 0; $j <= $gridRows; $j++) {
                $y = $top + ($plotHeight * $j / $gridRows);
                $svg[] = sprintf('<line x1="%0.2f" y1="%0.2f" x2="%0.2f" y2="%0.2f" stroke="#d9d9d9" stroke-width="0.6" />', $left, $y, $left + $plotWidth, $y);

                $yDeg = $yHalfDeg - (($j / $gridRows) * (2 * $yHalfDeg));
                $decDeg = $centerDecDeg + $yDeg;
                $svg[] = sprintf('<text x="%0.2f" y="%0.2f" font-size="9" text-anchor="start" fill="#6b7280">%s</text>', $left + $plotWidth + 6, $y + 3, htmlspecialchars($this->formatDecDms($decDeg), ENT_QUOTES, 'UTF-8'));
            }
        }

        foreach ($boundaries as $segment) {
            $boundary = $this->renderBoundaryCurvePath(
                (float) $segment['ra0_degrees'],
                (float) $segment['decl0_degrees'],
                (float) $segment['ra1_degrees'],
                (float) $segment['decl1_degrees'],
                $centerRaDeg,
                $centerDecDeg,
                $xHalfDeg,
                $yHalfDeg,
                $left,
                $top,
                $plotWidth,
                $plotHeight
            );

            if ($boundary['path'] === '') {
                continue;
            }

            $svg[] = sprintf('<path d="%s" fill="none" stroke="#6b7280" stroke-width="1.1" stroke-dasharray="3 2" />', $boundary['path']);

            $svg[] = $this->renderBoundaryLabelOnSide(
                (string) ($segment['label0'] ?? ''),
                $boundary['label_x'],
                $boundary['label_y'],
                $boundary['normal_x'],
                $boundary['normal_y'],
                (string) ($segment['label0_pos'] ?? ''),
                1.0,
                $left,
                $top,
                $plotWidth,
                $plotHeight
            );
            $svg[] = $this->renderBoundaryLabelOnSide(
                (string) ($segment['label1'] ?? ''),
                $boundary['label_x'],
                $boundary['label_y'],
                $boundary['normal_x'],
                $boundary['normal_y'],
                (string) ($segment['label1_pos'] ?? ''),
                -1.0,
                $left,
                $top,
                $plotWidth,
                $plotHeight
            );
        }

        $labelBudget = 40;

        foreach ($stars as $star) {
            $point = $this->projectToAtlasChart($star['ra_degrees'], $star['decl_degrees'], $centerRaDeg, $centerDecDeg, $xHalfDeg, $yHalfDeg, $left, $top, $plotWidth, $plotHeight);
            if ($point === null) {
                continue;
            }

            $svg[] = sprintf('<circle cx="%0.2f" cy="%0.2f" r="%0.2f" fill="#111827" />', $point['x'], $point['y'], $this->starRadius($star['vmag']));
        }

        foreach ($objects as $object) {
            $point = $this->projectToAtlasChart($object['ra_degrees'], $object['decl_degrees'], $centerRaDeg, $centerDecDeg, $xHalfDeg, $yHalfDeg, $left, $top, $plotWidth, $plotHeight);
            if ($point === null) {
                continue;
            }

            $svg[] = $this->renderObjectSymbol(
                $object['type'] ?? '',
                $point['x'],
                $point['y'],
                $object['diam1'] ?? null,
                $object['diam2'] ?? null,
                $object['pa'] ?? null,
                $object['mag'] ?? null,
                $arcMinToPx
            );

            if ($object['is_target']) {
                $svg[] = sprintf('<text x="%0.2f" y="%0.2f" font-size="11" font-weight="bold" fill="#111">%s</text>', $point['x'] + 12, $point['y'] - 8, htmlspecialchars($target->name, ENT_QUOTES, 'UTF-8'));
            } elseif ($labelBudget > 0 && $this->shouldRenderObjectLabel($object)) {
                $svg[] = $this->renderObjectLabel((string) ($object['name'] ?? ''), $point['x'], $point['y'], $left, $top, $plotWidth, $plotHeight);
                $labelBudget--;
            }
        }

        $footerText = sprintf(
            'FoV: %s x %s  -  Objects to mag: %s  -  Stars to mag: %s  -  (Center %s, %s)',
            number_format($xHalfDeg * 2, 1),
            number_format($yHalfDeg * 2, 1),
            $magnitudeLimit,
            $magnitudeLimit,
            $this->formatRaHours($centerRaDeg),
            $this->formatDecDms($centerDecDeg)
        );

        $svg[] = sprintf('<text x="%0.2f" y="%0.2f" font-size="10" fill="#6b7280">%s</text>', $left, $height - 18, htmlspecialchars($footerText, ENT_QUOTES, 'UTF-8'));
        $svg[] = '</svg>';

        return implode('', $svg);
    }

    private function renderBoundaryCurvePath(
        float $ra0Deg,
        float $dec0Deg,
        float $ra1Deg,
        float $dec1Deg,
        float $centerRaDeg,
        float $centerDecDeg,
        float $xHalfDeg,
        float $yHalfDeg,
        float $left,
        float $top,
        float $plotWidth,
        float $plotHeight
    ): array {
        $a = $this->toUnitVector($ra0Deg, $dec0Deg);
        $b = $this->toUnitVector($ra1Deg, $dec1Deg);
        $dot = max(-1.0, min(1.0, ($a[0] * $b[0]) + ($a[1] * $b[1]) + ($a[2] * $b[2])));
        $omega = acos($dot);
        $sinOmega = sin($omega);

        $rect = [
            'min_x' => $left,
            'min_y' => $top,
            'max_x' => $left + $plotWidth,
            'max_y' => $top + $plotHeight,
        ];

        if ($sinOmega < 1.0e-6) {
            $p0 = $this->projectToAtlasChartUnclipped($ra0Deg, $dec0Deg, $centerRaDeg, $centerDecDeg, $xHalfDeg, $yHalfDeg, $left, $top, $plotWidth, $plotHeight);
            $p1 = $this->projectToAtlasChartUnclipped($ra1Deg, $dec1Deg, $centerRaDeg, $centerDecDeg, $xHalfDeg, $yHalfDeg, $left, $top, $plotWidth, $plotHeight);
            $clipped = $this->clipLineToRect((float) $p0['x'], (float) $p0['y'], (float) $p1['x'], (float) $p1['y'], $rect);

            if ($clipped === null) {
                return ['path' => '', 'label_x' => null, 'label_y' => null, 'normal_x' => 0.0, 'normal_y' => -1.0];
            }

            $dx = $clipped['x2'] - $clipped['x1'];
            $dy = $clipped['y2'] - $clipped['y1'];
            $len = max(1.0e-6, hypot($dx, $dy));
            $normalX = -$dy / $len;
            $normalY = $dx / $len;

            return [
                'path' => sprintf('M %0.2f %0.2f L %0.2f %0.2f', $clipped['x1'], $clipped['y1'], $clipped['x2'], $clipped['y2']),
                'label_x' => ($clipped['x1'] + $clipped['x2']) / 2.0,
                'label_y' => ($clipped['y1'] + $clipped['y2']) / 2.0,
                'normal_x' => $normalX,
                'normal_y' => $normalY,
            ];
        }

        $steps = 120;
        $pathParts = [];
        $bestLabelLength = -1.0;
        $labelX = null;
        $labelY = null;
        $normalX = 0.0;
        $normalY = -1.0;
        $previousPoint = null;

        for ($i = 0; $i <= $steps; $i++) {
            $t = $i / $steps;
            $w0 = sin((1.0 - $t) * $omega) / $sinOmega;
            $w1 = sin($t * $omega) / $sinOmega;
            $x = ($w0 * $a[0]) + ($w1 * $b[0]);
            $y = ($w0 * $a[1]) + ($w1 * $b[1]);
            $z = ($w0 * $a[2]) + ($w1 * $b[2]);
            $norm = sqrt(($x * $x) + ($y * $y) + ($z * $z));

            if ($norm <= 0.0) {
                continue;
            }

            $x /= $norm;
            $y /= $norm;
            $z /= $norm;

            $ra = rad2deg(atan2($y, $x));
            if ($ra < 0.0) {
                $ra += 360.0;
            }
            $dec = rad2deg(asin(max(-1.0, min(1.0, $z))));

            $pt = $this->projectToAtlasChartUnclipped($ra, $dec, $centerRaDeg, $centerDecDeg, $xHalfDeg, $yHalfDeg, $left, $top, $plotWidth, $plotHeight);
            $currentPoint = ['x' => (float) $pt['x'], 'y' => (float) $pt['y']];

            if ($previousPoint !== null) {
                $clipped = $this->clipLineToRect($previousPoint['x'], $previousPoint['y'], $currentPoint['x'], $currentPoint['y'], $rect);
                if ($clipped !== null) {
                    $pathParts[] = sprintf('M %0.2f %0.2f L %0.2f %0.2f', $clipped['x1'], $clipped['y1'], $clipped['x2'], $clipped['y2']);

                    $segmentLength = hypot($clipped['x2'] - $clipped['x1'], $clipped['y2'] - $clipped['y1']);
                    if ($segmentLength > $bestLabelLength) {
                        $bestLabelLength = $segmentLength;
                        $labelX = ($clipped['x1'] + $clipped['x2']) / 2.0;
                        $labelY = ($clipped['y1'] + $clipped['y2']) / 2.0;
                        $dx = $clipped['x2'] - $clipped['x1'];
                        $dy = $clipped['y2'] - $clipped['y1'];
                        $len = max(1.0e-6, hypot($dx, $dy));
                        $normalX = -$dy / $len;
                        $normalY = $dx / $len;
                    }
                }
            }

            $previousPoint = $currentPoint;
        }

        return [
            'path' => implode(' ', $pathParts),
            'label_x' => $labelX,
            'label_y' => $labelY,
            'normal_x' => $normalX,
            'normal_y' => $normalY,
        ];
    }

    /**
     * @return array{0: float, 1: float, 2: float}
     */
    private function toUnitVector(float $raDeg, float $decDeg): array
    {
        $ra = deg2rad($raDeg);
        $dec = deg2rad($decDeg);
        $cosDec = cos($dec);

        return [
            $cosDec * cos($ra),
            $cosDec * sin($ra),
            sin($dec),
        ];
    }

    /**
     * @return array{x: float, y: float}
     */
    private function projectToAtlasChartUnclipped(
        float $raDeg,
        float $decDeg,
        float $centerRaDeg,
        float $centerDecDeg,
        float $xHalfDeg,
        float $yHalfDeg,
        float $left,
        float $top,
        float $plotWidth,
        float $plotHeight
    ): array {
        $deltaRa = $this->normalizeRaDelta($raDeg - $centerRaDeg);
        $xDegrees = $deltaRa * cos(deg2rad($centerDecDeg));
        $yDegrees = $decDeg - $centerDecDeg;

        $x = $left + ((($xHalfDeg - $xDegrees) / (2 * $xHalfDeg)) * $plotWidth);
        $y = $top + ((($yHalfDeg - $yDegrees) / (2 * $yHalfDeg)) * $plotHeight);

        return ['x' => $x, 'y' => $y];
    }

    /**
     * @return array{x: float, y: float}|null
     */
    private function projectToAtlasChart(
        float $raDeg,
        float $decDeg,
        float $centerRaDeg,
        float $centerDecDeg,
        float $xHalfDeg,
        float $yHalfDeg,
        float $left,
        float $top,
        float $plotWidth,
        float $plotHeight
    ): ?array {
        $point = $this->projectToAtlasChartUnclipped($raDeg, $decDeg, $centerRaDeg, $centerDecDeg, $xHalfDeg, $yHalfDeg, $left, $top, $plotWidth, $plotHeight);

        if ((float) $point['x'] < $left || (float) $point['x'] > $left + $plotWidth || (float) $point['y'] < $top || (float) $point['y'] > $top + $plotHeight) {
            return null;
        }

        return $point;
    }

    private function applyLegacyRaRange(Builder $builder, float $min, float $max, float $upperBound, float $lowerBound = 0.0): void
    {
        if ($min < $lowerBound) {
            $builder->whereBetween('ra', [$lowerBound, $max])
                ->orWhereBetween('ra', [$upperBound + $min, $upperBound]);
            return;
        }

        if ($max > $upperBound) {
            $builder->whereBetween('ra', [$min, $upperBound])
                ->orWhereBetween('ra', [$lowerBound, $max - $upperBound]);
            return;
        }

        $builder->whereBetween('ra', [$min, $max]);
    }

    private function applyRaWindow(Builder $builder, float $centerRaDeg, float $windowDeg): void
    {
        $min = $this->normalizeRaDegrees($centerRaDeg - $windowDeg);
        $max = $this->normalizeRaDegrees($centerRaDeg + $windowDeg);

        if ($min <= $max) {
            $builder->whereBetween('ra_degrees', [$min, $max]);
            return;
        }

        $builder->where(function (Builder $query) use ($min, $max) {
            $query->whereBetween('ra_degrees', [0.0, $max])
                ->orWhereBetween('ra_degrees', [$min, 360.0]);
        });
    }

    private function clipLineToRect(float $x1, float $y1, float $x2, float $y2, array $rect): ?array
    {
        $dx = $x2 - $x1;
        $dy = $y2 - $y1;
        $p = [-$dx, $dx, -$dy, $dy];
        $q = [
            $x1 - $rect['min_x'],
            $rect['max_x'] - $x1,
            $y1 - $rect['min_y'],
            $rect['max_y'] - $y1,
        ];

        $u1 = 0.0;
        $u2 = 1.0;

        for ($i = 0; $i < 4; $i++) {
            if (abs($p[$i]) < 1.0e-8) {
                if ($q[$i] < 0.0) {
                    return null;
                }
                continue;
            }

            $u = $q[$i] / $p[$i];
            if ($p[$i] < 0.0) {
                $u1 = max($u1, $u);
            } else {
                $u2 = min($u2, $u);
            }

            if ($u1 > $u2) {
                return null;
            }
        }

        return [
            'x1' => $x1 + ($u1 * $dx),
            'y1' => $y1 + ($u1 * $dy),
            'x2' => $x1 + ($u2 * $dx),
            'y2' => $y1 + ($u2 * $dy),
        ];
    }

    private function renderBoundaryLabelOnSide(string $label, ?float $x, ?float $y, float $normalX, float $normalY, string $positionHint, float $side, float $left, float $top, float $plotWidth, float $plotHeight): string
    {
        $trimmed = trim($label);
        if ($trimmed === '' || $x === null || $y === null) {
            return '';
        }

        $offset = 14.0;
        $plusX = $x + ($normalX * $offset);
        $plusY = $y + ($normalY * $offset);
        $minusX = $x - ($normalX * $offset);
        $minusY = $y - ($normalY * $offset);

        $hint = strtoupper(trim($positionHint));
        if ($hint === 'L') {
            [$rawX, $rawY] = $plusX <= $minusX ? [$plusX, $plusY] : [$minusX, $minusY];
        } elseif ($hint === 'R') {
            [$rawX, $rawY] = $plusX >= $minusX ? [$plusX, $plusY] : [$minusX, $minusY];
        } elseif ($hint === 'A') {
            [$rawX, $rawY] = $plusY <= $minusY ? [$plusX, $plusY] : [$minusX, $minusY];
        } elseif ($hint === 'B') {
            [$rawX, $rawY] = $plusY >= $minusY ? [$plusX, $plusY] : [$minusX, $minusY];
        } else {
            $signedOffset = $offset * $side;
            $rawX = $x + ($normalX * $signedOffset);
            $rawY = $y + ($normalY * $signedOffset);
        }

        $labelX = min($left + $plotWidth - 18.0, max($left + 10.0, $rawX));
        $labelY = min($top + $plotHeight - 8.0, max($top + 10.0, $rawY));

        $boxPaddingX = 4.0;
        $boxPaddingY = 2.0;
        $charWidth = 5.2;
        $boxWidth = max(16.0, (strlen($trimmed) * $charWidth) + (2 * $boxPaddingX));
        $boxHeight = 12.0;
        $rectX = $labelX - ($boxWidth / 2.0);
        $rectY = $labelY - ($boxHeight - $boxPaddingY);

        $box = sprintf(
            '<rect x="%0.2f" y="%0.2f" width="%0.2f" height="%0.2f" rx="2" ry="2" fill="#e5e7eb" fill-opacity="0.9" stroke="#9ca3af" stroke-width="0.5" />',
            $rectX,
            $rectY,
            $boxWidth,
            $boxHeight
        );
        $text = sprintf('<text x="%0.2f" y="%0.2f" font-size="8.5" font-weight="bold" fill="#374151" text-anchor="middle">%s</text>', $labelX, $labelY, htmlspecialchars($trimmed, ENT_QUOTES, 'UTF-8'));

        return $box . $text;
    }

    private function renderObjectLabel(string $label, float $x, float $y, float $left, float $top, float $plotWidth, float $plotHeight): string
    {
        $trimmed = trim($label);
        if ($trimmed === '') {
            return '';
        }

        $labelX = min($left + $plotWidth - 12.0, max($left + 8.0, $x + 10.0));
        $labelY = min($top + $plotHeight - 6.0, max($top + 10.0, $y - 7.0));

        return sprintf(
            '<text x="%0.2f" y="%0.2f" font-size="8.5" fill="#4b5563" text-anchor="start">%s</text>',
            $labelX,
            $labelY,
            htmlspecialchars($trimmed, ENT_QUOTES, 'UTF-8')
        );
    }

    private function isGalaxyClusterType(string $type): bool
    {
        $code = strtoupper(trim($type));

        return in_array($code, ['GALCL', 'GXAGC'], true);
    }

    /**
     * @param array<string,mixed> $object
     */
    private function shouldRenderObjectLabel(array $object): bool
    {
        $type = strtoupper(trim((string) ($object['type'] ?? '')));
        $name = strtoupper(trim((string) ($object['name'] ?? '')));

        if ($type === 'DS') {
            return $name !== '';
        }

        if ($this->isGalaxyClusterType($type)) {
            return true;
        }

        foreach (['AGC ', 'ABELL ', 'ACO ', 'ZW ', 'ZWCL ', 'UGC ', 'PGC ', 'IC ', 'NGC '] as $prefix) {
            if (str_starts_with($name, $prefix)) {
                return true;
            }
        }

        return false;
    }

    private function renderObjectSymbol(
        string $type,
        float $x,
        float $y,
        ?float $diam1ArcMin = null,
        ?float $diam2ArcMin = null,
        ?float $positionAngleDeg = null,
        ?float $magnitude = null,
        float $arcMinToPx = 1.0
    ): string {
        $code = strtoupper(trim($type));

        return match ($code) {
            'GALXY', 'GX', 'GXAGC', 'GALCL' => $this->renderGalaxySymbol($x, $y, $diam1ArcMin, $diam2ArcMin, $positionAngleDeg ?? 0.0, $arcMinToPx),
            'DS' => $this->renderDoubleStarSymbol($x, $y, $diam1ArcMin, $positionAngleDeg, $magnitude, $arcMinToPx),
            'GLOCL' => sprintf('<circle cx="%0.2f" cy="%0.2f" r="5" fill="none" stroke="#666" stroke-width="1" /><line x1="%0.2f" y1="%0.2f" x2="%0.2f" y2="%0.2f" stroke="#666" stroke-width="0.8" /><line x1="%0.2f" y1="%0.2f" x2="%0.2f" y2="%0.2f" stroke="#666" stroke-width="0.8" />', $x, $y, $x - 6, $y, $x + 6, $y, $x, $y - 6, $x, $y + 6),
            'OPNCL' => sprintf('<circle cx="%0.2f" cy="%0.2f" r="5" fill="none" stroke="#666" stroke-width="1" stroke-dasharray="2 2" />', $x, $y),
            'DRKNB' => sprintf('<rect x="%0.2f" y="%0.2f" width="10" height="10" fill="none" stroke="#666" stroke-width="1" stroke-dasharray="2 2" />', $x - 5, $y - 5),
            'PLNNB' => sprintf('<circle cx="%0.2f" cy="%0.2f" r="4.5" fill="none" stroke="#666" stroke-width="1" /><line x1="%0.2f" y1="%0.2f" x2="%0.2f" y2="%0.2f" stroke="#666" stroke-width="0.9" />', $x, $y, $x - 7, $y, $x + 7, $y),
            'SNREM' => sprintf('<circle cx="%0.2f" cy="%0.2f" r="5" fill="none" stroke="#666" stroke-width="1" stroke-dasharray="1.5 2" />', $x, $y),
            'QUASR' => sprintf('<line x1="%0.2f" y1="%0.2f" x2="%0.2f" y2="%0.2f" stroke="#666" stroke-width="1" /><line x1="%0.2f" y1="%0.2f" x2="%0.2f" y2="%0.2f" stroke="#666" stroke-width="1" />', $x - 5, $y, $x + 5, $y, $x, $y - 5, $x, $y + 5),
            default => sprintf('<polygon points="%0.2f,%0.2f %0.2f,%0.2f %0.2f,%0.2f %0.2f,%0.2f" fill="none" stroke="#666" stroke-width="1" />', $x, $y - 5, $x + 5, $y, $x, $y + 5, $x - 5, $y),
        };
    }

    private function renderDoubleStarSymbol(float $x, float $y, ?float $separationArcSec, ?float $positionAngleDeg, ?float $magnitude, float $arcMinToPx): string
    {
        $separationPx = 8.0;
        if ($separationArcSec !== null && $separationArcSec > 0.0) {
            $separationPx = max(5.5, min(18.0, (($separationArcSec / 60.0) * $arcMinToPx * 3.0)));
        }

        $strokeWidth = match (true) {
            $magnitude !== null && $magnitude <= 8.5 => 2.0,
            $magnitude !== null && $magnitude <= 10.0 => 1.6,
            default => 1.2,
        };

        $pa = $positionAngleDeg === null ? 90.0 : fmod($positionAngleDeg + 360.0, 360.0);
        $paRad = deg2rad($pa);
        $dx = sin($paRad) * $separationPx;
        $dy = -cos($paRad) * $separationPx;
        $endX = $x + $dx;
        $endY = $y + $dy;

        $headSize = max(2.2, min(4.2, $strokeWidth * 2.1));
        $leftX = $endX - ($dx * 0.18) - ($dy * 0.22 / max(1.0, $separationPx));
        $leftY = $endY - ($dy * 0.18) + ($dx * 0.22 / max(1.0, $separationPx));
        $rightX = $endX - ($dx * 0.18) + ($dy * 0.22 / max(1.0, $separationPx));
        $rightY = $endY - ($dy * 0.18) - ($dx * 0.22 / max(1.0, $separationPx));

        return sprintf(
            '<circle cx="%0.2f" cy="%0.2f" r="1.8" fill="#666" /><line x1="%0.2f" y1="%0.2f" x2="%0.2f" y2="%0.2f" stroke="#666" stroke-width="%0.2f" stroke-linecap="round" /><polygon points="%0.2f,%0.2f %0.2f,%0.2f %0.2f,%0.2f" fill="#666" />',
            $x,
            $y,
            $x,
            $y,
            $endX,
            $endY,
            $strokeWidth,
            $endX,
            $endY,
            $leftX - (($dx / max(1.0, $separationPx)) * $headSize),
            $leftY - (($dy / max(1.0, $separationPx)) * $headSize),
            $rightX - (($dx / max(1.0, $separationPx)) * $headSize),
            $rightY - (($dy / max(1.0, $separationPx)) * $headSize),
        );
    }

    private function renderGalaxySymbol(float $x, float $y, ?float $majorDiamArcMin, ?float $minorDiamArcMin, float $positionAngleDeg, float $arcMinToPx): string
    {
        $majorArcMin = $this->normalizeGalaxyArcMin($majorDiamArcMin);
        $minorArcMin = $this->normalizeGalaxyArcMin($minorDiamArcMin);

        if ($majorArcMin === null && $minorArcMin === null) {
            return sprintf('<ellipse cx="%0.2f" cy="%0.2f" rx="7" ry="4" fill="none" stroke="#666" stroke-width="1" />', $x, $y);
        }

        if ($majorArcMin !== null && $minorArcMin !== null && $minorArcMin > $majorArcMin) {
            [$majorArcMin, $minorArcMin] = [$minorArcMin, $majorArcMin];
        }

        $majorRadiusPx = $majorArcMin === null ? 7.0 : max(1.0, min(12.0, $majorArcMin * 1.1));
        $minorRadiusPx = $minorArcMin === null ? max(0.8, min(5.0, $majorRadiusPx * 0.8)) : max(0.8, min(7.0, $minorArcMin));
        $rotation = 90.0 - $positionAngleDeg;

        return sprintf('<ellipse cx="%0.2f" cy="%0.2f" rx="%0.2f" ry="%0.2f" fill="none" stroke="#666" stroke-width="1" transform="rotate(%0.2f %0.2f %0.2f)" />', $x, $y, $majorRadiusPx, $minorRadiusPx, $rotation, $x, $y);
    }

    private function normalizeGalaxyArcMin(?float $value): ?float
    {
        if ($value === null || $value <= 0.0) {
            return null;
        }

        return $value / 60.0;
    }

    private function renderTopLegend(float $startX, float $baselineY): string
    {
        $svg = [];
        $x = $startX;

        foreach ([6.5, 7.0, 8.0, 8.5, 9.0, 9.5, 10.0, 11.5, 12.0] as $mag) {
            $radius = $this->starRadius($mag);
            $svg[] = sprintf('<circle cx="%0.2f" cy="%0.2f" r="%0.2f" fill="#111" />', $x, $baselineY - 9, $radius);
            $svg[] = sprintf('<text x="%0.2f" y="%0.2f" font-size="8" fill="#4b5563">%s</text>', $x + 8, $baselineY - 7, number_format($mag, 1));
            $x += 39;
        }

        $x += 5;
        $keys = ['GALXY', 'PLNNB', 'GLOCL', 'OPNCL', 'DRKNB', 'CLANB', 'GALCL', 'QUASR'];
        foreach ($keys as $code) {
            $svg[] = $this->renderLegendSymbol($code, $x, $baselineY - 8);
            $svg[] = sprintf('<text x="%0.2f" y="%0.2f" font-size="8" fill="#4b5563">%s</text>', $x + 14, $baselineY - 6, $code);
            $x += 52;
        }

        return implode('', $svg);
    }

    private function renderLegendSymbol(string $code, float $x, float $y): string
    {
        return match ($code) {
            'GALXY' => sprintf('<ellipse cx="%0.2f" cy="%0.2f" rx="6" ry="3.5" fill="none" stroke="#666" stroke-width="1" />', $x, $y),
            'PLNNB' => sprintf('<circle cx="%0.2f" cy="%0.2f" r="4.5" fill="none" stroke="#666" stroke-width="1" />', $x, $y),
            'GLOCL' => sprintf('<circle cx="%0.2f" cy="%0.2f" r="4.5" fill="none" stroke="#666" stroke-width="1" /><line x1="%0.2f" y1="%0.2f" x2="%0.2f" y2="%0.2f" stroke="#666" stroke-width="0.8" />', $x, $y, $x - 5, $y, $x + 5, $y),
            'OPNCL' => sprintf('<circle cx="%0.2f" cy="%0.2f" r="4.5" fill="none" stroke="#666" stroke-width="1" stroke-dasharray="2 2" />', $x, $y),
            'DRKNB' => sprintf('<rect x="%0.2f" y="%0.2f" width="9" height="9" fill="none" stroke="#666" stroke-width="1" stroke-dasharray="2 2" />', $x - 4.5, $y - 4.5),
            'CLANB' => sprintf('<rect x="%0.2f" y="%0.2f" width="9" height="9" fill="none" stroke="#666" stroke-width="1" /><circle cx="%0.2f" cy="%0.2f" r="4.5" fill="none" stroke="#666" stroke-width="1" />', $x - 4.5, $y - 4.5, $x, $y),
            'GALCL' => sprintf('<polygon points="%0.2f,%0.2f %0.2f,%0.2f %0.2f,%0.2f %0.2f,%0.2f" fill="none" stroke="#666" stroke-width="1" />', $x, $y - 4.5, $x + 4.5, $y, $x, $y + 4.5, $x - 4.5, $y),
            'QUASR' => sprintf('<line x1="%0.2f" y1="%0.2f" x2="%0.2f" y2="%0.2f" stroke="#666" stroke-width="1" /><line x1="%0.2f" y1="%0.2f" x2="%0.2f" y2="%0.2f" stroke="#666" stroke-width="1" />', $x - 4.5, $y, $x + 4.5, $y, $x, $y - 4.5, $x, $y + 4.5),
            default => sprintf('<polygon points="%0.2f,%0.2f %0.2f,%0.2f %0.2f,%0.2f %0.2f,%0.2f" fill="none" stroke="#666" stroke-width="1" />', $x, $y - 4.5, $x + 4.5, $y, $x, $y + 4.5, $x - 4.5, $y),
        };
    }

    private function renderPolarGrid(float $centerRaDeg, float $centerDecDeg, float $xHalfDeg, float $yHalfDeg, float $left, float $top, float $plotWidth, float $plotHeight): string
    {
        $svg = [];
        $cx = $left + ($plotWidth / 2);
        $cy = $top + ($plotHeight / 2);
        $radius = min($plotWidth, $plotHeight) / 2;

        for ($i = 1; $i <= 4; $i++) {
            $svg[] = sprintf('<circle cx="%0.2f" cy="%0.2f" r="%0.2f" fill="none" stroke="#d9d9d9" stroke-width="0.6" />', $cx, $cy, $radius * ($i / 4));
        }

        for ($i = 0; $i < 12; $i++) {
            $angle = deg2rad($i * 30.0);
            $x = $cx + cos($angle) * $radius;
            $y = $cy + sin($angle) * $radius;
            $svg[] = sprintf('<line x1="%0.2f" y1="%0.2f" x2="%0.2f" y2="%0.2f" stroke="#d9d9d9" stroke-width="0.6" />', $cx, $cy, $x, $y);
        }

        return implode('', $svg);
    }

    private function starRadius(?float $magnitude): float
    {
        if ($magnitude === null) {
            return 0.8;
        }
        if ($magnitude <= 2.0) {
            return 2.8;
        }
        if ($magnitude <= 4.0) {
            return 2.2;
        }
        if ($magnitude <= 6.0) {
            return 1.7;
        }
        if ($magnitude <= 8.0) {
            return 1.2;
        }

        return 0.8;
    }

    private function resolveMagnitudeLimit(int $radiusArcMin, ?float $magnitudeLimit): float
    {
        if ($magnitudeLimit !== null) {
            return $magnitudeLimit;
        }
        if ($radiusArcMin <= 15) {
            return 15.0;
        }
        if ($radiusArcMin <= 30) {
            return 14.0;
        }
        if ($radiusArcMin <= 60) {
            return 13.0;
        }

        return 12.0;
    }

    private function maxStarCount(float $magnitudeLimit, float $radiusDeg): int
    {
        if ($radiusDeg <= 0.5) {
            return 5000;
        }
        if ($magnitudeLimit <= 10.0) {
            return 3500;
        }

        return 2800;
    }

    private function xHalfDegrees(float $radiusDeg): float
    {
        return max($radiusDeg, $radiusDeg * 2.3);
    }

    private function raWindowDegreesFromXHalf(float $centerDecDeg, float $xHalfDeg): float
    {
        $cosDec = max(0.12, abs(cos(deg2rad($centerDecDeg))));
        return min(180.0, $xHalfDeg / $cosDec);
    }

    private function normalizeRaDelta(float $deltaDeg): float
    {
        $deltaDeg = fmod($deltaDeg, 360.0);
        if ($deltaDeg > 180.0) {
            $deltaDeg -= 360.0;
        }
        if ($deltaDeg < -180.0) {
            $deltaDeg += 360.0;
        }

        return $deltaDeg;
    }

    private function normalizeRaDegrees(float $raDeg): float
    {
        $raDeg = fmod($raDeg, 360.0);
        if ($raDeg < 0.0) {
            $raDeg += 360.0;
        }

        return $raDeg;
    }

    private function formatRaHours(float $raDeg): string
    {
        $hours = $this->normalizeRaDegrees($raDeg) / 15.0;
        $h = (int) floor($hours);
        $minutes = (int) floor(($hours - $h) * 60.0);

        return sprintf('%02dh%02dm', $h, $minutes);
    }

    private function formatDecDms(float $decDeg): string
    {
        $sign = $decDeg < 0 ? '-' : '+';
        $abs = abs($decDeg);
        $deg = (int) floor($abs);
        $minutes = (int) floor(($abs - $deg) * 60.0);

        return sprintf('%s%02d°%02d\'', $sign, $deg, $minutes);
    }
}
