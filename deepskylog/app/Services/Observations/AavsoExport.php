<?php

namespace App\Services\Observations;

use App\Models\DeepskyObject;
use App\Models\Observation;
use App\Models\VariableStarEstimate;
use App\Services\AavsoVariableStars;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * The magnitude estimates of variable stars, novae and supernovae in the
 * AAVSO Visual File Format, which WebObs (https://apps.aavso.org/webobs/)
 * imports (https://www.aavso.org/aavso-visual-file-format):
 *
 *   #TYPE=Visual
 *   #OBSCODE=ABC
 *   ...
 *   SS Cyg,2454702.1234,<11.1,na,110,113,070613,na
 *
 * A file is one observer's (the header holds their AAVSO observer code).
 * The star is named as VSX names it (AavsoVariableStars), else by the
 * object's name. Estimates without a time are left out: the AAVSO needs the
 * moment to the minute.
 */
class AavsoExport
{
    private const EOL = "\n";

    private const DELIMITER = ',';

    /**
     * The file of the estimates among the given observations.
     *
     * skipped: the estimates of observations without a time.
     *
     * @return array{content: string, count: int, skipped: int}
     */
    public static function build(Collection $observations, string $observerCode): array
    {
        $observations = $observations->where('target_type', Observation::TYPE_DEEPSKY)->values();

        $estimates = collect();
        foreach ($observations->pluck('id')->chunk(1000) as $chunk) {
            $estimates = $estimates->merge(VariableStarEstimate::whereIn('observation_id', $chunk->all())->get());
        }
        $estimates = $estimates->keyBy('observation_id');

        $observations = $observations->filter(fn (Observation $o) => $estimates->has($o->id))->values();

        $stars = DeepskyObject::whereIn('name', $observations->pluck('target_key')->filter()->unique()->all())
            ->get()
            ->mapWithKeys(fn (DeepskyObject $object) => [
                $object->name => AavsoVariableStars::starOf($object)['name'] ?? $object->name,
            ]);

        $lines = [];
        $skipped = 0;
        foreach ($observations as $observation) {
            $time = $observation->time;
            if ($time === null || $observation->observed_on === null) {
                $skipped++;

                continue;
            }

            $estimate = $estimates[$observation->id];
            $moment = CarbonImmutable::parse($observation->observed_on->format('Y-m-d'), 'UTC')
                ->setTime(intdiv($time, 100), $time % 100);

            $lines[] = self::line([
                'star' => (string) ($stars[$observation->target_key] ?? $observation->target_name ?? $observation->target_key),
                'jd' => self::julianDay($moment),
                'magnitude' => $estimate->magnitude,
                'fainter' => $estimate->fainter,
                'comment_codes' => $estimate->comment_codes,
                'comparison1' => $estimate->comparison1,
                'comparison2' => $estimate->comparison2,
                'chart' => $estimate->chart,
            ]);
        }

        if ($lines === []) {
            return ['content' => '', 'count' => 0, 'skipped' => $skipped];
        }

        $header = [
            '#TYPE=Visual',
            '#OBSCODE='.strtoupper(trim($observerCode)),
            '#SOFTWARE=DeepskyLog (https://www.deepskylog.org)',
            '#DELIM='.self::DELIMITER,
            '#DATE=JD',
            '#NAME,DATE,MAG,COMMENTCODE,COMP1,COMP2,CHART,NOTES',
        ];

        return [
            'content' => implode(self::EOL, array_merge($header, $lines)).self::EOL,
            'count' => count($lines),
            'skipped' => $skipped,
        ];
    }

    /**
     * One line: name, Julian day to 0.0001, magnitude to 0.1 ("<" for
     * fainter than), comment codes, the two comparison stars, the chart and
     * the notes; "na" for what is not given.
     *
     * @param  array{star: string, jd: float, magnitude: float, fainter: bool, comment_codes: ?string, comparison1: ?string, comparison2: ?string, chart: ?string}  $v
     */
    public static function line(array $v): string
    {
        return implode(self::DELIMITER, [
            // Not in capitals: SN 2026ab and SN 2026AB are different supernovae
            self::field($v['star']),
            sprintf('%.4f', $v['jd']),
            ($v['fainter'] ? '<' : '').sprintf('%.1f', round($v['magnitude'], 1)),
            self::field($v['comment_codes']),
            self::field($v['comparison1']),
            self::field($v['comparison2']),
            self::field($v['chart']),
            'na',
        ]);
    }

    public static function julianDay(CarbonImmutable $moment): float
    {
        return $moment->getTimestamp() / 86400 + 2440587.5;
    }

    /** A field without the delimiter, "na" when empty. */
    private static function field(?string $value): string
    {
        $value = trim(preg_replace('/\s+/', ' ', str_replace(self::DELIMITER, ' ', (string) $value)));

        return $value === '' ? 'na' : $value;
    }
}
