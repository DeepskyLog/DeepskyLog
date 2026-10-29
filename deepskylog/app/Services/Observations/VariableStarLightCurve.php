<?php

namespace App\Services\Observations;

use App\Models\Observation;
use App\Models\VariableStarEstimate;
use Carbon\CarbonImmutable;

/**
 * The magnitude estimates of a variable star, nova or supernova recorded in
 * DeepskyLog (variable_star_estimates), oldest first, for the light curve on
 * the object page (App\Charts\VariableStarLightCurveChart). An estimate
 * without a time is placed at 0h UT of its date.
 */
class VariableStarLightCurve
{
    /** The most estimates read, the newest ones. */
    public const MAX_ESTIMATES = 5000;

    /**
     * @return list<array{at: int, magnitude: float, fainter: bool, observer: string, url: ?string, time_known: bool}>
     */
    public static function forObject(string $objectName): array
    {
        $observations = Observation::query()
            ->ofDeepskyObject($objectName)
            ->join('variable_star_estimates', 'variable_star_estimates.observation_id', '=', 'observations.id')
            ->whereNotNull('observations.observed_on')
            ->orderByDesc('observations.observed_on')
            ->orderByDesc('observations.observed_time')
            ->limit(self::MAX_ESTIMATES)
            ->get(['observations.*']);
        if ($observations->isEmpty()) {
            return [];
        }

        $estimates = VariableStarEstimate::whereIn('observation_id', $observations->pluck('id'))->get()->keyBy('observation_id');
        $cards = ObservationCardData::instance();
        $cards->preload($observations);

        $points = [];
        foreach ($observations as $observation) {
            $estimate = $estimates[$observation->id];
            $time = $observation->time;
            $moment = CarbonImmutable::parse($observation->observed_on->format('Y-m-d'), 'UTC');
            if ($time !== null) {
                $moment = $moment->setTime(intdiv($time, 100), $time % 100);
            }
            $user = $cards->user($observation->observer_username);

            $points[] = [
                'at' => $moment->getTimestamp(),
                'magnitude' => $estimate->magnitude,
                'fainter' => $estimate->fainter,
                'observer' => html_entity_decode((string) ($user?->name ?: $observation->observer_username), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                'url' => $observation->detailUrl($user?->slug),
                'time_known' => $time !== null,
            ];
        }
        usort($points, fn ($a, $b) => $a['at'] <=> $b['at']);

        return $points;
    }
}
