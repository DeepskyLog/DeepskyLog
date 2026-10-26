<?php

namespace App\Services;

use App\Models\Filter;
use App\Models\Instrument;
use App\Models\InstrumentSet;
use App\Models\Location;
use App\Models\User;
use deepskylog\AstronomyLibrary\Targets\Target as AstroTarget;
use Illuminate\Support\Collection;

/**
 * Which of the observer's nebula filters help to see an emission nebula, and
 * by how much: the contrast reserve with the filter, with the sky darkened by
 * the part of its light the filter blocks and the object dimmed by the part
 * of its light the filter blocks.
 *
 * DeepskyLog holds no transmission curves of filters, so every filter gets the
 * typical passband of its kind (PASSBANDS), recognised from its name (many
 * filters have the wrong type: UHC filters stored as broadband, O-III as
 * narrowband) or else its type. The spectrum of a nebula is that of its type
 * (SPECTRA): the part of its visual light in the [O III] lines (496/501 nm)
 * and in H-beta (486 nm), the rest continuum, which a filter passes as much
 * as the sky. The fractions are weighted with the sensitivity of the
 * dark-adapted eye, so H-alpha, which it hardly sees, does not count.
 */
class FilterAdvice
{
    /**
     * Part of the light passed by a filter of each kind: of the sky (and of a
     * continuum), of the [O III] lines and of H-beta.
     */
    public const PASSBANDS = [
        'broadband' => ['sky' => 0.50, 'oiii' => 0.90, 'hbeta' => 0.90],
        'uhc'       => ['sky' => 0.18, 'oiii' => 0.92, 'hbeta' => 0.92],
        'oiii'      => ['sky' => 0.07, 'oiii' => 0.90, 'hbeta' => 0.0],
        'hbeta'     => ['sky' => 0.06, 'oiii' => 0.0, 'hbeta' => 0.90],
    ];

    /**
     * Part of the visual light of a nebula of each type in the [O III] lines
     * and in H-beta. Types not listed shine in a continuum (galaxies,
     * clusters, reflection nebulae), which no filter helps.
     */
    public const SPECTRA = [
        'PLNNB' => ['oiii' => 0.75, 'hbeta' => 0.10],
        'SNREM' => ['oiii' => 0.50, 'hbeta' => 0.20],
        'EMINB' => ['oiii' => 0.50, 'hbeta' => 0.20],
        'HII'   => ['oiii' => 0.50, 'hbeta' => 0.20],
        'WRNEB' => ['oiii' => 0.45, 'hbeta' => 0.20],
        'GXADN' => ['oiii' => 0.40, 'hbeta' => 0.15],
        'BRTNB' => ['oiii' => 0.30, 'hbeta' => 0.15],
        'GACAN' => ['oiii' => 0.15, 'hbeta' => 0.05],
    ];

    /**
     * The nebulae that shine mostly in H-beta and hardly in [O III], the
     * objects the H-beta filter is known for.
     */
    public const HBETA_OBJECTS = ['IC 434', 'NGC 1499', 'IC 5146', 'IC 2177', 'NGC 2327', 'Sh 2-276'];

    private const HBETA_SPECTRUM = ['oiii' => 0.10, 'hbeta' => 0.50];

    /**
     * The name of a kind of passband, in the language of the request.
     */
    public static function label(string $passband): string
    {
        return match ($passband) {
            'broadband' => __('Broadband (light pollution)'),
            'uhc'       => __('UHC (narrowband)'),
            'oiii'      => __('O-III'),
            'hbeta'     => __('H-beta'),
        };
    }

    /**
     * The rows of advise() as plain arrays, for a Livewire property or a
     * view: the filter's name and page, the kind of passband, the contrast
     * reserve with its category and gain, the magnification with the
     * eyepieces and exit pupil, the ideal magnification and exit pupil, and
     * the values at the magnification of a view.
     *
     * @param  list<array<string, mixed>>  $advice
     * @return list<array{name: string, url: ?string, kind: string, contrast_reserve: float, category: ?string, gain: float,
     *     magnification: int, eyepieces: list<string>, exit_pupil: ?float, ideal_magnification: ?int, ideal_exit_pupil: ?float,
     *     current: array{contrast_reserve: float, category: ?string, gain: float}|null}>
     */
    public static function present(array $advice): array
    {
        $round = fn (?float $value, int $decimals = 2) => $value === null ? null : round($value, $decimals) + 0.0;

        return array_map(function (array $row) use ($round) {
            $filter = $row['filter'];
            $owner  = $filter->user;

            return [
                'name'                => html_entity_decode((string) $filter->name),
                'url'                 => $owner && $filter->slug ? route('filter.show', ['user' => $owner->slug, 'filter' => $filter->slug]) : null,
                'kind'                => self::label($row['passband']),
                'contrast_reserve'    => $round($row['contrast_reserve']),
                'category'            => $row['category'],
                'gain'                => $round($row['gain']),
                'magnification'       => $row['magnification'],
                'eyepieces'           => array_map(fn ($eyepiece) => html_entity_decode((string) $eyepiece->fullName()), $row['eyepieces']),
                'exit_pupil'          => $round($row['exit_pupil'], 1),
                'ideal_magnification' => $row['ideal_magnification'],
                'ideal_exit_pupil'    => $round($row['ideal_exit_pupil'], 1),
                'current'             => $row['current'] === null ? null : [
                    'contrast_reserve' => $round($row['current']['contrast_reserve']),
                    'category'         => $row['current']['category'],
                    'gain'             => $round($row['current']['gain']),
                ],
            ];
        }, $advice);
    }

    /**
     * The kind of passband of a filter (a key of PASSBANDS), or null for a
     * filter that is no nebula filter (colour, polarising, Moon, solar, comet
     * filters).
     */
    public static function passband(Filter $filter): ?string
    {
        $name = html_entity_decode((string) $filter->name);
        if (preg_match('/\bh\s*-?\s*(beta|β|ß)/iu', $name)) {
            return 'hbeta';
        }
        if (preg_match('/\bo\s*-?\s*(iii|3)\b/iu', $name)) {
            return 'oiii';
        }
        if (preg_match('/uhc|ultra\s*block|\bnpb\b|narrow\s*-?\s*band|l-booster|ultra high contrast/iu', $name)) {
            return 'uhc';
        }
        if (preg_match('/\blpr\b|\bcls\b|deep\s*-?\s*sky|sky\s*glow|light\s*pollution|broad\s*-?\s*band|neodymium/iu', $name)) {
            return 'broadband';
        }
        if (preg_match('/polari|swan|comet|komet|solar|\bsun\b|moon|maan|apodi|no filter|neutral/iu', $name)) {
            return null;
        }

        return match ((int) $filter->type_id) {
            2       => 'broadband',
            3       => 'uhc',
            4       => 'oiii',
            5       => 'hbeta',
            default => null,
        };
    }

    /**
     * The part of the light of an object of the type in the [O III] lines and
     * in H-beta, or null when the object shines in a continuum.
     *
     * @return array{oiii: float, hbeta: float}|null
     */
    public static function spectrum(?string $type, ?string $name = null): ?array
    {
        if ($name !== null && in_array($name, self::HBETA_OBJECTS, true)) {
            return self::HBETA_SPECTRUM;
        }

        return self::SPECTRA[strtoupper((string) $type)] ?? null;
    }

    /**
     * Magnitudes the filter darkens the sky and dims the object by.
     *
     * @param  array{oiii: float, hbeta: float}  $spectrum
     * @return array{sky: float, object: float}
     */
    public static function dimming(string $passband, array $spectrum): array
    {
        $pass      = self::PASSBANDS[$passband];
        $continuum = max(0.0, 1.0 - $spectrum['oiii'] - $spectrum['hbeta']);
        $object    = $spectrum['oiii'] * $pass['oiii'] + $spectrum['hbeta'] * $pass['hbeta'] + $continuum * $pass['sky'];

        return [
            'sky'    => -2.5 * log10($pass['sky']),
            'object' => -2.5 * log10(max($object, 1e-6)),
        ];
    }

    /**
     * The observer's nebula filters: the active ones of the instrument set
     * when it has any, else all active filters of the observer.
     *
     * @return Collection<int, Filter>
     */
    public static function filters(User $user, ?InstrumentSet $set = null): Collection
    {
        $usable = fn ($filters) => collect($filters)
            ->filter(fn (Filter $filter) => $filter->active && self::passband($filter) !== null)
            ->sortBy(fn (Filter $filter) => mb_strtolower(html_entity_decode((string) $filter->name)))
            ->each(fn (Filter $filter) => $filter->setRelation('user', $user))
            ->values();

        $fromSet = $set ? $usable($set->filters) : collect();

        return $fromSet->isNotEmpty() ? $fromSet : $usable(Filter::where('user_id', $user->id)->where('active', 1)->get());
    }

    /**
     * The advice of advise() for the observer's nebula filters, from the
     * magnifications of the observer's eyepieces with the instrument
     * (ContrastReserveCalculator::magnifications()), under the dark sky of
     * the location, with the object dimmed by the extinction at the altitude.
     * The filters are those of the instrument set, by default the standard
     * set (filters()).
     *
     * @return list<array<string, mixed>> see advise()
     */
    public static function forObject(AstroTarget $target, ?string $type, ?string $name, Instrument $instrument, Location $location, User $user, float $lensFactor = 1.0, ?float $altitude = null, ?InstrumentSet $set = null): array
    {
        $sqm        = $location->getSqm();
        $extinction = ContrastReserveCalculator::extinction($altitude);
        if (self::spectrum($type, $name) === null || ! is_numeric($sqm) || $target->getMagnitude() === null || is_infinite($extinction)) {
            return [];
        }
        $filters = self::filters($user, $set ?? $user->standardInstrumentSet);
        if ($filters->isEmpty()) {
            return [];
        }

        $dimmed = clone $target;
        $dimmed->setMagnitude($target->getMagnitude() + $extinction);
        $magnifications = ContrastReserveCalculator::magnifications($instrument, $user, $lensFactor);

        return self::advise($dimmed, $type, $name, $filters, $instrument, (float) $sqm, $magnifications, ContrastReserveCalculator::fields($instrument, $magnifications, $lensFactor));
    }

    /**
     * Per nebula filter, best first: the contrast reserve at the best of the
     * magnifications (ContrastReserveCalculator::bestMagnification(): the
     * object in the field), with the eyepieces giving it and its exit pupil,
     * and the gain against the best magnification without a filter (which
     * may be another one); the ideal magnification, the best of the exit
     * pupils from EYE_PUPIL_MM down to 0.5 mm in steps of 0.5 mm, whatever
     * eyepieces the observer has (none for an instrument with a fixed
     * magnification); with $current, the magnification of a view, the
     * contrast reserve and gain at that magnification too. A filter makes
     * the image darker, so its best exit pupil is often larger than without
     * one. Empty when the object shines in a continuum or has no contrast
     * reserve without a filter. The target is already dimmed by the
     * extinction.
     *
     * @param  iterable<Filter>  $filters
     * @param  array<int, iterable<\App\Models\Eyepiece>>  $magnifications  magnification => eyepieces
     * @param  array<int, float>  $fields  true field of view (degrees) per magnification (ContrastReserveCalculator::fields())
     * @return list<array{filter: Filter, passband: string, contrast_reserve: float, category: ?string, gain: float,
     *     magnification: int, eyepieces: list<\App\Models\Eyepiece>, exit_pupil: ?float,
     *     ideal_magnification: ?int, ideal_exit_pupil: ?float,
     *     current: array{contrast_reserve: float, category: ?string, gain: float}|null}>
     */
    public static function advise(AstroTarget $target, ?string $type, ?string $name, iterable $filters, Instrument $instrument, float $sqm, array $magnifications, array $fields = [], ?float $current = null): array
    {
        $spectrum = self::spectrum($type, $name);
        $aperture = (float) $instrument->aperture_mm;
        if ($spectrum === null || $target->getMagnitude() === null || $aperture <= 0 || ! $magnifications) {
            return [];
        }
        $without = self::best($target, $sqm, $aperture, array_keys($magnifications), $fields);
        if ($without === null) {
            return [];
        }
        $withoutCurrent = $current ? self::reserve($target, $sqm, $aperture, $current) : null;
        $fixed          = (float) $instrument->fixedMagnification > 0;

        $byPassband = [];
        $advice     = [];
        foreach ($filters as $filter) {
            $passband = self::passband($filter);
            if ($passband === null) {
                continue;
            }
            if (! array_key_exists($passband, $byPassband)) {
                $dimming  = self::dimming($passband, $spectrum);
                $filtered = clone $target;
                $filtered->setMagnitude($target->getMagnitude() + $dimming['object']);
                $filteredSqm = $sqm + $dimming['sky'];
                $with        = self::best($filtered, $filteredSqm, $aperture, array_keys($magnifications), $fields);
                $ideal       = $fixed ? null : self::best($filtered, $filteredSqm, $aperture, self::exitPupilMagnifications($aperture));
                $atCurrent   = $current ? self::reserve($filtered, $filteredSqm, $aperture, $current) : null;

                $byPassband[$passband] = $with === null ? null : [$with, $ideal, $atCurrent];
            }
            if ($byPassband[$passband] === null) {
                continue;
            }
            [$with, $ideal, $atCurrent] = $byPassband[$passband];

            $advice[] = [
                'filter'              => $filter,
                'passband'            => $passband,
                'contrast_reserve'    => $with[1],
                'category'            => ContrastReserveCalculator::category($with[1]),
                'gain'                => $with[1] - $without[1],
                'magnification'       => $with[0],
                'eyepieces'           => collect($magnifications[$with[0]] ?? [])->values()->all(),
                'exit_pupil'          => ContrastReserveCalculator::exitPupil($aperture, $with[0]),
                'ideal_magnification' => $ideal[0] ?? null,
                'ideal_exit_pupil'    => $ideal ? ContrastReserveCalculator::exitPupil($aperture, $ideal[0]) : null,
                'current'             => $atCurrent !== null && $withoutCurrent !== null
                    ? ['contrast_reserve' => $atCurrent, 'category' => ContrastReserveCalculator::category($atCurrent), 'gain' => $atCurrent - $withoutCurrent]
                    : null,
            ];
        }
        usort($advice, fn (array $a, array $b) => $b['contrast_reserve'] <=> $a['contrast_reserve']);

        return $advice;
    }

    /**
     * The magnifications of the exit pupils from EYE_PUPIL_MM down to 0.5 mm,
     * in steps of 0.5 mm.
     *
     * @return list<int>
     */
    public static function exitPupilMagnifications(float $aperture): array
    {
        $magnifications = [];
        for ($pupil = ContrastReserveCalculator::EYE_PUPIL_MM; $pupil >= 0.5; $pupil -= 0.5) {
            $magnifications[] = max(1, (int) round($aperture / $pupil));
        }

        return array_values(array_unique($magnifications));
    }

    /**
     * The best of the magnifications with its contrast reserve, or null.
     *
     * @param  list<int>  $magnifications
     * @param  array<int, float>  $fields
     * @return array{0: int, 1: float}|null
     */
    private static function best(AstroTarget $target, float $sqm, float $aperture, array $magnifications, array $fields = []): ?array
    {
        $sbobj = self::surfaceBrightness($target);
        $best  = $sbobj === null ? null : ContrastReserveCalculator::bestMagnification($target, $sbobj, $sqm, $aperture, $magnifications, $fields);
        $value = $best ? ContrastReserveCalculator::contrastAt($target, $sbobj, $sqm, $aperture, $best) : null;

        return $value === null ? null : [$best, $value];
    }

    private static function reserve(AstroTarget $target, float $sqm, float $aperture, float $magnification): ?float
    {
        $sbobj = self::surfaceBrightness($target);

        return $sbobj === null ? null : ContrastReserveCalculator::contrastAt($target, $sbobj, $sqm, $aperture, $magnification);
    }

    private static function surfaceBrightness(AstroTarget $target): ?float
    {
        try {
            return $target->calculateSBObj();
        } catch (\Throwable) {
            // no diameter or magnitude
            return null;
        }
    }
}
