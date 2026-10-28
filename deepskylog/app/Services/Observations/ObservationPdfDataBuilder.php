<?php

namespace App\Services\Observations;

use App\Models\CometObject;
use App\Models\Constellation;
use App\Models\Eyepiece;
use App\Models\Filter;
use App\Models\Instrument;
use App\Models\Lens;
use App\Models\Location;
use App\Models\Observation;
use App\Models\Target;
use App\Models\User;
use Carbon\Carbon;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;
use deepskylog\AstronomyLibrary\Targets\Moon as AstroMoon;
use deepskylog\AstronomyLibrary\Time;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ObservationPdfDataBuilder
{
    private const PDF_IMAGE_MAX_WIDTH = 1400;

    private const PDF_IMAGE_MAX_HEIGHT = 1400;

    private const PDF_IMAGE_JPEG_QUALITY = 72;

    public function build(Observation $selectedUnified): ?array
    {
        $obsType = $selectedUnified->target_type === Observation::TYPE_COMET ? 'comet' : 'deepsky';
        $selected = $selectedUnified;

        try {
            $details = DB::table('deepsky_observation_details')
                ->where('observation_id', $selectedUnified->id)
                ->first();

            if ($details) {
                $selected->visibility = $details->visibility ?? null;
                $selected->smallDiameter = $details->small_diameter ?? null;
                $selected->largeDiameter = $details->large_diameter ?? null;
                $selected->small_diameter = $details->small_diameter ?? null;
                $selected->large_diameter = $details->large_diameter ?? null;
                $selected->clusterType = $details->cluster_type ?? null;
                $selected->cluster_type = $details->cluster_type ?? null;
                $selected->stellar = isset($details->stellar) ? (int) $details->stellar : 0;
                $selected->extended = isset($details->extended) ? (int) $details->extended : 0;
                $selected->resolved = isset($details->resolved) ? (int) $details->resolved : 0;
                $selected->mottled = isset($details->mottled) ? (int) $details->mottled : 0;
                $selected->unusualShape = $details->unusual_shape ?? null;
                $selected->unusual_shape = $details->unusual_shape ?? null;
                $selected->partlyUnresolved = $details->partly_unresolved ?? null;
                $selected->partly_unresolved = $details->partly_unresolved ?? null;
                $selected->colorContrasts = $details->color_contrasts ?? null;
                $selected->color_contrasts = $details->color_contrasts ?? null;
                $selected->equalBrightness = $details->equal_brightness ?? null;
                $selected->equal_brightness = $details->equal_brightness ?? null;
                $selected->niceField = $details->nice_field ?? null;
                $selected->nice_field = $details->nice_field ?? null;
                $selected->component1 = $details->component1 ?? null;
                $selected->component2 = $details->component2 ?? null;
            }
        } catch (\Throwable $_) {
        }

        if ($obsType === 'comet') {
            $selected->resolved_comet_name = (string) ($selectedUnified->target_name ?? '');
            $preloadedData = $this->preloadCometData(collect([$selected]));
        } else {
            $preloadedData = $this->preloadDeepskyData(collect([$selected]));
        }

        $moonContext = $this->buildMoonContext(
            $selected,
            $preloadedData['preloaded_locations']->get($selected->locationid) ?? null,
        );

        $lens = null;
        if (!empty($selected->lensid) && $selected->lensid > 0) {
            $lens = ($preloadedData['preloaded_lenses'] ?? collect())->get($selected->lensid) ?? Lens::find($selected->lensid);
        }

        return array_merge([
            'observationType' => $obsType,
            'observation' => $selected,
            'lens' => $lens,
            'unified_observation_id' => $selectedUnified->id,
            'drawing_filename' => $selectedUnified->drawing_filename,
            'drawingDataUri' => $this->resolveDrawingDataUri($selectedUnified, $obsType),
            'moonContext' => $moonContext,
        ], $preloadedData);
    }

    private function preloadDeepskyData(Collection $observations): array
    {
        if ($observations->isEmpty()) {
            return [
                'preloaded_users' => collect(),
                'preloaded_objects' => collect(),
                'preloaded_locations' => collect(),
                'preloaded_instruments' => collect(),
                'preloaded_eyepieces' => collect(),
                'preloaded_filters' => collect(),
                'preloaded_lenses' => collect(),
                'preloaded_constellations' => collect(),
            ];
        }

        $usernames = $observations->pluck('observerid')->unique()->filter();
        $objectNames = $observations->pluck('objectname')->unique()->filter();
        $locationIds = $observations->pluck('locationid')->unique()->filter();
        $instrumentIds = $observations->pluck('instrumentid')->unique()->filter();
        $eyepieceIds = $observations->pluck('eyepieceid')->unique()->filter()->reject(fn($id) => $id <= 0);
        $filterIds = $observations->pluck('filterid')->unique()->filter()->reject(fn($id) => $id <= 0);
        $lensIds = $observations->pluck('lensid')->unique()->filter()->reject(fn($id) => $id <= 0);

        $users = User::whereIn('username', $usernames)->get()->keyBy('username');
        $objects = Target::whereIn('name', $objectNames)->get()->keyBy('name');

        try {
            $objects = $objects->merge($objects->mapWithKeys(fn($object) => [$object->slug => $object]));
        } catch (\Throwable $_) {
        }

        $locations = Location::whereIn('id', $locationIds)->get()->keyBy('id');
        $instruments = Instrument::whereIn('id', $instrumentIds)->get()->keyBy('id');
        $eyepieces = $eyepieceIds->isNotEmpty() ? Eyepiece::whereIn('id', $eyepieceIds)->get()->keyBy('id') : collect();
        $filters = $filterIds->isNotEmpty() ? Filter::whereIn('id', $filterIds)->get()->keyBy('id') : collect();
        $lenses = $lensIds->isNotEmpty() ? Lens::whereIn('id', $lensIds)->get()->keyBy('id') : collect();

        $constellationIds = $objects->pluck('con')->unique()->filter();
        $constellations = Constellation::whereIn('id', $constellationIds)->get()->keyBy('id');

        return [
            'preloaded_users' => $users,
            'preloaded_objects' => $objects,
            'preloaded_locations' => $locations,
            'preloaded_instruments' => $instruments,
            'preloaded_eyepieces' => $eyepieces,
            'preloaded_filters' => $filters,
            'preloaded_lenses' => $lenses,
            'preloaded_constellations' => $constellations,
        ];
    }

    private function preloadCometData(Collection $observations): array
    {
        if ($observations->isEmpty()) {
            return [
                'preloaded_users' => collect(),
                'preloaded_comets' => collect(),
                'preloaded_locations' => collect(),
                'preloaded_instruments' => collect(),
                'preloaded_lenses' => collect(),
            ];
        }

        $usernames = $observations->pluck('observerid')->unique()->filter();
        $objectIds = $observations->pluck('objectid')->unique()->filter();
        $locationIds = $observations->pluck('locationid')->unique()->filter();
        $instrumentIds = $observations->pluck('instrumentid')->unique()->filter();

        $users = User::whereIn('username', $usernames)->get()->keyBy('username');
        $locations = Location::whereIn('id', $locationIds)->get()->keyBy('id');
        $instruments = Instrument::whereIn('id', $instrumentIds)->get()->keyBy('id');

        $comets = collect();
        try {
            $comets = CometObject::whereIn('id', $objectIds)->get()->keyBy('id');
        } catch (\Throwable $_) {
            try {
                $legacyComets = DB::table('cometobjects')
                    ->whereIn('id', $objectIds)
                    ->get();
                $comets = collect($legacyComets)->keyBy('id');
            } catch (\Throwable $_) {
            }
        }

        try {
            $observationIds = $observations->pluck('id')->unique()->filter()->values()->all();
            if (!empty($observationIds)) {
                $detailRows = DB::table('comet_observation_details')
                    ->whereIn('observation_id', $observationIds)
                    ->get()
                    ->keyBy('observation_id');

                foreach ($observations as $observation) {
                    $detail = $detailRows->get($observation->id);
                    if (!$detail) {
                        continue;
                    }

                    $observation->mag = isset($detail->magnitude) ? (float) $detail->magnitude : null;
                    $observation->maguncertain = !empty($detail->magnitude_uncertain) ? 1 : 0;
                    $observation->lessmagnitude = !empty($detail->less_magnitude) ? 1 : 0;
                    $observation->dc = $detail->dc ?? null;
                    $observation->coma = isset($detail->coma) ? (int) $detail->coma : null;
                    $observation->tail = isset($detail->tail) ? (int) $detail->tail : null;
                    $observation->pa = isset($detail->position_angle) ? (int) $detail->position_angle : null;
                    $observation->methode = $detail->method ?? null;
                    $observation->chart = $detail->chart ?? null;
                }
            }
        } catch (\Throwable $_) {
        }

        return [
            'preloaded_users' => $users,
            'preloaded_comets' => $comets,
            'preloaded_locations' => $locations,
            'preloaded_instruments' => $instruments,
            'preloaded_lenses' => collect(),
        ];
    }

    private function buildMoonContext(object $observation, ?Location $location): ?array
    {
        $observedAt = $this->resolveObservationDateTime($observation, $location);
        if (!$observedAt) {
            return null;
        }

        $timezone = $location?->timezone ?? config('app.timezone');
        $moon = new AstroMoon();

        try {
            $illuminatedFraction = $moon->illuminatedFraction($observedAt->copy());
        } catch (\Throwable $_) {
            $illuminatedFraction = null;
        }

        try {
            $phaseRatio = $moon->getPhaseRatio($observedAt->copy());
        } catch (\Throwable $_) {
            $phaseRatio = null;
        }

        $frameIndex = $this->determineMoonFrameIndex($phaseRatio, $illuminatedFraction);

        $context = [
            'observed_at' => $observedAt,
            'timezone' => $timezone,
            'has_specific_time' => $this->hasObservationTime($observation),
            'phase_ratio' => $phaseRatio,
            'illuminated_fraction' => $illuminatedFraction,
            'illumination_percent' => is_null($illuminatedFraction)
                ? null
                : (int) round(max(0.0, min(1.0, (float) $illuminatedFraction)) * 100),
            'frame_index' => $frameIndex,
            'frame_path' => $frameIndex !== null ? asset("images/moon/m{$frameIndex}.gif") : null,
            'phase_name' => $this->determineMoonPhaseName($phaseRatio, $illuminatedFraction),
            'horizon_status' => 'unknown',
            'altitude_degrees' => null,
        ];

        if (!$context['has_specific_time'] || !$location || !is_numeric($location->latitude) || !is_numeric($location->longitude)) {
            return $context;
        }

        try {
            $coords = new GeographicalCoordinates((float) $location->longitude, (float) $location->latitude);
            $siderealTime = Time::apparentSiderialTime($observedAt->copy(), $coords);
            $moon->calculateEquatorialCoordinates($observedAt->copy(), $coords, (float) ($location->elevation ?? 0.0));
            $horizontal = $moon
                ->getEquatorialCoordinatesToday()
                ->convertToHorizontal($coords, $siderealTime);

            $altitude = (float) $horizontal->getAltitude()->getCoordinate();

            $context['altitude_degrees'] = round($altitude, 1);
            $context['horizon_status'] = $altitude >= 0 ? 'above' : 'below';
        } catch (\Throwable $_) {
        }

        return $context;
    }

    private function resolveObservationDateTime(object $observation, ?Location $location): ?Carbon
    {
        $dateValue = trim((string) ($observation->date ?? ''));
        if ($dateValue === '') {
            return null;
        }

        $formattedDate = strlen($dateValue) === 8
            ? substr($dateValue, 0, 4) . '-' . substr($dateValue, 4, 2) . '-' . substr($dateValue, 6, 2)
            : $dateValue;

        $timezone = $location?->timezone ?? config('app.timezone');

        try {
            if ($this->hasObservationTime($observation)) {
                $timeValue = (int) ($observation->time ?? 0);
                $hours = intdiv($timeValue, 100);
                $minutes = $timeValue % 100;

                return Carbon::createFromFormat(
                    'Y-m-d H:i',
                    sprintf('%s %02d:%02d', $formattedDate, $hours, $minutes),
                    // Observation times are UT.
                    'UTC',
                );
            }

            return Carbon::createFromFormat('Y-m-d H:i', $formattedDate . ' 12:00', $timezone);
        } catch (\Throwable $_) {
            return null;
        }
    }

    private function hasObservationTime(object $observation): bool
    {
        if (!isset($observation->time) || $observation->time === null || $observation->time === '') {
            return false;
        }

        $timeValue = (int) $observation->time;

        return $timeValue >= 0 && $timeValue <= 2359 && ($timeValue % 100) < 60;
    }

    private function determineMoonFrameIndex(mixed $phaseRatio, mixed $illuminatedFraction): ?int
    {
        if (is_null($phaseRatio) && is_null($illuminatedFraction)) {
            return null;
        }

        $phase = is_null($phaseRatio) ? 0.0 : max(0.0, min(1.0, (float) $phaseRatio));
        $illum = is_null($illuminatedFraction) ? 0.0 : max(0.0, min(1.0, (float) $illuminatedFraction));
        $indexHalf = max(0, min(20, (int) round($illum * 20)));

        return $phase <= 0.5 ? $indexHalf : 40 - $indexHalf;
    }

    private function determineMoonPhaseName(mixed $phaseRatio, mixed $illuminatedFraction): string
    {
        $phase = is_null($phaseRatio) ? null : max(0.0, min(1.0, (float) $phaseRatio));
        $illum = is_null($illuminatedFraction) ? null : max(0.0, min(1.0, (float) $illuminatedFraction));

        if (!is_null($illum)) {
            if ($illum <= 0.02) {
                return __('New moon');
            }

            if ($illum >= 0.98) {
                return __('Full moon');
            }
        }

        if (is_null($phase)) {
            return __('Moon phase');
        }

        if ($phase < 0.25) {
            return __('Waxing moon');
        }

        if ($phase < 0.5) {
            return __('Waxing gibbous');
        }

        if ($phase < 0.75) {
            return __('Waning moon');
        }

        return __('Waning crescent');
    }

    private function resolveDrawingDataUri(Observation $observation, string $observationType): ?string
    {
        if (!$observation->has_drawing && empty($observation->drawing_filename)) {
            return null;
        }

        $candidatePaths = [];
        $filename = trim((string) ($observation->drawing_filename ?? ''));

        if ($filename !== '') {
            try {
                $publicDisk = Storage::disk('public');
                if ($publicDisk->exists($filename)) {
                    $candidatePaths[] = $publicDisk->path($filename);
                }
            } catch (\Throwable $_) {
            }

            $candidatePaths[] = public_path('images/' . ltrim($filename, '/'));
        }

        if ($observation->has_drawing) {
            $subfolder = $observationType === 'comet' ? 'cometdrawings' : 'drawings';
            $candidatePaths[] = public_path('images/' . $subfolder . '/' . $observation->id . '.jpg');

            if (!empty($observation->legacy_id)) {
                $candidatePaths[] = public_path('images/' . $subfolder . '/' . $observation->legacy_id . '.jpg');
            }
        }

        foreach (array_unique(array_filter($candidatePaths)) as $path) {
            if (!is_string($path) || !is_file($path)) {
                continue;
            }

            $optimized = $this->buildOptimizedImageDataUri($path);
            if ($optimized !== null) {
                return $optimized;
            }
        }

        return null;
    }

    private function buildOptimizedImageDataUri(string $path): ?string
    {
        $contents = @file_get_contents($path);
        if ($contents === false) {
            return null;
        }

        $mimeType = @mime_content_type($path) ?: 'image/jpeg';

        if (!function_exists('imagecreatefromstring') || !function_exists('imagescale') || !function_exists('imagejpeg')) {
            return 'data:' . $mimeType . ';base64,' . base64_encode($contents);
        }

        try {
            $image = @imagecreatefromstring($contents);
            if ($image === false) {
                return 'data:' . $mimeType . ';base64,' . base64_encode($contents);
            }

            $width = imagesx($image);
            $height = imagesy($image);
            $scale = min(
                1,
                self::PDF_IMAGE_MAX_WIDTH / max(1, $width),
                self::PDF_IMAGE_MAX_HEIGHT / max(1, $height),
            );

            $targetWidth = max(1, (int) round($width * $scale));
            $targetHeight = max(1, (int) round($height * $scale));

            $resized = $image;
            if ($targetWidth !== $width || $targetHeight !== $height) {
                $scaled = @imagescale($image, $targetWidth, $targetHeight, IMG_BICUBIC_FIXED);
                if ($scaled !== false) {
                    $resized = $scaled;
                }
            }

            if ($resized !== $image && function_exists('imagedestroy')) {
                imagedestroy($image);
            }

            if ($mimeType === 'image/png' && function_exists('imagepng')) {
                ob_start();
                imagepng($resized, null, 6);
                $binary = ob_get_clean();
                if (function_exists('imagedestroy')) {
                    imagedestroy($resized);
                }

                if ($binary !== false && $binary !== null) {
                    return 'data:image/png;base64,' . base64_encode($binary);
                }
            }

            ob_start();
            imagejpeg($resized, null, self::PDF_IMAGE_JPEG_QUALITY);
            $binary = ob_get_clean();
            if (function_exists('imagedestroy')) {
                imagedestroy($resized);
            }

            if ($binary === false || $binary === null) {
                return 'data:' . $mimeType . ';base64,' . base64_encode($contents);
            }

            return 'data:image/jpeg;base64,' . base64_encode($binary);
        } catch (\Throwable $_) {
            return 'data:' . $mimeType . ';base64,' . base64_encode($contents);
        }
    }
}