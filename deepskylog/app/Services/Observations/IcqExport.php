<?php

namespace App\Services\Observations;

use App\Models\Observation;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Comet observations in the 80-column format of the International Comet
 * Quarterly, which COBS (https://cobs.si) imports:
 *
 *   IIIYYYYMnL YYYY MM DD.DD eM/mm.m:r AAA.ATF/xxxx /dd.ddnDC /t.ttmANG ICQ XX*OBSxx
 *
 * Only observations with a magnitude estimate are written: COBS collects
 * the estimates, and a line without one carries no measurement. Coma and tail
 * are stored in arcminutes; the tail is written in arcminutes ('m' in column
 * 64) up to 99.9', else in degrees.
 */
class IcqExport
{
    /** The line ends of the file. */
    private const EOL = "\n";

    /**
     * The instrument key of an instrument type (instrument_types.name; the
     * ids differ between databases). Kutter and Other have no key.
     */
    private const INSTRUMENT_KEYS = [
        'Naked Eye' => 'E',
        'Binoculars' => 'B',
        'Refractor' => 'R',
        'Finderscope' => 'R',
        'Reflector' => 'L',
        'Cassegrain' => 'C',
        'Maksutov' => 'M',
        'Schmidt Cassegrain' => 'T',
    ];

    /**
     * The ICQ lines of the comet observations among the given ones.
     *
     * skipped: the observations with a magnitude of a comet without designation.
     *
     * @return array{content: string, count: int, skipped: int}
     */
    public static function build(Collection $observations): array
    {
        $observations = $observations->where('target_type', Observation::TYPE_COMET)->values();
        $ids = $observations->pluck('id')->all();

        $details = collect();
        foreach (array_chunk($ids, 1000) as $chunk) {
            $details = $details->merge(DB::table('comet_observation_details')
                ->whereIn('observation_id', $chunk)
                ->whereNotNull('magnitude')
                ->get()
                ->keyBy('observation_id'));
        }
        $details = $details->keyBy('observation_id');

        $observations = $observations->filter(fn (Observation $o) => $details->has($o->id))->values();

        $cometIds = $observations->map(fn (Observation $o) => $details[$o->id]->comet_object_id ?: $o->target_key)
            ->filter(fn ($id) => ctype_digit((string) $id))->unique()->values()->all();
        $comets = DB::table('cometobjects')->whereIn('id', $cometIds)->pluck('name', 'id');

        $instruments = DB::table('instruments')
            ->leftJoin('instrument_types', 'instrument_types.id', '=', 'instruments.instrument_type_id')
            ->whereIn('instruments.id', $observations->pluck('instrument_id')->filter()->unique()->all())
            ->get(['instruments.id', 'instruments.aperture_mm', 'instruments.focal_length_mm', 'instruments.fixedMagnification', 'instrument_types.name as type_name'])
            ->keyBy('id');
        $eyepieces = DB::table('eyepieces')->whereIn('id', $observations->pluck('eyepiece_id')->filter()->unique()->all())
            ->pluck('focal_length_mm', 'id');
        $lenses = DB::table('lenses')->whereIn('id', $observations->pluck('lens_id')->filter()->unique()->all())
            ->pluck('factor', 'id');
        $observerCodes = DB::table('users')->whereIn('username', $observations->pluck('observer_username')->filter()->unique()->all())
            ->pluck('icqname', 'username');

        $lines = [];
        $skipped = 0;
        foreach ($observations as $observation) {
            $detail = $details[$observation->id];
            $cometId = (string) ($detail->comet_object_id ?: $observation->target_key);
            $designation = self::designation((string) ($comets[$cometId] ?? $observation->target_name ?? ''));
            if ($designation === null || $observation->observed_on === null) {
                $skipped++;

                continue;
            }

            $instrument = $instruments->get($observation->instrument_id);
            $type = self::INSTRUMENT_KEYS[$instrument->type_name ?? ''] ?? '';
            $aperture = $instrument && $type !== 'E' ? (float) $instrument->aperture_mm / 10 : ($type === 'E' ? 0.0 : null);
            $focalRatio = $instrument && ! in_array($type, ['E', 'B'], true) && (float) $instrument->aperture_mm > 0 && (float) $instrument->focal_length_mm > 0
                ? (int) round($instrument->focal_length_mm / $instrument->aperture_mm)
                : null;

            $lines[] = self::line([
                'designation' => $designation,
                'date' => $observation->observed_on->format('Y-m-d'),
                'time' => $observation->time,
                'method' => $detail->method,
                'fainter' => (bool) $detail->less_magnitude,
                'magnitude' => (float) $detail->magnitude,
                'uncertain' => (bool) $detail->magnitude_uncertain,
                'chart' => $detail->chart,
                'aperture' => $aperture,
                'instrument' => $type,
                'focal_ratio' => $focalRatio,
                'magnification' => $type === 'E' ? null : self::magnification(
                    $detail->magnification,
                    $instrument,
                    $eyepieces[$observation->eyepiece_id] ?? null,
                    $lenses[$observation->lens_id] ?? null,
                ),
                'coma' => $detail->coma,
                'dc' => $detail->dc,
                'tail' => $detail->tail,
                'pa' => $detail->position_angle,
                'observer' => $observerCodes[$observation->observer_username] ?? null,
            ]);
        }

        return [
            'content' => $lines === [] ? '' : implode(self::EOL, $lines).self::EOL,
            'count' => count($lines),
            'skipped' => $skipped,
        ];
    }

    /**
     * Columns 1-11 of a comet's name: the number of a numbered periodic comet
     * flush right in 1-3, else the type letter in column 3 (none for C/), the
     * year in 4-7 and the rest of the designation from column 8; the
     * fragment of a split comet in lower case in column 11. Null for a name
     * without designation.
     */
    public static function designation(string $name): ?string
    {
        $name = trim(html_entity_decode($name, ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        if (preg_match('~^(\d{1,3})([PDI])(?:-([A-Z]{1,2}))?/~', $name, $m)) {
            $fragment = $m[3] ?? '';
            if ($fragment === '' && preg_match('~\d-([A-Z])$~', $name, $f)) {
                $fragment = $f[1];
            }
            $number = $m[2] === 'P' ? $m[1] : $m[1].$m[2];
            if (strlen($number) > 3) {
                return null;
            }

            return str_pad($number, 3, ' ', STR_PAD_LEFT).str_repeat(' ', 7).str_pad(self::fragment($fragment), 1);
        }

        if (preg_match('~^([PCDXAI])/(\d{4}) ([A-Z]{1,2}\d{0,3})(?:-([A-Z]{1,2}))?(?:\s|$)~', $name, $m)) {
            $rest = $m[3];
            $fragment = $m[4] ?? '';
            if (strlen($rest) > 4 || (strlen($rest) === 4 && $fragment !== '')) {
                return null;
            }

            return '  '.($m[1] === 'C' ? ' ' : $m[1]).$m[2].str_pad($rest, 3).(strlen($rest) === 4 ? '' : str_pad(self::fragment($fragment), 1));
        }

        return null;
    }

    /**
     * One 80-column line (trailing spaces trimmed); date Y-m-d and time HHMM
     * in UT, aperture in cm, coma and tail in arcminutes.
     *
     * @param  array{designation: string, date: string, time: ?int, method: ?string, fainter: bool, magnitude: float, uncertain: bool, chart: ?string, aperture: ?float, instrument: string, focal_ratio: ?int, magnification: ?int, coma: ?int, dc: ?string, tail: ?int, pa: ?int, observer: ?string}  $v
     */
    public static function line(array $v): string
    {
        $line = str_repeat(' ', 80);
        $put = function (int $column, string $text) use (&$line): void {
            $line = substr_replace($line, $text, $column - 1, strlen($text));
        };

        $put(1, substr(str_pad($v['designation'], 11), 0, 11));

        // The UT date with the day to 0.01 (to the day without a time); a time
        // that rounds up to the next day moves the date.
        $date = CarbonImmutable::parse($v['date'], 'UTC');
        if ($v['time'] !== null) {
            $hundredths = (int) round((intdiv($v['time'], 100) * 60 + $v['time'] % 100) / 1440 * 100);
            if ($hundredths >= 100) {
                $date = $date->addDay();
                $hundredths -= 100;
            }
            $day = sprintf('%02d.%02d', $date->day, $hundredths);
        } else {
            $day = sprintf('%02d', $date->day);
        }
        $put(12, $date->format('Y').' '.$date->format('m').' '.$day);

        $method = trim((string) $v['method']);
        if ($method !== '') {
            $put(27, substr($method, 0, 1));
        }

        $magnitude = sprintf('%4.1f', round($v['magnitude'], 1));
        $put(strlen($magnitude) > 4 ? 28 : 29, $magnitude);
        if ($v['fainter'] && strlen($magnitude) === 4) {
            $put(28, '[');
        }
        if ($v['uncertain']) {
            $put(33, ':');
        }

        $chart = trim((string) $v['chart']);
        if ($chart !== '') {
            $put(34, substr($chart, 0, 2));
        }

        if ($v['aperture'] !== null && $v['aperture'] < 1000) {
            $put(36, sprintf('%5.1f', $v['aperture']));
        }
        if ($v['instrument'] !== '') {
            $put(41, $v['instrument']);
        }
        if ($v['focal_ratio'] !== null && $v['focal_ratio'] > 0 && $v['focal_ratio'] < 100) {
            $put(42, sprintf('%2d', $v['focal_ratio']));
        }
        if ($v['magnification'] !== null && $v['magnification'] > 0 && $v['magnification'] < 10000) {
            $put(44, sprintf('%4d', $v['magnification']));
        }

        if ($v['coma'] !== null && $v['coma'] > 0) {
            if ($v['coma'] < 100) {
                $put(50, sprintf('%2d', $v['coma']));
            } else {
                $put(49, sprintf('%3d', min($v['coma'], 999)));
            }
        }

        $dc = trim((string) $v['dc']);
        if (ctype_digit($dc)) {
            $put(56, substr($dc, 0, 1));
        }

        if ($v['tail'] !== null && $v['tail'] > 0) {
            if ($v['tail'] < 100) {
                $put(60, sprintf('%4.1f', $v['tail']).'m');
            } else {
                $degrees = sprintf('%.2f', $v['tail'] / 60);
                $put(strlen($degrees) > 4 ? 59 : 60, substr($degrees, 0, 5));
            }
        }
        if ($v['pa'] !== null && $v['pa'] >= 0 && $v['pa'] <= 360) {
            $put(65, sprintf('%3d', $v['pa'] % 360));
        }

        $put(69, 'ICQ XX');

        $observer = strtoupper(trim((string) $v['observer']));
        if (preg_match('/^[A-Z]{3}\d{0,2}$/', $observer)) {
            $put(76, $observer);
        }

        return rtrim($line);
    }

    /**
     * The magnification: the one entered with the observation, else the fixed
     * one of the instrument, else from the instrument, eyepiece and lens.
     */
    private static function magnification(?string $entered, ?object $instrument, mixed $eyepieceFocalLength, mixed $lensFactor): ?int
    {
        if ($entered !== null && preg_match('/\d+(?:[.,]\d+)?/', $entered, $m)) {
            return (int) round((float) str_replace(',', '.', $m[0]));
        }
        if ($instrument && (float) $instrument->fixedMagnification > 0) {
            return (int) round((float) $instrument->fixedMagnification);
        }
        if ($instrument && (float) $instrument->focal_length_mm > 0 && (float) $eyepieceFocalLength > 0) {
            $factor = (float) $lensFactor > 0 ? (float) $lensFactor : 1.0;

            return (int) round($instrument->focal_length_mm / $eyepieceFocalLength * $factor);
        }

        return null;
    }

    private static function fragment(string $fragment): string
    {
        return strlen($fragment) === 1 ? strtolower($fragment) : '';
    }
}
