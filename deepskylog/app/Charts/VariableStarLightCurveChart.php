<?php

namespace App\Charts;

use marineusde\LarapexCharts\Charts\LineChart as OriginalLineChart;

/**
 * The light curve of a variable star, nova or supernova from the estimates
 * of DeepskyLog's observers (App\Services\Observations\VariableStarLightCurve):
 * a point per estimate, the "fainter than" estimates as a series of their
 * own, the magnitude axis reversed (brighter up). All series are lines
 * without stroke, as in SkyQualityHistoryChart. Each point carries the
 * observer and the observation's page, which the script of the object page
 * shows in the tooltip and opens on a click.
 */
class VariableStarLightCurveChart
{
    private const COLORS = ['seen' => '#38bdf8', 'fainter' => '#9ca3af'];

    /**
     * @param  list<array{at: int, magnitude: float, fainter: bool, observer: string, url: ?string, time_known: bool}>  $points
     */
    public function build(string $objectName, array $points): OriginalLineChart
    {
        $series = [];
        $colors = [];
        $shapes = [];
        foreach (['seen' => __('Estimates'), 'fainter' => __('Fainter than (not seen)')] as $kind => $name) {
            $data = [];
            foreach ($points as $point) {
                if ($point['fainter'] !== ($kind === 'fainter')) {
                    continue;
                }
                $data[] = [
                    'x' => $point['at'] * 1000,
                    'y' => round($point['magnitude'], 2),
                    'observer' => $point['observer'],
                    'url' => $point['url'],
                    'timeKnown' => $point['time_known'],
                    'fainter' => $point['fainter'],
                ];
            }
            if ($data === []) {
                continue;
            }
            $series[] = ['name' => $name, 'data' => $data];
            $colors[] = self::COLORS[$kind];
            $shapes[] = $kind === 'fainter' ? 'triangle' : 'circle';
        }

        $magnitudes = array_column($points, 'magnitude');
        $min = floor(min($magnitudes) * 2) / 2 - 0.5;
        $max = ceil(max($magnitudes) * 2) / 2 + 0.5;

        return (new OriginalLineChart)
            ->setTitle(__('Light curve: ').$objectName)
            ->setSubtitle(__('Source: ').config('app.url'))
            ->setTheme('dark')
            ->setFontColor('#bbbbbb')
            ->setToolbar(true)
            ->setHeight(350)
            ->setAdditionalOptions([
                'chart' => ['animations' => ['enabled' => false], 'zoom' => ['enabled' => true, 'type' => 'x']],
                'series' => $series,
                'colors' => $colors,
                'yaxis' => [[
                    'reversed' => true,
                    'min' => $min,
                    'max' => $max,
                    'decimalsInFloat' => 1,
                    'title' => ['text' => __('Magnitude')],
                ]],
                'xaxis' => ['type' => 'datetime'],
                'markers' => ['size' => 5, 'shape' => $shapes, 'strokeWidth' => 0, 'fillOpacity' => 0.8],
                'stroke' => ['width' => 0],
                'tooltip' => ['shared' => false, 'intersect' => true, 'x' => ['format' => 'yyyy-MM-dd HH:mm']],
            ]);
    }
}
