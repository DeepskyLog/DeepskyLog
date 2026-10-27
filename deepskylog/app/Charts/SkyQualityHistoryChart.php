<?php

namespace App\Charts;

use App\Services\LocationSkyHistory;
use Carbon\Carbon;
use marineusde\LarapexCharts\Charts\LineChart as OriginalLineChart;

/**
 * The SQM and NELM of a location over the years: a point per night and a
 * line through the median of each year (at the middle of the year). Both
 * are drawn on one scale of sky brightness, so the same height is the same
 * sky: a NELM is placed at its SQM (sqmOfNelm(), with the fstOffset of the
 * location's owner, who made the observations), the left axis gives the
 * SQM and the right axis the NELM of the same heights (the script on the
 * location page labels it, and gives every point its own value in the
 * tooltip). All series are lines, the nights without stroke: ApexCharts
 * misplaces the lines of a mixed scatter/line chart whose series differ in
 * length.
 */
class SkyQualityHistoryChart
{
    private const COLORS = ['sqm' => '#38bdf8', 'nelm' => '#f59e0b'];

    /** The NELM of a sky brighter than LocationSkyHistory::SQM_RANGE[1] is drawn at that SQM. */
    private const MAX_SQM = LocationSkyHistory::SQM_RANGE[1];

    public function build(string $locationName, LocationSkyHistory $history, float $fstOffset = 0.0): OriginalLineChart
    {
        $series = [];
        $axes = [];
        $colors = [];
        $markers = [];
        $strokes = [];

        $showSqm = $history->has('sqm');
        // The series by their abbreviation (the translations of 'SQM' and 'NELM' explain them), the axes explained
        $quantities = [
            'sqm' => ['SQM', __('SQM (mag/arcsec²)')],
            'nelm' => ['NELM', __('NELM')],
        ];
        foreach ($quantities as $quantity => [$name, $axisTitle]) {
            if (! $history->has($quantity)) {
                continue;
            }

            $nights = [];
            foreach ($history->nights as $night) {
                if ($night[$quantity] !== null) {
                    $nights[] = self::point($night['date'], $quantity, $night[$quantity], $fstOffset);
                }
            }
            $years = [];
            foreach ($history->years as $year => $values) {
                if ($values[$quantity] !== null) {
                    $years[] = self::point($year.'-07-01', $quantity, $values[$quantity], $fstOffset);
                }
            }

            // The NELM on the right, unless there is no SQM
            $axis = [
                'seriesName' => $name,
                'opposite' => $quantity === 'nelm' && $showSqm,
                'title' => ['text' => $axisTitle],
                'nelmLabels' => $quantity === 'nelm',
                'nelmOffset' => $fstOffset,
            ];
            $series[] = ['name' => $name, 'data' => $nights];
            $axes[] = $axis;
            $colors[] = self::COLORS[$quantity];
            $markers[] = 4;
            $strokes[] = 0;

            // A line needs two years
            if (count($years) > 1) {
                $series[] = ['name' => $name.', '.__('median per year'), 'data' => $years];
                $axes[] = [...$axis, 'show' => false];
                $colors[] = self::COLORS[$quantity];
                $markers[] = 0;
                $strokes[] = 3;
            }
        }

        // Every axis the same scale, so the grid lines of the SQM and of the NELM are the same heights
        $heights = array_merge(...array_map(fn ($s) => array_column($s['data'], 'y'), $series));
        $scale = self::scale(min($heights), max($heights));
        $yaxis = array_map(fn ($axis) => [...$axis, ...$scale, 'decimalsInFloat' => 2], $axes);

        return (new OriginalLineChart)
            ->setTitle(__('Sky quality history: ').$locationName)
            ->setSubtitle(__('Source: ').config('app.url'))
            ->setTheme('dark')
            ->setFontColor('#bbbbbb')
            ->setToolbar(true)
            ->setHeight(400)
            ->setAdditionalOptions([
                // Hundreds of nights animate slowly
                'chart' => ['animations' => ['enabled' => false]],
                'series' => $series,
                'colors' => $colors,
                'yaxis' => $yaxis,
                'xaxis' => ['type' => 'datetime'],
                'markers' => ['size' => $markers, 'strokeWidth' => 0, 'fillOpacity' => 0.6],
                'stroke' => ['width' => $strokes, 'curve' => 'straight'],
                'tooltip' => ['shared' => false, 'x' => ['format' => 'yyyy-MM-dd']],
            ]);
    }

    /**
     * The SQM of the sky in which an observer with the fstOffset reaches the
     * NELM: the inverse of Magnitude::sqmToNelm() (the library's nelmToSqm()
     * without its cap at SQM 22, so the NELMs between 6.6 and 6.8 stay
     * apart), at most MAX_SQM.
     */
    public static function sqmOfNelm(float $nelm, float $fstOffset = 0.0): float
    {
        $x = 10 ** (1.586 - ($nelm + $fstOffset) / 5.0) - 1.0;

        return $x <= 0 ? self::MAX_SQM : min(self::MAX_SQM, 21.58 - 5 * log10($x));
    }

    /**
     * A point at the height of its sky brightness, with the value itself for
     * the tooltip.
     *
     * @return array{x: int, y: float, value: float}
     */
    private static function point(string $date, string $quantity, float $value, float $fstOffset): array
    {
        $height = $quantity === 'nelm' ? self::sqmOfNelm($value, $fstOffset) : $value;

        return ['x' => self::timestamp($date), 'y' => round($height, 3), 'value' => $value];
    }

    /**
     * The bounds of the axes around the heights, on whole half magnitudes
     * (whole magnitudes for a range of more than four).
     *
     * @return array{min: float, max: float, tickAmount: int}
     */
    private static function scale(float $lowest, float $highest): array
    {
        $step = $highest - $lowest > 4 ? 1.0 : 0.5;
        $min = floor($lowest / $step) * $step;
        $max = ceil($highest / $step) * $step;
        if ($max <= $min) {
            $min -= $step;
            $max += $step;
        }

        return ['min' => $min, 'max' => $max, 'tickAmount' => (int) round(($max - $min) / $step)];
    }

    /** Milliseconds since 1970 of the date, as ApexCharts wants them. */
    private static function timestamp(string $date): int
    {
        return Carbon::parse($date, 'UTC')->getTimestampMs();
    }
}
