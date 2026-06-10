<?php

namespace App\Services;

use App\Models\DeepskyObject;
use App\Models\Star;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StarChartService
{
    private const CHART_WIDTH = 1120;
    private const CHART_HEIGHT = 780;
    private const CHART_LEFT = 56;
    private const CHART_RIGHT = 56;
    private const CHART_TOP = 70;
    private const CHART_BOTTOM = 84;

    public function build(DeepskyObject $target, float $centerRaDeg, float $centerDecDeg, int $radiusArcMin, ?float $magnitudeLimit = null, ?float $objectMagnitudeLimit = null, ?float $starMagnitudeLimit = null): array
    {
        $radiusDeg = max(0.1, $radiusArcMin / 60);

        // If a single legacy magnitude limit was provided use it as a sensible
        // default. Specific per-type limits (object/star) override this when set.
        $defaultMag = $magnitudeLimit !== null ? $magnitudeLimit : $this->resolveMagnitudeLimit($radiusArcMin, null);

        $starMag = $starMagnitudeLimit ?? $defaultMag;
        $objectMag = $objectMagnitudeLimit ?? $defaultMag;

        $stars = $this->loadStars($centerRaDeg, $centerDecDeg, $radiusDeg, $starMag);
        $objects = $this->loadObjects($centerRaDeg, $centerDecDeg, $radiusDeg, $target, $objectMag);
        $contours = $this->loadObjectContours($objects);
        $boundaries = $this->loadConstellationBoundaries();

        return [
            'target' => $target,
            'radius_arc_min' => $radiusArcMin,
            'radius_deg' => $radiusDeg,
            // Legacy key continues to reference the star limit for backward
            // compatibility. New keys expose per-type limits explicitly.
            'magnitude_limit' => $starMag,
            'object_magnitude_limit' => $objectMag,
            'star_magnitude_limit' => $starMag,
            'stars' => $stars,
            'objects' => $objects,
            'svg' => $this->renderSvg($centerRaDeg, $centerDecDeg, $radiusDeg, $objectMag, $starMag, $target, $stars, $objects, $contours, $boundaries),
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
    private function loadObjects(float $centerRaDeg, float $centerDecDeg, float $radiusDeg, DeepskyObject $target, ?float $objectMagnitudeLimit = null): Collection
    {
        $xHalfDeg = $this->xHalfDegrees($radiusDeg);
        $raWindow = $this->raWindowDegreesFromXHalf($centerDecDeg, $xHalfDeg);
        $centerRaHours = $centerRaDeg / 15.0;
        $raWindowHours = $raWindow / 15.0;

        $raMinHours = $centerRaHours - $raWindowHours;
        $raMaxHours = $centerRaHours + $raWindowHours;
        $raMinDeg = $centerRaDeg - $raWindow;
        $raMaxDeg = $centerRaDeg + $raWindow;

        $query = DeepskyObject::query()
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
        ;

        if ($objectMagnitudeLimit !== null) {
            // Include objects that have no recorded magnitude (NULL), the
            // placeholder value 99.9 (treated as missing), or objects with
            // magnitude less than or equal to the limit.
            $query = $query->where(function (Builder $q) use ($objectMagnitudeLimit) {
                $q->whereNull('mag')
                    ->orWhere('mag', '=', 99.9)
                    ->orWhere('mag', '<=', $objectMagnitudeLimit);
            });
        }

        $objects = $query->limit(200)->get()
            ->map(function (DeepskyObject $object) use ($target) {
                $raDegrees = DeepskyObject::raToDecimal($object->ra);
                $rawPa = $object->pa === null ? null : (float) $object->pa;
                $normalizedPa = null;
                if ($rawPa !== null && $rawPa >= 0.0 && $rawPa < 999.0) {
                    $normalizedPa = fmod($rawPa, 360.0);
                    if ($normalizedPa < 0.0) {
                        $normalizedPa += 360.0;
                    }
                }

                $mag = $object->mag === null ? null : (float) $object->mag;
                if ($mag !== null && abs($mag - 99.9) < 0.001) {
                    // Treat the placeholder 99.9 as missing magnitude
                    $mag = null;
                }

                return [
                    'name' => $object->name,
                    'slug' => $object->slug,
                    'type' => $object->type,
                    'mag' => $mag,
                    'diam1' => $object->diam1 === null ? null : (float) $object->diam1,
                    'diam2' => $object->diam2 === null ? null : (float) $object->diam2,
                    'pa' => $normalizedPa,
                    'ra_degrees' => (float) ($raDegrees ?? 0.0),
                    'decl_degrees' => (float) $object->decl,
                    'is_target' => $object->name === $target->name,
                ];
            })->filter(function (array $object) use ($centerRaDeg, $centerDecDeg, $radiusDeg, $xHalfDeg) {
                $xDegrees = $this->normalizeRaDelta($object['ra_degrees'] - $centerRaDeg) * cos(deg2rad($centerDecDeg));
                $yDegrees = $object['decl_degrees'] - $centerDecDeg;

                return abs($xDegrees) <= $xHalfDeg && abs($yDegrees) <= $radiusDeg;
            })->sortBy(fn(array $object) => $object['is_target'] ? -999 : ($object['mag'] ?? 999))->values();

        return $objects;
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

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function loadObjectContours(Collection $objects): Collection
    {
        if (!Schema::hasTable('object_contours')) {
            return collect();
        }

        $objectNames = $objects
            ->pluck('name')
            ->filter(fn($name) => is_string($name) && trim($name) !== '')
            ->map(fn(string $name) => trim($name))
            ->unique()
            ->values();

        if ($objectNames->isEmpty()) {
            return collect();
        }

        return DB::table('object_contours')
            ->select(['object_name', 'contour_index', 'point_index', 'ra_degrees', 'decl_degrees'])
            ->whereIn('object_name', $objectNames->all())
            ->orderBy('object_name')
            ->orderBy('contour_index')
            ->orderBy('point_index')
            ->get()
            ->groupBy(fn($row) => (string) $row->object_name . '|' . (int) $row->contour_index)
            ->map(function (Collection $rows) {
                $first = $rows->first();

                return [
                    'object_name' => (string) $first->object_name,
                    'contour_index' => (int) $first->contour_index,
                    'points' => $rows->map(fn($row) => [
                        'ra_degrees' => (float) $row->ra_degrees,
                        'decl_degrees' => (float) $row->decl_degrees,
                    ])->values(),
                ];
            })->values();
    }

    private function renderSvg(
        float $centerRaDeg,
        float $centerDecDeg,
        float $radiusDeg,
        float $objectMagnitudeLimit,
        float $starMagnitudeLimit,
        DeepskyObject $target,
        Collection $stars,
        Collection $objects,
        Collection $contours,
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

        // Clip path for the plotting area so object symbols do not draw outside
        // the main star-plot rectangle. Use userSpaceOnUse so coordinates match
        // the SVG user coordinate system. Labels are rendered outside this clip.
        $svg[] = sprintf('<defs><clipPath id="plot-clip" clipPathUnits="userSpaceOnUse"><rect x="%0.2f" y="%0.2f" width="%0.2f" height="%0.2f" /></clipPath></defs>', $left, $top, $plotWidth, $plotHeight);

        // Define a clipping region matching the star-plot rectangle so that
        // object symbols (rectangles, ellipses, etc.) are not drawn outside
        // the visible star field area.

        $polarMode = abs($centerDecDeg) >= 85.0;
        if ($polarMode) {
            // Keep polar grid strictly inside the chart rectangle.
            $svg[] = sprintf('<g clip-path="url(#plot-clip)">%s</g>', $this->renderPolarGrid($centerRaDeg, $centerDecDeg, $xHalfDeg, $yHalfDeg, $left, $top, $plotWidth, $plotHeight));
        } else {
            $gridColumns = 10;
            for ($i = 0; $i <= $gridColumns; $i++) {
                $x = $left + ($plotWidth * $i / $gridColumns);
                $svg[] = sprintf('<line x1="%0.2f" y1="%0.2f" x2="%0.2f" y2="%0.2f" stroke="#d9d9d9" stroke-width="0.6" />', $x, $top, $x, $top + $plotHeight);

                $xDeg = $xHalfDeg - (($i / $gridColumns) * (2 * $xHalfDeg));
                $raDeg = $centerRaDeg + ($xDeg / $cosDec);
                // Place RA tick labels above the plot rectangle to avoid overlap
                // with in-plot annotations (e.g. constellation badge).
                $svg[] = sprintf('<text x="%0.2f" y="%0.2f" font-size="9" text-anchor="middle" fill="#6b7280">%s</text>', $x, $top - 8, htmlspecialchars($this->formatRaHours($raDeg), ENT_QUOTES, 'UTF-8'));
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

        // Begin clipped group so boundaries, contours, stars and object
        // symbols are constrained to the plotting rectangle.
        $svg[] = '<g clip-path="url(#plot-clip)">';

        // Boundary labels are collected and rendered after the clipped group so
        // labels near chart edges are not clipped away.
        $boundaryLabels = [];

        $visibleBoundaryCount = 0;

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

            $visibleBoundaryCount++;

            $svg[] = sprintf('<path d="%s" fill="none" stroke="#9ca3af" stroke-width="2.4" stroke-linecap="round" opacity="0.7" />', $boundary['path']);

            $boundaryLabels[] = $this->renderBoundaryLabelOnSide(
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
            $boundaryLabels[] = $this->renderBoundaryLabelOnSide(
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

        if ($visibleBoundaryCount === 0) {
            $targetCon = strtoupper(trim((string) ($target->con ?? '')));
            if ($targetCon !== '') {
                // Place constellation badge in the top-right corner inside the plot
                // area so it doesn't occupy the center (reserved for page numbers).
                $badgeX = $left + $plotWidth - 36.0;
                $badgeY = $top + 18.0;

                $svg[] = $this->renderBoundaryLabelOnSide(
                    $targetCon,
                    $badgeX,
                    $badgeY,
                    0.0,
                    0.0,
                    'R',
                    1.0,
                    $left,
                    $top,
                    $plotWidth,
                    $plotHeight
                );
            }
        }

        $chartRect = [
            'min_x' => $left,
            'min_y' => $top,
            'max_x' => $left + $plotWidth,
            'max_y' => $top + $plotHeight,
        ];

        foreach ($contours as $contour) {
            $path = $this->renderObjectContourPath(
                $contour,
                $centerRaDeg,
                $centerDecDeg,
                $xHalfDeg,
                $yHalfDeg,
                $left,
                $top,
                $plotWidth,
                $plotHeight,
                $chartRect
            );

            if ($path === '') {
                continue;
            }

            $isTargetContour = strcasecmp((string) ($contour['object_name'] ?? ''), (string) $target->name) === 0;
            $stroke = $isTargetContour ? '#1f2937' : '#6b7280';
            $strokeWidth = $isTargetContour ? 1.5 : 1.0;

            $svg[] = sprintf(
                '<path data-contour="%s" d="%s" fill="none" stroke="%s" stroke-width="%0.1f" stroke-linejoin="round" stroke-linecap="round" />',
                htmlspecialchars((string) ($contour['object_name'] ?? ''), ENT_QUOTES, 'UTF-8'),
                $path,
                $stroke,
                $strokeWidth
            );
        }

        $objectsWithContours = $contours
            ->pluck('object_name')
            ->filter(fn($name) => is_string($name) && trim($name) !== '')
            ->map(fn(string $name) => strtoupper(trim($name)))
            ->unique()
            ->values();

        // Allow labels for all objects by setting budget to the number of objects
        // visible in the chart (prevents type-based filtering hiding labels).
        $labelBudget = max(1000, $objects->count());
        $occupiedLabelBoxes = [];
        $labelTasks = [];

        // Bulk-query observations/drawings for visible objects so we can
        // annotate labels with seen/drawn indicators without per-object queries.
        $objectKeys = $objects->pluck('name')
            ->filter(fn($n) => is_string($n) && trim((string) $n) !== '')
            ->map(fn($n) => trim((string) $n))
            ->unique()
            ->values()
            ->all();

        $observedMap = [];
        $drawnMap = [];
        $observedByUserMap = [];
        $drawnByUserMap = [];

        if (!empty($objectKeys)) {
            $rows = DB::table('observations')
                ->select(['target_key', DB::raw('COUNT(*) AS cnt'), DB::raw('SUM(CASE WHEN has_drawing=1 THEN 1 ELSE 0 END) AS draw_cnt')])
                ->where('target_table', 'objects')
                ->whereIn('target_key', $objectKeys)
                ->groupBy('target_key')
                ->get();

            foreach ($rows as $r) {
                $k = (string) $r->target_key;
                $observedMap[$k] = ((int) $r->cnt) > 0;
                $drawnMap[$k] = ((int) $r->draw_cnt) > 0;
            }

            $user = auth()->user();
            if ($user) {
                $userRows = DB::table('observations')
                    ->select(['target_key', DB::raw('COUNT(*) AS cnt'), DB::raw('SUM(CASE WHEN has_drawing=1 THEN 1 ELSE 0 END) AS draw_cnt')])
                    ->where('target_table', 'objects')
                    ->whereIn('target_key', $objectKeys)
                    ->where('observer_user_id', $user->id)
                    ->groupBy('target_key')
                    ->get();

                foreach ($userRows as $r) {
                    $k = (string) $r->target_key;
                    $observedByUserMap[$k] = ((int) $r->cnt) > 0;
                    $drawnByUserMap[$k] = ((int) $r->draw_cnt) > 0;
                }
            }
        }

        $starLabelBoxes = [];

        foreach ($stars as $star) {
            $point = $this->projectToAtlasChart($star['ra_degrees'], $star['decl_degrees'], $centerRaDeg, $centerDecDeg, $xHalfDeg, $yHalfDeg, $left, $top, $plotWidth, $plotHeight);
            if ($point === null) {
                continue;
            }

            $svg[] = sprintf('<circle cx="%0.2f" cy="%0.2f" r="%0.2f" fill="#111827" />', $point['x'], $point['y'], $this->starRadius($star['vmag']));

            // Label bright stars in a restrained way similar to atlas charts.
            $starName = trim((string) ($star['name'] ?? ''));
            $starMag = $star['vmag'] ?? null;
            if ($starName !== '' && $starMag !== null && $starMag <= 4.6) {
                $labelSvg = $this->placeObjectLabel($starName, (float) $point['x'], (float) $point['y'], $left, $top, $plotWidth, $plotHeight, $starLabelBoxes, 7.8);
                if ($labelSvg !== '') {
                    // Use darker text than object labels for better readability.
                    $svg[] = str_replace('fill="#4b5563"', 'fill="#374151"', $labelSvg);
                }
            }
        }

        foreach ($objects as $object) {
            $point = $this->projectToAtlasChart($object['ra_degrees'], $object['decl_degrees'], $centerRaDeg, $centerDecDeg, $xHalfDeg, $yHalfDeg, $left, $top, $plotWidth, $plotHeight);
            if ($point === null) {
                continue;
            }

            $hasContour = $objectsWithContours->contains(strtoupper(trim((string) ($object['name'] ?? ''))));

            if (!$hasContour) {
                $svg[] = $this->renderObjectSymbol(
                    $object['type'] ?? '',
                    $point['x'],
                    $point['y'],
                    $object['diam1'] ?? null,
                    $object['diam2'] ?? null,
                    $object['pa'] ?? null,
                    $object['mag'] ?? null,
                    $arcMinToPx,
                    $chartRect
                );
            }
            // Collect label tasks and render them after the clipped group so
            // labels are never clipped even when symbols are constrained.
            $objName = trim((string) ($object['name'] ?? ''));
            $observedAny = $objName !== '' && !empty($observedMap[$objName]);
            $drawnAny = $objName !== '' && !empty($drawnMap[$objName]);
            $observedByUser = $objName !== '' && !empty($observedByUserMap[$objName]);
            $drawnByUser = $objName !== '' && !empty($drawnByUserMap[$objName]);
            $userLoggedIn = auth()->check();

            if ($object['is_target']) {
                $labelTasks[] = [
                    'label' => (string) $target->name,
                    'x' => $point['x'],
                    'y' => $point['y'],
                    'font' => 11.0,
                    'is_target' => true,
                    'observed_any' => $observedAny,
                    'drawn_any' => $drawnAny,
                    'observed_by_user' => $observedByUser,
                    'drawn_by_user' => $drawnByUser,
                    'user_logged_in' => $userLoggedIn,
                ];
            } elseif ($labelBudget > 0) {
                // Add a label task for every object (user requested all objects labeled)
                $labelTasks[] = [
                    'label' => (string) ($object['name'] ?? ''),
                    'x' => $point['x'],
                    'y' => $point['y'],
                    'font' => 8.5,
                    'is_target' => false,
                    'observed_any' => $observedAny,
                    'drawn_any' => $drawnAny,
                    'observed_by_user' => $observedByUser,
                    'drawn_by_user' => $drawnByUser,
                    'user_logged_in' => $userLoggedIn,
                ];
            }
        }

        $svg[] = '</g>';

        foreach ($boundaryLabels as $boundaryLabelSvg) {
            if ($boundaryLabelSvg !== '') {
                $svg[] = $boundaryLabelSvg;
            }
        }

        // Render collected labels (outside the clip) using the collision-avoiding
        // placement helper. Decrement non-target label budget when successfully placed.
        foreach ($labelTasks as $task) {
            if (!is_string($task['label']) || trim($task['label']) === '') {
                continue;
            }

            if (!empty($task['is_target'])) {
                $labelSvg = $this->placeObjectLabel(
                    $task['label'],
                    $task['x'],
                    $task['y'],
                    $left,
                    $top,
                    $plotWidth,
                    $plotHeight,
                    $occupiedLabelBoxes,
                    $task['font'],
                    $task['observed_any'] ?? false,
                    $task['drawn_any'] ?? false,
                    $task['observed_by_user'] ?? false,
                    $task['drawn_by_user'] ?? false,
                    $task['user_logged_in'] ?? false
                );
                if ($labelSvg !== '') {
                    $svg[] = $labelSvg;
                }
                continue;
            }

            if ($labelBudget <= 0) {
                continue;
            }

            $labelSvg = $this->placeObjectLabel(
                $task['label'],
                $task['x'],
                $task['y'],
                $left,
                $top,
                $plotWidth,
                $plotHeight,
                $occupiedLabelBoxes,
                $task['font'],
                $task['observed_any'] ?? false,
                $task['drawn_any'] ?? false,
                $task['observed_by_user'] ?? false,
                $task['drawn_by_user'] ?? false,
                $task['user_logged_in'] ?? false
            );
            if ($labelSvg !== '') {
                $svg[] = $labelSvg;
                $labelBudget--;
            }
        }

        $footerText = sprintf(
            'FoV: %s° x %s°  -  Objects to mag: %s  -  Stars to mag: %s  -  (Center %s, %s)',
            number_format($xHalfDeg * 2, 1),
            number_format($yHalfDeg * 2, 1),
            $objectMagnitudeLimit,
            $starMagnitudeLimit,
            $this->formatRaHours($centerRaDeg),
            $this->formatDecDms($centerDecDeg)
        );

        // Footer text (keep near bottom)
        $svg[] = sprintf('<text x="%0.2f" y="%0.2f" font-size="10" fill="#6b7280">%s</text>', $left, $height - 18, htmlspecialchars($footerText, ENT_QUOTES, 'UTF-8'));

        // Small legend for observed/drawn indicators: place it between the
        // plot rectangle and the footer so it's clearly separated from the FoV
        // line. Use the plot bottom as a reference and add a small gap.
        $legendX = $left + 8.0;
        // Lower the legend further below the plot to avoid crowding the plot border.
        $legendY = min($height - 30.0, $top + $plotHeight + 34.0);

        $svg[] = sprintf('<text x="%0.2f" y="%0.2f" font-size="9" fill="#6b7280">%s</text>', $legendX, $legendY - 8, htmlspecialchars(__('Legend:'), ENT_QUOTES, 'UTF-8'));
        $lx = $legendX + 48;
        // Overline solid (drawn by you)
        $svg[] = sprintf('<line x1="%0.2f" y1="%0.2f" x2="%0.2f" y2="%0.2f" stroke="#374151" stroke-width="1" />', $lx, $legendY - 12, $lx + 40, $legendY - 12);
        $svg[] = sprintf('<text x="%0.2f" y="%0.2f" font-size="9" fill="#6b7280">%s</text>', $lx + 46, $legendY - 8, htmlspecialchars(__('drawn by you'), ENT_QUOTES, 'UTF-8'));
        $lx += 140;
        // Overline dashed (drawn by others / when logged out)
        $svg[] = sprintf('<line x1="%0.2f" y1="%0.2f" x2="%0.2f" y2="%0.2f" stroke="#374151" stroke-width="1" stroke-dasharray="3 2" />', $lx, $legendY - 12, $lx + 40, $legendY - 12);
        $svg[] = sprintf('<text x="%0.2f" y="%0.2f" font-size="9" fill="#6b7280">%s</text>', $lx + 46, $legendY - 8, htmlspecialchars(__('drawn by others'), ENT_QUOTES, 'UTF-8'));
        $lx += 160;
        // Underline solid (seen by you)
        $svg[] = sprintf('<line x1="%0.2f" y1="%0.2f" x2="%0.2f" y2="%0.2f" stroke="#374151" stroke-width="1" />', $lx, $legendY, $lx + 40, $legendY);
        $svg[] = sprintf('<text x="%0.2f" y="%0.2f" font-size="9" fill="#6b7280">%s</text>', $lx + 46, $legendY + 4, htmlspecialchars(__('seen by you'), ENT_QUOTES, 'UTF-8'));
        $lx += 140;
        // Underline dashed (seen by others / when logged out)
        $svg[] = sprintf('<line x1="%0.2f" y1="%0.2f" x2="%0.2f" y2="%0.2f" stroke="#374151" stroke-width="1" stroke-dasharray="3 2" />', $lx, $legendY, $lx + 40, $legendY);
        $svg[] = sprintf('<text x="%0.2f" y="%0.2f" font-size="9" fill="#6b7280">%s</text>', $lx + 46, $legendY + 4, htmlspecialchars(__('seen by others'), ENT_QUOTES, 'UTF-8'));

        $svg[] = '</svg>';

        return implode('', $svg);
    }

    /**
     * @param array<string, mixed> $contour
     * @param array<string, float> $chartRect
     */
    private function renderObjectContourPath(
        array $contour,
        float $centerRaDeg,
        float $centerDecDeg,
        float $xHalfDeg,
        float $yHalfDeg,
        float $left,
        float $top,
        float $plotWidth,
        float $plotHeight,
        array $chartRect
    ): string {
        $points = $contour['points'] ?? null;
        if (!($points instanceof Collection) || $points->count() < 2) {
            return '';
        }

        $pathParts = [];
        $previous = null;
        $first = null;

        foreach ($points as $point) {
            $ra = isset($point['ra_degrees']) ? (float) $point['ra_degrees'] : null;
            $dec = isset($point['decl_degrees']) ? (float) $point['decl_degrees'] : null;

            if ($ra === null || $dec === null) {
                continue;
            }

            $projected = $this->projectToAtlasChartUnclipped(
                $ra,
                $dec,
                $centerRaDeg,
                $centerDecDeg,
                $xHalfDeg,
                $yHalfDeg,
                $left,
                $top,
                $plotWidth,
                $plotHeight
            );

            $current = [
                'x' => (float) $projected['x'],
                'y' => (float) $projected['y'],
            ];

            if ($first === null) {
                $first = $current;
            }

            if ($previous !== null) {
                $clipped = $this->clipLineToRect($previous['x'], $previous['y'], $current['x'], $current['y'], $chartRect);
                if ($clipped !== null) {
                    $pathParts[] = sprintf('M %0.2f %0.2f L %0.2f %0.2f', $clipped['x1'], $clipped['y1'], $clipped['x2'], $clipped['y2']);
                }
            }

            $previous = $current;
        }

        // Object outlines are closed contours; connect end back to start.
        if ($previous !== null && $first !== null) {
            $clipped = $this->clipLineToRect($previous['x'], $previous['y'], $first['x'], $first['y'], $chartRect);
            if ($clipped !== null) {
                $pathParts[] = sprintf('M %0.2f %0.2f L %0.2f %0.2f', $clipped['x1'], $clipped['y1'], $clipped['x2'], $clipped['y2']);
            }
        }

        return implode(' ', $pathParts);
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
        $rect = [
            'min_x' => $left,
            'min_y' => $top,
            'max_x' => $left + $plotWidth,
            'max_y' => $top + $plotHeight,
        ];

        // Sample the tabulated RA/Dec boundary segment and clip each sub-segment.
        // This is robust near poles and avoids over-curved great-circle artifacts.
        $steps = 24;
        $pathParts = [];
        $bestSegmentLength = -1.0;
        $labelX = null;
        $labelY = null;

        for ($i = 0; $i < $steps; $i++) {
            $t0 = $i / $steps;
            $t1 = ($i + 1) / $steps;

            $raStart = $ra0Deg + ($this->normalizeRaDelta($ra1Deg - $ra0Deg) * $t0);
            $raEnd = $ra0Deg + ($this->normalizeRaDelta($ra1Deg - $ra0Deg) * $t1);
            $decStart = $dec0Deg + (($dec1Deg - $dec0Deg) * $t0);
            $decEnd = $dec0Deg + (($dec1Deg - $dec0Deg) * $t1);

            $p0 = $this->projectToAtlasChartUnclipped($raStart, $decStart, $centerRaDeg, $centerDecDeg, $xHalfDeg, $yHalfDeg, $left, $top, $plotWidth, $plotHeight);
            $p1 = $this->projectToAtlasChartUnclipped($raEnd, $decEnd, $centerRaDeg, $centerDecDeg, $xHalfDeg, $yHalfDeg, $left, $top, $plotWidth, $plotHeight);
            $clipped = $this->clipLineToRect((float) $p0['x'], (float) $p0['y'], (float) $p1['x'], (float) $p1['y'], $rect);

            if ($clipped === null) {
                continue;
            }

            $pathParts[] = sprintf('M %0.2f %0.2f L %0.2f %0.2f', $clipped['x1'], $clipped['y1'], $clipped['x2'], $clipped['y2']);

            $segmentLength = hypot($clipped['x2'] - $clipped['x1'], $clipped['y2'] - $clipped['y1']);
            if ($segmentLength > $bestSegmentLength) {
                $bestSegmentLength = $segmentLength;
                $labelX = ($clipped['x1'] + $clipped['x2']) / 2.0;
                $labelY = ($clipped['y1'] + $clipped['y2']) / 2.0;
            }
        }

        return [
            'path' => implode(' ', $pathParts),
            'label_x' => $labelX,
            'label_y' => $labelY,
            // Prefer sky-frame orientation for reliable side placement.
            'normal_x' => 0.0,
            'normal_y' => -1.0,
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

    /**
     * Place an object label trying multiple candidate positions to avoid overlaps.
     * Updates the passed $occupied array with the chosen label bounding box.
     *
     * @param array<int, array{left: float, top: float, right: float, bottom: float}> $occupied
     */
    private function placeObjectLabel(
        string $label,
        float $x,
        float $y,
        float $left,
        float $top,
        float $plotWidth,
        float $plotHeight,
        array &$occupied,
        float $fontSize = 8.5,
        bool $observedAny = false,
        bool $drawnAny = false,
        bool $observedByUser = false,
        bool $drawnByUser = false,
        bool $userLoggedIn = false
    ): string {
        $trimmed = trim($label);
        if ($trimmed === '') {
            return '';
        }

        $charWidth = 5.2 * ($fontSize / 8.5);
        $textWidth = max(10.0, strlen($trimmed) * $charWidth);
        $textHeight = $fontSize + 4.0;

        $minX = $left + 8.0;
        $maxX = $left + $plotWidth - 12.0 - $textWidth;
        if ($maxX < $minX) {
            $maxX = $minX;
        }
        $minY = $top + 10.0 + ($textHeight * 0.2);
        $maxY = $top + $plotHeight - 6.0;
        if ($maxY < $minY) {
            $maxY = $minY;
        }

        $candidates = [
            ['lx' => $x + 10.0, 'ly' => $y - 7.0],
            ['lx' => $x + 10.0, 'ly' => $y + 10.0],
            ['lx' => $x - 10.0 - $textWidth, 'ly' => $y - 7.0],
            ['lx' => $x - 10.0 - $textWidth, 'ly' => $y + 10.0],
            ['lx' => $x - ($textWidth / 2.0), 'ly' => $y - 14.0],
            ['lx' => $x - ($textWidth / 2.0), 'ly' => $y + 14.0],
        ];

        $tryBox = function (float $lx, float $ly) use ($minX, $maxX, $minY, $maxY, $textWidth, $textHeight) {
            $lx = min(max($lx, $minX), $maxX);
            $ly = min(max($ly, $minY), $maxY);

            $topY = $ly - $textHeight;
            $bottomY = $ly + 3.0;

            return ['left' => $lx, 'top' => $topY, 'right' => $lx + $textWidth, 'bottom' => $bottomY];
        };

        $intersects = function (array $a, array $b) {
            $pad = 3.0;
            return !($a['right'] + $pad < $b['left'] - $pad || $a['left'] - $pad > $b['right'] + $pad || $a['bottom'] + $pad < $b['top'] - $pad || $a['top'] - $pad > $b['bottom'] + $pad);
        };

        foreach ($candidates as $c) {
            $box = $tryBox($c['lx'], $c['ly']);
            $ok = true;
            foreach ($occupied as $occ) {
                if ($intersects($box, $occ)) {
                    $ok = false;
                    break;
                }
            }
            if ($ok) {
                $occupied[] = $box;

                // Determine line positions
                $textBaseline = $box['bottom'] - 3.0;
                $overY = $box['top'] + 2.0;
                $underY = $textBaseline + 3.0;
                $leftX = $box['left'];
                $rightX = $box['right'];

                $lines = '';
                // Overline (drawn)
                $overSolid = $userLoggedIn && $drawnByUser;
                $overDashed = !$overSolid && $drawnAny;
                if ($overSolid || $overDashed) {
                    $dash = $overSolid ? '' : ' stroke-dasharray="3 2"';
                    $lines .= sprintf('<line x1="%0.2f" y1="%0.2f" x2="%0.2f" y2="%0.2f" stroke="#374151" stroke-width="1"%s />', $leftX, $overY, $rightX, $overY, $dash);
                }

                // Underline (observed)
                $underSolid = $userLoggedIn && $observedByUser;
                $underDashed = !$underSolid && $observedAny;
                if ($underSolid || $underDashed) {
                    $dash = $underSolid ? '' : ' stroke-dasharray="3 2"';
                    $lines .= sprintf('<line x1="%0.2f" y1="%0.2f" x2="%0.2f" y2="%0.2f" stroke="#374151" stroke-width="1"%s />', $leftX, $underY, $rightX, $underY, $dash);
                }

                $text = sprintf('<text x="%0.2f" y="%0.2f" font-size="%0.1f" fill="#4b5563" text-anchor="start">%s</text>', $box['left'], $textBaseline, $fontSize, htmlspecialchars($trimmed, ENT_QUOTES, 'UTF-8'));

                return '<g>' . $lines . $text . '</g>';
            }
        }

        // Fallback: spiral search outward until a free slot is found.
        $angles = [];
        for ($a = 0; $a < 360; $a += 30) {
            $angles[] = deg2rad($a);
        }

        foreach ([20, 40, 60, 90] as $radius) {
            foreach ($angles as $angle) {
                $lx = $x + cos($angle) * $radius - ($textWidth / 2.0);
                $ly = $y + sin($angle) * $radius;
                $box = $tryBox($lx, $ly);
                $ok = true;
                foreach ($occupied as $occ) {
                    if ($intersects($box, $occ)) {
                        $ok = false;
                        break;
                    }
                }
                if ($ok) {
                    $occupied[] = $box;

                    $textBaseline = $box['bottom'] - 3.0;
                    $overY = $box['top'] + 2.0;
                    $underY = $textBaseline + 3.0;
                    $leftX = $box['left'];
                    $rightX = $box['right'];

                    $lines = '';
                    $overSolid = $userLoggedIn && $drawnByUser;
                    $overDashed = !$overSolid && $drawnAny;
                    if ($overSolid || $overDashed) {
                        $dash = $overSolid ? '' : ' stroke-dasharray="3 2"';
                        $lines .= sprintf('<line x1="%0.2f" y1="%0.2f" x2="%0.2f" y2="%0.2f" stroke="#374151" stroke-width="1"%s />', $leftX, $overY, $rightX, $overY, $dash);
                    }
                    $underSolid = $userLoggedIn && $observedByUser;
                    $underDashed = !$underSolid && $observedAny;
                    if ($underSolid || $underDashed) {
                        $dash = $underSolid ? '' : ' stroke-dasharray="3 2"';
                        $lines .= sprintf('<line x1="%0.2f" y1="%0.2f" x2="%0.2f" y2="%0.2f" stroke="#374151" stroke-width="1"%s />', $leftX, $underY, $rightX, $underY, $dash);
                    }

                    $text = sprintf('<text x="%0.2f" y="%0.2f" font-size="%0.1f" fill="#4b5563" text-anchor="start">%s</text>', $box['left'], $textBaseline, $fontSize, htmlspecialchars($trimmed, ENT_QUOTES, 'UTF-8'));

                    return '<g>' . $lines . $text . '</g>';
                }
            }
        }

        // As a last resort, place at the original default position (may overlap).
        $defaultBox = $tryBox($x + 10.0, $y - 7.0);
        $occupied[] = $defaultBox;

        $textBaseline = $defaultBox['bottom'] - 3.0;
        $overY = $defaultBox['top'] + 2.0;
        $underY = $textBaseline + 3.0;
        $leftX = $defaultBox['left'];
        $rightX = $defaultBox['right'];

        $lines = '';
        $overSolid = $userLoggedIn && $drawnByUser;
        $overDashed = !$overSolid && $drawnAny;
        if ($overSolid || $overDashed) {
            $dash = $overSolid ? '' : ' stroke-dasharray="3 2"';
            $lines .= sprintf('<line x1="%0.2f" y1="%0.2f" x2="%0.2f" y2="%0.2f" stroke="#374151" stroke-width="1"%s />', $leftX, $overY, $rightX, $overY, $dash);
        }
        $underSolid = $userLoggedIn && $observedByUser;
        $underDashed = !$underSolid && $observedAny;
        if ($underSolid || $underDashed) {
            $dash = $underSolid ? '' : ' stroke-dasharray="3 2"';
            $lines .= sprintf('<line x1="%0.2f" y1="%0.2f" x2="%0.2f" y2="%0.2f" stroke="#374151" stroke-width="1"%s />', $leftX, $underY, $rightX, $underY, $dash);
        }

        $text = sprintf('<text x="%0.2f" y="%0.2f" font-size="%0.1f" fill="#4b5563" text-anchor="start">%s</text>', $defaultBox['left'], $textBaseline, $fontSize, htmlspecialchars($trimmed, ENT_QUOTES, 'UTF-8'));

        return '<g>' . $lines . $text . '</g>';
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
        ,
        ?array $chartRect = null
    ): string {
        $code = $this->normalizeObjectTypeCode($type);
        $majorRadiusPx = $this->diameterArcSecToRadiusPx($diam1ArcMin, $arcMinToPx);
        $minorRadiusPx = $this->diameterArcSecToRadiusPx($diam2ArcMin, $arcMinToPx);
        $rotationTransform = $this->rotationTransform($positionAngleDeg, $x, $y);

        return match ($code) {
            'GALXY', 'GX', 'GXAGC', 'GALCL' => $this->renderGalaxySymbol($x, $y, $diam1ArcMin, $diam2ArcMin, $positionAngleDeg, $arcMinToPx, $chartRect),
            'DS' => $this->renderDoubleStarSymbol($x, $y, $diam1ArcMin, $positionAngleDeg, $magnitude, $arcMinToPx, $chartRect),
            'AA1STAR', 'SNOVA' => $this->renderPointSourceSymbol($x, $y, $magnitude, $chartRect),
            // Render asterisms using the same dashed-circle symbol as open clusters
            // (Lederman 1 is an asterism but should appear as an open cluster).
            'ASTER' => $this->renderClippedCircle($x, $y, max(4.5, $majorRadiusPx ?? 5.0), $chartRect, '#666', 1.0, '2 2'),
            'GLOCL' => $this->renderGlobularClusterSymbol($x, $y, $majorRadiusPx ?? 5.0, $chartRect),
            'OPNCL' => $this->renderClippedCircle($x, $y, max(4.5, $majorRadiusPx ?? 5.0), $chartRect, '#666', 1.0, '2 2'),
            'DRKNB' => $this->renderClippedRect(
                $x - max(4.5, $majorRadiusPx ?? 5.0),
                $y - max(4.5, $minorRadiusPx ?? $majorRadiusPx ?? 5.0),
                max(9.0, 2 * (float) ($majorRadiusPx ?? 5.0)),
                max(9.0, 2 * (float) ($minorRadiusPx ?? $majorRadiusPx ?? 5.0)),
                null,
                $chartRect,
                '#666',
                1.0,
                '2 2'
            ),
            'PLNNB' => $this->renderPlnnbSymbol($x, $y, $majorRadiusPx, $minorRadiusPx, $positionAngleDeg, $arcMinToPx, $chartRect),
            'CLANB', 'EMINB', 'HII', 'RNHII', 'REFNB', 'BRTNB', 'WRNEB', 'STNEB', 'ENRNN' => $this->renderNebSymbol($x, $y, $majorRadiusPx, $minorRadiusPx, $positionAngleDeg, $chartRect),
            'SNREM' => $this->renderSupernovaRemnantSymbol($x, $y, $diam1ArcMin, $diam2ArcMin, $positionAngleDeg, $arcMinToPx, $chartRect),
            'QUASR' => $this->renderClippedCross($x, $y, 5.0, $chartRect),
            default => ($majorRadiusPx !== null)
            ? $this->renderClippedEllipse(
                $x,
                $y,
                max(3.5, $majorRadiusPx),
                max(3.0, $minorRadiusPx ?? $majorRadiusPx * 0.8),
                $positionAngleDeg,
                $chartRect,
                '#666',
                1.0,
                ''
            )
            : $this->renderClippedPolygonFromPoints([
                [$x, $y - 5],
                [$x + 5, $y],
                [$x, $y + 5],
                [$x - 5, $y],
            ], $chartRect, '#666', 1.0, ''),
        };
    }

    private function normalizeObjectTypeCode(string $type): string
    {
        $code = strtoupper(trim($type));

        return match ($code) {
            'G', 'GALAXY', 'GXADN', 'GACAN' => 'GALXY',
            'AA1STAR', 'SNOVA' => $code,
            'HII', 'RNHII', 'EMINB', 'REFNB', 'BRTNB', 'WRNEB', 'STNEB', 'ENRNN' => $code,
            default => $code,
        };
    }

    private function renderPointSourceSymbol(float $x, float $y, ?float $magnitude, ?array $chartRect = null): string
    {
        $r = $this->starRadius($magnitude) + 0.5;

        if ($chartRect === null) {
            return sprintf(
                '<circle cx="%0.2f" cy="%0.2f" r="%0.2f" fill="none" stroke="#666" stroke-width="1" /><line x1="%0.2f" y1="%0.2f" x2="%0.2f" y2="%0.2f" stroke="#666" stroke-width="0.8" /><line x1="%0.2f" y1="%0.2f" x2="%0.2f" y2="%0.2f" stroke="#666" stroke-width="0.8" />',
                $x,
                $y,
                $r,
                $x - ($r + 2.0),
                $y,
                $x + ($r + 2.0),
                $y,
                $x,
                $y - ($r + 2.0),
                $x,
                $y + ($r + 2.0)
            );
        }

        $svg = [];
        $circlePath = $this->renderClippedCircle($x, $y, $r, $chartRect, '#666', 1.0, '');
        if ($circlePath !== '') {
            $svg[] = $circlePath;
        }

        $h = $this->clipLineToRect($x - ($r + 2.0), $y, $x + ($r + 2.0), $y, $chartRect);
        if ($h !== null) {
            $svg[] = sprintf('<line x1="%0.2f" y1="%0.2f" x2="%0.2f" y2="%0.2f" stroke="#666" stroke-width="0.8" />', $h['x1'], $h['y1'], $h['x2'], $h['y2']);
        }

        $v = $this->clipLineToRect($x, $y - ($r + 2.0), $x, $y + ($r + 2.0), $chartRect);
        if ($v !== null) {
            $svg[] = sprintf('<line x1="%0.2f" y1="%0.2f" x2="%0.2f" y2="%0.2f" stroke="#666" stroke-width="0.8" />', $v['x1'], $v['y1'], $v['x2'], $v['y2']);
        }

        return implode('', $svg);
    }

    private function renderAsterismSymbol(float $x, float $y, ?float $majorRadiusPx, ?array $chartRect = null): string
    {
        $r = max(4.5, $majorRadiusPx ?? 6.0);
        $p1x = $x - $r;
        $p1y = $y + ($r * 0.6);
        $p2x = $x;
        $p2y = $y - $r;
        $p3x = $x + $r;
        $p3y = $y + ($r * 0.5);

        if ($chartRect === null) {
            return sprintf(
                '<polyline points="%0.2f,%0.2f %0.2f,%0.2f %0.2f,%0.2f" fill="none" stroke="#666" stroke-width="1" /><circle cx="%0.2f" cy="%0.2f" r="1.1" fill="#666" /><circle cx="%0.2f" cy="%0.2f" r="1.1" fill="#666" /><circle cx="%0.2f" cy="%0.2f" r="1.1" fill="#666" />',
                $p1x,
                $p1y,
                $p2x,
                $p2y,
                $p3x,
                $p3y,
                $p1x,
                $p1y,
                $p2x,
                $p2y,
                $p3x,
                $p3y
            );
        }

        $svg = [];
        // Clip polyline segments
        $seg = $this->clipLineToRect($p1x, $p1y, $p2x, $p2y, $chartRect);
        if ($seg !== null) {
            $svg[] = sprintf('M %0.2f %0.2f L %0.2f %0.2f', $seg['x1'], $seg['y1'], $seg['x2'], $seg['y2']);
        }
        $seg = $this->clipLineToRect($p2x, $p2y, $p3x, $p3y, $chartRect);
        if ($seg !== null) {
            $svg[] = sprintf('M %0.2f %0.2f L %0.2f %0.2f', $seg['x1'], $seg['y1'], $seg['x2'], $seg['y2']);
        }

        // Small filled markers only if fully inside
        $markerR = 1.1;
        foreach ([[$p1x, $p1y], [$p2x, $p2y], [$p3x, $p3y]] as [$mx, $my]) {
            if ($mx - $markerR >= $chartRect['min_x'] && $mx + $markerR <= $chartRect['max_x'] && $my - $markerR >= $chartRect['min_y'] && $my + $markerR <= $chartRect['max_y']) {
                $svg[] = sprintf('<circle cx="%0.2f" cy="%0.2f" r="1.1" fill="#666" />', $mx, $my);
            }
        }

        if (empty($svg)) {
            return '';
        }

        return implode('', $svg);
    }

    private function renderSupernovaRemnantSymbol(
        float $x,
        float $y,
        ?float $majorDiamArcSec,
        ?float $minorDiamArcSec,
        ?float $positionAngleDeg,
        float $arcMinToPx,
        ?array $chartRect = null
    ): string {
        $majorRadiusPx = $this->diameterArcSecToRadiusPx($majorDiamArcSec, $arcMinToPx);
        $minorRadiusPx = $this->diameterArcSecToRadiusPx($minorDiamArcSec, $arcMinToPx);

        if ($majorRadiusPx === null && $minorRadiusPx === null) {
            return sprintf('<rect x="%0.2f" y="%0.2f" width="10" height="10" fill="none" stroke="#666" stroke-width="1.2" />', $x - 5, $y - 5);
        }

        if ($majorRadiusPx !== null && $minorRadiusPx !== null && $minorRadiusPx > $majorRadiusPx) {
            [$majorRadiusPx, $minorRadiusPx] = [$minorRadiusPx, $majorRadiusPx];
        }

        $majorRadiusPx = $majorRadiusPx === null ? 6.0 : max(4.0, $majorRadiusPx);
        $minorRadiusPx = $minorRadiusPx === null ? max(3.0, $majorRadiusPx * 0.7) : max(3.0, $minorRadiusPx);

        // Unknown PA should not imply a fixed shared orientation.
        if ($positionAngleDeg === null) {
            $minorRadiusPx = $majorRadiusPx;
        }

        $halfWidth = $majorRadiusPx;
        $halfHeight = $minorRadiusPx;

        // If no chartRect provided, fall back to drawing a raw rect with rotation.
        if ($chartRect === null) {
            $rotationTransform = $this->rotationTransform($positionAngleDeg, $x, $y);
            return sprintf(
                '<rect x="%0.2f" y="%0.2f" width="%0.2f" height="%0.2f" fill="none" stroke="#666" stroke-width="1.2"%s />',
                $x - $halfWidth,
                $y - $halfHeight,
                $halfWidth * 2.0,
                $halfHeight * 2.0,
                $rotationTransform
            );
        }

        // Render rectangle as clipped line segments so PDF generators that do not
        // honor clipPath still only show the portion inside the chart rect.
        $theta = $positionAngleDeg === null ? 0.0 : $positionAngleDeg;
        $rad = deg2rad($theta);
        $cos = cos($rad);
        $sin = sin($rad);

        $corners = [
            [$x - $halfWidth, $y - $halfHeight],
            [$x + $halfWidth, $y - $halfHeight],
            [$x + $halfWidth, $y + $halfHeight],
            [$x - $halfWidth, $y + $halfHeight],
        ];

        // Rotate corners around center
        $rotated = [];
        foreach ($corners as [$cx, $cy]) {
            $dx = $cx - $x;
            $dy = $cy - $y;
            $rx = $x + ($dx * $cos) - ($dy * $sin);
            $ry = $y + ($dx * $sin) + ($dy * $cos);
            $rotated[] = ['x' => $rx, 'y' => $ry];
        }

        $pathParts = [];
        // clip each edge to chartRect
        for ($i = 0; $i < 4; $i++) {
            $p1 = $rotated[$i];
            $p2 = $rotated[($i + 1) % 4];
            $clipped = $this->clipLineToRect((float) $p1['x'], (float) $p1['y'], (float) $p2['x'], (float) $p2['y'], $chartRect);
            if ($clipped !== null) {
                $pathParts[] = sprintf('M %0.2f %0.2f L %0.2f %0.2f', $clipped['x1'], $clipped['y1'], $clipped['x2'], $clipped['y2']);
            }
        }

        if (empty($pathParts)) {
            return '';
        }

        return sprintf('<path d="%s" fill="none" stroke="#666" stroke-width="1.2" />', implode(' ', $pathParts));
    }

    private function renderDoubleStarSymbol(float $x, float $y, ?float $separationArcSec, ?float $positionAngleDeg, ?float $magnitude, float $arcMinToPx, ?array $chartRect = null): string
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

        $svg = [];
        $r = 1.8;
        // Filled dot only when fully inside
        if ($chartRect === null || ($x - $r >= $chartRect['min_x'] && $x + $r <= $chartRect['max_x'] && $y - $r >= $chartRect['min_y'] && $y + $r <= $chartRect['max_y'])) {
            $svg[] = sprintf('<circle cx="%0.2f" cy="%0.2f" r="%0.2f" fill="#666" />', $x, $y, $r);
        } else {
            $circleOutline = $this->renderClippedCircle($x, $y, $r, $chartRect, '#666', 1.0, '');
            if ($circleOutline !== '') {
                $svg[] = $circleOutline;
            }
        }

        $lineSeg = $this->clipLineToRect($x, $y, $endX, $endY, $chartRect);
        if ($lineSeg !== null) {
            $svg[] = sprintf('<line x1="%0.2f" y1="%0.2f" x2="%0.2f" y2="%0.2f" stroke="#666" stroke-width="%0.2f" stroke-linecap="round" />', $lineSeg['x1'], $lineSeg['y1'], $lineSeg['x2'], $lineSeg['y2'], $strokeWidth);
        }

        // Render arrow-head as clipped outline segments (no fill) to avoid spilling
        $p1x = $endX;
        $p1y = $endY;
        $p2x = $leftX - (($dx / max(1.0, $separationPx)) * $headSize);
        $p2y = $leftY - (($dy / max(1.0, $separationPx)) * $headSize);
        $p3x = $rightX - (($dx / max(1.0, $separationPx)) * $headSize);
        $p3y = $rightY - (($dy / max(1.0, $separationPx)) * $headSize);

        $edges = [[$p1x, $p1y, $p2x, $p2y], [$p2x, $p2y, $p3x, $p3y], [$p3x, $p3y, $p1x, $p1y]];
        $pathParts = [];
        foreach ($edges as [$ax, $ay, $bx, $by]) {
            $cl = $this->clipLineToRect($ax, $ay, $bx, $by, $chartRect);
            if ($cl !== null) {
                $pathParts[] = sprintf('M %0.2f %0.2f L %0.2f %0.2f', $cl['x1'], $cl['y1'], $cl['x2'], $cl['y2']);
            }
        }

        if (!empty($pathParts)) {
            $svg[] = sprintf('<path d="%s" fill="none" stroke="#666" stroke-width="1" />', implode(' ', $pathParts));
        }

        return implode('', $svg);
    }

    private function renderGalaxySymbol(float $x, float $y, ?float $majorDiamArcMin, ?float $minorDiamArcMin, ?float $positionAngleDeg, float $arcMinToPx, ?array $chartRect = null): string
    {
        $majorRadiusPx = $this->diameterArcSecToRadiusPx($majorDiamArcMin, $arcMinToPx);
        $minorRadiusPx = $this->diameterArcSecToRadiusPx($minorDiamArcMin, $arcMinToPx);

        if ($majorRadiusPx === null && $minorRadiusPx === null) {
            return $this->renderClippedEllipse($x, $y, 7.0, 4.0, $positionAngleDeg, $chartRect, '#666', 1.0, '');
        }

        if ($majorRadiusPx !== null && $minorRadiusPx !== null && $minorRadiusPx > $majorRadiusPx) {
            [$majorRadiusPx, $minorRadiusPx] = [$minorRadiusPx, $majorRadiusPx];
        }

        $majorRadiusPx = $majorRadiusPx === null ? 7.0 : max(1.0, $majorRadiusPx);
        $minorRadiusPx = $minorRadiusPx === null ? max(0.8, $majorRadiusPx * 0.8) : max(0.8, $minorRadiusPx);

        // Unknown PA should not imply a shared orientation; draw circular fallback.
        if ($positionAngleDeg === null) {
            $minorRadiusPx = $majorRadiusPx;
        }

        return $this->renderClippedEllipse($x, $y, $majorRadiusPx, $minorRadiusPx, $positionAngleDeg, $chartRect, '#666', 1.0, '');
    }

    private function rotationTransform(?float $positionAngleDeg, float $x, float $y): string
    {
        if ($positionAngleDeg === null) {
            return '';
        }

        $rotation = 90.0 - $positionAngleDeg;

        return sprintf(' transform="rotate(%0.2f %0.2f %0.2f)"', $rotation, $x, $y);
    }

    private function diameterArcSecToRadiusPx(?float $diameterArcSec, float $arcMinToPx): ?float
    {
        if ($diameterArcSec === null || $diameterArcSec <= 0.0) {
            return null;
        }

        // Legacy diameters are stored in arcseconds.
        $diameterArcMin = $diameterArcSec / 60.0;

        return ($diameterArcMin * $arcMinToPx) / 2.0;
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
        $keys = ['GALXY', 'PLNNB', 'GLOCL', 'OPNCL', 'DRKNB', 'SNREM', 'CLANB', 'GALCL', 'QUASR'];
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
            'SNREM' => sprintf('<rect x="%0.2f" y="%0.2f" width="11" height="7.6" fill="none" stroke="#666" stroke-width="1.1" />', $x - 5.5, $y - 3.8),
            'CLANB' => sprintf('<rect x="%0.2f" y="%0.2f" width="9" height="9" fill="none" stroke="#666" stroke-width="1" /><circle cx="%0.2f" cy="%0.2f" r="4.5" fill="none" stroke="#666" stroke-width="1" />', $x - 4.5, $y - 4.5, $x, $y),
            'GALCL' => sprintf('<polygon points="%0.2f,%0.2f %0.2f,%0.2f %0.2f,%0.2f %0.2f,%0.2f" fill="none" stroke="#666" stroke-width="1" />', $x, $y - 4.5, $x + 4.5, $y, $x, $y + 4.5, $x - 4.5, $y),
            'QUASR' => sprintf('<line x1="%0.2f" y1="%0.2f" x2="%0.2f" y2="%0.2f" stroke="#666" stroke-width="1" /><line x1="%0.2f" y1="%0.2f" x2="%0.2f" y2="%0.2f" stroke="#666" stroke-width="1" />', $x - 4.5, $y, $x + 4.5, $y, $x, $y - 4.5, $x, $y + 4.5),
            default => sprintf('<polygon points="%0.2f,%0.2f %0.2f,%0.2f %0.2f,%0.2f %0.2f,%0.2f" fill="none" stroke="#666" stroke-width="1" />', $x, $y - 4.5, $x + 4.5, $y, $x, $y + 4.5, $x - 4.5, $y),
        };
    }

    private function renderClippedCircle(float $x, float $y, float $radius, ?array $chartRect = null, string $stroke = '#666', float $strokeWidth = 1.0, string $strokeDash = ''): string
    {
        if ($chartRect === null) {
            $dashAttr = $strokeDash !== '' ? ' stroke-dasharray="' . $strokeDash . '"' : '';
            return sprintf('<circle cx="%0.2f" cy="%0.2f" r="%0.2f" fill="none" stroke="%s" stroke-width="%0.1f"%s />', $x, $y, $radius, $stroke, $strokeWidth, $dashAttr);
        }

        $steps = 72;
        $segments = [];
        for ($s = 0; $s < $steps; $s++) {
            $a0 = 2.0 * M_PI * ($s / $steps);
            $a1 = 2.0 * M_PI * (($s + 1) / $steps);

            $x0 = $x + cos($a0) * $radius;
            $y0 = $y + sin($a0) * $radius;
            $x1 = $x + cos($a1) * $radius;
            $y1 = $y + sin($a1) * $radius;

            $clipped = $this->clipLineToRect($x0, $y0, $x1, $y1, $chartRect);
            if ($clipped !== null) {
                $segments[] = sprintf('M %0.2f %0.2f L %0.2f %0.2f', $clipped['x1'], $clipped['y1'], $clipped['x2'], $clipped['y2']);
            }
        }

        if (empty($segments)) {
            return '';
        }

        $dashAttr = $strokeDash !== '' ? ' stroke-dasharray="' . $strokeDash . '"' : '';
        return sprintf('<path d="%s" fill="none" stroke="%s" stroke-width="%0.1f"%s />', implode(' ', $segments), $stroke, $strokeWidth, $dashAttr);
    }

    private function renderGlobularClusterSymbol(float $x, float $y, float $majorRadiusPx, ?array $chartRect = null): string
    {
        $r = max(4.5, $majorRadiusPx ?? 5.0);
        $parts = [];

        $circle = $this->renderClippedCircle($x, $y, $r, $chartRect, '#666', 1.0, '');
        if ($circle !== '') {
            $parts[] = $circle;
        }

        $len = max(6.0, ($majorRadiusPx ?? 5.0) + 1.0);

        $hclipped = $this->clipLineToRect($x - $len, $y, $x + $len, $y, $chartRect);
        if ($hclipped !== null) {
            $parts[] = sprintf('<line x1="%0.2f" y1="%0.2f" x2="%0.2f" y2="%0.2f" stroke="#666" stroke-width="0.8" />', $hclipped['x1'], $hclipped['y1'], $hclipped['x2'], $hclipped['y2']);
        }

        $vclipped = $this->clipLineToRect($x, $y - $len, $x, $y + $len, $chartRect);
        if ($vclipped !== null) {
            $parts[] = sprintf('<line x1="%0.2f" y1="%0.2f" x2="%0.2f" y2="%0.2f" stroke="#666" stroke-width="0.8" />', $vclipped['x1'], $vclipped['y1'], $vclipped['x2'], $vclipped['y2']);
        }

        return implode('', $parts);
    }

    private function renderClippedEllipse(float $x, float $y, float $rx, float $ry, ?float $positionAngleDeg = null, ?array $chartRect = null, string $stroke = '#666', float $strokeWidth = 1.0, string $strokeDash = ''): string
    {
        if ($chartRect === null) {
            $rot = $positionAngleDeg === null ? 0.0 : 90.0 - $positionAngleDeg;
            $rotAttr = $positionAngleDeg === null ? '' : sprintf(' transform="rotate(%0.2f %0.2f %0.2f)"', $rot, $x, $y);
            $dashAttr = $strokeDash !== '' ? ' stroke-dasharray="' . $strokeDash . '"' : '';
            return sprintf('<ellipse cx="%0.2f" cy="%0.2f" rx="%0.2f" ry="%0.2f" fill="none" stroke="%s" stroke-width="%0.1f"%s%s />', $x, $y, $rx, $ry, $stroke, $strokeWidth, $rotAttr, $dashAttr);
        }

        $steps = 96;
        $segments = [];
        $rotation = $positionAngleDeg === null ? 0.0 : (90.0 - $positionAngleDeg);
        $rotRad = deg2rad($rotation);
        $cosR = cos($rotRad);
        $sinR = sin($rotRad);

        for ($s = 0; $s < $steps; $s++) {
            $a0 = 2.0 * M_PI * ($s / $steps);
            $a1 = 2.0 * M_PI * (($s + 1) / $steps);

            $u0 = cos($a0) * $rx;
            $v0 = sin($a0) * $ry;
            $u1 = cos($a1) * $rx;
            $v1 = sin($a1) * $ry;

            $x0 = $x + ($u0 * $cosR - $v0 * $sinR);
            $y0 = $y + ($u0 * $sinR + $v0 * $cosR);
            $x1 = $x + ($u1 * $cosR - $v1 * $sinR);
            $y1 = $y + ($u1 * $sinR + $v1 * $cosR);

            $cl = $this->clipLineToRect($x0, $y0, $x1, $y1, $chartRect);
            if ($cl !== null) {
                $segments[] = sprintf('M %0.2f %0.2f L %0.2f %0.2f', $cl['x1'], $cl['y1'], $cl['x2'], $cl['y2']);
            }
        }

        if (empty($segments)) {
            return '';
        }

        $dashAttr = $strokeDash !== '' ? ' stroke-dasharray="' . $strokeDash . '"' : '';
        return sprintf('<path d="%s" fill="none" stroke="%s" stroke-width="%0.1f"%s />', implode(' ', $segments), $stroke, $strokeWidth, $dashAttr);
    }

    private function renderClippedRect(float $x, float $y, float $width, float $height, ?float $rotationDeg = null, ?array $chartRect = null, string $stroke = '#666', float $strokeWidth = 1.0, string $strokeDash = ''): string
    {
        // If no chartRect provided, render raw rect (with rotation if present)
        if ($chartRect === null) {
            $rotAttr = $rotationDeg === null ? '' : sprintf(' transform="rotate(%0.2f %0.2f %0.2f)"', $rotationDeg, $x + ($width / 2.0), $y + ($height / 2.0));
            $dashAttr = $strokeDash !== '' ? ' stroke-dasharray="' . $strokeDash . '"' : '';
            return sprintf('<rect x="%0.2f" y="%0.2f" width="%0.2f" height="%0.2f" fill="none" stroke="%s" stroke-width="%0.1f"%s />', $x, $y, $width, $height, $stroke, $strokeWidth, $dashAttr . $rotAttr);
        }

        // Compute corners
        $cx = $x + ($width / 2.0);
        $cy = $y + ($height / 2.0);
        $corners = [
            [$x, $y],
            [$x + $width, $y],
            [$x + $width, $y + $height],
            [$x, $y + $height],
        ];

        if ($rotationDeg !== null) {
            $rad = deg2rad($rotationDeg);
            $cos = cos($rad);
            $sin = sin($rad);
            $rotated = [];
            foreach ($corners as [$px, $py]) {
                $dx = $px - $cx;
                $dy = $py - $cy;
                $rx = $cx + ($dx * $cos) - ($dy * $sin);
                $ry = $cy + ($dx * $sin) + ($dy * $cos);
                $rotated[] = [$rx, $ry];
            }
            $corners = $rotated;
        }

        $parts = [];
        for ($i = 0; $i < 4; $i++) {
            [$x1, $y1] = $corners[$i];
            [$x2, $y2] = $corners[($i + 1) % 4];
            $cl = $this->clipLineToRect($x1, $y1, $x2, $y2, $chartRect);
            if ($cl !== null) {
                $parts[] = sprintf('M %0.2f %0.2f L %0.2f %0.2f', $cl['x1'], $cl['y1'], $cl['x2'], $cl['y2']);
            }
        }

        if (empty($parts)) {
            return '';
        }

        $dashAttr = $strokeDash !== '' ? ' stroke-dasharray="' . $strokeDash . '"' : '';
        return sprintf('<path d="%s" fill="none" stroke="%s" stroke-width="%0.1f"%s />', implode(' ', $parts), $stroke, $strokeWidth, $dashAttr);
    }

    private function renderClippedPolygonFromPoints(array $points, ?array $chartRect = null, string $stroke = '#666', float $strokeWidth = 1.0, string $strokeDash = ''): string
    {
        if (empty($points)) {
            return '';
        }

        if ($chartRect === null) {
            $pts = array_map(fn($p) => sprintf('%0.2f,%0.2f', $p[0], $p[1]), $points);
            $dashAttr = $strokeDash !== '' ? ' stroke-dasharray="' . $strokeDash . '"' : '';
            return sprintf('<polygon points="%s" fill="none" stroke="%s" stroke-width="%0.1f"%s />', implode(' ', $pts), $stroke, $strokeWidth, $dashAttr);
        }

        $parts = [];
        $n = count($points);
        for ($i = 0; $i < $n; $i++) {
            [$x1, $y1] = $points[$i];
            [$x2, $y2] = $points[($i + 1) % $n];
            $cl = $this->clipLineToRect($x1, $y1, $x2, $y2, $chartRect);
            if ($cl !== null) {
                $parts[] = sprintf('M %0.2f %0.2f L %0.2f %0.2f', $cl['x1'], $cl['y1'], $cl['x2'], $cl['y2']);
            }
        }

        if (empty($parts)) {
            return '';
        }

        $dashAttr = $strokeDash !== '' ? ' stroke-dasharray="' . $strokeDash . '"' : '';
        return sprintf('<path d="%s" fill="none" stroke="%s" stroke-width="%0.1f"%s />', implode(' ', $parts), $stroke, $strokeWidth, $dashAttr);
    }

    private function renderPlnnbSymbol(float $x, float $y, ?float $majorRadiusPx, ?float $minorRadiusPx, ?float $positionAngleDeg, float $arcMinToPx, ?array $chartRect = null): string
    {
        $rx = max(3.8, $majorRadiusPx ?? 4.5);
        $ry = max(3.0, $minorRadiusPx ?? $majorRadiusPx ?? 4.5);
        $out = [];
        $ellipse = $this->renderClippedEllipse($x, $y, $rx, $ry, $positionAngleDeg, $chartRect, '#666', 1.0, '');
        if ($ellipse !== '') {
            $out[] = $ellipse;
        }

        $len = max(7.0, ($majorRadiusPx ?? 4.5) + 2.5);
        $h = $this->clipLineToRect($x - $len, $y, $x + $len, $y, $chartRect);
        if ($h !== null) {
            $out[] = sprintf('<line x1="%0.2f" y1="%0.2f" x2="%0.2f" y2="%0.2f" stroke="#666" stroke-width="0.9" />', $h['x1'], $h['y1'], $h['x2'], $h['y2']);
        }

        return implode('', $out);
    }

    private function renderNebSymbol(float $x, float $y, ?float $majorRadiusPx, ?float $minorRadiusPx, ?float $positionAngleDeg, ?array $chartRect = null): string
    {
        $rx = max(4.5, $majorRadiusPx ?? 5.0);
        $ry = max(3.8, $minorRadiusPx ?? $majorRadiusPx ?? 4.2);
        $ellipse = $this->renderClippedEllipse($x, $y, $rx, $ry, $positionAngleDeg, $chartRect, '#666', 1.0, '2 2');
        $rect = $this->renderClippedRect($x - 2.0, $y - 2.0, 4.0, 4.0, null, $chartRect, '#666', 0.8, '');

        return $ellipse . $rect;
    }

    private function renderClippedCross(float $x, float $y, float $halfLen, ?array $chartRect = null): string
    {
        if ($chartRect === null) {
            return sprintf('<line x1="%0.2f" y1="%0.2f" x2="%0.2f" y2="%0.2f" stroke="#666" stroke-width="1" /><line x1="%0.2f" y1="%0.2f" x2="%0.2f" y2="%0.2f" stroke="#666" stroke-width="1" />', $x - $halfLen, $y, $x + $halfLen, $y, $x, $y - $halfLen, $x, $y + $halfLen);
        }

        $parts = [];
        $h = $this->clipLineToRect($x - $halfLen, $y, $x + $halfLen, $y, $chartRect);
        if ($h !== null) {
            $parts[] = sprintf('<line x1="%0.2f" y1="%0.2f" x2="%0.2f" y2="%0.2f" stroke="#666" stroke-width="1" />', $h['x1'], $h['y1'], $h['x2'], $h['y2']);
        }
        $v = $this->clipLineToRect($x, $y - $halfLen, $x, $y + $halfLen, $chartRect);
        if ($v !== null) {
            $parts[] = sprintf('<line x1="%0.2f" y1="%0.2f" x2="%0.2f" y2="%0.2f" stroke="#666" stroke-width="1" />', $v['x1'], $v['y1'], $v['x2'], $v['y2']);
        }

        return implode('', $parts);
    }

    private function renderPolarGrid(float $centerRaDeg, float $centerDecDeg, float $xHalfDeg, float $yHalfDeg, float $left, float $top, float $plotWidth, float $plotHeight): string
    {
        $svg = [];

        // In polar mode the coordinate grid must be centered on the celestial
        // pole, not on the target object. This keeps near-pole targets from
        // appearing artificially at the grid origin.
        $poleDec = $centerDecDeg >= 0.0 ? 90.0 : -90.0;
        $polePoint = $this->projectToAtlasChartUnclipped(
            $centerRaDeg,
            $poleDec,
            $centerRaDeg,
            $centerDecDeg,
            $xHalfDeg,
            $yHalfDeg,
            $left,
            $top,
            $plotWidth,
            $plotHeight
        );

        $cx = (float) $polePoint['x'];
        $cy = (float) $polePoint['y'];

        // When the pole lies outside the frame, use the farthest-corner
        // distance so concentric declination circles still cross the chart.
        $corners = [
            [$left, $top],
            [$left + $plotWidth, $top],
            [$left, $top + $plotHeight],
            [$left + $plotWidth, $top + $plotHeight],
        ];
        $radius = 0.0;
        foreach ($corners as [$cornerX, $cornerY]) {
            $radius = max($radius, hypot($cornerX - $cx, $cornerY - $cy));
        }

        $rect = [
            'min_x' => $left,
            'min_y' => $top,
            'max_x' => $left + $plotWidth,
            'max_y' => $top + $plotHeight,
        ];

        for ($i = 1; $i <= 4; $i++) {
            $r = $radius * ($i / 4);
            $segments = [];
            $steps = 240;

            for ($s = 0; $s < $steps; $s++) {
                $a0 = 2.0 * M_PI * ($s / $steps);
                $a1 = 2.0 * M_PI * (($s + 1) / $steps);

                $x0 = $cx + cos($a0) * $r;
                $y0 = $cy + sin($a0) * $r;
                $x1 = $cx + cos($a1) * $r;
                $y1 = $cy + sin($a1) * $r;

                $clipped = $this->clipLineToRect($x0, $y0, $x1, $y1, $rect);
                if ($clipped !== null) {
                    $segments[] = sprintf('M %0.2f %0.2f L %0.2f %0.2f', $clipped['x1'], $clipped['y1'], $clipped['x2'], $clipped['y2']);
                }
            }

            if ($segments !== []) {
                $svg[] = sprintf('<path d="%s" fill="none" stroke="#d9d9d9" stroke-width="0.6" />', implode(' ', $segments));
            }
        }

        for ($i = 0; $i < 12; $i++) {
            $angle = deg2rad($i * 30.0);
            $x = $cx + cos($angle) * $radius;
            $y = $cy + sin($angle) * $radius;
            $clipped = $this->clipLineToRect($cx, $cy, $x, $y, $rect);
            if ($clipped !== null) {
                $svg[] = sprintf('<line x1="%0.2f" y1="%0.2f" x2="%0.2f" y2="%0.2f" stroke="#d9d9d9" stroke-width="0.6" />', $clipped['x1'], $clipped['y1'], $clipped['x2'], $clipped['y2']);
            }
        }

        return implode('', $svg);
    }

    private function starRadius(?float $magnitude): float
    {
        if ($magnitude === null) {
            return 1.1;
        }

        // Scale across the full chart range (roughly mag 1..14) so stars near
        // the faint limit are small, while brighter stars pop clearly.
        $normalized = (14.0 - $magnitude) / 13.0;
        $normalized = max(0.0, min(1.0, $normalized));

        return 1.1 + (7.0 * ($normalized ** 1.15));
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
        // Keep the RA window physically correct at high declinations.
        // A large floor (e.g. 0.12) underestimates the required RA span near
        // the poles and drops many valid stars/objects from the chart.
        $cosDec = max(1.0e-6, abs(cos(deg2rad($centerDecDeg))));
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
