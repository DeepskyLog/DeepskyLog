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
    private const PORTRAIT_ATLAS_CHART_WIDTH = 756;
    private const PORTRAIT_ATLAS_CHART_HEIGHT = 1120;
    private const CHART_LEFT = 56;
    private const CHART_RIGHT = 56;
    private const CHART_TOP = 70;
    private const CHART_BOTTOM = 84;

    /** Star magnitudes shown in the top legend. */
    private const LEGEND_MAGNITUDES = [6.5, 7.0, 8.0, 8.5, 9.0, 9.5, 10.0, 11.5, 12.0];

    /** Object types shown in the top legend. */
    private const LEGEND_OBJECT_TYPES = ['GALXY', 'PLNNB', 'GLOCL', 'OPNCL', 'DRKNB', 'SNREM', 'CLANB', 'GALCL', 'QUASR'];

    /** Width of one digit as a fraction of the font size, for sizing boxes. */
    private const DIGIT_WIDTH_RATIO = 0.62;

    /**
     * Object type codes that are stars rather than deep sky objects.
     *
     * DS is a double star, AA1STAR a single one. Both are drawn by the star
     * layer already, and the chart legend has no symbol for either.
     */
    private const STAR_OBJECT_TYPES = ['DS', 'AA1STAR'];

    /** Declination graticule steps in degrees, largest first. */
    private const GRID_DEC_STEPS_DEG = [30.0, 20.0, 15.0, 10.0, 5.0, 2.0, 1.0, 0.5, 0.25, 1.0 / 6.0, 1.0 / 12.0, 1.0 / 30.0, 1.0 / 60.0];

    /** Right ascension graticule steps in hours, largest first. */
    private const GRID_RA_STEPS_HOURS = [6.0, 4.0, 3.0, 2.0, 1.0, 0.5, 1.0 / 3.0, 0.25, 1.0 / 6.0, 1.0 / 12.0, 1.0 / 30.0, 1.0 / 60.0];

    /** Horizontal distance between two entries of the magnitude legend. */
    private const LEGEND_MAGNITUDE_STEP = 39.0;

    /** Horizontal distance between two entries of the object type legend. */
    private const LEGEND_OBJECT_STEP = 52.0;

    /** Vertical distance between the two legend rows when the legend wraps. */
    private const LEGEND_ROW_STEP = 20.0;

    /**
     * Cached atlas projection parameters for the current render.
     *
     * @var array<string, float>
     */
    private array $atlasProjectionContext = [];

    private bool $atlasProjectionEnabled = false;

    /**
     * @param  array{page?: int, total?: int, left?: ?int, right?: ?int, top?: ?int, bottom?: ?int}|null  $atlasPage
     *     Atlas page number of this chart and of the charts adjoining it.
     * @param  bool  $showUnknownMagnitudes
     *     Whether to plot objects with no recorded magnitude. They cannot be
     *     held to the magnitude limit, so only the deepest charts show them.
     */
    public function build(DeepskyObject $target, float $centerRaDeg, float $centerDecDeg, float $radiusArcMin, ?float $magnitudeLimit = null, ?float $objectMagnitudeLimit = null, ?float $starMagnitudeLimit = null, bool $fast = false, bool $atlasMode = false, bool $portraitAtlasLayout = false, ?array $atlasPage = null, bool $showUnknownMagnitudes = true): array
    {
        $radiusDeg = max(0.1, $radiusArcMin / 60);
        $layout = $this->resolveAtlasLayout($atlasMode, $portraitAtlasLayout);
        $atlasPlotAspect = ((float) $layout['plot_width']) / max(1.0, (float) $layout['plot_height']);

        // If a single legacy magnitude limit was provided use it as a sensible
        // default. Specific per-type limits (object/star) override this when set.
        $defaultMag = $magnitudeLimit !== null ? $magnitudeLimit : $this->resolveMagnitudeLimit($radiusArcMin, null);

        $starMag = $starMagnitudeLimit ?? $defaultMag;
        $objectMag = $objectMagnitudeLimit ?? $defaultMag;

        $stars = $this->loadStars($centerRaDeg, $centerDecDeg, $radiusDeg, $starMag, $fast, $atlasMode, $atlasPlotAspect, $layout);
        $objects = $this->loadObjects($centerRaDeg, $centerDecDeg, $radiusDeg, $target, $objectMag, $fast, $atlasMode, $atlasPlotAspect, $layout, $showUnknownMagnitudes);
        $contours = $this->loadObjectContours($objects);
        $boundaries = $this->loadConstellationBoundaries();
        $constellationLines = $this->loadConstellationLines();

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
            // The fast flag may optimize query mechanics, but must not remove
            // chart content such as labels or observation indicators.
            'svg' => $this->renderSvg($centerRaDeg, $centerDecDeg, $radiusDeg, $objectMag, $starMag, $target, $stars, $objects, $contours, $boundaries, $constellationLines, false, $atlasMode, $layout, $atlasPage),
        ];
    }

    /**
     * Front page of a generated atlas.
     *
     * Carries the atlas title, which of the six atlases this is, and the
     * symbol every plotted object type is drawn with on the pages that
     * follow. The symbols come from the same renderer the charts use, so the
     * legend cannot drift away from what is actually on the pages.
     *
     * The canvas matches an atlas page so the bundle scales it identically.
     */
    public function buildTitlePage(string $atlasType, string $paper, string $orientation): string
    {
        $layout = $this->resolveAtlasLayout(true, strtolower($orientation) === 'portrait');
        $font = htmlspecialchars($this->monospaceFontFamily(), ENT_QUOTES, 'UTF-8');
        $width = (float) $layout['width'];
        $height = (float) $layout['height'];
        $left = (float) $layout['left'];

        $svg = [];
        $svg[] = sprintf('<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d" viewBox="0 0 %d %d" preserveAspectRatio="xMidYMid meet">', $width, $height, $width, $height);
        $svg[] = '<rect width="100%" height="100%" fill="#ffffff" />';

        $titleX = $width * 0.175;
        $ruleY = $height * 0.06;
        $svg[] = sprintf(
            '<line x1="%0.2f" y1="%0.2f" x2="%0.2f" y2="%0.2f" stroke="#111827" stroke-width="1.6" />',
            $titleX + 4,
            $ruleY,
            $width - ($left * 0.3),
            $ruleY
        );

        $titleSize = $width * 0.062;
        $svg[] = sprintf(
            '<text x="%0.2f" y="%0.2f" font-family="%s" font-size="%0.1f" fill="#111827">%s</text>',
            $titleX,
            $ruleY + ($titleSize * 1.5),
            $font,
            $titleSize,
            htmlspecialchars(__('Deepskylog Atlas'), ENT_QUOTES, 'UTF-8')
        );

        $subtitleSize = $titleSize * 0.26;
        $svg[] = sprintf(
            '<text x="%0.2f" y="%0.2f" font-family="%s" font-size="%0.1f" fill="#4b5563">%s</text>',
            $titleX + 4,
            $ruleY + ($titleSize * 2.3),
            $font,
            $subtitleSize,
            htmlspecialchars($this->atlasDescription($atlasType, $paper, $orientation), ENT_QUOTES, 'UTF-8')
        );

        // The legend sits at the bottom left, one row per symbol, ending a
        // little above the foot of the page.
        $entries = $this->titlePageLegendEntries();
        $rowStep = 30.0;
        $symbolX = $left + 24.0;
        $labelX = $symbolX + 28.0;
        $firstRowY = $height - 64.0 - ((count($entries) - 1) * $rowStep);

        // Chart symbols are drawn at the size a small object gets on a page,
        // which is too fine to read as a key; enlarge them about half again.
        $symbolScale = 1.5;

        $row = 0;
        foreach ($entries as $code => $label) {
            $y = $firstRowY + ($row * $rowStep);
            $svg[] = sprintf(
                '<g transform="translate(%0.2f %0.2f) scale(%0.2f) translate(%0.2f %0.2f)">%s</g>',
                $symbolX,
                $y,
                $symbolScale,
                -$symbolX,
                -$y,
                $this->renderObjectSymbol((string) $code, $symbolX, $y)
            );
            $svg[] = sprintf(
                '<text x="%0.2f" y="%0.2f" font-family="%s" font-size="12" fill="#111827">%s</text>',
                $labelX,
                $y + 4.5,
                $font,
                htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
            );
            $row++;
        }

        $svg[] = '</svg>';

        return implode('', $svg);
    }

    /**
     * The monospaced family to ask for on the front page and in the index.
     *
     * php-svg-lib hands the family name straight to Dompdf's selectFont(),
     * which resolves it against the working directory rather than Dompdf's
     * own font directory, so the generic "monospace" quietly falls back to
     * the default serif face. Naming the metrics file Dompdf ships gets the
     * real thing; if it ever moves, the generic name is still correct
     * anywhere else the SVG is rendered.
     */
    public function monospaceFontFamily(): string
    {
        if (! class_exists(\Dompdf\Dompdf::class)) {
            return 'monospace';
        }

        $reflected = (new \ReflectionClass(\Dompdf\Dompdf::class))->getFileName();

        if ($reflected === false) {
            return 'monospace';
        }

        $courier = dirname($reflected, 2) . '/lib/fonts/Courier';

        return is_file($courier . '.afm') ? $courier : 'monospace';
    }

    /**
     * Which atlas this is, e.g. "Detail atlas - A4 portrait".
     */
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
     * The object types of the front page legend, keyed by the type code that
     * draws each symbol.
     *
     * One row per distinct symbol: types that share a symbol -- every diffuse
     * nebula, say -- share a row, so no two rows show the same drawing. Stars
     * and double stars are absent because loadObjects() never plots them;
     * they belong to the star layer, which the page header already explains.
     *
     * @return array<string, string>
     */
    private function titlePageLegendEntries(): array
    {
        return [
            'GALXY' => __('Galaxy, galaxy cluster'),
            'GLOCL' => __('Globular cluster'),
            'OPNCL' => __('Open cluster, asterism'),
            'CLANB' => __('Cluster with nebulosity, diffuse nebula'),
            'PLNNB' => __('Planetary nebula'),
            'DRKNB' => __('Dark nebula'),
            'SNREM' => __('Supernova remnant'),
            'QUASR' => __('Quasar, active nucleus'),
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
    private function loadStars(float $centerRaDeg, float $centerDecDeg, float $radiusDeg, float $magnitudeLimit, bool $fast = false, bool $atlasMode = false, ?float $atlasPlotAspect = null, ?array $layout = null): Collection
    {
        $xHalfDeg = $this->xHalfDegrees($radiusDeg, $fast, $atlasMode, $atlasPlotAspect);
        $plotWidth = isset($layout['plot_width']) ? (float) $layout['plot_width'] : (self::CHART_WIDTH - self::CHART_LEFT - self::CHART_RIGHT);
        $plotHeight = isset($layout['plot_height']) ? (float) $layout['plot_height'] : (self::CHART_HEIGHT - self::CHART_TOP - self::CHART_BOTTOM);

        // Default decl/RA window (flat approximation)
        $declMin = max(-90.0, $centerDecDeg - $radiusDeg);
        $declMax = min(90.0, $centerDecDeg + $radiusDeg);
        $raWindow = $this->raWindowDegreesFromXHalf($centerDecDeg, $xHalfDeg);

        // In atlas (spherical) mode the page is a stereographic projection, so
        // derive the search window from the projected page bounds: near a pole
        // the corners of the page reach far beyond the flat RA/Dec window.
        $minProjX = INF;
        $maxProjX = -INF;
        $minProjY = INF;
        $maxProjY = -INF;
        if ($atlasMode) {
            [$minProjX, $maxProjX, $minProjY, $maxProjY] = $this->atlasProjectionBounds($xHalfDeg, $radiusDeg, $plotWidth, $plotHeight);

            $window = $this->atlasSkyWindow($centerRaDeg, $centerDecDeg, [$minProjX, $maxProjX, $minProjY, $maxProjY]);
            $declMin = $window['dec_min'];
            $declMax = $window['dec_max'];
            $raWindow = $window['ra_window'];
        }

        // Projection epsilon margins to avoid boundary precision gaps when
        // filtering by projected coordinates. Only used when atlas mode is on.
        $projEpsX = 0.0;
        $projEpsY = 0.0;
        if ($atlasMode && $minProjX !== INF && $maxProjX !== -INF && $minProjY !== INF && $maxProjY !== -INF) {
            // Widen epsilon margin to avoid precision-related exclusion of
            // stars that project near the curved plotting rectangle edges.
            $projEpsFactor = 0.12; // 12% margin
            $projEpsX = max(1.0e-6, ($maxProjX - $minProjX) * $projEpsFactor);
            $projEpsY = max(1.0e-6, ($maxProjY - $minProjY) * $projEpsFactor);
        }

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
        })->filter(function (array $star) use ($xHalfDeg, $radiusDeg, $atlasMode, $centerRaDeg, $centerDecDeg, $minProjX, $maxProjX, $minProjY, $maxProjY, $projEpsX, $projEpsY) {
            if ($atlasMode) {
                $proj = $this->atlasSphericalProject($star['ra_degrees'], $star['decl_degrees'], $centerRaDeg, $centerDecDeg);
                return $proj['x'] >= ($minProjX - $projEpsX) && $proj['x'] <= ($maxProjX + $projEpsX) && $proj['y'] >= ($minProjY - $projEpsY) && $proj['y'] <= ($maxProjY + $projEpsY);
            }

            return abs($star['x_degrees']) <= $xHalfDeg && abs($star['y_degrees']) <= $radiusDeg;
        })->sortBy(fn(array $star) => $star['vmag'] ?? 99)->values();

        $maxStars = $this->maxStarCount($magnitudeLimit, $radiusDeg);
        if (!$atlasMode && $stars->count() > $maxStars) {
            $stars = $stars->take($maxStars)->values();
        }

        return $stars;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function loadObjects(float $centerRaDeg, float $centerDecDeg, float $radiusDeg, DeepskyObject $target, ?float $objectMagnitudeLimit = null, bool $fast = false, bool $atlasMode = false, ?float $atlasPlotAspect = null, ?array $layout = null, bool $showUnknownMagnitudes = true): Collection
    {
        $xHalfDeg = $this->xHalfDegrees($radiusDeg, $fast, $atlasMode, $atlasPlotAspect);
        $plotWidth = isset($layout['plot_width']) ? (float) $layout['plot_width'] : (self::CHART_WIDTH - self::CHART_LEFT - self::CHART_RIGHT);
        $plotHeight = isset($layout['plot_height']) ? (float) $layout['plot_height'] : (self::CHART_HEIGHT - self::CHART_TOP - self::CHART_BOTTOM);
        $raWindow = $this->raWindowDegreesFromXHalf($centerDecDeg, $xHalfDeg);
        $centerRaHours = $centerRaDeg / 15.0;
        $raWindowHours = $raWindow / 15.0;

        $raMinHours = $centerRaHours - $raWindowHours;
        $raMaxHours = $centerRaHours + $raWindowHours;
        $raMinDeg = $centerRaDeg - $raWindow;
        $raMaxDeg = $centerRaDeg + $raWindow;
        $declMin = max(-90.0, $centerDecDeg - $radiusDeg);
        $declMax = min(90.0, $centerDecDeg + $radiusDeg);

        // In atlas mode take the search window from the projected page bounds,
        // the same way loadStars() does, so object selection matches exactly
        // what the stereographic page can show.
        $minProjX = INF;
        $maxProjX = -INF;
        $minProjY = INF;
        $maxProjY = -INF;
        if ($atlasMode) {
            [$minProjX, $maxProjX, $minProjY, $maxProjY] = $this->atlasProjectionBounds($xHalfDeg, $radiusDeg, $plotWidth, $plotHeight);

            $window = $this->atlasSkyWindow($centerRaDeg, $centerDecDeg, [$minProjX, $maxProjX, $minProjY, $maxProjY]);
            $declMin = $window['dec_min'];
            $declMax = $window['dec_max'];
            $raWindow = $window['ra_window'];
            $raMinHours = $centerRaHours - ($raWindow / 15.0);
            $raMaxHours = $centerRaHours + ($raWindow / 15.0);
            $raMinDeg = $centerRaDeg - $raWindow;
            $raMaxDeg = $centerRaDeg + $raWindow;
        }

        $query = DeepskyObject::query()
            ->select(['name', 'slug', 'type', 'ra', 'decl', 'mag', 'diam1', 'diam2', 'pa'])
            ->whereBetween('decl', [$declMin, $declMax]);

        // Stars listed in the object catalogues are left to the star layer,
        // which plots them already. Carrying them here only crowded the chart
        // with a second dot and a STF/STT/HJ or HD catalogue label. A chart
        // centred on such a star still shows its own target.
        $query->where(function (Builder $builder) use ($target) {
            $builder->whereNotIn('type', self::STAR_OBJECT_TYPES);

            // Atlas pages pass a synthetic, unsaved target, so only a real
            // object earns the exception.
            if ($target->exists && $target->name !== null && $target->name !== '') {
                $builder->orWhere('name', $target->name);
            }
        });

        // At a pole the chart covers every right ascension. Applying the
        // legacy hour/degree range in that case can turn a full-circle range
        // into a narrow or empty predicate after wrap-around normalization.
        if ($raWindow < 180.0 - 1.0e-9) {
            $query->where(function (Builder $builder) use ($raMinHours, $raMaxHours, $raMinDeg, $raMaxDeg) {
                $builder->where(function (Builder $hoursQuery) use ($raMinHours, $raMaxHours) {
                    $hoursQuery->where('ra', '<=', 24.0);
                    $this->applyLegacyRaRange($hoursQuery, $raMinHours, $raMaxHours, 24.0);
                })->orWhere(function (Builder $degQuery) use ($raMinDeg, $raMaxDeg) {
                    $degQuery->where('ra', '>', 24.0);
                    $this->applyLegacyRaRange($degQuery, $raMinDeg, $raMaxDeg, 360.0, 24.000001);
                });
            });
        }

        if ($objectMagnitudeLimit !== null) {
            // A missing magnitude is stored either as NULL or as the
            // placeholder 99.9. Such an object cannot be held to the limit at
            // all, so it is plotted only where the chart is meant to show
            // everything it can -- otherwise an overview chart fills up with
            // objects far below its own limit.
            $query = $query->where(function (Builder $q) use ($objectMagnitudeLimit, $showUnknownMagnitudes) {
                $q->where('mag', '<=', $objectMagnitudeLimit);

                if ($showUnknownMagnitudes) {
                    $q->orWhereNull('mag')->orWhere('mag', '=', 99.9);
                }
            });
        }

        // Projection epsilon margins for object filtering (atlas-mode).
        $objProjEpsX = 0.0;
        $objProjEpsY = 0.0;
        if ($atlasMode && $minProjX !== INF && $maxProjX !== -INF && $minProjY !== INF && $maxProjY !== -INF) {
            $projEpsFactor = 0.12; // keep same 12% margin for objects
            $objProjEpsX = max(1.0e-6, ($maxProjX - $minProjX) * $projEpsFactor);
            $objProjEpsY = max(1.0e-6, ($maxProjY - $minProjY) * $projEpsFactor);
        }

        // Atlas pages must show everything that falls inside them, so the row
        // cap is left to the projection filter below. Capping the query itself
        // dropped objects well inside the magnitude limit from polar pages,
        // where the declination band alone matches several hundred rows and
        // no right ascension predicate applies. loadStars() already leaves
        // atlas charts uncapped in the same way.
        if (! $atlasMode) {
            $query->limit(200);
        }

        $objects = $query->get()
            ->map(function (DeepskyObject $object) use ($target, $atlasMode, $minProjX, $maxProjX, $minProjY, $maxProjY) {
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
            })->filter(function (array $object) use ($centerRaDeg, $centerDecDeg, $radiusDeg, $xHalfDeg, $atlasMode, $minProjX, $maxProjX, $minProjY, $maxProjY, $objProjEpsX, $objProjEpsY) {
                if ($atlasMode) {
                    $proj = $this->atlasSphericalProject($object['ra_degrees'], $object['decl_degrees'], $centerRaDeg, $centerDecDeg);
                    return $proj['x'] >= ($minProjX - $objProjEpsX) && $proj['x'] <= ($maxProjX + $objProjEpsX) && $proj['y'] >= ($minProjY - $objProjEpsY) && $proj['y'] <= ($maxProjY + $objProjEpsY);
                }

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
        static $cached = null;

        if ($cached instanceof Collection) {
            return $cached;
        }

        $boundaries = DB::table('constellation_boundaries')
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

        $cached = $boundaries;

        return $cached;
    }

    /**
     * The constellation figures, flattened into individual line segments.
     *
     * The table stores each figure as ordered polylines; a chart only ever
     * draws the pieces that fall on it, so the joins carry no information and
     * segments are what the renderer wants. Each one carries its own angular
     * length, which is what lets a chart reject the ones nowhere near it
     * without projecting them first.
     *
     * @return Collection<int, array<string, float>>
     */
    public function loadConstellationLines(): Collection
    {
        static $cached = null;

        if ($cached instanceof Collection) {
            return $cached;
        }

        if (! Schema::hasTable('constellation_lines')) {
            return $cached = collect();
        }

        $segments = [];
        $previous = null;

        $rows = DB::table('constellation_lines')
            ->select(['con', 'line_index', 'point_index', 'ra_degrees', 'decl_degrees'])
            ->orderBy('con')
            ->orderBy('line_index')
            ->orderBy('point_index')
            ->get();

        foreach ($rows as $row) {
            $current = [
                'key' => (string) $row->con . '|' . (int) $row->line_index,
                'ra' => (float) $row->ra_degrees,
                'decl' => (float) $row->decl_degrees,
            ];

            if ($previous !== null && $previous['key'] === $current['key']) {
                $segments[] = [
                    'ra0_degrees' => $previous['ra'],
                    'decl0_degrees' => $previous['decl'],
                    'ra1_degrees' => $current['ra'],
                    'decl1_degrees' => $current['decl'],
                    'length_deg' => $this->angularDistanceDeg(
                        $previous['ra'],
                        $previous['decl'],
                        $current['ra'],
                        $current['decl']
                    ),
                ];
            }

            $previous = $current;
        }

        return $cached = collect($segments);
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
        Collection $boundaries,
        Collection $constellationLines,
        bool $fast = false,
        bool $atlasMode = false,
        ?array $layout = null,
        ?array $atlasPage = null
    ): string {
        $layout ??= $this->resolveAtlasLayout($atlasMode, false);
        $width = (int) $layout['width'];
        $height = (int) $layout['height'];
        $left = (float) $layout['left'];
        $right = (float) $layout['right'];
        $top = (float) $layout['top'];
        $bottom = (float) $layout['bottom'];
        $plotWidth = (float) $layout['plot_width'];
        $plotHeight = (float) $layout['plot_height'];
        $this->atlasProjectionEnabled = $atlasMode;
        $atlasPlotAspect = $plotWidth / max(1.0, $plotHeight);
        $xHalfDeg = $this->xHalfDegrees($radiusDeg, $fast, $atlasMode, $atlasPlotAspect);
        $yHalfDeg = $radiusDeg;
        $cosDec = max(0.01, abs(cos(deg2rad($centerDecDeg))));
        $degPerPx = (2 * $yHalfDeg) / max(1.0, $plotHeight);
        $arcMinToPx = $degPerPx > 0.0 ? (1.0 / (60.0 * $degPerPx)) : 1.0;

        $svg = [];
        $svg[] = sprintf('<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d" viewBox="0 0 %d %d" preserveAspectRatio="xMidYMid meet">', $width, $height, $width, $height);
        $svg[] = '<rect width="100%" height="100%" fill="#ffffff" />';
        $svg[] = sprintf('<rect x="%0.2f" y="14" width="%0.2f" height="%0.2f" fill="#ffffff" stroke="#888" stroke-width="0.8" />', $left, $plotWidth, $this->topLegendBoxHeight($plotWidth));
        $svg[] = $this->renderTopLegend($left + 8, 33, $plotWidth);
        $svg[] = $this->renderAtlasPageNumber($width, $plotWidth, $atlasPage);
        $svg[] = sprintf(
            '<text x="%0.2f" y="27" font-size="10" text-anchor="end" fill="#4b5563">%s</text>',
            $left + $plotWidth - 8,
            htmlspecialchars($target->name . '  -  ' . now()->format('d M Y'), ENT_QUOTES, 'UTF-8')
        );
        $svg[] = sprintf('<rect x="%0.2f" y="%0.2f" width="%0.2f" height="%0.2f" fill="#f8f8f8" stroke="#111827" stroke-width="2" />', $left, $top, $plotWidth, $plotHeight);

        // Define a clipping region matching the star-plot rectangle so that
        // object symbols (rectangles, ellipses, etc.) are not drawn outside
        // the visible star field area.

        $neighbourBoxes = $atlasMode
            ? $this->atlasNeighbourBoxes($left, $top, $plotWidth, $plotHeight, $atlasPage)
            : [];

        if ($atlasMode) {
            // Atlas pages always use the stereographic (cos/sin-corrected)
            // projection centered on the requested coordinates, so the same
            // grid renderer produces correctly curved RA/Dec lines whether
            // or not the field happens to include a celestial pole.
            $svg[] = sprintf(
                '<g>%s</g>',
                $this->renderAtlasGrid($centerRaDeg, $centerDecDeg, $xHalfDeg, $yHalfDeg, $left, $top, $plotWidth, $plotHeight, $neighbourBoxes)
            );
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

        // Begin group for boundaries, contours, stars and object symbols.
        // We avoid using an SVG clipPath here because some rasterizers
        // (used for PDF generation) incorrectly apply clipPathUnits and
        // can hide entire groups. Individual drawing helpers already
        // perform clipping so the group wrapper is sufficient.
        $svg[] = '<g>';

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

        // The constellation figures go on top of the boundaries but under
        // everything the chart is actually about, so they read as background.
        $figurePath = $this->renderConstellationFigurePaths(
            $constellationLines,
            $centerRaDeg,
            $centerDecDeg,
            $xHalfDeg,
            $yHalfDeg,
            $left,
            $top,
            $plotWidth,
            $plotHeight
        );

        if ($figurePath !== '') {
            $svg[] = sprintf(
                '<path d="%s" fill="none" stroke="#4b5563" stroke-width="1.1" stroke-linecap="round" opacity="0.55" />',
                $figurePath
            );
        }

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

        // Keep label placement complete even when callers request query
        // optimizations through the fast flag.
        $labelBudget = max(1000, $objects->count());
        $occupiedLabelBoxes = [];
        $labelTasks = [];

        // Bulk-query observations/drawings for visible objects so labels retain
        // their seen/drawn indicators without per-object queries.
        $objectKeys = [];
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
                $labelSvg = $this->placeObjectLabel($starName, (float) $point['x'], (float) $point['y'], $left, $top, $plotWidth, $plotHeight, $occupiedLabelBoxes, 7.8);
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
        // Target labels go first: they may never be dropped, so they claim their
        // spot before the surrounding objects compete for it.
        usort($labelTasks, fn(array $a, array $b) => ((int) !empty($b['is_target'])) <=> ((int) !empty($a['is_target'])));

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
                    $task['user_logged_in'] ?? false,
                    true
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

        // Drawn last so no star or label can end up on top of them.
        $svg[] = $this->renderAtlasNeighbourPages($neighbourBoxes);

        $svg[] = '</svg>';

        $this->atlasProjectionEnabled = false;
        $this->atlasProjectionContext = [];

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

    /**
     * One SVG path holding every constellation figure line that touches the
     * chart.
     *
     * Figure lines join star to star, so they are great circles and have to
     * be sampled as such -- interpolating in right ascension and declination,
     * the way the boundaries are, would bend them away from the stars they
     * connect. Segments that cannot reach the field are dropped before any of
     * that: a full atlas projects these for every one of its pages.
     *
     * @param  Collection<int, array<string, float>>  $constellationLines
     */
    private function renderConstellationFigurePaths(
        Collection $constellationLines,
        float $centerRaDeg,
        float $centerDecDeg,
        float $xHalfDeg,
        float $yHalfDeg,
        float $left,
        float $top,
        float $plotWidth,
        float $plotHeight
    ): string {
        if ($constellationLines->isEmpty()) {
            return '';
        }

        $rect = [
            'min_x' => $left,
            'min_y' => $top,
            'max_x' => $left + $plotWidth,
            'max_y' => $top + $plotHeight,
        ];

        // Half the diagonal of the field, with a little room for the
        // projection stretching a corner further than the flat geometry says.
        $fieldRadiusDeg = 1.1 * sqrt(($xHalfDeg * $xHalfDeg) + ($yHalfDeg * $yHalfDeg));

        // Sample finely enough that a straight line between two samples is
        // indistinguishable from the projected curve at this field size.
        $stepDeg = max(0.25, $fieldRadiusDeg / 8.0);

        $pathParts = [];

        foreach ($constellationLines as $segment) {
            $lengthDeg = (float) $segment['length_deg'];
            $reach = $fieldRadiusDeg + $lengthDeg;

            if ($this->angularDistanceDeg($centerRaDeg, $centerDecDeg, (float) $segment['ra0_degrees'], (float) $segment['decl0_degrees']) > $reach
                && $this->angularDistanceDeg($centerRaDeg, $centerDecDeg, (float) $segment['ra1_degrees'], (float) $segment['decl1_degrees']) > $reach) {
                continue;
            }

            $steps = max(1, min(64, (int) ceil($lengthDeg / $stepDeg)));
            $previous = null;

            for ($i = 0; $i <= $steps; $i++) {
                [$ra, $dec] = $this->greatCirclePoint(
                    (float) $segment['ra0_degrees'],
                    (float) $segment['decl0_degrees'],
                    (float) $segment['ra1_degrees'],
                    (float) $segment['decl1_degrees'],
                    $i / $steps
                );

                $current = $this->projectToAtlasChartUnclipped($ra, $dec, $centerRaDeg, $centerDecDeg, $xHalfDeg, $yHalfDeg, $left, $top, $plotWidth, $plotHeight);

                if ($previous !== null) {
                    $clipped = $this->clipLineToRect((float) $previous['x'], (float) $previous['y'], (float) $current['x'], (float) $current['y'], $rect);
                    if ($clipped !== null) {
                        $pathParts[] = sprintf('M %0.2f %0.2f L %0.2f %0.2f', $clipped['x1'], $clipped['y1'], $clipped['x2'], $clipped['y2']);
                    }
                }

                $previous = $current;
            }
        }

        return implode(' ', $pathParts);
    }

    /**
     * A point a fraction of the way along the great circle joining two
     * positions, as [right ascension, declination] in degrees.
     *
     * @return array{0: float, 1: float}
     */
    public function greatCirclePoint(float $ra0Deg, float $dec0Deg, float $ra1Deg, float $dec1Deg, float $fraction): array
    {
        $a = $this->toUnitVector($ra0Deg, $dec0Deg);
        $b = $this->toUnitVector($ra1Deg, $dec1Deg);

        $dot = max(-1.0, min(1.0, ($a[0] * $b[0]) + ($a[1] * $b[1]) + ($a[2] * $b[2])));
        $omega = acos($dot);

        if ($omega < 1.0e-9) {
            return [$ra0Deg, $dec0Deg];
        }

        $sinOmega = sin($omega);
        $scaleA = sin((1.0 - $fraction) * $omega) / $sinOmega;
        $scaleB = sin($fraction * $omega) / $sinOmega;

        $x = ($a[0] * $scaleA) + ($b[0] * $scaleB);
        $y = ($a[1] * $scaleA) + ($b[1] * $scaleB);
        $z = ($a[2] * $scaleA) + ($b[2] * $scaleB);

        return [
            $this->normalizeRaDegrees(rad2deg(atan2($y, $x))),
            rad2deg(asin(max(-1.0, min(1.0, $z / max(1.0e-12, sqrt(($x * $x) + ($y * $y) + ($z * $z))))))),
        ];
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
        float $plotHeight,
        ?array $projectionBounds = null
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
        if ($this->atlasProjectionEnabled) {
            if ($this->atlasProjectionContext === []) {
                $this->prepareAtlasProjectionContext($centerRaDeg, $centerDecDeg, $xHalfDeg, $yHalfDeg, $left, $top, $plotWidth, $plotHeight);
            }

            $projected = $this->atlasSphericalProject($raDeg, $decDeg, $centerRaDeg, $centerDecDeg);
            $minX = $this->atlasProjectionContext['min_x'];
            $maxX = $this->atlasProjectionContext['max_x'];
            $minY = $this->atlasProjectionContext['min_y'];
            $maxY = $this->atlasProjectionContext['max_y'];

            $xNorm = ($maxX - $projected['x']) / max(1.0e-9, $maxX - $minX);
            $yNorm = ($maxY - $projected['y']) / max(1.0e-9, $maxY - $minY);

            $x = $left + ($xNorm * $plotWidth);
            $y = $top + ($yNorm * $plotHeight);

            return ['x' => $x, 'y' => $y];
        }

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
        // If the requested RA window spans (or nearly spans) the entire
        // circle, do not apply any RA-based filtering. This avoids the
        // degenerate case where a ±180° window normalizes to equal
        // min/max values and would inadvertently select only a narrow
        // band (or none) due to normalization semantics.
        if ($windowDeg >= 180.0 - 1.0e-9) {
            return;
        }

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

    /**
     * Clip a line to the plot rectangle, or leave it whole when there is none.
     *
     * Symbols drawn outside a chart -- the front page legend -- have no
     * rectangle to clip against, and every one of them is meant to be shown
     * in full.
     */
    private function clipLineToRect(float $x1, float $y1, float $x2, float $y2, ?array $rect): ?array
    {
        if ($rect === null) {
            return ['x1' => $x1, 'y1' => $y1, 'x2' => $x2, 'y2' => $y2];
        }

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
        bool $userLoggedIn = false,
        bool $allowOverlap = false
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
        for ($a = 0; $a < 360; $a += 15) {
            $angles[] = deg2rad($a);
        }

        foreach ([20, 30, 40, 55, 70, 90] as $radius) {
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

        // Nothing is free. Drawing here would put this label on top of one that
        // is already placed, so the label is dropped instead — an unreadable
        // pile of overlapping names is worse than a missing one. Only the
        // target of the chart is important enough to be drawn anyway.
        if (!$allowOverlap) {
            return '';
        }

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

    private function renderTopLegend(float $startX, float $baselineY, float $plotWidth): string
    {
        $svg = [];
        $x = $startX;

        foreach (self::LEGEND_MAGNITUDES as $mag) {
            $radius = $this->starRadius($mag);
            $svg[] = sprintf('<circle cx="%0.2f" cy="%0.2f" r="%0.2f" fill="#111" />', $x, $baselineY - 9, $radius);
            $svg[] = sprintf('<text x="%0.2f" y="%0.2f" font-size="8" fill="#4b5563">%s</text>', $x + 8, $baselineY - 7, number_format($mag, 1));
            $x += self::LEGEND_MAGNITUDE_STEP;
        }

        // Narrow (portrait) charts cannot fit both groups on one line, so the
        // object types move to a second row underneath the star magnitudes.
        if ($this->topLegendWraps($plotWidth)) {
            $x = $startX;
            $baselineY += self::LEGEND_ROW_STEP;
        } else {
            $x += 5;
        }

        foreach (self::LEGEND_OBJECT_TYPES as $code) {
            $svg[] = $this->renderLegendSymbol($code, $x, $baselineY - 8);
            $svg[] = sprintf('<text x="%0.2f" y="%0.2f" font-size="8" fill="#4b5563">%s</text>', $x + 14, $baselineY - 6, $code);
            $x += self::LEGEND_OBJECT_STEP;
        }

        return implode('', $svg);
    }

    /**
     * Whether the top legend needs a second row for the object types.
     */
    private function topLegendWraps(float $plotWidth): bool
    {
        // Width taken by both groups on a single row, including the 8px inset
        // on the left, the 5px gap between the groups and the widest label of
        // the last object type entry.
        $required = 8.0
            + (count(self::LEGEND_MAGNITUDES) * self::LEGEND_MAGNITUDE_STEP)
            + 5.0
            + ((count(self::LEGEND_OBJECT_TYPES) - 1) * self::LEGEND_OBJECT_STEP)
            + 50.0;

        return $required > $plotWidth;
    }

    /**
     * Height of the header box holding the top legend.
     */
    private function topLegendBoxHeight(float $plotWidth): float
    {
        return $this->topLegendWraps($plotWidth) ? 34.0 + self::LEGEND_ROW_STEP : 34.0;
    }

    /**
     * Render only a large page number for the selected atlas variant.
     * The number is rendered to the right of the plot rectangle.
     */
    /**
     * Page number of this chart, in the margin beside the top legend box.
     *
     * The type size follows the widest page number in the atlas, so a
     * four-digit number in the detail atlas still fits the margin and every
     * page of one atlas is set the same way.
     *
     * @param  array{page?: int, total?: int}|null  $atlasPage
     */
    private function renderAtlasPageNumber(float $chartWidth, float $plotWidth, ?array $atlasPage): string
    {
        $page = (int) ($atlasPage['page'] ?? 0);
        if ($page < 1) {
            return '';
        }

        $widest = max($page, (int) ($atlasPage['total'] ?? $page));
        $digits = max(1, strlen((string) $widest));

        // Right margin, less the padding on either side of the number.
        $available = self::CHART_RIGHT - 12.0;
        $fontSize = max(11.0, min(26.0, $available / ($digits * self::DIGIT_WIDTH_RATIO)));

        return sprintf(
            '<text x="%0.2f" y="%0.2f" font-size="%0.1f" font-weight="700" fill="#111827" text-anchor="end">%d</text>',
            $chartWidth - 6.0,
            14.0 + ($this->topLegendBoxHeight($plotWidth) / 2.0) + ($fontSize * 0.36),
            $fontSize,
            $page
        );
    }

    /** Type size of the page number boxes on the edges of an atlas page. */
    private const NEIGHBOUR_PAGE_FONT_SIZE = 13.0;

    /**
     * Boxes naming the charts that adjoin this one, one per edge.
     *
     * Each box straddles the border it points at, so it reads as "the chart
     * continues here on page N". The rectangles are also handed to the
     * graticule, which skips any coordinate label they would cover.
     *
     * @param  array{left?: ?int, right?: ?int, top?: ?int, bottom?: ?int}|null  $atlasPage
     * @return array<int, array{page: int, min_x: float, min_y: float, max_x: float, max_y: float}>
     */
    private function atlasNeighbourBoxes(float $left, float $top, float $plotWidth, float $plotHeight, ?array $atlasPage): array
    {
        if ($atlasPage === null) {
            return [];
        }

        $centerX = $left + ($plotWidth / 2.0);
        $centerY = $top + ($plotHeight / 2.0);
        $edges = [
            'left' => [$left, $centerY],
            'right' => [$left + $plotWidth, $centerY],
            'top' => [$centerX, $top],
            'bottom' => [$centerX, $top + $plotHeight],
        ];

        $fontSize = self::NEIGHBOUR_PAGE_FONT_SIZE;
        $height = $fontSize + 11.0;
        $boxes = [];

        foreach ($edges as $side => [$x, $y]) {
            $page = (int) ($atlasPage[$side] ?? 0);
            if ($page < 1) {
                continue;
            }

            $width = (strlen((string) $page) * $fontSize * self::DIGIT_WIDTH_RATIO) + 14.0;

            $boxes[] = [
                'page' => $page,
                'min_x' => $x - ($width / 2.0),
                'min_y' => $y - ($height / 2.0),
                'max_x' => $x + ($width / 2.0),
                'max_y' => $y + ($height / 2.0),
            ];
        }

        return $boxes;
    }

    /**
     * @param  array<int, array{page: int, min_x: float, min_y: float, max_x: float, max_y: float}>  $boxes
     */
    private function renderAtlasNeighbourPages(array $boxes): string
    {
        $fontSize = self::NEIGHBOUR_PAGE_FONT_SIZE;
        $svg = [];

        foreach ($boxes as $box) {
            $svg[] = sprintf(
                '<g><rect x="%0.2f" y="%0.2f" width="%0.2f" height="%0.2f" fill="#ffffff" stroke="#111827" stroke-width="1.2" />'
                . '<text x="%0.2f" y="%0.2f" font-size="%0.1f" text-anchor="middle" fill="#111827">%d</text></g>',
                $box['min_x'],
                $box['min_y'],
                $box['max_x'] - $box['min_x'],
                $box['max_y'] - $box['min_y'],
                ($box['min_x'] + $box['max_x']) / 2.0,
                (($box['min_y'] + $box['max_y']) / 2.0) + ($fontSize * 0.36),
                $fontSize,
                $box['page']
            );
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

    /**
     * Draw the RA/Dec graticule of an atlas page.
     *
     * Lines are drawn at round coordinates (whole degrees, whole minutes of
     * time, ...) right across the page, and every label sits where its own
     * line actually meets the edge of the plot. The projection is linear in
     * neither axis -- next to a pole least of all -- so labels placed at
     * evenly spaced pixel positions name the wrong lines: a "+90" written in
     * the top right corner while the pole itself is drawn well below it.
     *
     * @param  array<int, array{min_x: float, min_y: float, max_x: float, max_y: float}>  $reserved
     *     Areas already spoken for, such as the adjoining-page boxes.
     */
    private function renderAtlasGrid(float $centerRaDeg, float $centerDecDeg, float $xHalfDeg, float $yHalfDeg, float $left, float $top, float $plotWidth, float $plotHeight, array $reserved = []): string
    {
        if ($this->atlasProjectionContext === []) {
            $this->prepareAtlasProjectionContext($centerRaDeg, $centerDecDeg, $xHalfDeg, $yHalfDeg, $left, $top, $plotWidth, $plotHeight);
        }

        $rect = [
            'min_x' => $left,
            'min_y' => $top,
            'max_x' => $left + $plotWidth,
            'max_y' => $top + $plotHeight,
        ];

        $window = $this->atlasSkyWindow($centerRaDeg, $centerDecDeg, [
            $this->atlasProjectionContext['min_x'],
            $this->atlasProjectionContext['max_x'],
            $this->atlasProjectionContext['min_y'],
            $this->atlasProjectionContext['max_y'],
        ]);

        $decMin = $window['dec_min'];
        $decMax = $window['dec_max'];
        $raWindow = $window['ra_window'];
        $poleOnPage = $raWindow >= 180.0 - 1.0e-9;

        $decStep = $this->niceGridStep($decMax - $decMin, self::GRID_DEC_STEPS_DEG);
        // With a pole on the page every meridian runs through it, so use the
        // hour lines a polar chart is normally drawn with rather than a step
        // derived from a right ascension range that spans the whole circle.
        $raStepHours = $poleOnPage
            ? 1.0
            : $this->niceGridStep((2.0 * $raWindow) / 15.0, self::GRID_RA_STEPS_HOURS);

        $svg = [];
        $decLabels = [];
        // Right ascension is read off both the top and the bottom of the page,
        // so a meridian can be followed from either end. The two scales differ
        // wherever the meridians are not vertical, and next to a pole they
        // differ completely: the edge beyond the pole carries the meridians
        // that run over it to the far side of the sky.
        $raLabels = ['top' => [], 'bottom' => []];

        // Parallels of declination.
        $raSpan = $poleOnPage ? 180.0 : min(180.0, $raWindow * 1.05);
        $raSamples = $poleOnPage ? 360 : 180;
        $firstDecIndex = (int) ceil(($decMin / $decStep) - 1.0e-9);
        $lastDecIndex = (int) floor(($decMax / $decStep) + 1.0e-9);

        for ($k = $firstDecIndex; $k <= $lastDecIndex; $k++) {
            $decDeg = $k * $decStep;
            if (abs($decDeg) >= 90.0) {
                // A pole is a point, not a parallel; it gets its own label.
                continue;
            }

            $points = [];
            for ($i = 0; $i <= $raSamples; $i++) {
                $raDeg = $centerRaDeg - $raSpan + ((2.0 * $raSpan * $i) / $raSamples);
                $points[] = $this->atlasGridPoint($raDeg, $decDeg, $centerRaDeg, $centerDecDeg, $xHalfDeg, $yHalfDeg, $left, $top, $plotWidth, $plotHeight);
            }

            $svg[] = $this->renderGridPolyline($points, $rect);

            $crossing = $this->gridEdgeCrossing($points, $rect, 'right');
            if ($crossing !== null) {
                $decLabels[] = ['y' => $crossing['y'], 'text' => $this->formatDecDms($decDeg)];
            }
        }

        // Meridians of right ascension.
        $raStepDeg = $raStepHours * 15.0;
        $decSamples = 180;
        $firstRaIndex = (int) ceil((($centerRaDeg - $raWindow) / $raStepDeg) - 1.0e-9);
        $lastRaIndex = (int) floor((($centerRaDeg + $raWindow) / $raStepDeg) + 1.0e-9);
        // A full circle must not draw its first meridian twice.
        $lastRaIndex = min($lastRaIndex, $firstRaIndex + (int) round(360.0 / $raStepDeg) - 1);

        for ($k = $firstRaIndex; $k <= $lastRaIndex; $k++) {
            $raDeg = $k * $raStepDeg;

            $points = [];
            for ($i = 0; $i <= $decSamples; $i++) {
                $decDeg = $decMin + ((($decMax - $decMin) * $i) / $decSamples);
                $points[] = $this->atlasGridPoint($raDeg, $decDeg, $centerRaDeg, $centerDecDeg, $xHalfDeg, $yHalfDeg, $left, $top, $plotWidth, $plotHeight);
            }

            $svg[] = $this->renderGridPolyline($points, $rect);

            foreach (['top', 'bottom'] as $edge) {
                $crossing = $this->gridEdgeCrossing($points, $rect, $edge);
                if ($crossing !== null) {
                    $raLabels[$edge][] = ['x' => $crossing['x'], 'text' => $this->formatRaHours($raDeg)];
                }
            }
        }

        foreach (['top' => $top - 8.0, 'bottom' => $top + $plotHeight + 14.0] as $edge => $raLabelY) {
            foreach ($this->spacedGridLabels($raLabels[$edge], 'x', 34.0) as $label) {
                $textWidth = strlen($label['text']) * 9.0 * self::DIGIT_WIDTH_RATIO;
                if ($this->overlapsReserved($label['x'] - ($textWidth / 2.0), $raLabelY - 9.0, $label['x'] + ($textWidth / 2.0), $raLabelY + 2.0, $reserved)) {
                    continue;
                }

                $svg[] = sprintf('<text x="%0.2f" y="%0.2f" font-size="9" text-anchor="middle" fill="#6b7280">%s</text>', $label['x'], $raLabelY, htmlspecialchars($label['text'], ENT_QUOTES, 'UTF-8'));
            }
        }

        foreach ($this->spacedGridLabels($decLabels, 'y', 12.0) as $label) {
            $textWidth = strlen($label['text']) * 9.0 * self::DIGIT_WIDTH_RATIO;
            if ($this->overlapsReserved($left + $plotWidth + 6, $label['y'] - 5.0, $left + $plotWidth + 6 + $textWidth, $label['y'] + 6.0, $reserved)) {
                continue;
            }

            $svg[] = sprintf('<text x="%0.2f" y="%0.2f" font-size="9" text-anchor="start" fill="#6b7280">%s</text>', $left + $plotWidth + 6, $label['y'] + 3, htmlspecialchars($label['text'], ENT_QUOTES, 'UTF-8'));
        }

        if ($poleOnPage) {
            $svg[] = $this->renderAtlasPoleLabel($centerRaDeg, $centerDecDeg, $xHalfDeg, $yHalfDeg, $left, $top, $plotWidth, $plotHeight, $rect);
        }

        return implode('', $svg);
    }

    /**
     * Project one graticule sample, or null when it lies on the far half of
     * the sky, where the stereographic projection runs away to infinity and
     * would otherwise draw a line straight across the page.
     *
     * @return array{x: float, y: float}|null
     */
    private function atlasGridPoint(float $raDeg, float $decDeg, float $centerRaDeg, float $centerDecDeg, float $xHalfDeg, float $yHalfDeg, float $left, float $top, float $plotWidth, float $plotHeight): ?array
    {
        $cosDistance = (sin(deg2rad($decDeg)) * sin(deg2rad($centerDecDeg)))
            + (cos(deg2rad($decDeg)) * cos(deg2rad($centerDecDeg)) * cos(deg2rad($raDeg - $centerRaDeg)));

        if ($cosDistance <= 0.0) {
            return null;
        }

        return $this->projectToAtlasChartUnclipped($raDeg, $decDeg, $centerRaDeg, $centerDecDeg, $xHalfDeg, $yHalfDeg, $left, $top, $plotWidth, $plotHeight);
    }

    /**
     * @param  array<int, array{x: float, y: float}|null>  $points
     * @param  array{min_x: float, min_y: float, max_x: float, max_y: float}  $rect
     */
    private function renderGridPolyline(array $points, array $rect): string
    {
        $svg = '';
        $count = count($points);

        for ($i = 1; $i < $count; $i++) {
            $from = $points[$i - 1];
            $to = $points[$i];
            if ($from === null || $to === null) {
                continue;
            }

            $clipped = $this->clipLineToRect($from['x'], $from['y'], $to['x'], $to['y'], $rect);
            if ($clipped !== null) {
                $svg .= sprintf('<line x1="%0.2f" y1="%0.2f" x2="%0.2f" y2="%0.2f" stroke="#d9d9d9" stroke-width="0.6" />', $clipped['x1'], $clipped['y1'], $clipped['x2'], $clipped['y2']);
            }
        }

        return $svg;
    }

    /**
     * First point at which a graticule line meets the given edge of the plot.
     *
     * @param  array<int, array{x: float, y: float}|null>  $points
     * @param  array{min_x: float, min_y: float, max_x: float, max_y: float}  $rect
     * @param  string  $edge  'top', 'bottom' or 'right'
     * @return array{x: float, y: float}|null
     */
    private function gridEdgeCrossing(array $points, array $rect, string $edge): ?array
    {
        $count = count($points);

        for ($i = 1; $i < $count; $i++) {
            $from = $points[$i - 1];
            $to = $points[$i];
            if ($from === null || $to === null) {
                continue;
            }

            $edgeValue = match ($edge) {
                'right' => $rect['max_x'],
                'bottom' => $rect['max_y'],
                default => $rect['min_y'],
            };
            $fromOffset = $edge === 'right' ? $from['x'] - $edgeValue : $from['y'] - $edgeValue;
            $toOffset = $edge === 'right' ? $to['x'] - $edgeValue : $to['y'] - $edgeValue;

            if (($fromOffset > 0.0 && $toOffset > 0.0) || ($fromOffset < 0.0 && $toOffset < 0.0)) {
                continue;
            }
            if (abs($fromOffset - $toOffset) < 1.0e-12) {
                continue;
            }

            $t = $fromOffset / ($fromOffset - $toOffset);
            $x = $from['x'] + (($to['x'] - $from['x']) * $t);
            $y = $from['y'] + (($to['y'] - $from['y']) * $t);

            if ($x < $rect['min_x'] - 0.5 || $x > $rect['max_x'] + 0.5) {
                continue;
            }
            if ($y < $rect['min_y'] - 0.5 || $y > $rect['max_y'] + 0.5) {
                continue;
            }

            return ['x' => $x, 'y' => $y];
        }

        return null;
    }

    /**
     * Thin out graticule labels that would be printed on top of each other.
     *
     * @param  array<int, array{x?: float, y?: float, text: string}>  $labels
     * @return array<int, array{x?: float, y?: float, text: string}>
     */
    private function spacedGridLabels(array $labels, string $axis, float $minimumGap): array
    {
        usort($labels, fn(array $a, array $b): int => $a[$axis] <=> $b[$axis]);

        $kept = [];
        foreach ($labels as $label) {
            if ($kept !== [] && ($label[$axis] - end($kept)[$axis]) < $minimumGap) {
                continue;
            }

            $kept[] = $label;
        }

        return $kept;
    }

    /**
     * @param  array<int, array{min_x: float, min_y: float, max_x: float, max_y: float}>  $reserved
     */
    private function overlapsReserved(float $minX, float $minY, float $maxX, float $maxY, array $reserved): bool
    {
        foreach ($reserved as $box) {
            if ($minX < $box['max_x'] && $maxX > $box['min_x'] && $minY < $box['max_y'] && $maxY > $box['min_y']) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array{min_x: float, min_y: float, max_x: float, max_y: float}  $rect
     */
    private function renderAtlasPoleLabel(float $centerRaDeg, float $centerDecDeg, float $xHalfDeg, float $yHalfDeg, float $left, float $top, float $plotWidth, float $plotHeight, array $rect): string
    {
        $poleDec = $centerDecDeg >= 0.0 ? 90.0 : -90.0;
        $point = $this->projectToAtlasChartUnclipped($centerRaDeg, $poleDec, $centerRaDeg, $centerDecDeg, $xHalfDeg, $yHalfDeg, $left, $top, $plotWidth, $plotHeight);

        if ($point['x'] < $rect['min_x'] || $point['x'] > $rect['max_x'] || $point['y'] < $rect['min_y'] || $point['y'] > $rect['max_y']) {
            return '';
        }

        return sprintf(
            '<text x="%0.2f" y="%0.2f" font-size="9" text-anchor="start" fill="#6b7280">%s</text>',
            min($point['x'] + 5.0, $rect['max_x'] - 34.0),
            max($point['y'] - 5.0, $rect['min_y'] + 9.0),
            htmlspecialchars($this->formatDecDms($poleDec), ENT_QUOTES, 'UTF-8')
        );
    }

    /**
     * Largest candidate step that still puts at least six lines across a span.
     *
     * @param  array<int, float>  $candidates  descending
     */
    private function niceGridStep(float $span, array $candidates): float
    {
        $target = max(1.0e-9, $span) / 6.0;

        foreach ($candidates as $candidate) {
            if ($candidate <= $target) {
                return $candidate;
            }
        }

        return (float) end($candidates);
    }

    private function starRadius(?float $magnitude): float
    {
        if ($magnitude === null) {
            return 1.0;
        }

        // Scale across the full chart range (roughly mag 1..14). The atlas
        // PDF pipeline rasterizes the SVG at roughly 1.5x its viewBox units,
        // so the floor/curve here are kept small enough that the faintest
        // stars still shrink to near-invisible points after that upscale,
        // matching traditional printed star atlases.
        $normalized = (14.0 - $magnitude) / 13.0;
        $normalized = max(0.0, min(1.0, $normalized));

        return 0.25 + (7.25 * ($normalized ** 2.8));
    }

    private function resolveMagnitudeLimit(float $radiusArcMin, ?float $magnitudeLimit): float
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

    private function xHalfDegrees(float $radiusDeg, bool $fast = false, bool $atlasMode = false, ?float $atlasPlotAspect = null): float
    {
        if ($atlasMode) {
            $factor = $atlasPlotAspect
                ?? (self::CHART_WIDTH - self::CHART_LEFT - self::CHART_RIGHT)
                / (self::CHART_HEIGHT - self::CHART_TOP - self::CHART_BOTTOM);

            // Keep landscape atlas behavior unchanged. For portrait atlas
            // pages, do not clamp to a square field: horizontal FoV should
            // shrink with the portrait plot aspect.
            if ($factor < 1.0) {
                return max(0.1, $radiusDeg * $factor);
            }

            return max($radiusDeg, $radiusDeg * $factor);
        }

        return max($radiusDeg, $radiusDeg * 2.3);
    }

    /**
     * @return array{width:int,height:int,left:int,right:int,top:int,bottom:int,plot_width:int,plot_height:int}
     */
    private function resolveAtlasLayout(bool $atlasMode, bool $portraitAtlasLayout): array
    {
        $width = self::CHART_WIDTH;
        $height = self::CHART_HEIGHT;

        if ($atlasMode && $portraitAtlasLayout) {
            $width = self::PORTRAIT_ATLAS_CHART_WIDTH;
            $height = self::PORTRAIT_ATLAS_CHART_HEIGHT;
        }

        $left = self::CHART_LEFT;
        $right = self::CHART_RIGHT;
        $bottom = self::CHART_BOTTOM;
        $plotWidth = $width - $left - $right;

        // A wrapped legend makes the header box taller, so the plot starts
        // lower to keep the same gap below the header.
        $top = self::CHART_TOP + ($this->topLegendBoxHeight($plotWidth) - 34.0);

        return [
            'width' => $width,
            'height' => $height,
            'left' => $left,
            'right' => $right,
            'top' => $top,
            'bottom' => $bottom,
            'plot_width' => $plotWidth,
            'plot_height' => $height - $top - $bottom,
        ];
    }

    /**
     * Projected bounds of an atlas page.
     *
     * A page shows a fixed angular field: $xHalfDeg horizontally and
     * $yHalfDeg vertically from its centre to its edge. In the stereographic
     * projection used here a point at angular distance theta lands at radius
     * 2*tan(theta/2), so the bounds follow straight from the requested field.
     *
     * Deriving them instead by sampling the border of an RA/Dec rectangle --
     * as this used to -- breaks next to a pole: RA offsets divided by
     * cos(dec) wrap right around the pole, and the samples that land on the
     * far side of it inflate the field, push the pole away from the top edge
     * and put sky beyond the pole on the page.
     *
     * @return array{0: float, 1: float, 2: float, 3: float} [minX, maxX, minY, maxY]
     */
    private function atlasProjectionBounds(float $xHalfDeg, float $yHalfDeg, float $plotWidth, float $plotHeight): array
    {
        $halfX = 2.0 * tan(deg2rad(max(0.001, min(89.0, $xHalfDeg))) / 2.0);
        $halfY = 2.0 * tan(deg2rad(max(0.001, min(89.0, $yHalfDeg))) / 2.0);

        [$minX, $maxX, $minY, $maxY] = $this->fitAtlasBoundsToPlot(
            -$halfX,
            $halfX,
            -$halfY,
            $halfY,
            $plotWidth,
            $plotHeight
        );

        // Hairline margin so content sitting exactly on an edge is not lost
        // to floating point comparisons.
        $dx = max(1.0e-9, $maxX - $minX);
        $dy = max(1.0e-9, $maxY - $minY);
        $marginFactor = 0.002;

        return [
            $minX - ($dx * $marginFactor),
            $maxX + ($dx * $marginFactor),
            $minY - ($dy * $marginFactor),
            $maxY + ($dy * $marginFactor),
        ];
    }

    /**
     * RA/Dec window covered by the projected bounds of an atlas page.
     *
     * Everything the page can show lies inside this window, so it is what the
     * star and object queries select on. The extremes of a stereographic page
     * always sit on its border -- unless that border encloses a celestial
     * pole, in which case every right ascension is on the page.
     *
     * @param  array{0: float, 1: float, 2: float, 3: float}  $bounds
     * @return array{dec_min: float, dec_max: float, ra_window: float}
     */
    private function atlasSkyWindow(float $centerRaDeg, float $centerDecDeg, array $bounds): array
    {
        [$minX, $maxX, $minY, $maxY] = $bounds;

        $decMin = 90.0;
        $decMax = -90.0;
        $raWindow = 0.0;
        $steps = 96;

        for ($i = 0; $i <= $steps; $i++) {
            $t = $i / $steps;
            $x = $minX + (($maxX - $minX) * $t);
            $y = $minY + (($maxY - $minY) * $t);

            foreach ([[$x, $minY], [$x, $maxY], [$minX, $y], [$maxX, $y]] as [$px, $py]) {
                [$raDeg, $decDeg] = $this->atlasSphericalUnproject($px, $py, $centerRaDeg, $centerDecDeg);
                $decMin = min($decMin, $decDeg);
                $decMax = max($decMax, $decDeg);
                $raWindow = max($raWindow, abs($this->normalizeRaDelta($raDeg - $centerRaDeg)));
            }
        }

        foreach ([90.0, -90.0] as $poleDec) {
            $pole = $this->atlasSphericalProject($centerRaDeg, $poleDec, $centerRaDeg, $centerDecDeg);
            if ($pole['x'] < $minX || $pole['x'] > $maxX || $pole['y'] < $minY || $pole['y'] > $maxY) {
                continue;
            }

            // The pole is on the page, so the page spans every right ascension.
            $raWindow = 180.0;
            if ($poleDec > 0.0) {
                $decMax = 90.0;
            } else {
                $decMin = -90.0;
            }
        }

        return [
            'dec_min' => max(-90.0, $decMin),
            'dec_max' => min(90.0, $decMax),
            'ra_window' => min(180.0, $raWindow),
        ];
    }

    /**
     * Prepare spherical projection bounds for atlas rendering.
     */
    private function prepareAtlasProjectionContext(float $centerRaDeg, float $centerDecDeg, float $xHalfDeg, float $yHalfDeg, float $left, float $top, float $plotWidth, float $plotHeight): void
    {
        [$minX, $maxX, $minY, $maxY] = $this->atlasProjectionBounds($xHalfDeg, $yHalfDeg, $plotWidth, $plotHeight);

        $this->atlasProjectionContext = [
            'min_x' => $minX,
            'max_x' => $maxX,
            'min_y' => $minY,
            'max_y' => $maxY,
            'left' => $left,
            'top' => $top,
            'plot_width' => $plotWidth,
            'plot_height' => $plotHeight,
        ];
    }

    /**
     * Spherical projection used for atlas charts.
     * Returns raw projected coordinates before normalization to the SVG box.
     *
     * @return array{x: float, y: float}
     */
    private function atlasSphericalProject(float $raDeg, float $decDeg, float $centerRaDeg, float $centerDecDeg): array
    {
        $ra = deg2rad($raDeg);
        $dec = deg2rad($decDeg);
        $ra0 = deg2rad($centerRaDeg);
        $dec0 = deg2rad($centerDecDeg);

        $dra = $ra - $ra0;
        $sinDec = sin($dec);
        $cosDec = cos($dec);
        $sinDec0 = sin($dec0);
        $cosDec0 = cos($dec0);

        $denom = 1.0 + ($sinDec0 * $sinDec) + ($cosDec0 * $cosDec * cos($dra));
        if (abs($denom) < 1.0e-9) {
            $denom = 1.0e-9;
        }

        $k = 2.0 / $denom;

        return [
            'x' => $k * $cosDec * sin($dra),
            'y' => $k * (($cosDec0 * $sinDec) - ($sinDec0 * $cosDec * cos($dra))),
        ];
    }

    /**
     * Inverse of atlasSphericalProject(): projected plane back to RA/Dec.
     *
     * @return array{0: float, 1: float} [raDeg, decDeg]
     */
    private function atlasSphericalUnproject(float $x, float $y, float $centerRaDeg, float $centerDecDeg): array
    {
        $rho = hypot($x, $y);
        if ($rho < 1.0e-12) {
            return [$this->normalizeRaDegrees($centerRaDeg), $centerDecDeg];
        }

        $c = 2.0 * atan($rho / 2.0);
        $sinC = sin($c);
        $cosC = cos($c);
        $dec0 = deg2rad($centerDecDeg);

        $dec = asin(max(-1.0, min(1.0, ($cosC * sin($dec0)) + (($y * $sinC * cos($dec0)) / $rho))));
        $ra = deg2rad($centerRaDeg) + atan2(
            $x * $sinC,
            ($rho * cos($dec0) * $cosC) - ($y * sin($dec0) * $sinC)
        );

        return [$this->normalizeRaDegrees(rad2deg($ra)), rad2deg($dec)];
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

    /**
     * Make the projected sky footprint match the rectangular chart aspect
     * ratio without changing the underlying stereographic projection.
     *
     * @return array{0: float, 1: float, 2: float, 3: float}
     */
    private function fitAtlasBoundsToPlot(float $minX, float $maxX, float $minY, float $maxY, float $plotWidth, float $plotHeight): array
    {
        $width = max(1.0e-9, $maxX - $minX);
        $height = max(1.0e-9, $maxY - $minY);
        $plotAspect = max(1.0e-9, $plotWidth / $plotHeight);
        $boundsAspect = $width / $height;

        if ($boundsAspect < $plotAspect) {
            $newWidth = $height * $plotAspect;
            $padding = ($newWidth - $width) / 2.0;
            $minX -= $padding;
            $maxX += $padding;
        } elseif ($boundsAspect > $plotAspect) {
            $newHeight = $width / $plotAspect;
            $padding = ($newHeight - $height) / 2.0;
            $minY -= $padding;
            $maxY += $padding;
        }

        return [$minX, $maxX, $minY, $maxY];
    }

    private function formatRaHours(float $raDeg): string
    {
        // Round rather than truncate: a grid line at a round 21h reaches this
        // as 20.99999... and must not be labelled 20h59m.
        $totalMinutes = (int) round(($this->normalizeRaDegrees($raDeg) / 15.0) * 60.0);
        $totalMinutes %= 24 * 60;

        return sprintf('%02dh%02dm', intdiv($totalMinutes, 60), $totalMinutes % 60);
    }

    private function formatDecDms(float $decDeg): string
    {
        $sign = $decDeg < 0 ? '-' : '+';
        $totalMinutes = (int) round(abs($decDeg) * 60.0);

        return sprintf('%s%02d°%02d\'', $sign, intdiv($totalMinutes, 60), $totalMinutes % 60);
    }
}
