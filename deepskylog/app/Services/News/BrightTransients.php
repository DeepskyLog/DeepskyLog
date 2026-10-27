<?php

namespace App\Services\News;

use App\Models\DeepskyObject;
use App\Services\AavsoVariableStars;

/**
 * The supernovae and novae the news announces: those whose latest AAVSO
 * estimate (AavsoVariableStars) is at least as bright as SUPERNOVA_MAGNITUDE,
 * or for a nova NOVA_MAGNITUDE and NOVA_OUTBURST brighter than its minimum
 * (a recurrent nova such as T CrB is announced in outburst, not when quiet).
 * The aavso:refresh command keeps the news item up to date while it stays
 * that bright.
 */
class BrightTransients
{
    /** The faintest supernova announced: one for an amateur telescope. */
    public const SUPERNOVA_MAGNITUDE = 13.0;

    /** The faintest nova announced: one for binoculars. */
    public const NOVA_MAGNITUDE = 10.0;

    /** Magnitudes a nova has to be brighter than its minimum. */
    public const NOVA_OUTBURST = 3.0;

    /**
     * The event of the object's news item, or null when it is not bright.
     *
     * @param  array{star: array<string, mixed>, latest: array<string, mixed>|null}  $variable  as AavsoVariableStars::forObject() gives it
     * @return array{kind: string, at: int, data: array<string, mixed>}|null
     */
    public static function event(DeepskyObject $object, array $variable): ?array
    {
        $star   = $variable['star'];
        $latest = $variable['latest'];
        $kind   = AavsoVariableStars::kind($star);
        if ($latest === null || $kind === 'variable') {
            return null;
        }

        $magnitude = (float) $latest['magnitude'];
        if ($kind === 'supernova' && $magnitude > self::SUPERNOVA_MAGNITUDE) {
            return null;
        }
        if ($kind === 'nova' && ($magnitude > self::NOVA_MAGNITUDE
            || ($star['min_magnitude'] !== null && $magnitude > $star['min_magnitude'] - self::NOVA_OUTBURST))) {
            return null;
        }

        return [
            'kind' => $kind,
            'at'   => (int) $latest['at'],
            'data' => [
                'name'      => (string) $object->name,
                'slug'      => (string) $object->slug,
                'star'      => $star['name'],
                'oid'       => $star['oid'],
                'type'      => $star['type'],
                'magnitude' => round($magnitude, 1),
                'band'      => $latest['band'],
                'brightest' => round((float) $latest['brightest'], 1),
                'minimum'   => $star['min'],
                'ra'        => round($star['ra'], 4),
                'decl'      => round($star['decl'], 4),
            ],
        ];
    }
}
