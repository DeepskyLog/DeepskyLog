<?php

namespace App\Services\Observations;

use App\Http\Controllers\ObservationSearchExportController;
use App\Models\Observation;
use App\Models\User;
use App\Services\ObservationSearchQuery;
use App\Support\MemoryLimit;
use App\Support\SpreadsheetCell;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

/**
 * A zip of observations with their drawings (GenerateObservationsZip):
 * observations.xml (OAL, each drawing named in its observation's image
 * element), the drawings in drawings/, and drawings.csv listing them with
 * their date, object, observer and page.
 *
 * The drawings are the observer's originals, stored without compression
 * (they are JPEGs already): the drawings of the observer with the most of
 * them take 2.1 GB.
 */
class ObservationZipBuilder
{
    /** The most observations one zip holds (the most of one observer: 25,000). */
    public const MAX_OBSERVATIONS = 30000;

    /** The most drawings one zip holds (the most of one observer: 2,650). */
    public const MAX_DRAWINGS = 5000;

    /** Space left free on the disk next to the zip. */
    private const SPARE_BYTES = 1024 ** 3;

    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    /**
     * The observations of a zip: those of an observation search (`q` and
     * `filters`, as the exports of ObservationSearchExportController take
     * them), with `observer_id` only those of that observer (all of them
     * without a search), with `drawings_only` only those with a drawing.
     *
     * @param  array{observer_id?: int, q?: string, filters?: array<string, mixed>, drawings_only?: bool}  $source
     */
    public static function query(array $source): EloquentBuilder
    {
        $query = app(ObservationSearchQuery::class)->observations(
            trim((string) ($source['q'] ?? '')),
            (array) ($source['filters'] ?? [])
        );

        if (isset($source['observer_id'])) {
            $query->where('observations.observer_user_id', (int) $source['observer_id']);
        }

        if (! empty($source['drawings_only'])) {
            $query->where('observations.has_drawing', true);
        }

        return $query;
    }

    /**
     * Why a zip of these observations cannot be made, or null when it can.
     *
     * @param  array{observations: int, drawings: int}  $counts
     */
    public static function refusal(array $counts): ?string
    {
        if ($counts['observations'] === 0) {
            return __('There are no observations to put in a zip file.');
        }

        if ($counts['observations'] > self::MAX_OBSERVATIONS) {
            return __('These are :count observations, but a zip file holds at most :max. Please narrow down your search.', [
                'count' => number_format($counts['observations']),
                'max' => number_format(self::MAX_OBSERVATIONS),
            ]);
        }

        if ($counts['drawings'] > self::MAX_DRAWINGS) {
            return __('These observations have :count drawings, but a zip file holds at most :max. Please narrow down your search.', [
                'count' => number_format($counts['drawings']),
                'max' => number_format(self::MAX_DRAWINGS),
            ]);
        }

        return null;
    }

    /**
     * @return array{observations: int, drawings: int}
     */
    public static function counts(EloquentBuilder $query): array
    {
        return [
            'observations' => (clone $query)->count(),
            'drawings' => (clone $query)->where('observations.has_drawing', true)->count(),
        ];
    }

    /**
     * The file of an observation's drawing, or null when it has none or the
     * file is missing. A drawing uploaded on this site is on the public disk
     * under drawing_filename; one of the old site is
     * public/images/{drawings,cometdrawings}/{legacy_id}.jpg, or a
     * drawing_filename relative to public/images.
     */
    public static function drawingPath(Observation $observation): ?string
    {
        if (! $observation->has_drawing && empty($observation->drawing_filename)) {
            return null;
        }

        $candidates = [];
        $filename = ltrim(trim((string) ($observation->drawing_filename ?? '')), '/');

        if ($filename !== '' && ! str_contains($filename, '..')) {
            try {
                $publicDisk = Storage::disk('public');
                if ($publicDisk->exists($filename)) {
                    $candidates[] = $publicDisk->path($filename);
                }
            } catch (\Throwable $_) {
            }

            $candidates[] = public_path('images/' . $filename);
        }

        if ($observation->has_drawing && ! empty($observation->legacy_id)) {
            $folder = $observation->legacy_source === 'comet' || $observation->target_type === 'comet' ? 'cometdrawings' : 'drawings';
            $candidates[] = public_path('images/' . $folder . '/' . (int) $observation->legacy_id . '.jpg');
        }

        foreach ($candidates as $path) {
            if (is_file($path) && is_readable($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * The zip's file name for the observations of one observer, or of a search.
     */
    public static function filenameFor(array $source): string
    {
        $name = 'observations';
        if (isset($source['observer_id'])) {
            $name .= '-' . (User::find((int) $source['observer_id'])?->slug ?: $source['observer_id']);
        }

        $q = Str::slug(trim((string) ($source['q'] ?? '')));
        if ($q !== '') {
            $name .= '-' . $q;
        }
        if (! empty($source['filters'])) {
            $name .= '-' . substr(md5(json_encode($source['filters'])), 0, 8);
        }

        return $name . (! empty($source['drawings_only']) ? '-drawings' : '') . '.zip';
    }

    /**
     * Write the zip of the source's observations to $path.
     *
     * @param  callable(array<string, mixed>): void  $progress
     * @return array{observations: int, drawings: int, missing: int}
     */
    public function write(string $path, array $source, callable $progress): array
    {
        // 25,000 observations take about 250 MB as OAL.
        MemoryLimit::atLeast('1024M');

        $observations = self::query($source)->orderBy('observations.observed_on')->orderBy('observations.id')->get();
        if (($refusal = self::refusal(['observations' => $observations->count(), 'drawings' => $observations->where('has_drawing', true)->count()])) !== null) {
            throw new \RuntimeException($refusal);
        }

        $progress(['phase' => 'drawings', 'total' => $observations->count()]);

        [$drawings, $missing] = $this->collectDrawings($observations);
        $this->assertDiskSpace($path, $drawings);

        $progress(['phase' => 'oal']);
        $oal = app(ObservationSearchExportController::class)->oalWithImages(
            $observations,
            $drawings->mapWithKeys(fn (array $drawing) => [$drawing['observation']->id => $drawing['entry']])->all()
        );

        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Could not create the zip file.');
        }

        $zip->addFromString('observations.xml', $oal);
        unset($oal);
        $zip->addFromString('drawings.csv', $this->csv($drawings, $missing));

        foreach ($drawings as $drawing) {
            $zip->addFile($drawing['file'], $drawing['entry']);
            $zip->setCompressionName($drawing['entry'], ZipArchive::CM_STORE);
        }

        // The files are only read when the zip is closed, which takes most of
        // the time with many drawings.
        $zip->registerProgressCallback(0.02, fn (float $done) => $progress(['phase' => 'writing', 'percent' => (int) round($done * 100)]));

        if (! $zip->close()) {
            throw new \RuntimeException('Could not write the zip file.');
        }

        return ['observations' => $observations->count(), 'drawings' => $drawings->count(), 'missing' => $missing->count()];
    }

    /**
     * The drawings found on disk, each with its name in the zip, and the
     * observations whose drawing file is missing.
     *
     * @param  Collection<int, Observation>  $observations
     * @return array{0: Collection<int, array{observation: Observation, file: string, entry: string}>, 1: Collection<int, Observation>}
     */
    private function collectDrawings(Collection $observations): array
    {
        $drawings = collect();
        $missing = collect();

        foreach ($observations->filter(fn (Observation $observation) => $observation->has_drawing) as $observation) {
            $file = self::drawingPath($observation);
            if ($file === null) {
                $missing->push($observation);

                continue;
            }

            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            $base = 'drawings/' . implode('_', array_filter([
                $observation->observed_on?->format('Y-m-d'),
                Str::slug((string) ($observation->target_name ?? $observation->target_key ?? '')),
                $observation->id,
            ]));
            $entry = $base . '.' . (in_array($extension, self::IMAGE_EXTENSIONS, true) ? $extension : 'jpg');

            $drawings->push(['observation' => $observation, 'file' => $file, 'entry' => $entry]);
        }

        return [$drawings, $missing];
    }

    /**
     * Fail before writing a zip the disk has no room for, rather than fill it.
     *
     * @param  Collection<int, array{file: string}>  $drawings
     */
    private function assertDiskSpace(string $path, Collection $drawings): void
    {
        $needed = $drawings->sum(fn (array $drawing) => (int) @filesize($drawing['file']));
        $free = @disk_free_space(dirname($path));

        if ($free !== false && $free < $needed + self::SPARE_BYTES) {
            throw new \RuntimeException(sprintf('Not enough disk space for the zip: %d bytes needed, %d free.', $needed, $free));
        }
    }

    /**
     * @param  Collection<int, array{observation: Observation, entry: string}>  $drawings
     * @param  Collection<int, Observation>  $missing
     */
    private function csv(Collection $drawings, Collection $missing): string
    {
        ObservationCardData::instance()->preloadDetailUrls($drawings->pluck('observation')->merge($missing));

        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, [__('File'), __('Date'), __('Object'), __('Observer'), __('Observation')], ',', '"', '');

        $row = fn (?string $file, Observation $observation) => SpreadsheetCell::escapeRow([
            $file ?? __('Drawing missing'),
            $observation->observed_on?->format('Y-m-d') ?? '',
            html_entity_decode((string) ($observation->target_name ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            html_entity_decode((string) (ObservationCardData::instance()->user($observation->observer_username)?->name ?? $observation->observer_username ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            $observation->detailUrl() ?? '',
        ]);

        foreach ($drawings as $drawing) {
            fputcsv($handle, $row($drawing['entry'], $drawing['observation']), ',', '"', '');
        }
        foreach ($missing as $observation) {
            fputcsv($handle, $row(null, $observation), ',', '"', '');
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }
}
