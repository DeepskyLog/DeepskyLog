<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The index maps that follow the title page of a generated atlas.
 *
 * They answer the question a printed atlas is otherwise bad at: which chart
 * do I turn to for this constellation. Each map is a polar projection of one
 * hemisphere carrying the atlas page grid, the page numbers, the constellation
 * figures and the naked-eye stars, so a reader recognises the sky on it and
 * reads off the page number underneath.
 *
 * The sky is split into a north and a south map, and each hemisphere into as
 * many right ascension sectors as it takes to keep the page numbers readable
 * -- two for the overview and lookup atlases, more for the detail atlas,
 * whose grid is far finer.
 */
class AtlasIndexPageService
{
    /** Chart cells one index map carries before the hemisphere is split further. */
    private const MAX_CELLS_PER_MAP = 280;

    /** How far past the equator a map reaches, so nothing falls between maps. */
    private const EQUATOR_OVERLAP_DEG = 6.0;

    /** How much right ascension neighbouring maps of a hemisphere share. */
    private const SECTOR_OVERLAP_DEG = 15.0;

    /** Faintest star plotted on an index map. */
    private const STAR_MAGNITUDE_LIMIT = 5.0;

    /** Star magnitudes shown in the legend, brightest first. */
    private const LEGEND_MAGNITUDES = [-1.0, 0.0, 1.0, 2.0, 3.0, 4.0, 5.0];

    /** Margin between the projected map and the edge of the drawing area. */
    private const PLOT_MARGIN = 26.0;

    public function __construct(private readonly StarChartService $starChartService)
    {
    }

    /**
     * The index maps of an atlas, as SVG documents on an atlas-sized canvas.
     *
     * @return array<int, string>
     */
    public function build(string $atlasType, string $paper, string $orientation): array
    {
        $variant = AtlasLayout::variant($atlasType, $orientation);
        if (! AtlasLayout::isVariant($variant)) {
            return [];
        }

        $sectors = $this->sectorCount($variant);
        $maps = [];

        foreach ([true, false] as $north) {
            for ($sector = 0; $sector < $sectors; $sector++) {
                $maps[] = [
                    'north' => $north,
                    'center_ra_deg' => (($sector + 0.5) * 360.0) / $sectors,
                    'half_width_deg' => (180.0 / $sectors) + self::SECTOR_OVERLAP_DEG,
                ];
            }
        }

        $svg = [];
        foreach ($maps as $index => $map) {
            $svg[] = $this->renderMap($variant, $atlasType, $paper, $orientation, $map, $index + 1, count($maps));
        }

        return $svg;
    }

    /**
     * How many right ascension sectors each hemisphere is split into.
     *
     * Two is the minimum, which is what a hemisphere on a single sheet would
     * cost in legibility: the whole circle of right ascension squeezed into
     * one disc leaves the cells too narrow to number. Beyond that the split
     * grows with the number of cells to keep.
     */
    private function sectorCount(int $variant): int
    {
        $cellsPerHemisphere = AtlasLayout::totalPages($variant) / 2.0;

        return max(2, (int) ceil($cellsPerHemisphere / self::MAX_CELLS_PER_MAP));
    }

    /**
     * @param  array{north: bool, center_ra_deg: float, half_width_deg: float}  $map
     */
    private function renderMap(int $variant, string $atlasType, string $paper, string $orientation, array $map, int $mapNumber, int $mapCount): string
    {
        $north = (bool) $map['north'];
        $centerRaDeg = (float) $map['center_ra_deg'];
        $halfWidthDeg = (float) $map['half_width_deg'];

        $layout = $this->layout($orientation);
        $width = (float) $layout['width'];
        $height = (float) $layout['height'];
        $left = (float) $layout['left'];
        $top = (float) $layout['top'];
        $plotWidth = (float) $layout['plot_width'];
        $plotHeight = (float) $layout['plot_height'];

        // Polar distance of the far edge of the map: past the equator, so the
        // two hemispheres overlap rather than meet.
        $maxPolarDeg = 90.0 + self::EQUATOR_OVERLAP_DEG;

        $frame = $this->fitFrame($centerRaDeg, $halfWidthDeg, $maxPolarDeg, $north, $left, $top, $plotWidth, $plotHeight);

        $svg = [];
        $svg[] = sprintf('<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d" viewBox="0 0 %d %d" preserveAspectRatio="xMidYMid meet">', $width, $height, $width, $height);
        $svg[] = '<rect width="100%" height="100%" fill="#ffffff" />';

        $svg[] = $this->renderHeading($atlasType, $paper, $orientation, $map, $mapNumber, $mapCount, $left, $width);
        $svg[] = $this->renderGraticule($frame, $map, $maxPolarDeg);
        $svg[] = $this->renderConstellationFigures($frame, $map, $maxPolarDeg);
        $svg[] = $this->renderStars($frame, $map, $maxPolarDeg);
        $svg[] = $this->renderCells($variant, $frame, $map);
        $svg[] = $this->renderDeclinationLabels($variant, $frame, $map, $maxPolarDeg);
        $svg[] = $this->renderConstellationNames($frame, $map, $maxPolarDeg);
        $svg[] = $this->renderRightAscensionLabels($frame, $map, $maxPolarDeg);
        $svg[] = $this->renderMagnitudeLegend($left, $height - 40.0);

        $svg[] = '</svg>';

        return implode('', $svg);
    }

    /**
     * The atlas page canvas an index map is drawn on.
     *
     * @return array<string, float>
     */
    private function layout(string $orientation): array
    {
        $portrait = strtolower(trim($orientation)) === 'portrait';
        $width = $portrait ? 756.0 : 1120.0;
        $height = $portrait ? 1120.0 : 780.0;
        $margin = 56.0;
        $top = 92.0;
        $bottom = 64.0;

        return [
            'width' => $width,
            'height' => $height,
            'left' => $margin,
            'top' => $top,
            'plot_width' => $width - (2 * $margin),
            'plot_height' => $height - $top - $bottom,
        ];
    }

    /**
     * Where the projected sector lands on the page.
     *
     * The projection produces coordinates in a unit disc centred on the pole.
     * A sector of that disc is a pie slice, and how well a pie slice fits a
     * sheet of paper depends on which way it points: a shallow one -- the
     * detail atlas cuts the sky into seven of them -- is more than four times
     * as wide as it is deep, so on a portrait page it has to be turned on its
     * side. Each quarter turn is tried and the one that lets the map be drawn
     * largest wins.
     *
     * @return array<string, float|bool>
     */
    private function fitFrame(float $centerRaDeg, float $halfWidthDeg, float $maxPolarDeg, bool $north, float $left, float $top, float $plotWidth, float $plotHeight): array
    {
        $available = [
            'x' => $plotWidth - (2 * self::PLOT_MARGIN),
            'y' => $plotHeight - (2 * self::PLOT_MARGIN),
        ];

        // The outline of the slice: its two straight edges meet at the pole,
        // which is the origin, and its arc runs between their far ends.
        $outline = [[0.0, 0.0]];
        $steps = 180;
        for ($i = 0; $i <= $steps; $i++) {
            $angle = deg2rad(-$halfWidthDeg + (($i / $steps) * 2 * $halfWidthDeg));
            $outline[] = [($north ? -1.0 : 1.0) * sin($angle), cos($angle)];
        }

        $best = null;

        foreach ([0.0, 90.0, 180.0, 270.0] as $turn) {
            $cos = cos(deg2rad($turn));
            $sin = sin(deg2rad($turn));

            $minX = $maxX = $minY = $maxY = null;
            foreach ($outline as [$x, $y]) {
                $rotatedX = ($x * $cos) - ($y * $sin);
                $rotatedY = ($x * $sin) + ($y * $cos);

                $minX = $minX === null ? $rotatedX : min($minX, $rotatedX);
                $maxX = $maxX === null ? $rotatedX : max($maxX, $rotatedX);
                $minY = $minY === null ? $rotatedY : min($minY, $rotatedY);
                $maxY = $maxY === null ? $rotatedY : max($maxY, $rotatedY);
            }

            $spanX = max(1.0e-6, $maxX - $minX);
            $spanY = max(1.0e-6, $maxY - $minY);
            $scale = min($available['x'] / $spanX, $available['y'] / $spanY);

            if ($best === null || $scale > $best['scale']) {
                $best = [
                    'turn' => $turn,
                    'scale' => $scale,
                    'origin_x' => $left + (($plotWidth - ($spanX * $scale)) / 2.0) - ($minX * $scale),
                    'origin_y' => $top + (($plotHeight - ($spanY * $scale)) / 2.0) - ($minY * $scale),
                ];
            }
        }

        return [
            'north' => $north,
            'center_ra_deg' => $centerRaDeg,
            'half_width_deg' => $halfWidthDeg,
            'max_polar_deg' => $maxPolarDeg,
            'turn_deg' => $best['turn'],
            'scale' => $best['scale'],
            'origin_x' => $best['origin_x'],
            'origin_y' => $best['origin_y'],
        ];
    }

    /**
     * Project a position onto the map, or null when it falls off it.
     *
     * An azimuthal equal-area projection about the pole: every chart cell of
     * a declination band covers the same area of paper wherever it is in
     * right ascension, which is what makes a page grid drawn on it look even.
     *
     * @param  array<string, float|bool>  $frame
     * @return array{x: float, y: float}|null
     */
    private function project(array $frame, float $raDeg, float $decDeg): ?array
    {
        $north = (bool) $frame['north'];
        $polarDeg = $north ? 90.0 - $decDeg : 90.0 + $decDeg;

        if ($polarDeg < 0.0 || $polarDeg > (float) $frame['max_polar_deg']) {
            return null;
        }

        $rho = sin(deg2rad($polarDeg / 2.0)) / sin(deg2rad(((float) $frame['max_polar_deg']) / 2.0));
        $angle = deg2rad($this->raDelta($raDeg, (float) $frame['center_ra_deg']));

        // Right ascension runs the other way round the southern pole, which
        // is what the sign does; beyond that the map is only ever turned by
        // whole quarters, to fit the paper.
        $x = ($north ? -1.0 : 1.0) * $rho * sin($angle);
        $y = $rho * cos($angle);

        $turn = deg2rad((float) $frame['turn_deg']);
        $scale = (float) $frame['scale'];

        return [
            'x' => ((float) $frame['origin_x']) + ((($x * cos($turn)) - ($y * sin($turn))) * $scale),
            'y' => ((float) $frame['origin_y']) + ((($x * sin($turn)) + ($y * cos($turn))) * $scale),
        ];
    }

    /**
     * Signed difference between two right ascensions, in degrees (-180..180].
     */
    private function raDelta(float $raDeg, float $referenceDeg): float
    {
        $delta = fmod($raDeg - $referenceDeg, 360.0);

        if ($delta <= -180.0) {
            $delta += 360.0;
        }
        if ($delta > 180.0) {
            $delta -= 360.0;
        }

        return $delta;
    }

    /**
     * Whether a position is inside the part of the sky this map shows.
     *
     * @param  array{north: bool, center_ra_deg: float, half_width_deg: float}  $map
     */
    private function covers(array $map, float $raDeg, float $decDeg, float $maxPolarDeg): bool
    {
        $polarDeg = $map['north'] ? 90.0 - $decDeg : 90.0 + $decDeg;

        if ($polarDeg < 0.0 || $polarDeg > $maxPolarDeg) {
            return false;
        }

        return abs($this->raDelta($raDeg, (float) $map['center_ra_deg'])) <= (float) $map['half_width_deg'];
    }

    /**
     * The atlas page grid, each cell outlined and numbered.
     *
     * @param  array<string, float|bool>  $frame
     * @param  array{north: bool, center_ra_deg: float, half_width_deg: float}  $map
     */
    private function renderCells(int $variant, array $frame, array $map): string
    {
        $north = (bool) $map['north'];
        $decLimit = $north ? -self::EQUATOR_OVERLAP_DEG : self::EQUATOR_OVERLAP_DEG;
        $bands = AtlasLayout::bandCount($variant);

        $svg = [];

        for ($band = 0; $band < $bands; $band++) {
            [$upper, $lower] = AtlasLayout::bandBounds($variant, $band);

            // A map runs a little past the equator, so a band wholly on the
            // far side of that line has nothing left to draw here. The
            // remaining bands are cut off at it.
            if ($north ? $upper <= $decLimit : $lower >= $decLimit) {
                continue;
            }

            // The edge nearer the pole of this map, and the edge further from
            // it: on the southern map that is the other way round.
            $decNear = $north ? $upper : $lower;
            $decFar = $north ? max($lower, $decLimit) : min($upper, $decLimit);

            $columns = AtlasLayout::columnCount($variant, $band);
            $columnWidthDeg = 360.0 / max(1, $columns);

            for ($column = 0; $column < $columns; $column++) {
                $centerRaDeg = (float) $map['center_ra_deg'];
                $halfWidthDeg = (float) $map['half_width_deg'];

                // Work relative to the middle of the map: a cell straddling
                // 0h is then no different from any other, and cutting it off
                // at the edge of the sector is a matter of clamping.
                $delta = $this->raDelta(360.0 - (($column + 0.5) * $columnWidthDeg), $centerRaDeg);
                $deltaMin = max($delta - ($columnWidthDeg / 2.0), -$halfWidthDeg);
                $deltaMax = min($delta + ($columnWidthDeg / 2.0), $halfWidthDeg);

                if ($deltaMax - $deltaMin < 1.0e-6) {
                    continue;
                }

                $svg[] = $this->renderCellOutline($frame, $centerRaDeg + $deltaMin, $centerRaDeg + $deltaMax, $decNear, $decFar);
                $svg[] = $this->renderCellNumber(
                    $frame,
                    AtlasLayout::pageNumber($variant, $band, $column),
                    $centerRaDeg + $deltaMin,
                    $centerRaDeg + $deltaMax,
                    $decNear,
                    $decFar
                );
            }
        }

        return implode('', $svg);
    }

    /**
     * @param  array<string, float|bool>  $frame
     */
    private function renderCellOutline(array $frame, float $raMinDeg, float $raMaxDeg, float $decNear, float $decFar): string
    {
        $arcSteps = 8;
        $points = [];

        for ($i = 0; $i <= $arcSteps; $i++) {
            $points[] = $this->project($frame, $raMinDeg + (($i / $arcSteps) * ($raMaxDeg - $raMinDeg)), $decNear);
        }
        for ($i = $arcSteps; $i >= 0; $i--) {
            $points[] = $this->project($frame, $raMinDeg + (($i / $arcSteps) * ($raMaxDeg - $raMinDeg)), $decFar);
        }

        $path = [];
        foreach ($points as $point) {
            if ($point === null) {
                return '';
            }

            $path[] = sprintf('%s %0.2f %0.2f', $path === [] ? 'M' : 'L', $point['x'], $point['y']);
        }

        return sprintf('<path d="%s Z" fill="none" stroke="#111827" stroke-width="0.7" stroke-linejoin="round" />', implode(' ', $path));
    }

    /**
     * The page number, set along the cell rather than across it.
     *
     * Cells are wedges, so a horizontal number would run out of them near
     * the pole; following the declination band keeps it inside. Numbers past
     * the horizontal are turned the other way up so they all stay readable.
     *
     * @param  array<string, float|bool>  $frame
     */
    private function renderCellNumber(array $frame, int $pageNumber, float $raMinDeg, float $raMaxDeg, float $decNear, float $decFar): string
    {
        $raCenterDeg = ($raMinDeg + $raMaxDeg) / 2.0;
        $midDec = ($decNear + $decFar) / 2.0;
        $center = $this->project($frame, $raCenterDeg, $midDec);
        $edgeStart = $this->project($frame, $raMinDeg, $midDec);
        $edgeEnd = $this->project($frame, $raMaxDeg, $midDec);
        $inner = $this->project($frame, $raCenterDeg, $decNear);
        $outer = $this->project($frame, $raCenterDeg, $decFar);

        if ($center === null || $edgeStart === null || $edgeEnd === null || $inner === null || $outer === null) {
            return '';
        }

        $alongCell = hypot($edgeEnd['x'] - $edgeStart['x'], $edgeEnd['y'] - $edgeStart['y']);
        $acrossCell = hypot($outer['x'] - $inner['x'], $outer['y'] - $inner['y']);

        // Set the number the long way of the cell. Most cells are wider than
        // they are deep, so that is along the ring; the ones right at the
        // pole are the other way round, and a four digit number will not fit
        // across them.
        $alongRing = $alongCell >= $acrossCell;
        [$length, $height] = $alongRing ? [$alongCell, $acrossCell] : [$acrossCell, $alongCell];

        $digits = max(1, strlen((string) $pageNumber));

        // 0.62 em per digit is what the chart renderer sizes digits at.
        $fontSize = min(13.0, ($length * 0.8) / ($digits * 0.62), $height * 0.6);
        if ($fontSize < 3.0) {
            return '';
        }

        return $this->renderRotatedLabel(
            (string) $pageNumber,
            $center['x'],
            $center['y'],
            $fontSize,
            $alongRing ? $this->readableAngle($edgeStart, $edgeEnd) : $this->readableAngle($inner, $outer),
            '#111827',
            false
        );
    }

    /**
     * The angle text should be set at to run from one point to the other and
     * still be the right way up, in degrees.
     *
     * @param  array{x: float, y: float}  $from
     * @param  array{x: float, y: float}  $to
     */
    private function readableAngle(array $from, array $to): float
    {
        $angle = rad2deg(atan2($to['y'] - $from['y'], $to['x'] - $from['x']));

        if ($angle > 90.0) {
            $angle -= 180.0;
        }
        if ($angle <= -90.0) {
            $angle += 180.0;
        }

        return $angle;
    }

    /**
     * A piece of text set at an angle, optionally on a patch of white.
     *
     * The grid and the constellation figures run underneath the names and the
     * declination labels, and the two together are unreadable; the patch
     * breaks whatever passes behind the text, the way an engraved chart does.
     */
    private function renderRotatedLabel(string $text, float $x, float $y, float $fontSize, float $angleDeg, string $fill, bool $halo = true, float $letterSpacing = 0.0): string
    {
        $svg = [sprintf('<g transform="rotate(%0.2f %0.2f %0.2f)">', $angleDeg, $x, $y)];

        if ($halo) {
            $width = (mb_strlen($text) * (($fontSize * 0.62) + $letterSpacing)) + 3.0;
            $svg[] = sprintf(
                '<rect x="%0.2f" y="%0.2f" width="%0.2f" height="%0.2f" fill="#ffffff" />',
                $x - ($width / 2.0),
                $y - ($fontSize * 0.62),
                $width,
                $fontSize * 1.2
            );
        }

        $svg[] = sprintf(
            '<text x="%0.2f" y="%0.2f" font-size="%0.1f"%s text-anchor="middle" fill="%s">%s</text>',
            $x,
            $y + ($fontSize * 0.36),
            $fontSize,
            $letterSpacing > 0.0 ? sprintf(' letter-spacing="%0.2f"', $letterSpacing) : '',
            $fill,
            htmlspecialchars($text, ENT_QUOTES, 'UTF-8')
        );
        $svg[] = '</g>';

        return implode('', $svg);
    }

    /**
     * The constellation figures, clipped to the part of the sky shown.
     *
     * @param  array<string, float|bool>  $frame
     * @param  array{north: bool, center_ra_deg: float, half_width_deg: float}  $map
     */
    private function renderConstellationFigures(array $frame, array $map, float $maxPolarDeg): string
    {
        $segments = $this->starChartService->loadConstellationLines();
        if ($segments->isEmpty()) {
            return '';
        }

        $path = [];

        foreach ($segments as $segment) {
            $steps = max(1, min(48, (int) ceil(((float) $segment['length_deg']) / 1.0)));
            $previous = null;

            for ($i = 0; $i <= $steps; $i++) {
                [$ra, $dec] = $this->starChartService->greatCirclePoint(
                    (float) $segment['ra0_degrees'],
                    (float) $segment['decl0_degrees'],
                    (float) $segment['ra1_degrees'],
                    (float) $segment['decl1_degrees'],
                    $i / $steps
                );

                $current = $this->covers($map, $ra, $dec, $maxPolarDeg)
                    ? $this->project($frame, $ra, $dec)
                    : null;

                if ($previous !== null && $current !== null) {
                    $path[] = sprintf('M %0.2f %0.2f L %0.2f %0.2f', $previous['x'], $previous['y'], $current['x'], $current['y']);
                }

                $previous = $current;
            }
        }

        if ($path === []) {
            return '';
        }

        return sprintf('<path d="%s" fill="none" stroke="#4b5563" stroke-width="0.8" stroke-linecap="round" opacity="0.8" />', implode(' ', $path));
    }

    /**
     * The naked-eye stars, so the figures have something to hang on.
     *
     * @param  array<string, float|bool>  $frame
     * @param  array{north: bool, center_ra_deg: float, half_width_deg: float}  $map
     */
    private function renderStars(array $frame, array $map, float $maxPolarDeg): string
    {
        if (! Schema::hasTable('stars')) {
            return '';
        }

        $decLimit = $map['north'] ? -self::EQUATOR_OVERLAP_DEG : self::EQUATOR_OVERLAP_DEG;

        $query = DB::table('stars')
            ->select(['ra_degrees', 'decl_degrees', 'vmag'])
            ->whereNotNull('ra_degrees')
            ->whereNotNull('decl_degrees')
            ->where('vmag', '<=', self::STAR_MAGNITUDE_LIMIT);

        $query = $map['north']
            ? $query->where('decl_degrees', '>=', $decLimit)
            : $query->where('decl_degrees', '<=', $decLimit);

        $svg = [];

        foreach ($query->get() as $star) {
            $ra = (float) $star->ra_degrees;
            $dec = (float) $star->decl_degrees;

            if (! $this->covers($map, $ra, $dec, $maxPolarDeg)) {
                continue;
            }

            $point = $this->project($frame, $ra, $dec);
            if ($point === null) {
                continue;
            }

            $svg[] = sprintf(
                '<circle cx="%0.2f" cy="%0.2f" r="%0.2f" fill="#111827" />',
                $point['x'],
                $point['y'],
                $this->starRadius((float) $star->vmag)
            );
        }

        return implode('', $svg);
    }

    /**
     * Dot size for a star on an index map, from the legend's brightest entry
     * down to its faintest.
     */
    private function starRadius(float $magnitude): float
    {
        $normalized = (self::STAR_MAGNITUDE_LIMIT - $magnitude) / (self::STAR_MAGNITUDE_LIMIT - min(self::LEGEND_MAGNITUDES));
        $normalized = max(0.0, min(1.0, $normalized));

        return 0.6 + (2.6 * ($normalized ** 1.6));
    }

    /**
     * @param  array<string, float|bool>  $frame
     * @param  array{north: bool, center_ra_deg: float, half_width_deg: float}  $map
     */
    private function renderConstellationNames(array $frame, array $map, float $maxPolarDeg): string
    {
        if (! Schema::hasColumn('constellations', 'ra_degrees')) {
            return '';
        }

        $svg = [];

        $names = DB::table('constellations')
            ->select(['name', 'ra_degrees', 'decl_degrees'])
            ->whereNotNull('ra_degrees')
            ->whereNotNull('decl_degrees')
            ->get();

        foreach ($names as $constellation) {
            $ra = (float) $constellation->ra_degrees;
            $dec = (float) $constellation->decl_degrees;

            if (! $this->covers($map, $ra, $dec, $maxPolarDeg)) {
                continue;
            }

            $point = $this->project($frame, $ra, $dec);
            $along = $this->project($frame, $ra + 1.0, $dec);
            if ($point === null || $along === null) {
                continue;
            }

            $svg[] = $this->renderRotatedLabel(
                mb_strtoupper((string) $constellation->name),
                $point['x'],
                $point['y'],
                7.0,
                $this->readableAngle($point, $along),
                '#374151',
                true,
                0.6
            );
        }

        return implode('', $svg);
    }

    /**
     * The outline of the part of the sky this map covers.
     *
     * @param  array<string, float|bool>  $frame
     * @param  array{north: bool, center_ra_deg: float, half_width_deg: float}  $map
     */
    private function renderGraticule(array $frame, array $map, float $maxPolarDeg): string
    {
        $north = (bool) $map['north'];
        $halfWidth = (float) $map['half_width_deg'];
        $centerRa = (float) $map['center_ra_deg'];
        $svg = [];

        $outerDec = $north ? 90.0 - $maxPolarDeg : $maxPolarDeg - 90.0;

        // The two radial edges and the outer arc, as one closed outline.
        $outline = [];
        $steps = 240;
        for ($i = 0; $i <= $steps; $i++) {
            $point = $this->project($frame, $centerRa - $halfWidth + (($i / $steps) * 2 * $halfWidth), $outerDec);
            if ($point !== null) {
                $outline[] = sprintf('%s %0.2f %0.2f', $outline === [] ? 'M' : 'L', $point['x'], $point['y']);
            }
        }

        $pole = $this->project($frame, $centerRa, $north ? 90.0 : -90.0);
        if ($outline !== [] && $pole !== null && $halfWidth < 180.0) {
            $outline[] = sprintf('L %0.2f %0.2f Z', $pole['x'], $pole['y']);
        }

        if ($outline !== []) {
            $svg[] = sprintf('<path d="%s" fill="none" stroke="#111827" stroke-width="1.2" />', implode(' ', $outline));
        }

        return implode('', $svg);
    }

    /**
     * Where each ring of charts ends, labelled up the middle of the map.
     *
     * The labels sit on the ring itself rather than between two rings, so
     * they land on a line of the grid instead of on top of a page number,
     * and they go on last so the grid does not run through them.
     *
     * @param  array<string, float|bool>  $frame
     * @param  array{north: bool, center_ra_deg: float, half_width_deg: float}  $map
     */
    private function renderDeclinationLabels(int $variant, array $frame, array $map, float $maxPolarDeg): string
    {
        $north = (bool) $map['north'];
        $centerRa = (float) $map['center_ra_deg'];
        $svg = [];

        $bands = AtlasLayout::bandCount($variant);

        // Every edge is too many once the bands are only a few degrees deep.
        $stride = max(1, (int) ceil($bands / 20));

        for ($band = $stride - 1; $band < $bands; $band += $stride) {
            [$upper, $lower] = AtlasLayout::bandBounds($variant, $band);

            // The edge of the ring furthest from the pole of this map.
            $edge = $north ? $lower : $upper;

            if (! $this->covers($map, $centerRa, $edge, $maxPolarDeg)) {
                continue;
            }

            $point = $this->project($frame, $centerRa, $edge);
            if ($point === null) {
                continue;
            }

            $svg[] = $this->renderRotatedLabel(
                sprintf('%+d°', (int) round($edge)),
                $point['x'],
                $point['y'],
                8.0,
                0.0,
                '#374151'
            );
        }

        return implode('', $svg);
    }

    /**
     * Hour labels just outside the rim of the map.
     *
     * @param  array<string, float|bool>  $frame
     * @param  array{north: bool, center_ra_deg: float, half_width_deg: float}  $map
     */
    private function renderRightAscensionLabels(array $frame, array $map, float $maxPolarDeg): string
    {
        $north = (bool) $map['north'];
        $outerDec = $north ? 90.0 - $maxPolarDeg : $maxPolarDeg - 90.0;
        $pole = $this->project($frame, (float) $map['center_ra_deg'], $north ? 90.0 : -90.0);

        if ($pole === null) {
            return '';
        }

        $svg = [];

        for ($hour = 0; $hour < 24; $hour++) {
            $ra = $hour * 15.0;

            if (abs($this->raDelta($ra, (float) $map['center_ra_deg'])) > (float) $map['half_width_deg']) {
                continue;
            }

            $point = $this->project($frame, $ra, $outerDec);
            if ($point === null) {
                continue;
            }

            // Outside the rim, on the line from the pole through the tick:
            // upright rather than turned with the rim, because there is room
            // out there and an upright hour is quicker to read.
            $dx = $point['x'] - $pole['x'];
            $dy = $point['y'] - $pole['y'];
            $length = max(1.0e-6, hypot($dx, $dy));

            $svg[] = sprintf(
                '<text x="%0.2f" y="%0.2f" font-size="9" text-anchor="middle" fill="#374151">%dh</text>',
                $point['x'] + (($dx / $length) * 13.0),
                $point['y'] + (($dy / $length) * 13.0) + 3.2,
                $hour
            );
        }

        return implode('', $svg);
    }

    /**
     * @param  array{north: bool, center_ra_deg: float, half_width_deg: float}  $map
     */
    private function renderHeading(string $atlasType, string $paper, string $orientation, array $map, int $mapNumber, int $mapCount, float $left, float $width): string
    {
        $hemisphere = $map['north'] ? __('Northern sky') : __('Southern sky');
        $range = __(':from to :to', [
            'from' => $this->formatHours(((float) $map['center_ra_deg'] - (float) $map['half_width_deg']) / 15.0),
            'to' => $this->formatHours(((float) $map['center_ra_deg'] + (float) $map['half_width_deg']) / 15.0),
        ]);

        return sprintf(
            '<text x="%0.2f" y="34" font-size="18" fill="#111827">%s</text>'
            . '<text x="%0.2f" y="52" font-size="10" fill="#4b5563">%s</text>'
            . '<text x="%0.2f" y="34" font-size="10" text-anchor="end" fill="#4b5563">%s</text>'
            . '<line x1="%0.2f" y1="62" x2="%0.2f" y2="62" stroke="#111827" stroke-width="0.8" />',
            $left,
            htmlspecialchars(__('Index map :number of :total', ['number' => $mapNumber, 'total' => $mapCount]), ENT_QUOTES, 'UTF-8'),
            $left,
            htmlspecialchars($hemisphere . '  -  ' . $range, ENT_QUOTES, 'UTF-8'),
            $width - $left,
            htmlspecialchars(__('Deepskylog Atlas') . '  -  ' . $this->atlasDescription($atlasType, $paper, $orientation), ENT_QUOTES, 'UTF-8'),
            $left,
            $width - $left
        );
    }

    private function formatHours(float $hours): string
    {
        $hours = fmod(fmod($hours, 24.0) + 24.0, 24.0);

        return sprintf('%dh', (int) round($hours) % 24);
    }

    private function atlasDescription(string $atlasType, string $paper, string $orientation): string
    {
        $types = [
            'overview' => __('Overview'),
            'lookup' => __('Lookup'),
            'detail' => __('Detail'),
        ];
        $type = $types[strtolower(trim($atlasType))] ?? ucfirst(strtolower(trim($atlasType)));
        $orientation = strtolower(trim($orientation)) === 'portrait' ? __('portrait') : __('landscape');

        return __(':type atlas - :paper :orientation', [
            'type' => $type,
            'paper' => strtoupper(trim($paper)),
            'orientation' => $orientation,
        ]);
    }

    /**
     * The magnitude key, in the bottom left corner as on the charts.
     */
    private function renderMagnitudeLegend(float $x, float $y): string
    {
        $svg = [sprintf('<text x="%0.2f" y="%0.2f" font-size="8" fill="#4b5563">%s</text>', $x, $y - 12.0, htmlspecialchars(__('Magnitudes'), ENT_QUOTES, 'UTF-8'))];
        $step = 18.0;

        foreach (self::LEGEND_MAGNITUDES as $index => $magnitude) {
            $dotX = $x + 6.0 + ($index * $step);

            $svg[] = sprintf('<circle cx="%0.2f" cy="%0.2f" r="%0.2f" fill="#111827" />', $dotX, $y, $this->starRadius($magnitude));
            $svg[] = sprintf(
                '<text x="%0.2f" y="%0.2f" font-size="7" text-anchor="middle" fill="#6b7280">%d</text>',
                $dotX,
                $y + 13.0,
                (int) $magnitude
            );
        }

        return implode('', $svg);
    }
}
