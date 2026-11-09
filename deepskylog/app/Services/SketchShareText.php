<?php

namespace App\Services;

use App\Models\Constellation;
use App\Models\Observation;
use App\Models\SketchOfTheWeek;
use App\Models\Target;
use App\Models\User;
use Carbon\Carbon;

/**
 * The post (Facebook, X, …) announcing a sketch of the week or of the month, in
 * English: shown after choosing one, and in the share buttons of its card for
 * administrators and database experts. Also the posts of the vote for the sketch
 * of the month (Astroforum in Dutch, the social media in English) and the
 * Astroforum post announcing the chosen one.
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

    /**
     * The Astroforum post (Dutch) asking to vote for the sketch of the month of $date
     * (the 8th) from $candidates (its sketches of the week). The vote ends the day before.
     *
     * @param  iterable<SketchOfTheWeek>  $candidates
     */
    public static function voteAstroforum(Carbon $date, iterable $candidates): string
    {
        $month = $date->copy()->startOfMonth()->subMonthNoOverflow()->locale('nl');
        $deadline = $date->copy()->subDay()->locale('nl');

        return '## Verkiezing DeepskyLog schets van de maand ('.$month->translatedFormat('F Y').')'
            ."\n\nBeste waarnemers,"
            ."\n\nTijd om een nieuwe DeepskyLog schets van de maand te kiezen.  Vrijwilligers die willen meehelpen om objecten te kiezen of om de posts voor de DeepskyLog schets van de week en van de maand online te zetten (Facebook, X, Instagram, Bluesky), mogen me altijd contacteren."
            ."\n\nDe kanshebbers deze maand zijn:\n\n"
            .self::candidateLines($candidates, 'door')
            ."\n\nStemmen kan tot ".$deadline->translatedFormat('j F').'.';
    }

    /**
     * The post (Facebook, X, …, English) asking to vote for the sketch of the month of $date.
     *
     * @param  iterable<SketchOfTheWeek>  $candidates
     */
    public static function voteSocial(Carbon $date, iterable $candidates): string
    {
        $deadline = $date->copy()->subDay()->locale('en');

        return 'Dear observers,'
            ."\n\nLet’s select a new #deepskylog sketch of the month.  The candidates this month are:\n\n"
            .self::candidateLines($candidates, 'by')
            ."\n\nPlease vote (by leaving a comment) before ".$deadline->translatedFormat('F j').'.';
    }

    /**
     * The Astroforum post (Dutch) announcing the sketch of the month of $date (the 8th),
     * with the link in BBCode.
     */
    public static function monthAstroforum(Observation $observation, Carbon $date, ?User $observer = null): string
    {
        $observer ??= self::observer($observation);
        $month = $date->copy()->startOfMonth()->subMonthNoOverflow()->locale('nl');
        $name = self::name($observation, $observer);

        return 'De DeepskyLog schets van '.$month->translatedFormat('F Y')
            .' is de schets geworden van '.self::objectName($observation).' door '.$name.'.'
            .' Proficiat '.explode(' ', $name)[0].'!'
            ."\n\nMeer informatie over deze schets kunnen jullie vinden op [URL=".self::url($observation, $observer).']DeepskyLog[/URL].';
    }

    /**
     * @param  iterable<SketchOfTheWeek>  $candidates
     */
    private static function candidateLines(iterable $candidates, string $by): string
    {
        $lines = [];
        foreach ($candidates as $sketch) {
            $observation = $sketch->observation;
            if (! $observation) {
                continue;
            }
            $lines[] = '- '.self::objectName($observation).' '.$by.' '.self::name($observation, self::observer($observation));
        }

        return implode("\n", $lines);
    }

    private static function objectName(Observation $observation): string
    {
        return html_entity_decode((string) ($observation->target_name ?? $observation->target_key ?? ''));
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
