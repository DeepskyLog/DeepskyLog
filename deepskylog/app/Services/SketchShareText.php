<?php

namespace App\Services;

use App\Models\Constellation;
use App\Models\Observation;
use App\Models\Target;
use App\Models\User;
use Carbon\Carbon;

/**
 * The post (Facebook, X, …) announcing a sketch of the week or of the month, in
 * English: shown after choosing one, and in the share buttons of its card for
 * administrators and database experts.
 */
class SketchShareText
{
    public static function forWeek(Observation $observation, ?User $observer = null): string
    {
        $observer ??= self::observer($observation);

        return 'As new #deepskylog sketch of the week, we selected this sketch of '
            .self::subject($observation).' by '.self::name($observation, $observer).'.'
            ."\nMore information can be found here:\n\n"
            .self::url($observation, $observer)
            ."\n\n#sketch #sketchoftheweek #deepsky #astronomy #deepskydrawing #sketches";
    }

    /**
     * $date is the date of the sketch of the month (the 8th); it is the sketch of the month before.
     */
    public static function forMonth(Observation $observation, Carbon $date, ?User $observer = null): string
    {
        $observer ??= self::observer($observation);
        $month = $date->copy()->startOfMonth()->subMonthNoOverflow();
        $name = self::name($observation, $observer);

        return 'The #deepskylog sketch of '.$month->format('F').' '.$month->format('Y')
            .' is this sketch of '.self::subject($observation).' by '.$name.'.'
            ."\nCongratulations, ".explode(' ', $name)[0].'!'
            ."\nMore information can be found here:\n\n"
            .self::url($observation, $observer)
            ."\n\n#sketch #sketchofthemonth #deepsky #astronomy #deepskydrawing #sketches";
    }

    private static function observer(Observation $observation): ?User
    {
        $username = html_entity_decode((string) $observation->observer_username);

        return $username === '' ? null : User::where('username', $username)->first();
    }

    private static function name(Observation $observation, ?User $observer): string
    {
        return $observer?->name ?? html_entity_decode((string) $observation->observer_username);
    }

    private static function url(Observation $observation, ?User $observer): string
    {
        $segment = $observer ? ($observer->slug ?: $observer->username) : null;

        return (string) ($observation->detailUrl($segment) ?? url('/'));
    }

    private static function subject(Observation $observation): string
    {
        if ($observation->target_type === Observation::TYPE_COMET) {
            return 'comet '.($observation->target_name ?? 'unknown object');
        }

        $objectName = $observation->target_name;
        $object = $objectName ? Target::where('name', $objectName)->first() : null;
        $type = $object ? $object->long_type() : null;

        $text = '';
        if ($type && strtolower($type) !== 'object') {
            $text .= 'the '.$type.' ';
        }
        $text .= $objectName ?? 'unknown object';

        if ($object) {
            $constellation = Constellation::where('id', $object->con)->first();
            if ($constellation) {
                $text .= ' in '.$constellation->name;
            }
        }

        return $text;
    }
}
