<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Monarobase\CountryList\CountryListFacade as Countries;

/**
 * The countries a location can be in. The names in this table are the
 * canonical names stored in locations.country: the statistics, the countries
 * chart and the observer page translate a location's country by looking it
 * up here.
 */
class Country extends Model
{
    public $timestamps = false;

    /**
     * Spellings of the old site that are neither in the table nor among the
     * English names of the country list, lower case => code.
     */
    private const ALIASES = [
        'madagaskar' => 'MG',
    ];

    /** @var array<string, string>|null lower-case name => code */
    private static ?array $codes = null;

    /** @var array<string, string>|null code => canonical name */
    private static ?array $names = null;

    /**
     * The canonical name of a country: the name of the table, also for the
     * names the reverse geocoder and the country list use ('Czechia' becomes
     * 'Czech Republic'). An unknown name ('Unknown', a former country) is
     * returned trimmed and without a part between brackets.
     */
    public static function canonicalName(?string $name): ?string
    {
        if ($name === null) {
            return null;
        }

        $code = self::codeFor($name);

        return $code !== null && isset(self::$names[$code])
            ? self::$names[$code]
            : self::withoutBrackets($name);
    }

    /**
     * The ISO code of a country name of the table, of the English country
     * list or of the old site, compared case-insensitively. Names such as
     * 'Netherlands (the)' of the reverse geocoder are also tried without
     * the part between brackets.
     */
    public static function codeFor(?string $name): ?string
    {
        self::loadNames();

        $name = trim((string) $name);

        return self::$codes[mb_strtolower($name)]
            ?? self::$codes[mb_strtolower(self::withoutBrackets($name))]
            ?? null;
    }

    /**
     * The canonical name (as stored in locations.country) of an ISO code,
     * such as the country of residence of an observer; the English name of
     * the country list for a code that is not in the table.
     */
    public static function nameForCode(?string $code): ?string
    {
        self::loadNames();

        $code = strtoupper(trim((string) $code));
        if ($code === '') {
            return null;
        }

        return self::$names[$code] ?? (Countries::getList('en')[$code] ?? null);
    }

    private static function withoutBrackets(string $name): string
    {
        return trim(preg_replace('/\s*\(.*\)\s*/', ' ', $name));
    }

    private static function loadNames(): void
    {
        if (self::$codes !== null) {
            return;
        }

        $codes = self::ALIASES;
        foreach (Countries::getList('en') as $code => $name) {
            $codes[mb_strtolower($name)] = $code;
        }

        // A code with two names in the table (United States, United States
        // of America) gets the shortest as its canonical name.
        $names = [];
        foreach (self::query()->get(['country', 'code']) as $country) {
            $codes[mb_strtolower($country->country)] = $country->code;

            $current = $names[$country->code] ?? null;
            if ($current === null || mb_strlen($country->country) < mb_strlen($current)) {
                $names[$country->code] = $country->country;
            }
        }

        self::$codes = $codes;
        self::$names = $names;
    }
}
