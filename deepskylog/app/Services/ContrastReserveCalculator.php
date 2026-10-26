<?php

namespace App\Services;

use App\Models\Eyepiece;
use App\Models\Instrument;
use App\Models\Lens;
use App\Models\Location;
use App\Models\User;
use deepskylog\AstronomyLibrary\Targets\Target as AstroTarget;

/**
 * The contrast reserve of an object with the equipment of an observer, for the
 * preview on the object pages and for the stored metrics (user_object_metrics)
 * of the tables, so both give the same value, magnification and category.
 */
class ContrastReserveCalculator
{
    /**
     * Magnifications tried when the observer has no eyepieces.
     */
    public const GENERIC_MAGNIFICATIONS = [25, 50, 75, 100, 150, 200];

    /**
     * The diameter (mm) of the dark-adapted pupil of the eye. At a lower
     * magnification the exit pupil is larger than the eye pupil: the eye
     * then takes in only part of the light of the instrument, so the sky
     * gets no brighter (contrastAt()).
     */
    public const EYE_PUPIL_MM = 7.0;

    /**
     * The categories, from the lowest contrast reserve above which they apply.
     */
    private const CATEGORIES = [
        'very_easy'       => 1.0,
        'easy'            => 0.5,
        'quite_difficult' => 0.35,
        'difficult'       => 0.1,
        'questionable'    => -0.2,
    ];

    /**
     * The category of a contrast reserve: very_easy, easy, quite_difficult,
     * difficult, questionable or not_visible.
     */
    public static function category(mixed $contrastReserve): ?string
    {
        if (! is_numeric($contrastReserve)) {
            return null;
        }
        foreach (self::CATEGORIES as $category => $above) {
            if ((float) $contrastReserve > $above) {
                return $category;
            }
        }

        return 'not_visible';
    }

    /**
     * Whether an object of the type has a contrast reserve: not a double
     * star, which is a pair of points whose diam1 holds the separation
     * (DoubleStarResolvability says whether it is split instead).
     */
    public static function appliesTo(?string $type): bool
    {
        return strtoupper(trim((string) $type)) !== 'DS';
    }

    /**
     * The factor of a lens (1 without a lens or with an invalid factor).
     */
    public static function lensFactor(Lens|int|string|null $lens): float
    {
        if ($lens !== null && ! $lens instanceof Lens) {
            $lens = Lens::find($lens);
        }
        $factor = $lens?->factor;

        return is_numeric($factor) && (float) $factor > 0 ? (float) $factor : 1.0;
    }

    /**
     * A target with the diameters and the magnitude of an object; one
     * diameter makes a round object, a magnitude of 99.9 or 0 is unknown
     * (0 is what the old site stored for a missing magnitude: AND IV, PGC
     * 12203; read as a magnitude, the object would be very easy to see).
     */
    public static function target(mixed $diam1, mixed $diam2, mixed $magnitude): AstroTarget
    {
        $target = new AstroTarget();
        $d1     = is_numeric($diam1) && (float) $diam1 > 0 ? (float) $diam1 : null;
        $d2     = is_numeric($diam2) && (float) $diam2 > 0 ? (float) $diam2 : null;
        $d1 ??= $d2;
        $d2 ??= $d1;
        if ($d1 !== null) {
            $target->setDiameter($d1, $d2);
        }
        if (is_numeric($magnitude) && (float) $magnitude != 99.9 && (float) $magnitude != 0.0) {
            $target->setMagnitude((float) $magnitude);
        }

        return $target;
    }

    /**
     * The highest altitude of an object at the location, when it crosses the
     * meridian: the altitude the contrast reserve is calculated at, with the
     * extinction there. Null when the declination or latitude is unknown.
     */
    public static function highestAltitude(mixed $decDeg, Location $location): ?float
    {
        if (! is_numeric($decDeg) || ! is_numeric($location->latitude)) {
            return null;
        }

        return AtmosphericExtinction::highestAltitude((float) $decDeg, (float) $location->latitude);
    }

    /**
     * The eyepieces the magnifications are taken from: the active eyepieces of
     * the observer's standard instrument set, else all active eyepieces of the
     * observer.
     *
     * @return list<Eyepiece>
     */
    public static function eyepieces(?User $user): array
    {
        if (! $user) {
            return [];
        }
        $usable = fn ($eyepieces) => collect($eyepieces)
            ->filter(fn ($ep) => $ep->active && (float) $ep->focal_length_mm > 0)
            ->values()
            ->all();

        $set     = $user->standardInstrumentSet;
        $fromSet = $set ? $usable($set->eyepieces) : [];

        return $fromSet ?: $usable(Eyepiece::where('user_id', $user->id)->where('active', 1)->get());
    }

    /**
     * The magnifications to choose from, with the eyepieces giving each of
     * them. An instrument with a fixed magnification (binoculars, finders)
     * only has that magnification, without the lens: the lens is the
     * observer's default lens, which does not fit those. Without eyepieces
     * the generic magnifications are used, without eyepieces.
     *
     * @return array<int, list<Eyepiece>> magnification => eyepieces
     */
    public static function magnifications(Instrument $instrument, ?User $user, float $lensFactor = 1.0): array
    {
        if ((float) $instrument->fixedMagnification > 0) {
            return [(int) round((float) $instrument->fixedMagnification) => []];
        }

        $magnifications = [];
        if ((float) $instrument->focal_length_mm > 0) {
            foreach (self::eyepieces($user) as $ep) {
                $mag = (int) round((float) $instrument->focal_length_mm / (float) $ep->focal_length_mm * $lensFactor);
                if ($mag > 0) {
                    $magnifications[$mag][] = $ep;
                }
            }
        }

        return $magnifications ?: array_fill_keys(self::GENERIC_MAGNIFICATIONS, []);
    }

    /**
     * The optimum detection magnification: of the magnifications whose
     * field of view holds the whole object (its major axis), the one with
     * the highest contrast reserve. When none holds it, the one with the
     * widest field. The library alone would choose the highest
     * magnification for a large object, at which only a part of it is seen.
     *
     * @param  list<int|float>  $magnifications
     * @param  array<int, float>  $fields  true field of view (degrees) per magnification; else estimated
     */
    public static function bestMagnification(AstroTarget $target, float $sbobj, ?float $sqm, ?float $aperture, array $magnifications, array $fields = []): ?int
    {
        $magnifications = array_values(array_unique(array_filter(array_map('intval', $magnifications), fn (int $m) => $m > 0)));
        if (! $magnifications) {
            return null;
        }

        $field = fn (int $m) => (float) ($fields[$m] ?? Instrument::FIXED_MAGNIFICATION_APPARENT_FOV / $m);
        $size  = max(array_map('floatval', $target->getDiameter())) / 3600.0;
        if ($size > 0) {
            $fitting = array_values(array_filter($magnifications, fn (int $m) => $field($m) >= $size));
            if (! $fitting) {
                usort($magnifications, fn (int $a, int $b) => $field($b) <=> $field($a));
                $fitting = [$magnifications[0]];
            }
            $magnifications = $fitting;
        }

        $best         = null;
        $bestContrast = null;
        foreach ($magnifications as $magnification) {
            $contrast = self::contrastAt($target, $sbobj, $sqm, $aperture, $magnification);
            if (is_numeric($contrast) && ($bestContrast === null || $contrast > $bestContrast)) {
                [$best, $bestContrast] = [$magnification, (float) $contrast];
            }
        }

        return $best;
    }

    /**
     * The contrast reserve at a magnification (Clark's model of the
     * library), with the light the eye takes in limited by the eye pupil:
     * at an exit pupil larger than EYE_PUPIL_MM, the instrument works as one
     * of the aperture that gives that exit pupil. The library would let the
     * sky in the eyepiece get ever brighter at lower magnifications, and
     * choose the lowest magnification for a faint nebula, the more so
     * behind a filter.
     */
    public static function contrastAt(AstroTarget $target, float $sbobj, ?float $sqm, ?float $aperture, float $magnification): ?float
    {
        if (! $aperture || $magnification <= 0) {
            return null;
        }
        $contrast = $target->calculateContrastReserve($sbobj, $sqm, min($aperture, $magnification * self::EYE_PUPIL_MM), $magnification);

        return is_numeric($contrast) ? (float) $contrast : null;
    }

    /**
     * The exit pupil (mm) of the aperture at the magnification.
     */
    public static function exitPupil(float $aperture, float $magnification): ?float
    {
        return $aperture > 0 && $magnification > 0 ? $aperture / $magnification : null;
    }

    /**
     * The true field of view (degrees) at each magnification: the widest
     * of the eyepieces giving it, from their field stop or apparent field;
     * the field of an instrument with a fixed magnification; else (no
     * eyepieces, or without a field) none, so that it is estimated.
     *
     * @param  array<int, iterable<Eyepiece>>  $magnifications  magnification => eyepieces
     * @return array<int, float>
     */
    public static function fields(Instrument $instrument, array $magnifications, float $lensFactor = 1.0): array
    {
        $fields = [];
        foreach ($magnifications as $magnification => $eyepieces) {
            $magnification = (int) $magnification;
            if ($magnification <= 0) {
                continue;
            }
            $widest = (float) $instrument->fixedMagnification > 0 ? $instrument->fixedFieldOfViewDeg() : null;
            foreach ($eyepieces as $eyepiece) {
                $fov = null;
                if ((float) $eyepiece->field_stop_mm > 0 && (float) $instrument->focal_length_mm > 0) {
                    $fov = (float) $eyepiece->field_stop_mm / ((float) $instrument->focal_length_mm * $lensFactor) * 57.2958;
                } elseif ((float) $eyepiece->apparentFOV > 10) {
                    $fov = (float) $eyepiece->apparentFOV / $magnification;
                }
                if ($fov !== null && ($widest === null || $fov > $widest)) {
                    $widest = $fov;
                }
            }
            if ($widest !== null) {
                $fields[$magnification] = $widest;
            }
        }

        return $fields;
    }

    /**
     * The magnifications the eyepieces give with the instrument, with the
     * eyepieces giving each, for fields().
     *
     * @param  iterable<Eyepiece>  $eyepieces
     * @return array<int, list<Eyepiece>>
     */
    public static function eyepieceMagnifications(Instrument $instrument, iterable $eyepieces, float $lensFactor = 1.0): array
    {
        $magnifications = [];
        if ((float) $instrument->focal_length_mm > 0) {
            foreach ($eyepieces as $eyepiece) {
                if ((float) $eyepiece->focal_length_mm > 0) {
                    $magnifications[(int) round((float) $instrument->focal_length_mm / (float) $eyepiece->focal_length_mm * $lensFactor)][] = $eyepiece;
                }
            }
        }

        return $magnifications;
    }

    /**
     * The contrast reserve of a point source, such as an asteroid, which
     * Clark's model of calculate() cannot handle (it needs a size): the
     * margin of its brightness above the faintest star visible in the
     * instrument, in the same log10 units as the contrast reserve,
     * 0.4 (limit - magnitude), so the categories mean the same. The limit
     * (TelescopicLimitingMagnitude) comes from the naked-eye limit of the
     * location and grows with the magnification as the sky darkens; the best
     * magnification is the lowest with the largest margin. At an altitude,
     * the object is dimmed by the extinction there (extinction()).
     *
     * @param  array<int, list<Eyepiece>>|null  $magnifications  from magnifications(), when already known
     * @param  float|null  $altitude  the altitude (degrees) the object is seen at; null for the zenith
     * @return array{contrast_reserve: ?float, category: ?string, magnification: ?int, eyepieces: list<Eyepiece>, sqm: ?float, extinction: ?float}
     */
    public static function pointSource(float $magnitude, Instrument $instrument, Location $location, ?User $user, float $lensFactor = 1.0, ?array $magnifications = null, ?float $altitude = null): array
    {
        $sqm    = $location->getSqm();
        $result = ['contrast_reserve' => null, 'category' => null, 'magnification' => null, 'eyepieces' => [], 'sqm' => is_numeric($sqm) ? (float) $sqm : null, 'extinction' => null];

        $extinction = self::extinction($altitude);
        if (is_infinite($extinction)) {
            return [...$result, 'category' => 'not_visible'];
        }
        $result['extinction'] = $altitude === null ? null : $extinction;
        $magnitude += $extinction;

        $nelm     = $location->getNelm((float) ($user?->fstOffset ?? 0));
        $aperture = (float) $instrument->aperture_mm;
        if (! is_numeric($nelm) || $aperture <= 0 || $magnitude >= 99.0) {
            return $result;
        }

        $magnifications ??= self::magnifications($instrument, $user, $lensFactor);
        $magnificationList = array_keys($magnifications);
        sort($magnificationList);
        $best = null;
        foreach ($magnificationList as $magnification) {
            $limit   = TelescopicLimitingMagnitude::calculate((float) $nelm, $aperture, (float) $magnification, (float) $instrument->obstruction_perc, $instrument->hasMirrors());
            $reserve = 0.4 * ($limit - $magnitude);
            // the lowest magnification wins a tie: the limit stops growing at small exit pupils
            if ($best === null || $reserve > $best[1] + 1e-9) {
                $best = [(int) $magnification, $reserve];
            }
        }
        if ($best === null) {
            return $result;
        }

        return [
            'contrast_reserve' => $best[1],
            'category'         => self::category($best[1]),
            'magnification'    => $best[0],
            'eyepieces'        => $magnifications[$best[0]] ?? [],
            'sqm'              => $result['sqm'],
            'extinction'       => $result['extinction'],
        ];
    }

    /**
     * The contrast reserve of the target with the instrument at the location,
     * at the best of the observer's magnifications. At an altitude, the
     * object is dimmed by the extinction there (extinction()): the stored
     * metrics use the highest altitude of the object (highestAltitude()), so
     * an object low in the south of the observer's sky gets a lower contrast
     * reserve than one passing overhead. An object that is below the horizon
     * is not visible, without a contrast reserve.
     *
     * @param  array<int, list<Eyepiece>>|null  $magnifications  from magnifications(), when already known
     * @param  float|null  $sqm  the sky brightness to use instead of the location's (a sky lit by the Moon)
     * @param  float|null  $altitude  the altitude (degrees) the object is seen at; null for the zenith
     * @return array{contrast_reserve: ?float, category: ?string, magnification: ?int, eyepieces: list<Eyepiece>, sqm: ?float, extinction: ?float}
     */
    public static function calculate(AstroTarget $target, Instrument $instrument, Location $location, ?User $user, float $lensFactor = 1.0, ?array $magnifications = null, ?float $sqm = null, ?float $altitude = null): array
    {
        $result = ['contrast_reserve' => null, 'category' => null, 'magnification' => null, 'eyepieces' => [], 'sqm' => null, 'extinction' => null];

        $sqm         ??= $location->getSqm();
        $result['sqm'] = is_numeric($sqm) ? (float) $sqm : null;
        $aperture      = (float) $instrument->aperture_mm;
        if ($result['sqm'] === null || $aperture <= 0) {
            return $result;
        }

        $extinction = self::extinction($altitude);
        if (is_infinite($extinction)) {
            return [...$result, 'category' => 'not_visible'];
        }
        $result['extinction'] = $altitude === null ? null : $extinction;
        if ($extinction > 0 && $target->getMagnitude() !== null) {
            // the caller's target stays as it is
            $target = clone $target;
            $target->setMagnitude($target->getMagnitude() + $extinction);
        }

        try {
            $sbobj = $target->calculateSBObj();
        } catch (\Throwable $_) {
            // an object without a diameter or magnitude has no surface brightness
            $sbobj = null;
        }
        if ($sbobj === null) {
            return $result;
        }

        $magnifications ??= self::magnifications($instrument, $user, $lensFactor);
        $best = self::bestMagnification($target, $sbobj, $result['sqm'], $aperture, array_keys($magnifications), self::fields($instrument, $magnifications, $lensFactor));
        if (! $best) {
            return $result;
        }
        $best     = (int) $best;
        $contrast = self::contrastAt($target, $sbobj, $result['sqm'], $aperture, $best);

        return [
            'contrast_reserve' => is_numeric($contrast) ? (float) $contrast : null,
            'category'         => self::category($contrast),
            'magnification'    => $best,
            'eyepieces'        => $magnifications[$best] ?? [],
            'sqm'              => $result['sqm'],
            'extinction'       => $result['extinction'],
        ];
    }

    /**
     * Magnitudes the object loses at the altitude (AtmosphericExtinction),
     * 0 without an altitude, INF below the horizon.
     */
    public static function extinction(?float $altitude): float
    {
        return $altitude === null ? 0.0 : AtmosphericExtinction::magnitudes($altitude);
    }
}
