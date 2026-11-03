<?php

namespace App\Services\Comets;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

/**
 * Osculating orbital elements of a comet at a chosen epoch from JPL Horizons
 * (free, no key): heliocentric, ecliptic and equinox of J2000, integrated by
 * JPL with the planets and the comet's non-gravitational forces from the
 * solution of the apparition nearest before the epoch (`CAP<year`).
 */
class HorizonsElements
{
    /**
     * The elements as the columns of `comet_epoch_elements`, or a string
     * saying why there are none (no such comet, several, Horizons down).
     *
     * @return array{epoch: float, q: float, e: float, i: float, w: float, node: float, Tp: float, source: string}|string
     */
    public function fetch(string $designation, float $epoch): array|string
    {
        if (! config('services.horizons.enabled')) {
            return 'Horizons is turned off';
        }

        $year = (int) Carbon::createFromTimestampUTC((int) round(($epoch - 2440587.5) * 86400))->year;
        // A fragment (73P-B) is its own object; NOFRAG leaves out the fragments of the others
        $command = 'DES=' . $designation . ';CAP<' . ($year + 1) . ';' . (str_contains($designation, '-') ? '' : 'NOFRAG;');

        try {
            $response = Http::timeout(30)->retry(2, 2000, throw: false)->get(config('services.horizons.url'), [
                'format' => 'json',
                'COMMAND' => "'" . $command . "'",
                'OBJ_DATA' => 'NO',
                'MAKE_EPHEM' => 'YES',
                'EPHEM_TYPE' => 'ELEMENTS',
                'CENTER' => "'500@10'",
                'TLIST' => "'" . sprintf('%.1f', $epoch) . "'",
                'REF_PLANE' => 'ECLIPTIC',
                'REF_SYSTEM' => 'ICRF',
                'TP_TYPE' => 'ABSOLUTE',
                'OUT_UNITS' => "'AU-D'",
                'CSV_FORMAT' => 'YES',
            ]);
        } catch (\Throwable $e) {
            return 'Horizons did not answer: ' . $e->getMessage();
        }
        if (! $response->successful()) {
            return 'Horizons answered ' . $response->status();
        }

        return self::parse((string) $response->json('result', ''), $command);
    }

    /**
     * The elements of the first line between $$SOE and $$EOE of a Horizons
     * answer in CSV: JDTDB, date, EC, QR, IN, OM, W, Tp, ...
     *
     * @return array{epoch: float, q: float, e: float, i: float, w: float, node: float, Tp: float, source: string}|string
     */
    public static function parse(string $result, string $command): array|string
    {
        if (! preg_match('/\$\$SOE\s*\n(.*?)\n/s', $result, $m)) {
            if (preg_match('/Matching small-bodies|Multiple|No matches found/i', $result, $why)) {
                return 'Horizons: ' . trim($why[0]) . ' for ' . $command;
            }

            return 'No elements in the answer of Horizons for ' . $command;
        }
        $fields = array_map('trim', explode(',', $m[1]));
        if (count($fields) < 8 || ! is_numeric($fields[0]) || ! is_numeric($fields[2]) || ! is_numeric($fields[7])) {
            return 'Unreadable elements from Horizons for ' . $command;
        }

        return [
            'epoch' => (float) $fields[0],
            'e' => (float) $fields[2],
            'q' => (float) $fields[3],
            'i' => (float) $fields[4],
            'node' => (float) $fields[5],
            'w' => (float) $fields[6],
            'Tp' => self::perihelionNumber((float) $fields[7]),
            'source' => mb_substr('JPL Horizons ' . $command, 0, 64),
        ];
    }

    /**
     * A Julian day as YYYYMMDD.dddd, the form of the time of perihelion in
     * comets_orbital_elements (App\Services\CometCoordinates::perihelionDate()).
     */
    public static function perihelionNumber(float $jd): float
    {
        $seconds = ($jd - 2440587.5) * 86400.0;
        $date = Carbon::createFromTimestampUTC((int) floor($seconds / 86400.0) * 86400);
        $fraction = ($seconds - $date->getTimestamp()) / 86400.0;

        return (float) $date->format('Ymd') + $fraction;
    }
}
