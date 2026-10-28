<?php

namespace App\Services;

use App\Models\Country;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Monarobase\CountryList\CountryListFacade as Countries;

/**
 * The countries of residence of the observers (users.country, an ISO code):
 * the countries the rankings and statistics can be limited to, and the
 * country an observer's locations suggest.
 */
class ObserverCountries
{
    /**
     * A country is offered as a filter of the rankings once this many
     * observers with observations live there: with fewer, a national
     * ranking would point at one or two people.
     */
    public const MIN_OBSERVERS = 3;

    /** The countries whose observers give the aperture of a telescope in inches. */
    private const INCH_COUNTRIES = ['US', 'LR', 'MM'];

    /** Fresh for an hour, for a day after that recomputed after the response (Cache::flexible()). */
    private const CACHE_SECONDS = [3600, 86400];

    /**
     * The number of observers with observations per country of residence,
     * most first.
     *
     * @return array<string, int> code => observers
     */
    public static function observerCounts(): array
    {
        return Cache::flexible('observer_countries:counts', self::CACHE_SECONDS, fn() => DB::table('users')
            ->where('country', '!=', '')
            ->whereNotNull('country')
            ->whereExists(function ($query) {
                $query->selectRaw('1')->from('observations')->whereColumn('observations.observer_username', 'users.username');
            })
            ->groupBy('country')
            ->selectRaw('country, COUNT(*) as observers')
            ->orderByDesc('observers')
            ->pluck('observers', 'country')
            ->mapWithKeys(fn($count, $code) => [strtoupper((string) $code) => (int) $count])
            ->all());
    }

    /**
     * The countries with at least MIN_OBSERVERS observers, in the language of
     * the visitor and sorted on that name.
     *
     * @return array<string, string> code => name
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::observerCounts() as $code => $count) {
            if ($count >= self::MIN_OBSERVERS && ($label = self::label($code)) !== null) {
                $options[$code] = $label;
            }
        }
        asort($options, SORT_NATURAL | SORT_FLAG_CASE);

        return $options;
    }

    /**
     * Whether a country can be chosen as a filter (see options()).
     */
    public static function isOption(?string $code): bool
    {
        return $code !== null && $code !== '' && (self::observerCounts()[strtoupper($code)] ?? 0) >= self::MIN_OBSERVERS;
    }

    /**
     * The name of a country in the language of the visitor, null for an
     * empty or unknown code.
     */
    public static function label(?string $code): ?string
    {
        $code = strtoupper(trim((string) $code));
        if ($code === '') {
            return null;
        }

        try {
            return Countries::getOne($code, app()->getLocale());
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Whether the code is one of the country list.
     */
    public static function isCode(?string $code): bool
    {
        return is_string($code) && $code !== '' && array_key_exists(strtoupper($code), Countries::getList('en'));
    }

    /**
     * Whether observers of the country usually give apertures in inches.
     */
    public static function usesInches(?string $code): bool
    {
        return in_array(strtoupper((string) $code), self::INCH_COUNTRIES, true);
    }

    /**
     * Top-level domains of e-mail addresses that are not the ISO code of
     * their country.
     */
    private const DOMAIN_CODES = ['UK' => 'GB'];

    /**
     * Country domains that are mostly used like .com, by companies and mail
     * services anywhere: they say nothing about where the observer lives.
     */
    private const GENERIC_DOMAINS = ['AI', 'AM', 'CC', 'CO', 'FM', 'GG', 'IO', 'LA', 'LY', 'ME', 'NU', 'SH', 'TO', 'TV', 'WS'];

    /**
     * The country of residence the observer's data suggest, the first of:
     * the country of the domain of their e-mail address, the country of most
     * of their locations, the country of their standard location and the
     * country of most of their observations. A tie for "most" leaves the
     * choice to the next rule. Null when none of these is a known country.
     */
    public static function suggestFor(User $user): ?string
    {
        if (($code = self::codeForEmail($user->email)) !== null) {
            return $code;
        }

        $locations = DB::table('locations')
            ->where('user_id', $user->id)
            ->groupBy('country')
            ->selectRaw('country, COUNT(*) as count')
            ->pluck('count', 'country');
        if (($code = self::mostCommon($locations)) !== null) {
            return $code;
        }

        $standard = $user->stdlocation
            ? DB::table('locations')->where('id', $user->stdlocation)->value('country')
            : null;
        if (($code = Country::codeFor($standard)) !== null) {
            return $code;
        }

        $observations = DB::table('observations')
            ->join('locations', 'locations.id', '=', 'observations.location_id')
            ->where('observations.observer_username', $user->username)
            ->groupBy('locations.country')
            ->selectRaw('locations.country, COUNT(*) as count')
            ->pluck('count', 'locations.country');

        return self::mostCommon($observations);
    }

    /**
     * The country of the top-level domain of an e-mail address
     * (observer@kuleuven.be is Belgium), null for a generic domain such as
     * .com or .io.
     */
    public static function codeForEmail(?string $email): ?string
    {
        $domain = strtoupper((string) strrchr(trim((string) $email), '.'));
        $code = substr($domain, 1);
        $code = self::DOMAIN_CODES[$code] ?? $code;

        return strlen($code) === 2 && !in_array($code, self::GENERIC_DOMAINS, true) && self::isCode($code) ? $code : null;
    }

    /**
     * The country with the highest count, the names merged per country and
     * unknown names left out; null when there is none or two share it.
     *
     * @param  iterable<string|null, int|string>  $counts  country name => count
     */
    private static function mostCommon(iterable $counts): ?string
    {
        $perCode = [];
        foreach ($counts as $country => $count) {
            if (($code = Country::codeFor((string) $country)) !== null) {
                $perCode[$code] = ($perCode[$code] ?? 0) + (int) $count;
            }
        }
        if ($perCode === []) {
            return null;
        }

        arsort($perCode);
        $top = array_slice($perCode, 0, 2, true);
        $codes = array_keys($top);

        return count($top) > 1 && $top[$codes[0]] === $top[$codes[1]] ? null : $codes[0];
    }
}
