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
     * The categories, from the lowest contrast reserve above which they apply.
     */
    private const CATEGORIES = [
        'very_easy' => 1.0,
        'easy' => 0.5,
        'quite_difficult' => 0.35,
        'difficult' => 0.1,
        'questionable' => -0.2,
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
     * diameter makes a round object, a magnitude of 99.9 is unknown.
     */
    public static function target(mixed $diam1, mixed $diam2, mixed $magnitude): AstroTarget
    {
        $target = new AstroTarget();
        $d1 = is_numeric($diam1) && (float) $diam1 > 0 ? (float) $diam1 : null;
        $d2 = is_numeric($diam2) && (float) $diam2 > 0 ? (float) $diam2 : null;
        $d1 ??= $d2;
        $d2 ??= $d1;
        if ($d1 !== null) {
            $target->setDiameter($d1, $d2);
        }
        if (is_numeric($magnitude) && (float) $magnitude != 99.9) {
            $target->setMagnitude((float) $magnitude);
        }

        return $target;
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

        $set = $user->standardInstrumentSet;
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
     * The contrast reserve of the target with the instrument at the location,
     * at the best of the observer's magnifications.
     *
     * @param  array<int, list<Eyepiece>>|null  $magnifications  from magnifications(), when already known
     * @return array{contrast_reserve: ?float, category: ?string, magnification: ?int, eyepieces: list<Eyepiece>, sqm: ?float}
     */
    public static function calculate(AstroTarget $target, Instrument $instrument, Location $location, ?User $user, float $lensFactor = 1.0, ?array $magnifications = null): array
    {
        $result = ['contrast_reserve' => null, 'category' => null, 'magnification' => null, 'eyepieces' => [], 'sqm' => null];

        $sqm = $location->getSqm();
        $result['sqm'] = is_numeric($sqm) ? (float) $sqm : null;
        $aperture = (float) $instrument->aperture_mm;
        if ($result['sqm'] === null || $aperture <= 0) {
            return $result;
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
        $best = $target->calculateBestMagnification($sbobj, $result['sqm'], $aperture, array_keys($magnifications));
        if (! $best) {
            return $result;
        }
        $best = (int) $best;
        $contrast = $target->calculateContrastReserve($sbobj, $result['sqm'], $aperture, $best);

        return [
            'contrast_reserve' => is_numeric($contrast) ? (float) $contrast : null,
            'category' => self::category($contrast),
            'magnification' => $best,
            'eyepieces' => $magnifications[$best] ?? [],
            'sqm' => $result['sqm'],
        ];
    }
}
