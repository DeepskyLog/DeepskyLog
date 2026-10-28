<?php

namespace App\Services;

use App\Models\Instrument;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Whether a telescope splits a double star, and at which magnification.
 *
 * DeepskyLog stores a double star (type DS) with the separation of the pair
 * in diam1 (arcseconds), its position angle in pa, the magnitude of the
 * primary in mag and that of the companion in subr (0 or 99.9 when unknown).
 *
 * - Two equally bright stars are split down to the Dawes limit, R0 = 116″ /
 *   aperture (mm). The limit also holds up to a magnitude difference of
 *   EQUAL_DELTA_MAG.
 * - A fainter companion is lost in the glare and diffraction rings of the
 *   primary: the separation has to grow with the magnitude difference,
 *   Δm = 1 + 4 log(ρ / R0) (Öpik 1924, in the form of B. MacEvoy, "Double
 *   star astronomy: training the binary eye"), so ρ = R0 10^((Δm − 1) / 4):
 *   3.2 R0 at Δm = 3, 10 R0 at Δm = 5.
 * - The companion itself has to be bright enough: the faintest companion
 *   seen next to a primary is LM − 2.4 + 1.6 log(ρ / R0) (Peterson 1954),
 *   with LM the limiting magnitude of the telescope (TelescopicLimitingMagnitude).
 * - The eye has to see the two stars apart: they have to appear
 *   EYE_SEPARATION_ARCSEC apart, a magnification of 240 / ρ, which is twice
 *   the aperture in mm for a pair at the Dawes limit.
 * - Below STEADY_SEEING_ARCSEC the seeing of a common night blurs the pair.
 */
class DoubleStarResolvability
{
    /** The Dawes limit of an aperture of 1 mm, in arcseconds. */
    public const DAWES_ARCSEC_MM = 116.0;

    /** Up to this magnitude difference a pair splits as an equal one. */
    public const EQUAL_DELTA_MAG = 1.0;

    /** Magnitudes the companion may be fainter per tenfold separation (Öpik). */
    public const OPIK_SLOPE = 4.0;

    /** The faintest companion: LM − PETERSON_OFFSET + PETERSON_SLOPE log(ρ / R0). */
    public const PETERSON_OFFSET = 2.4;

    public const PETERSON_SLOPE = 1.6;

    /** The apparent separation (arcseconds) at which the eye sees two stars apart. */
    public const EYE_SEPARATION_ARCSEC = 240.0;

    /** Separations (arcseconds) below which a pair needs a steady night. */
    public const STEADY_SEEING_ARCSEC = 1.5;

    /** The separation against the limit from which a pair is easy, and clearly split. */
    public const EASY_RATIO = 2.0;

    public const SPLIT_RATIO = 1.25;

    /** The verdicts, from easy to not split. */
    public const VERDICTS = ['easy', 'split', 'difficult', 'not_split', 'companion_too_faint'];

    /**
     * The name of a verdict, in the language of the request.
     */
    public static function label(string $verdict): string
    {
        return match ($verdict) {
            'easy'                => __('Easily split'),
            'split'               => __('Split'),
            'difficult'           => __('Just split, at the limit of the telescope'),
            'not_split'           => __('Not split'),
            'companion_too_faint' => __('Companion too faint to see'),
        };
    }

    /**
     * The separation, position angle and magnitudes of a double star, from a
     * row of the objects table; null for another type or a double star
     * without a separation. A magnitude of 0 or 99.9 is unknown.
     *
     * @return array{separation: float, pa: ?int, primary: ?float, companion: ?float}|null
     */
    public static function pair(object $object): ?array
    {
        if (strtoupper(trim((string) ($object->type ?? ''))) !== 'DS' || ! is_numeric($object->diam1 ?? null) || (float) $object->diam1 <= 0) {
            return null;
        }
        $magnitude = fn (mixed $value) => is_numeric($value) && (float) $value != 0.0 && (float) $value < 90 ? (float) $value : null;
        $pa        = $object->pa ?? null;

        return [
            'separation' => (float) $object->diam1,
            'pa'         => is_numeric($pa) && (int) $pa >= 0 && (int) $pa < 360 ? (int) $pa : null,
            'primary'    => $magnitude($object->mag ?? null),
            'companion'  => $magnitude($object->subr ?? null),
        ];
    }

    /** The Dawes limit of the aperture, in arcseconds. */
    public static function dawes(float $aperture): float
    {
        return self::DAWES_ARCSEC_MM / $aperture;
    }

    /**
     * The smallest separation (arcseconds) the aperture splits at the
     * magnitude difference; an unknown difference counts as an equal pair.
     */
    public static function limit(float $aperture, ?float $deltaMag): float
    {
        $excess = max(0.0, abs((float) $deltaMag) - self::EQUAL_DELTA_MAG);

        return self::dawes($aperture) * 10 ** ($excess / self::OPIK_SLOPE);
    }

    /** The faintest companion seen at the separation (Peterson). */
    public static function companionLimit(float $limitingMagnitude, float $separation, float $aperture): float
    {
        return $limitingMagnitude - self::PETERSON_OFFSET + self::PETERSON_SLOPE * log10(max(1.0, $separation / self::dawes($aperture)));
    }

    /** The lowest magnification at which the eye sees the pair apart. */
    public static function minimumMagnification(float $separation): int
    {
        return max(1, (int) ceil(self::EYE_SEPARATION_ARCSEC / $separation));
    }

    /**
     * Whether the aperture splits the pair, with $limitingMagnitude (of the
     * telescope at the magnification used, less the extinction) whether the
     * companion is bright enough. The limit and verdict do not depend on
     * the magnification: forObject() and atMagnification() say whether one
     * is high enough.
     *
     * @param  array{separation: float, pa: ?int, primary: ?float, companion: ?float}  $pair
     * @return array{verdict: string, separation: float, pa: ?int, primary: ?float, companion: ?float, delta_mag: ?float,
     *     dawes: float, limit: float, ratio: float, steady_seeing: bool, minimum_magnification: int, companion_limit: ?float}
     */
    public static function assess(array $pair, float $aperture, ?float $limitingMagnitude = null): array
    {
        $separation = $pair['separation'];
        $deltaMag   = $pair['primary'] !== null && $pair['companion'] !== null ? abs($pair['companion'] - $pair['primary']) : null;
        $limit      = self::limit($aperture, $deltaMag);
        $ratio      = $separation / $limit;
        $faintest   = $pair['companion'] !== null && $pair['primary'] !== null ? max($pair['companion'], $pair['primary']) : null;

        $companionLimit = $limitingMagnitude !== null ? self::companionLimit($limitingMagnitude, $separation, $aperture) : null;

        $verdict = match (true) {
            $ratio < 1.0                                                  => 'not_split',
            $companionLimit !== null && $faintest !== null && $faintest > $companionLimit => 'companion_too_faint',
            $ratio >= self::EASY_RATIO                                    => 'easy',
            $ratio >= self::SPLIT_RATIO                                   => 'split',
            default                                                       => 'difficult',
        };

        return [
            'verdict'               => $verdict,
            'separation'            => $separation,
            'pa'                    => $pair['pa'],
            'primary'               => $pair['primary'],
            'companion'             => $pair['companion'],
            'delta_mag'             => $deltaMag,
            'dawes'                 => self::dawes($aperture),
            'limit'                 => $limit,
            'ratio'                 => $ratio,
            'steady_seeing'         => $verdict !== 'not_split' && $separation < self::STEADY_SEEING_ARCSEC,
            'minimum_magnification' => self::minimumMagnification($separation),
            'companion_limit'       => $companionLimit,
        ];
    }

    /**
     * The limiting magnitude of the instrument at the magnification under
     * the sky of the NELM, less the extinction.
     */
    public static function limitingMagnitude(Instrument $instrument, float $nelm, ?float $magnification, float $extinction = 0.0): float
    {
        return TelescopicLimitingMagnitude::forInstrument($nelm, $instrument, $magnification) - $extinction;
    }

    /**
     * assess() with the instrument at the location, at the best of the
     * observer's magnifications (ContrastReserveCalculator::magnifications()):
     * the lowest that shows the pair apart, else the highest, with
     * magnification_too_low. The companion is dimmed by the extinction at
     * the highest altitude of the pair; one that never rises is not visible.
     * Null when the object is no double star with a separation or the
     * instrument has no aperture.
     *
     * @param  array<int, list<\App\Models\Eyepiece>>|null  $magnifications  from ContrastReserveCalculator::magnifications(), when already known
     * @return array<string, mixed>|null  assess() with magnification, eyepieces, exit_pupil, magnification_too_low, not_visible, instrument
     */
    public static function forObject(object $object, Instrument $instrument, ?Location $location = null, ?User $user = null, float $lensFactor = 1.0, ?array $magnifications = null): ?array
    {
        $pair     = self::pair($object);
        $aperture = (float) $instrument->aperture_mm;
        if ($pair === null || $aperture <= 0) {
            return null;
        }

        $altitude   = $location && is_numeric($object->decl ?? null) ? ContrastReserveCalculator::highestAltitude((float) $object->decl, $location) : null;
        $extinction = ContrastReserveCalculator::extinction($altitude);
        $notVisible = is_infinite($extinction);

        $magnifications ??= ContrastReserveCalculator::magnifications($instrument, $user, $lensFactor);
        $list = array_keys($magnifications);
        sort($list);
        $minimum = self::minimumMagnification($pair['separation']);
        $chosen  = null;
        foreach ($list as $magnification) {
            if ($magnification >= $minimum) {
                $chosen = (int) $magnification;
                break;
            }
        }
        $tooLow = $chosen === null;
        $chosen ??= $list ? (int) end($list) : null;

        $nelm  = $location?->getNelm((float) ($user?->fstOffset ?? 0));
        $limit = is_numeric($nelm) && ! $notVisible ? self::limitingMagnitude($instrument, (float) $nelm, $chosen ?? $minimum, $extinction) : null;

        return self::assess($pair, $aperture, $limit) + [
            'magnification'         => $chosen,
            'eyepieces'             => $chosen !== null ? ($magnifications[$chosen] ?? []) : [],
            'exit_pupil'            => $chosen !== null ? ContrastReserveCalculator::exitPupil($aperture, $chosen) : null,
            'magnification_too_low' => $tooLow && $chosen !== null,
            'not_visible'           => $notVisible,
            'instrument'            => html_entity_decode((string) $instrument->fullName()),
        ];
    }

    /**
     * assess() at one magnification (a field view), with whether that
     * magnification shows the pair apart; null when the object is no double
     * star with a separation.
     *
     * @return array<string, mixed>|null  assess() with magnification and magnification_too_low
     */
    public static function atMagnification(object $object, float $aperture, mixed $magnification, ?float $limitingMagnitude): ?array
    {
        $pair = self::pair($object);
        if ($pair === null || $aperture <= 0) {
            return null;
        }
        $result = self::assess($pair, $aperture, $limitingMagnitude);

        return $result + [
            'magnification'         => is_numeric($magnification) ? (int) round((float) $magnification) : null,
            'magnification_too_low' => is_numeric($magnification) && (float) $magnification < $result['minimum_magnification'],
        ];
    }

    /**
     * The slugs of the double stars the instrument splits (or, with $split
     * false, does not split yet) at the location: forObject() at the
     * minimum magnification of each pair, so the eyepieces the observer has
     * do not matter. A pair that never rises at the location counts as not
     * split. Without a location the companions are not checked.
     *
     * @return list<string>
     */
    public static function slugs(Instrument $instrument, ?Location $location, ?User $user, bool $split = true): array
    {
        $aperture = (float) $instrument->aperture_mm;
        if ($aperture <= 0) {
            return [];
        }
        $nelm = $location?->getNelm((float) ($user?->fstOffset ?? 0));

        $slugs = [];
        foreach (DB::table('objects')->where('type', 'DS')->where('diam1', '>', 0)->get(['slug', 'type', 'diam1', 'pa', 'mag', 'subr', 'decl']) as $row) {
            $pair       = self::pair($row);
            $altitude   = $location && is_numeric($row->decl) ? ContrastReserveCalculator::highestAltitude((float) $row->decl, $location) : null;
            $extinction = ContrastReserveCalculator::extinction($altitude);
            if (is_infinite($extinction)) {
                $splits = false;
            } else {
                $limit  = is_numeric($nelm) ? self::limitingMagnitude($instrument, (float) $nelm, self::minimumMagnification($pair['separation']), $extinction) : null;
                $splits = in_array(self::assess($pair, $aperture, $limit)['verdict'], ['easy', 'split', 'difficult'], true);
            }
            if ($splits === $split && $row->slug !== null) {
                $slugs[] = (string) $row->slug;
            }
        }

        return $slugs;
    }

    /**
     * The result of forObject() as plain values for a Livewire property or a
     * view: rounded numbers and the names of the eyepieces.
     *
     * @param  array<string, mixed>  $result
     * @return array<string, mixed>
     */
    public static function present(array $result): array
    {
        $round = fn (?float $value, int $decimals) => $value === null ? null : round($value, $decimals) + 0.0;

        return [
            'verdict'               => $result['verdict'],
            'label'                 => self::label($result['verdict']),
            'instrument'            => $result['instrument'] ?? null,
            'separation'            => $round($result['separation'], 1),
            'pa'                    => $result['pa'],
            'primary'               => $round($result['primary'], 1),
            'companion'             => $round($result['companion'], 1),
            'delta_mag'             => $round($result['delta_mag'], 1),
            'dawes'                 => $round($result['dawes'], 2),
            'limit'                 => $round($result['limit'], 2),
            'ratio'                 => $round($result['ratio'], 2),
            'steady_seeing'         => $result['steady_seeing'],
            'minimum_magnification' => $result['minimum_magnification'],
            'companion_limit'       => $round($result['companion_limit'], 1),
            'magnification'         => $result['magnification'] ?? null,
            'eyepieces'             => array_map(fn ($eyepiece) => html_entity_decode((string) $eyepiece->fullName()), $result['eyepieces'] ?? []),
            'exit_pupil'            => $round($result['exit_pupil'] ?? null, 1),
            'magnification_too_low' => (bool) ($result['magnification_too_low'] ?? false),
            'not_visible'           => (bool) ($result['not_visible'] ?? false),
        ];
    }
}
