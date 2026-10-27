<?php

namespace App\Services\News;

use App\Models\Location;
use App\Models\NewsItem;
use App\Models\ObservingChallenge;
use Carbon\Carbon;

/**
 * The titles and short summaries of the automatic news items, in the
 * language of the request; the full text is drawn by the views in
 * resources/views/news/events. Dates are those of the reader's location.
 */
class EventTexts
{
    public static function title(NewsItem $item, ?Location $location = null): string
    {
        $data = $item->data ?? [];
        $date = self::date($item->event_at, $location);

        return match ($item->kind) {
            'solar_eclipse' => match ($data['type'] ?? 'partial') {
                'total'   => __('Total solar eclipse on :date', ['date' => $date]),
                'annular' => __('Annular solar eclipse on :date', ['date' => $date]),
                'hybrid'  => __('Hybrid solar eclipse on :date', ['date' => $date]),
                default   => __('Partial solar eclipse on :date', ['date' => $date]),
            },
            'lunar_eclipse' => match ($data['type'] ?? 'penumbral') {
                'total'   => __('Total lunar eclipse on :date', ['date' => $date]),
                'partial' => __('Partial lunar eclipse on :date', ['date' => $date]),
                default   => __('Penumbral lunar eclipse on :date', ['date' => $date]),
            },
            'planet_conjunction' => __(':first and :second close together on :date', [
                'first'  => self::body($data['bodies'][0] ?? ''),
                'second' => self::body($data['bodies'][1] ?? ''),
                'date'   => $date,
            ]),
            'moon_conjunction'         => __('The Moon passes :planet on :date', ['planet' => self::body($data['bodies'][1] ?? ''), 'date' => $date]),
            'moon_deepsky_conjunction' => __('The Moon passes :planet on :date', ['planet' => $data['object']['name'] ?? '', 'date' => $date]),
            'deepsky_conjunction'      => __(':planet passes :object on :date', [
                'planet' => self::body($data['bodies'][0] ?? ''),
                'object' => $data['object']['name'] ?? '',
                'date'   => $date,
            ]),
            'opposition'          => __(':planet at opposition on :date', ['planet' => self::body($data['bodies'][0] ?? ''), 'date' => $date]),
            'greatest_elongation' => ($data['side'] ?? 'east') === 'east'
                ? __(':planet at greatest eastern elongation on :date', ['planet' => self::body($data['bodies'][0] ?? ''), 'date' => $date])
                : __(':planet at greatest western elongation on :date', ['planet' => self::body($data['bodies'][0] ?? ''), 'date' => $date]),
            'meteor_shower' => __('Maximum of the :shower on :date', ['shower' => self::shower($data['shower'] ?? ''), 'date' => $date]),
            'comet'         => __('Comet :comet at its brightest on :date', ['comet' => $data['name'] ?? '', 'date' => $date]),
            'supernova'     => __('Supernova :name at magnitude :magnitude', ['name' => $data['name'] ?? '', 'magnitude' => self::number($data['magnitude'] ?? 0, 1)]),
            'nova'          => __('Nova :name at magnitude :magnitude', ['name' => $data['name'] ?? '', 'magnitude' => self::number($data['magnitude'] ?? 0, 1)]),
            'challenge'     => __('Observing challenge: :name', ['name' => $data['name'] ?? '']),
            default         => (string) $item->title,
        };
    }

    /** One or two sentences for the home page and the feed. */
    public static function summary(NewsItem $item, ?Location $location = null): string
    {
        $data = $item->data ?? [];

        return match ($item->kind) {
            'solar_eclipse' => __('Greatest eclipse at :time UT, at latitude :latitude° and longitude :longitude°.', [
                'time'      => $item->event_at->format('H:i'),
                'latitude'  => self::number($data['latitude'] ?? 0, 1),
                'longitude' => self::number($data['longitude'] ?? 0, 1),
            ]),
            'lunar_eclipse' => __('Greatest eclipse at :time UT, umbral magnitude :magnitude.', [
                'time'      => $item->event_at->format('H:i'),
                'magnitude' => self::number($data['umbral_magnitude'] ?? 0, 3),
            ]),
            'planet_conjunction', 'moon_conjunction', 'deepsky_conjunction', 'moon_deepsky_conjunction' => __('Smallest distance :distance° at :time UT.', [
                'distance' => self::number($data['separation'] ?? 0, 2),
                'time'     => $item->event_at->format('H:i'),
            ]),
            'opposition' => __(':planet is at its closest and brightest (magnitude :magnitude) and visible all night.', [
                'planet'    => self::body($data['bodies'][0] ?? ''),
                'magnitude' => self::number($data['magnitude'] ?? 0, 1),
            ]),
            'greatest_elongation' => ($data['side'] ?? 'east') === 'east'
                ? __(':planet is :elongation° from the Sun in the evening sky.', ['planet' => self::body($data['bodies'][0] ?? ''), 'elongation' => self::number($data['elongation'] ?? 0, 1)])
                : __(':planet is :elongation° from the Sun in the morning sky.', ['planet' => self::body($data['bodies'][0] ?? ''), 'elongation' => self::number($data['elongation'] ?? 0, 1)]),
            'meteor_shower' => __('Up to :zhr meteors an hour under a dark sky; the Moon is :illumination% illuminated.', [
                'zhr'          => $data['zhr'] ?? 0,
                'illumination' => (int) round(100 * ($data['moon_illumination'] ?? 0)),
            ]),
            'comet' => __('Predicted magnitude :magnitude, :elongation° from the Sun.', [
                'magnitude'  => self::number($data['magnitude'] ?? 0, 1),
                'elongation' => self::number(abs($data['elongation'] ?? 0), 0),
            ]),
            'supernova', 'nova' => __('Latest AAVSO estimate :magnitude (:band) on :date.', [
                'magnitude' => self::number($data['magnitude'] ?? 0, 1),
                'band'      => $data['band'] ?? '',
                'date'      => self::date($item->event_at, $location),
            ]),
            'challenge' => self::challenge($item),
            default => '',
        };
    }

    /** What an observing challenge asks, and when. */
    private static function challenge(NewsItem $item): string
    {
        $challenge = ObservingChallenge::find($item->data['challenge_id'] ?? null);
        if ($challenge === null) {
            return '';
        }

        return __(':task between :start and :end. Join the challenge and follow the leaderboard.', [
            'task'  => $challenge->task(),
            'start' => $challenge->starts_on->locale(app()->getLocale())->translatedFormat('j F Y'),
            'end'   => $challenge->ends_on->locale(app()->getLocale())->translatedFormat('j F Y'),
        ]);
    }

    /** The name of the Moon or a planet in the language of the request. */
    public static function body(string $body): string
    {
        return match ($body) {
            'moon'    => __('Moon'),
            'mercury' => __('Mercury'),
            'venus'   => __('Venus'),
            'mars'    => __('Mars'),
            'jupiter' => __('Jupiter'),
            'saturn'  => __('Saturn'),
            'uranus'  => __('Uranus'),
            'neptune' => __('Neptune'),
            default   => ucfirst($body),
        };
    }

    public static function shower(string $shower): string
    {
        return match ($shower) {
            'quadrantids'   => __('Quadrantids'),
            'lyrids'        => __('Lyrids'),
            'eta_aquariids' => __('Eta Aquariids'),
            'perseids'      => __('Perseids'),
            'orionids'      => __('Orionids'),
            'leonids'       => __('Leonids'),
            'geminids'      => __('Geminids'),
            default         => ucfirst($shower),
        };
    }

    /** The time zone of a location, UTC when it has none or an invalid one. */
    public static function timezone(?Location $location): string
    {
        $timezone = (string) ($location?->timezone ?? '');

        return $timezone !== '' && in_array($timezone, timezone_identifiers_list(), true) ? $timezone : 'UTC';
    }

    /** A date in the reader's language and the location's time zone. */
    public static function date(?Carbon $moment, ?Location $location = null): string
    {
        if ($moment === null) {
            return '';
        }

        return $moment->copy()->setTimezone(self::timezone($location))->locale(app()->getLocale())->translatedFormat('j F Y');
    }

    /** A moment as local time with the date, for the circumstances. */
    public static function localTime(int $timestamp, ?Location $location): string
    {
        return Carbon::createFromTimestamp($timestamp, 'UTC')->setTimezone(self::timezone($location))
            ->locale(app()->getLocale())->translatedFormat('j F H:i');
    }

    public static function number(float|int $value, int $decimals): string
    {
        return number_format((float) $value, $decimals, app()->getLocale() === 'en' ? '.' : ',', '');
    }
}
