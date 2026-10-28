<?php

namespace App\Services;

use App\Models\DeepskyObject;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * The variable stars, novae and supernovae of the AAVSO: which star of the
 * Variable Star Index (VSX) an object is, and the latest estimates of it in
 * the AAVSO International Database (both through the VSX API, free without a
 * key).
 *
 * An object is matched by its name and alternative names, but only when the
 * star VSX gives is within MATCH_ARCMIN of the object and, when the object
 * has a magnitude, at most MAG_TOLERANCE fainter at maximum: VSX reads
 * "Mu Cep" as the 12th magnitude MU Cep, not the Garnet Star. Without such a
 * name the brightest variable within CONE_ARCMIN is taken, with the same
 * magnitude check. Suspected variables are left out.
 *
 * The star is cached for STAR_SECONDS, the estimates for LATEST_SECONDS.
 * Pages pass $fetch = false: what is not cached yet is then fetched after
 * the response (defer()), so a page never waits for the AAVSO.
 */
class AavsoVariableStars
{
    /** The object types that can be a variable star, nova or supernova. */
    public const TYPES = ['AA1STAR', 'DS', 'SNOVA'];

    /** Arcminutes between the object and the star VSX gives for one of its names. */
    public const MATCH_ARCMIN = 2.0;

    /** Arcminutes around the object searched for a variable when no name matches. */
    public const CONE_ARCMIN = 1.0;

    /** How much fainter at maximum than the object's magnitude the star may be. */
    public const MAG_TOLERANCE = 1.5;

    /** The days of estimates fetched. */
    public const LOOKBACK_DAYS = 30;

    /** The bands of the estimates shown: visual, V and the unfiltered and green CCD ones reduced to V. */
    public const BANDS = ['Vis.', 'V', 'CV', 'TG'];

    /** The names tried before the cone search. */
    private const MAX_NAMES = 3;

    private const STAR_SECONDS = 30 * 86400;

    private const LATEST_SECONDS = 6 * 3600;

    /** A failed request is not repeated for this long. */
    private const FAILURE_SECONDS = 900;

    /** After the AAVSO refused a request, nothing is asked for this long. */
    private const PAUSE_SECONDS = 3600;

    private const PAUSE_KEY = 'aavso:paused';

    /** Milliseconds to wait before each request (the refresh command sets it). */
    private static int $delay = 0;

    public static function enabled(): bool
    {
        return (bool) config('services.aavso.enabled', true);
    }

    /** Whether the AAVSO refused a request in the last PAUSE_SECONDS. */
    public static function paused(): bool
    {
        return Cache::has(self::PAUSE_KEY);
    }

    /** Whether it is known (cached) which star the object is, or that it is none. */
    public static function isIdentified(DeepskyObject $object): bool
    {
        $star = Cache::get(self::starKey($object));

        return $star !== null && $star !== 'failed';
    }

    public static function appliesTo(?string $type): bool
    {
        return self::enabled() && in_array(strtoupper((string) $type), self::TYPES, true);
    }

    public static function throttle(int $milliseconds): void
    {
        self::$delay = max(0, $milliseconds);
    }

    /**
     * The star and its latest estimates for the object of this name, as
     * forObject() gives them.
     *
     * @return array{star: array<string, mixed>, latest: array<string, mixed>|null}|null
     */
    public static function forObjectName(?string $name, bool $fetch = true): ?array
    {
        if (! self::enabled() || $name === null || $name === '') {
            return null;
        }
        $object = DeepskyObject::where('name', $name)->first();

        return $object === null ? null : self::forObject($object, $fetch);
    }

    /**
     * The star of the object in VSX and its latest estimates (null when
     * there are none in the last LOOKBACK_DAYS), or null when the object is
     * no known variable, the AAVSO failed or, with $fetch false, nothing is
     * cached yet.
     *
     * @return array{star: array<string, mixed>, latest: array<string, mixed>|null}|null
     */
    public static function forObject(DeepskyObject $object, bool $fetch = true): ?array
    {
        if (! self::appliesTo($object->type)) {
            return null;
        }

        $starKey = self::starKey($object);
        $star    = Cache::get($starKey);
        if ($star === null) {
            if (! $fetch) {
                defer(fn () => self::forObject($object), 'aavso:'.$starKey);

                return null;
            }
            if (Cache::has(self::PAUSE_KEY)) {
                return null;
            }
            $star = self::identify($object);
            if ($star === null) {
                Cache::put($starKey, 'failed', self::FAILURE_SECONDS);

                return null;
            }
            Cache::put($starKey, $star, self::STAR_SECONDS);
        }
        if (! is_array($star)) {
            return null;
        }

        $latestKey = self::latestKey($star);
        $latest    = Cache::get($latestKey);
        if ($latest === null) {
            if (! $fetch) {
                defer(fn () => self::latest($star), 'aavso:'.$latestKey);

                return ['star' => $star, 'latest' => null];
            }
            $latest = self::latest($star);
        }

        return ['star' => $star, 'latest' => is_array($latest) ? $latest : null];
    }

    /**
     * Fetches the estimates of the last LOOKBACK_DAYS again.
     *
     * @return array{star: array<string, mixed>, latest: array<string, mixed>|null}|null
     */
    public static function refresh(DeepskyObject $object): ?array
    {
        $star = Cache::get(self::starKey($object));
        if (is_array($star)) {
            Cache::forget(self::latestKey($star));
        }

        return self::forObject($object);
    }

    /**
     * The latest estimates of the star, cached: the last in BANDS that is no
     * "fainter than", with the number, brightest and faintest of those of the
     * last LOOKBACK_DAYS; false when there are none, null when the AAVSO
     * failed.
     *
     * @param  array<string, mixed>  $star
     * @return array<string, mixed>|false|null
     */
    public static function latest(array $star): array|false|null
    {
        $key    = self::latestKey($star);
        $cached = Cache::get($key);
        if ($cached !== null) {
            return $cached === 'failed' ? null : $cached;
        }
        if (Cache::has(self::PAUSE_KEY)) {
            return null;
        }

        $from = now()->subDays(self::LOOKBACK_DAYS)->getTimestamp() / 86400 + 2440587.5;
        $body = self::request(['view' => 'api.delim', 'ident' => $star['name'], 'fromjd' => round($from, 2), 'delimiter' => '@@@'], json: false);
        if ($body === null) {
            Cache::put($key, 'failed', self::FAILURE_SECONDS);

            return null;
        }

        $latest = self::summarize(self::parseEstimates($body));
        Cache::put($key, $latest ?? false, self::LATEST_SECONDS);

        return $latest ?? false;
    }

    /**
     * The estimates of an api.delim answer in BANDS, without the "fainter
     * than" ones and those that failed validation, oldest first.
     *
     * @return list<array{at: int, magnitude: float, band: string, observer: string}>
     */
    public static function parseEstimates(string $body): array
    {
        $lines  = preg_split('/\r?\n/', trim($body));
        $header = explode('@@@', (string) array_shift($lines));
        $index  = array_flip($header);
        if (! isset($index['JD'], $index['mag'], $index['band'])) {
            return [];
        }

        $estimates = [];
        foreach ($lines as $line) {
            $fields = explode('@@@', $line);
            $band   = $fields[$index['band']] ?? '';
            $mag    = $fields[$index['mag']]  ?? '';
            if (! in_array($band, self::BANDS, true) || ! is_numeric($mag) || ! is_numeric($fields[$index['JD']] ?? null)) {
                continue;
            }
            if (($fields[$index['fainterThan'] ?? -1] ?? '0') === '1' || ($fields[$index['val'] ?? -1] ?? '') === 'T') {
                continue;
            }
            $estimates[] = [
                'at'        => (int) round(((float) $fields[$index['JD']] - 2440587.5) * 86400),
                'magnitude' => (float) $mag,
                'band'      => $band,
                'observer'  => (string) ($fields[$index['by'] ?? -1] ?? ''),
            ];
        }
        usort($estimates, fn ($a, $b) => $a['at'] <=> $b['at']);

        return $estimates;
    }

    /**
     * @param  list<array{at: int, magnitude: float, band: string, observer: string}>  $estimates
     * @return array<string, mixed>|null
     */
    public static function summarize(array $estimates): ?array
    {
        if ($estimates === []) {
            return null;
        }
        $last       = $estimates[count($estimates) - 1];
        $magnitudes = array_column($estimates, 'magnitude');

        return $last + [
            'count'     => count($estimates),
            'brightest' => min($magnitudes),
            'faintest'  => max($magnitudes),
        ];
    }

    /**
     * What the star is: 'supernova', 'nova' (also recurrent and uncertain
     * novae, not the nova-like variables) or 'variable'.
     *
     * @param  array<string, mixed>  $star
     */
    public static function kind(array $star): string
    {
        $type = (string) ($star['type'] ?? '');
        if (str_starts_with($type, 'SN')) {
            return 'supernova';
        }

        return preg_match('/^N[ABCR]?(?![A-Z])/', $type) ? 'nova' : 'variable';
    }

    /** The VSX page of the star. */
    public static function url(array $star): string
    {
        return 'https://vsx.aavso.org/index.php?view=detail.top&oid='.urlencode((string) $star['oid']);
    }

    /**
     * The numeric magnitude of a VSX range limit such as "4.4 V", "<22 V" or
     * "(0.5) V" (an amplitude, which gives null).
     */
    public static function magnitude(?string $value): ?float
    {
        if (! preg_match('/^\s*[<>]?\s*(-?\d+(?:\.\d+)?)/', (string) $value, $m)) {
            return null;
        }

        return (float) $m[1];
    }

    /**
     * The star of VSX the object is, false when it is none, null when the
     * AAVSO failed.
     *
     * @return array<string, mixed>|false|null
     */
    public static function identify(DeepskyObject $object): array|false|null
    {
        $ra   = $object->raHours() * 15.0;
        $decl = $object->declDegrees();
        $mag  = is_numeric($object->mag) && (float) $object->mag < 30 ? (float) $object->mag : null;

        $names = DB::table('objectnames')->where('objectname', $object->name)->pluck('altname')
            ->prepend($object->name)
            ->map(fn ($name) => html_entity_decode(trim((string) $name), ENT_QUOTES | ENT_HTML5, 'UTF-8'))
            ->filter()->unique()->take(self::MAX_NAMES);

        foreach ($names as $name) {
            $answer = self::request(['view' => 'api.object', 'ident' => $name, 'format' => 'json']);
            if ($answer === null) {
                return null;
            }
            $star = self::star($answer['VSXObject'] ?? null);
            if ($star !== null && self::matches($star, $ra, $decl, $mag, self::MATCH_ARCMIN)) {
                return $star;
            }
        }

        $answer = self::request(['view' => 'api.list', 'ra' => round($ra, 5), 'dec' => round($decl, 5), 'radius' => round(self::CONE_ARCMIN / 60, 4), 'format' => 'json']);
        if ($answer === null) {
            return null;
        }
        $list = $answer['VSXObjects']['VSXObject'] ?? [];
        // A single star comes as an object, not a list of one
        if (isset($list['Name'])) {
            $list = [$list];
        }
        $best = null;
        foreach (is_array($list) ? $list : [] as $row) {
            $star = self::star($row);
            if ($star === null || ! self::matches($star, $ra, $decl, $mag, self::CONE_ARCMIN)) {
                continue;
            }
            if ($best === null || ($star['max_magnitude'] ?? 99) < ($best['max_magnitude'] ?? 99)) {
                $best = $star;
            }
        }

        return $best ?? false;
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function star(mixed $row): ?array
    {
        if (! is_array($row) || empty($row['Name']) || empty($row['OID']) || ($row['Category'] ?? '') !== 'Variable') {
            return null;
        }

        return [
            'name'          => (string) $row['Name'],
            'auid'          => (string) ($row['AUID'] ?? ''),
            'oid'           => (string) $row['OID'],
            'type'          => (string) ($row['VariabilityType'] ?? ''),
            'period'        => is_numeric($row['Period'] ?? null) ? (float) $row['Period'] : null,
            'max'           => (string) ($row['MaxMag'] ?? ''),
            'min'           => (string) ($row['MinMag'] ?? ''),
            'max_magnitude' => self::magnitude($row['MaxMag'] ?? null),
            'min_magnitude' => self::magnitude($row['MinMag'] ?? null),
            'ra'            => (float) ($row['RA2000'] ?? 0),
            'decl'          => (float) ($row['Declination2000'] ?? 0),
        ];
    }

    /**
     * @param  array<string, mixed>  $star
     */
    private static function matches(array $star, float $ra, float $decl, ?float $mag, float $arcmin): bool
    {
        if (60 * self::separation($star['ra'], $star['decl'], $ra, $decl) > $arcmin) {
            return false;
        }

        return $mag === null || $star['max_magnitude'] === null || $star['max_magnitude'] <= $mag + self::MAG_TOLERANCE;
    }

    /** Degrees between two positions in degrees. */
    private static function separation(float $ra1, float $dec1, float $ra2, float $dec2): float
    {
        $d1  = deg2rad($dec1);
        $d2  = deg2rad($dec2);
        $cos = sin($d1) * sin($d2) + cos($d1) * cos($d2) * cos(deg2rad($ra1 - $ra2));

        return rad2deg(acos(max(-1.0, min(1.0, $cos))));
    }

    /**
     * A VSX API request: the decoded JSON (or the text with $json false), or
     * null when it failed. A refusal or Cloudflare's challenge page pauses
     * all requests.
     *
     * @param  array<string, mixed>  $query
     */
    private static function request(array $query, bool $json = true): array|string|null
    {
        if (self::$delay > 0) {
            usleep(self::$delay * 1000);
        }

        try {
            $response = Http::timeout(20)
                ->withUserAgent('DeepskyLog (https://www.deepskylog.org)')
                ->get(config('services.aavso.url'), $query);
        } catch (\Throwable $e) {
            Log::warning('AAVSO request failed', ['view' => $query['view'], 'error' => $e->getMessage()]);

            return null;
        }

        $body = $response->body();
        // Cloudflare's challenge, or the captcha of AAVSO's firewall (a 405 with x-amzn-waf-action)
        if (in_array($response->status(), [403, 405, 429, 503], true) || $response->header('x-amzn-waf-action') !== ''
                                                                      || str_contains($body, '<title>Just a moment') || str_contains($body, '<title>Human Verification')) {
            Cache::put(self::PAUSE_KEY, true, self::PAUSE_SECONDS);
            Log::warning('AAVSO refused a request', ['view' => $query['view'], 'status' => $response->status()]);

            return null;
        }
        if (! $response->successful()) {
            Log::warning('AAVSO request failed', ['view' => $query['view'], 'status' => $response->status()]);

            return null;
        }
        if (! $json) {
            return $body;
        }
        $decoded = $response->json();

        return is_array($decoded) ? $decoded : null;
    }

    private static function starKey(DeepskyObject $object): string
    {
        return 'aavso:star:v1:'.md5((string) $object->name);
    }

    /**
     * @param  array<string, mixed>  $star
     */
    private static function latestKey(array $star): string
    {
        return 'aavso:latest:v1:'.md5((string) $star['name']);
    }
}
