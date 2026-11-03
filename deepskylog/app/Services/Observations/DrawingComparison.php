<?php

namespace App\Services\Observations;

use App\Models\Observation;
use App\Models\ObservationLike;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * The drawings of a deep-sky object grouped by the aperture of the instrument
 * they were made with, so that the page /object/{slug}/drawings/compare can
 * show how the object looks in 8" against 16".
 */
class DrawingComparison
{
    /**
     * Aperture classes: key => [smallest aperture in mm, first aperture of
     * the next class, size in inches for the label]. They follow the usual
     * sizes of telescopes, so that an 8" (203 mm) and a 200 mm both fall in
     * the class 8".
     */
    public const CLASSES = [
        'small' => [0, 110, [null, 4]],
        '5-6' => [110, 180, [5, 6]],
        '8-inch' => [180, 230, [8, 8]],
        '10-12' => [230, 330, [10, 12]],
        '14-16' => [330, 430, [14, 16]],
        '18-20' => [430, 530, [18, 20]],
        'large' => [530, PHP_INT_MAX, [22, null]],
    ];

    /** Drawings without an instrument, or with one without an aperture. */
    public const UNKNOWN = 'unknown';

    /** Drawings read for an object; the newest ones when there are more. */
    public const MAX_DRAWINGS = 1000;

    /** Drawings shown per class on the comparison page. */
    public const PER_CLASS = 60;

    /**
     * The drawings of the object per aperture class, from small to large
     * (unknown last), each class newest first; classes without drawings left
     * out.
     *
     * @return array<string, Collection<int, Observation>>
     */
    public function forObject(string $objectName): array
    {
        $drawings = Observation::ofDeepskyObject($objectName)
            ->where('observations.has_drawing', 1)
            ->leftJoin('instruments', 'instruments.id', '=', 'observations.instrument_id')
            ->select('observations.*', 'instruments.aperture_mm as drawing_aperture_mm')
            ->orderByDesc('observations.observed_on')
            ->orderByDesc('observations.id')
            ->limit(self::MAX_DRAWINGS)
            ->get();

        $classes = [];
        foreach (array_merge(array_keys(self::CLASSES), [self::UNKNOWN]) as $key) {
            $inClass = $drawings->filter(fn (Observation $o) => self::classFor($o->drawing_aperture_mm) === $key)->values();
            if ($inClass->isNotEmpty()) {
                $classes[$key] = $inClass;
            }
        }

        return $classes;
    }

    /**
     * One drawing per aperture class with a known aperture, the most liked
     * (then the newest), for the object page.
     *
     * @param  array<string, Collection<int, Observation>>  $classes  from forObject()
     * @return array<string, Observation>
     */
    public function highlights(array $classes): array
    {
        unset($classes[self::UNKNOWN]);
        $ids = collect($classes)->flatten(1)->pluck('id');
        $likes = $ids->isEmpty() ? collect() : ObservationLike::where('observation_type', ObservationLike::TYPE_OBSERVATION)
            ->whereIn('observation_id', $ids)
            ->groupBy('observation_id')
            ->select('observation_id', DB::raw('COUNT(*) AS likes_count'))
            ->pluck('likes_count', 'observation_id');

        $highlights = [];
        foreach ($classes as $key => $drawings) {
            // The drawings are newest first, so a stable sort keeps the newest of equal likes first.
            $highlights[$key] = $drawings->sortByDesc(fn (Observation $o) => (int) ($likes[$o->id] ?? 0))->first();
        }

        return $highlights;
    }

    /** The aperture class of an aperture in mm. */
    public static function classFor(mixed $apertureMm): string
    {
        if (! is_numeric($apertureMm) || (float) $apertureMm <= 0) {
            return self::UNKNOWN;
        }
        foreach (self::CLASSES as $key => [$from, $to]) {
            if ($apertureMm >= $from && $apertureMm < $to) {
                return $key;
            }
        }

        return self::UNKNOWN;
    }

    public static function isClass(?string $key): bool
    {
        return $key === self::UNKNOWN || isset(self::CLASSES[$key]);
    }

    /** Label of an aperture class, in inches or millimetres. */
    public static function label(string $key, bool $inches): string
    {
        if (! isset(self::CLASSES[$key])) {
            return __('Unknown aperture');
        }
        [$from, $to, [$fromInch, $toInch]] = self::CLASSES[$key];

        if ($inches) {
            if ($fromInch === null) {
                return __('up to :size', ['size' => $toInch.'″']);
            }
            if ($toInch === null) {
                return __(':size and more', ['size' => $fromInch.'″']);
            }

            return $fromInch === $toInch ? $fromInch.'″' : $fromInch.'–'.$toInch.'″';
        }

        if ($from === 0) {
            return __('up to :size', ['size' => ($to - 1).' mm']);
        }
        if ($to === PHP_INT_MAX) {
            return __(':size and more', ['size' => $from.' mm']);
        }

        return $from.'–'.($to - 1).' mm';
    }

    /** An aperture in mm as the viewer reads it. */
    public static function aperture(mixed $apertureMm, bool $inches): ?string
    {
        if (! is_numeric($apertureMm) || (float) $apertureMm <= 0) {
            return null;
        }

        return $inches
            ? rtrim(rtrim(number_format($apertureMm / 25.4, 1, '.', ''), '0'), '.').'″'
            : (int) round($apertureMm).' mm';
    }

    /**
     * The two classes to compare at first: the smallest and the largest
     * known aperture, else the two there are.
     *
     * @param  array<string, Collection>  $classes
     * @return array{0: ?string, 1: ?string}
     */
    public static function defaultPair(array $classes): array
    {
        $known = array_values(array_diff(array_keys($classes), [self::UNKNOWN]));
        if (count($known) >= 2) {
            return [$known[0], $known[count($known) - 1]];
        }
        $keys = array_keys($classes);

        return [$keys[0] ?? null, $keys[1] ?? null];
    }
}
