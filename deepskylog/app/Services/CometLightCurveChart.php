<?php

namespace App\Services;

use Carbon\Carbon;

/**
 * An SVG light curve of a comet: the predicted total magnitude, with the
 * magnitudes estimated by DeepskyLog observers in the same period.
 */
class CometLightCurveChart
{
    private const WIDTH = 640;

    private const HEIGHT = 260;

    private const MARGIN_LEFT = 44;

    private const MARGIN_RIGHT = 10;

    private const MARGIN_TOP = 12;

    private const MARGIN_BOTTOM = 44;

    /**
     * @param  array<int, array{date: string, mag: float}>  $predicted  From CometCoordinates::lightCurve()
     * @param  array<int, array{date: string, mag: float}>  $observed  Estimates of the observers
     * @param  string|null  $date  The selected date, marked with a vertical line
     */
    public static function svg(array $predicted, array $observed, ?string $date = null): ?string
    {
        $predicted = self::points($predicted);
        if (count($predicted) < 2) {
            return null;
        }

        $from = $predicted[0]['ts'];
        $to = $predicted[count($predicted) - 1]['ts'];
        $observed = array_values(array_filter(self::points($observed), fn ($p) => $p['ts'] >= $from && $p['ts'] <= $to));

        $mags = array_column(array_merge($predicted, $observed), 'mag');
        $bright = floor(min($mags));
        $faint = ceil(max($mags));
        if ($faint - $bright < 2) {
            $faint = $bright + 2;
        }

        $plotW = self::WIDTH - self::MARGIN_LEFT - self::MARGIN_RIGHT;
        $plotH = self::HEIGHT - self::MARGIN_TOP - self::MARGIN_BOTTOM;
        // Bright magnitudes at the top
        $x = fn (int $ts) => self::MARGIN_LEFT + ($ts - $from) / ($to - $from) * $plotW;
        $y = fn (float $mag) => self::MARGIN_TOP + ($mag - $bright) / ($faint - $bright) * $plotH;

        $bottom = self::MARGIN_TOP + $plotH;
        $right = self::MARGIN_LEFT + $plotW;
        $svg = sprintf('<svg xmlns="http://www.w3.org/2000/svg" width="100%%" viewBox="0 0 %d %d" role="img" aria-label="%s">', self::WIDTH, self::HEIGHT, e(__('Light curve')));
        $svg .= '<style>.dsl-lc{font-family:Arial,Helvetica,sans-serif;font-size:11px;fill:#bbbbbb}</style>';

        // Magnitude grid
        $step = ($faint - $bright) > 8 ? 2 : 1;
        for ($m = $bright; $m <= $faint; $m += $step) {
            $svg .= sprintf('<line x1="%d" y1="%.1f" x2="%d" y2="%.1f" stroke="#333" stroke-width="1"/>', self::MARGIN_LEFT, $y($m), $right, $y($m));
            $svg .= sprintf('<text x="%d" y="%.1f" class="dsl-lc" text-anchor="end">%s</text>', self::MARGIN_LEFT - 6, $y($m) + 4, e(number_format($m, 0)));
        }

        // A label at the start of every other month
        $month = Carbon::createFromTimestamp($from, 'UTC')->startOfMonth()->addMonth();
        for ($i = 0; $month->getTimestamp() <= $to; $month->addMonth(), $i++) {
            $mx = $x($month->getTimestamp());
            $svg .= sprintf('<line x1="%.1f" y1="%d" x2="%.1f" y2="%d" stroke="#444" stroke-width="1"/>', $mx, $bottom, $mx, $bottom + 4);
            if ($i % 2 === 0) {
                $svg .= sprintf('<text x="%.1f" y="%d" class="dsl-lc" text-anchor="middle">%s</text>', $mx, $bottom + 16, e($month->isoFormat('MMM YYYY')));
            }
        }
        $svg .= sprintf('<line x1="%d" y1="%d" x2="%d" y2="%d" stroke="#444" stroke-width="1"/>', self::MARGIN_LEFT, self::MARGIN_TOP, self::MARGIN_LEFT, $bottom);
        $svg .= sprintf('<line x1="%d" y1="%d" x2="%d" y2="%d" stroke="#444" stroke-width="1"/>', self::MARGIN_LEFT, $bottom, $right, $bottom);

        // The selected date
        $selected = $date ? strtotime($date) : false;
        if ($selected !== false && $selected >= $from && $selected <= $to) {
            $sx = $x($selected);
            $svg .= sprintf('<line x1="%.1f" y1="%d" x2="%.1f" y2="%d" stroke="#888" stroke-width="1" stroke-dasharray="4 3"/>', $sx, self::MARGIN_TOP, $sx, $bottom);
        }

        // The predicted magnitude and the observations
        $line = implode(' ', array_map(fn ($p) => sprintf('%.1f,%.1f', $x($p['ts']), $y($p['mag'])), $predicted));
        $svg .= '<polyline points="'.$line.'" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linejoin="round"/>';
        foreach ($observed as $p) {
            $svg .= sprintf('<circle cx="%.1f" cy="%.1f" r="3" fill="#3b82f6"><title>%s: %s</title></circle>', $x($p['ts']), $y($p['mag']), e($p['date']), e(number_format($p['mag'], 1)));
        }

        // Legend
        $ly = self::HEIGHT - 8;
        $svg .= sprintf('<line x1="%d" y1="%d" x2="%d" y2="%d" stroke="#f59e0b" stroke-width="2"/>', self::MARGIN_LEFT, $ly - 4, self::MARGIN_LEFT + 18, $ly - 4);
        $svg .= sprintf('<text x="%d" y="%d" class="dsl-lc">%s</text>', self::MARGIN_LEFT + 24, $ly, e(__('Predicted magnitude')));
        if (! empty($observed)) {
            $svg .= sprintf('<circle cx="%d" cy="%d" r="3" fill="#3b82f6"/>', self::MARGIN_LEFT + 220, $ly - 4);
            $svg .= sprintf('<text x="%d" y="%d" class="dsl-lc">%s</text>', self::MARGIN_LEFT + 230, $ly, e(__('Magnitudes estimated by observers')));
        }

        return $svg.'</svg>';
    }

    /**
     * Valid points with a timestamp, sorted by date.
     */
    private static function points(array $points): array
    {
        $valid = [];
        foreach ($points as $p) {
            $ts = isset($p['date']) ? strtotime((string) $p['date']) : false;
            $mag = $p['mag'] ?? null;
            if ($ts !== false && is_numeric($mag) && abs((float) $mag) < 99) {
                $valid[] = ['ts' => $ts, 'mag' => (float) $mag, 'date' => (string) $p['date']];
            }
        }
        usort($valid, fn ($a, $b) => $a['ts'] <=> $b['ts']);

        return $valid;
    }
}
