<?php

namespace App\Services\Observations;

use App\Models\Location;
use App\Services\ContrastReserveCalculator;
use App\Services\EquipmentGain;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * How many of the observers who looked for a deep-sky object saw it, per
 * aperture class (DrawingComparison::CLASSES, the naked eye a class of its
 * own), next to what the contrast reserve model predicts for a telescope of
 * that class: the object page's "seen by X% of observers with 4″, 6″, 8″…".
 *
 * The observations are those ApertureRecords::credible() trusts: a known
 * aperture, no open finding of the suspect-observation check, and a seen one
 * only when the object's magnitude is within reach of the aperture. An
 * observer counts once per class, as seen when any of their observations in
 * it saw the object. The prediction is the contrast reserve with
 * REPRESENTATIVE_MM of the class at the generic magnifications, at the
 * zenith, under the median SQM of the observations (their own, else their
 * location's), DEFAULT_SQM when fewer than MIN_SKY_VALUES have one.
 */
class SeenByAperture
{
    /** The class of the naked eye, before DrawingComparison::CLASSES. */
    public const NAKED_EYE = 'naked-eye';

    /** Observers a class needs before its percentage is shown. */
    public const MIN_OBSERVERS = 3;

    /** The aperture (mm) the prediction of a class is calculated for. */
    public const REPRESENTATIVE_MM = [
        'small' => 100,
        '5-6' => 150,
        '8-inch' => 203,
        '10-12' => 280,
        '14-16' => 380,
        '18-20' => 480,
        'large' => 600,
    ];

    /** The sky of the prediction when too few observations have one. */
    public const DEFAULT_SQM = 21.0;

    public const MIN_SKY_VALUES = 3;

    /** Bump when the shape of the cached result changes. */
    private const VERSION = 1;

    /**
     * The classes with observers, from the naked eye to large, and the sky
     * of the predictions; null when no class has MIN_OBSERVERS observers.
     *
     * @return array{classes: list<array{class: string, observers: int, seen: int, percentage: ?float, contrast_reserve: ?float, category: ?string}>, sqm: float, sqm_from_observations: bool}|null
     */
    public function forObject(string $objectName): ?array
    {
        return Cache::flexible('seen_by_aperture:v'.self::VERSION.':'.$objectName, ApertureRecords::CACHE_SECONDS,
            fn () => $this->compute($objectName));
    }

    /** The class of an aperture: NAKED_EYE or a key of DrawingComparison::CLASSES. */
    public static function classFor(mixed $apertureMm, bool $nakedEye): string
    {
        return $nakedEye ? self::NAKED_EYE : DrawingComparison::classFor($apertureMm);
    }

    public static function label(string $class, bool $inches): string
    {
        return $class === self::NAKED_EYE ? __('Naked eye') : DrawingComparison::label($class, $inches);
    }

    private function compute(string $objectName): ?array
    {
        $rows = ApertureRecords::credible(DB::table('observations'))
            ->where('observations.target_key', $objectName)
            ->select('observations.observer_username')
            ->selectRaw(ApertureRecords::apertureSql().' AS aperture_mm')
            ->selectRaw(ApertureRecords::nakedEyeSql().' AS naked_eye')
            ->selectRaw('CASE WHEN '.ApertureRecords::notSeenSql().' THEN 0 ELSE 1 END AS seen')
            ->selectRaw(ApertureRecords::skySql('sqm').' AS sky_sqm')
            ->get();

        // observer => seen, per class
        $observers = [];
        $skies = [];
        foreach ($rows as $row) {
            $class = self::classFor($row->aperture_mm, (bool) $row->naked_eye);
            $observer = mb_strtolower((string) $row->observer_username);
            $observers[$class][$observer] = ($observers[$class][$observer] ?? false) || (bool) $row->seen;
            if (is_numeric($row->sky_sqm)) {
                $skies[] = (float) $row->sky_sqm;
            }
        }
        $shown = array_filter($observers, fn (array $inClass) => count($inClass) >= self::MIN_OBSERVERS);
        if (! $shown) {
            return null;
        }

        $fromObservations = count($skies) >= self::MIN_SKY_VALUES;
        $sqm = $fromObservations ? round(self::median($skies), 2) : self::DEFAULT_SQM;
        $object = DB::table('objects')->where('name', $objectName)->first(['type', 'mag', 'diam1', 'diam2']);

        $classes = [];
        foreach ([self::NAKED_EYE, ...array_keys(DrawingComparison::CLASSES)] as $class) {
            if (! isset($observers[$class])) {
                continue;
            }
            $total = count($observers[$class]);
            $seen = count(array_filter($observers[$class]));
            $prediction = $object ? $this->predict($object, $class, $sqm) : null;
            $classes[] = [
                'class' => $class,
                'observers' => $total,
                'seen' => $seen,
                'percentage' => $total >= self::MIN_OBSERVERS ? round(100 * $seen / $total, 1) : null,
                'contrast_reserve' => $prediction,
                'category' => ContrastReserveCalculator::category($prediction),
            ];
        }

        return ['classes' => $classes, 'sqm' => (float) $sqm, 'sqm_from_observations' => $fromObservations];
    }

    /** The contrast reserve of the object with REPRESENTATIVE_MM of a class; null without one. */
    private function predict(object $object, string $class, float $sqm): ?float
    {
        if (! isset(self::REPRESENTATIVE_MM[$class]) || ! ContrastReserveCalculator::appliesTo($object->type)) {
            return null;
        }
        $instrument = EquipmentGain::plannedInstrument(EquipmentGain::plannedSpec(['aperture' => self::REPRESENTATIVE_MM[$class]]));
        $target = ContrastReserveCalculator::target($object->diam1, $object->diam2, $object->mag);

        try {
            $result = ContrastReserveCalculator::calculate($target, $instrument, new Location, null, sqm: $sqm);
        } catch (\Throwable $_) {
            return null;
        }

        return is_numeric($result['contrast_reserve']) ? round((float) $result['contrast_reserve'], 2) : null;
    }

    /** @param  non-empty-list<float>  $values */
    private static function median(array $values): float
    {
        sort($values);
        $n = count($values);
        $middle = intdiv($n, 2);

        return $n % 2 ? $values[$middle] : ($values[$middle - 1] + $values[$middle]) / 2;
    }
}
