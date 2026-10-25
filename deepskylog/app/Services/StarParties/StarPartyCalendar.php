<?php

namespace App\Services\StarParties;

use App\Models\StarParty;

/**
 * A starparty (#315) as an iCalendar file (RFC 5545) for the calendar of the
 * observer: one event over the days from the first evening to the morning
 * after the last night, with the site as location and geographic position.
 */
class StarPartyCalendar
{
    public static function ics(StarParty $party): string
    {
        $url         = route('star-parties.show', $party);
        $description = trim($party->excerpt(1000)."\n\n".$url);
        $location    = $party->location_name.($party->country ? ', '.$party->country : '');
        if ($party->venue) {
            $location .= ' - '.$party->venue;
        }

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//DeepskyLog//Starparties//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:starparty-'.$party->id.'@'.(parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'deepskylog.org'),
            'DTSTAMP:'.now()->utc()->format('Ymd\THis\Z'),
            'LAST-MODIFIED:'.($party->updated_at ?? now())->copy()->utc()->format('Ymd\THis\Z'),
            'DTSTART;VALUE=DATE:'.$party->start_date->format('Ymd'),
            // The end of an all-day event is exclusive: the day after the morning after the last night.
            'DTEND;VALUE=DATE:'.$party->end_date->copy()->addDays(2)->format('Ymd'),
            'SUMMARY:'.self::escape($party->name),
            'LOCATION:'.self::escape($location),
            'GEO:'.round($party->latitude, 6).';'.round($party->longitude, 6),
            'DESCRIPTION:'.self::escape($description),
            'URL:'.self::escape($url),
            'STATUS:'.($party->isCancelled() ? 'CANCELLED' : 'CONFIRMED'),
            'TRANSP:TRANSPARENT',
            'END:VEVENT',
            'END:VCALENDAR',
        ];

        return implode("\r\n", array_map([self::class, 'fold'], $lines))."\r\n";
    }

    /** Escape a text value: backslash, semicolon, comma and new lines. */
    public static function escape(string $text): string
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return str_replace(["\\", ';', ',', "\r\n", "\n", "\r"], ["\\\\", '\;', '\,', '\n', '\n', '\n'], $text);
    }

    /** Fold a line into lines of at most 75 octets, without splitting a UTF-8 character. */
    public static function fold(string $line): string
    {
        if (strlen($line) <= 75) {
            return $line;
        }

        $parts   = [];
        $current = '';
        $limit   = 75;
        foreach (mb_str_split($line) as $char) {
            if (strlen($current) + strlen($char) > $limit) {
                $parts[] = $current;
                $current = '';
                // The continuation lines start with a space.
                $limit = 74;
            }
            $current .= $char;
        }
        $parts[] = $current;

        return implode("\r\n ", $parts);
    }
}
