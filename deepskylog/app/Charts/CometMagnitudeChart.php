<?php

namespace App\Charts;

use marineusde\LarapexCharts\Charts\LineChart as OriginalLineChart;
use marineusde\LarapexCharts\Options\XAxisOption;

class CometMagnitudeChart
{
    /**
     * Build a Larapex line chart for comet magnitudes.
     *
     * @param string $objectName
     * @param array $points Array of ['date' => 'YYYY-MM-DD', 'mag' => float]
     * @return OriginalLineChart
     */
    public function build(string $objectName, array $points): OriginalLineChart
    {
        $labels = [];
        $data = [];
        foreach ($points as $p) {
            $labels[] = $p['date'] ?? '';
            $data[] = is_numeric($p['mag']) ? floatval($p['mag']) : null;
        }

        return (new OriginalLineChart())
            ->setTitle(__('Magnitude history: ') . $objectName)
            ->setSubtitle(__('Source: ') . config('app.old_url'))
            ->addData(__('Magnitude'), $data)
            ->setXAxisOption(new XAxisOption($labels))
            ->setTheme('dark')
            ->setFontColor('#bbbbbb')
            ->setToolbar(true);
    }
}
