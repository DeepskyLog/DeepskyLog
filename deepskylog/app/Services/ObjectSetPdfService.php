<?php

namespace App\Services;

use App\Models\DeepskyObject;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Renders an object set: for every object a data page, a page with two
 * photos from the Digitized Sky Survey, one atlas chart per requested field
 * of view, and an index of the objects on those charts.
 *
 * The charts are atlas pages from StarChartService centred on the object, so
 * they look exactly like the pages of the DeepskyLog atlases.
 */
class ObjectSetPdfService
{
    /** Largest photo STScI hands out in one request, in arcminutes. */
    public const MAX_PHOTO_ARCMIN = 120;

    private const PAGE_WIDTH_MM = 297;

    private const PAGE_HEIGHT_MM = 210;

    /** Photos are scaled down to this many pixels square before going in. */
    private const PHOTO_PIXELS = 1000;

    public function __construct(
        private readonly StarChartService $starChartService,
        private readonly ObjectSetDataBuilder $dataBuilder,
    ) {
    }

    /**
     * Parse a field such as "120 60 15" into its numbers.
     *
     * Commas, semicolons and runs of spaces all separate values; anything
     * that is not a positive number is dropped.
     *
     * @return array<int, float>
     */
    public static function parseNumbers(?string $value): array
    {
        $parts = preg_split('/[\s,;]+/', trim((string) $value), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return array_values(array_map(
            'floatval',
            array_filter($parts, fn (string $part) => is_numeric($part) && (float) $part > 0.0)
        ));
    }

    /**
     * @param  array{objects: array<int, array{name: string, fovs: array<int, float>, dsos: array<int, float>, stars: array<int, float>, photos: array<int, float>}>, data_page?: bool, ephemerides?: bool, year_ephemerides?: bool, index_page?: bool, date?: string, list_id?: ?int}  $parameters
     * @param  callable(int $done, int $total, string $name): void|null  $onProgress
     * @return string The PDF document
     */
    public function render(array $parameters, User $user, ?callable $onProgress = null): string
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }

        $date = Carbon::parse($parameters['date'] ?? now()->toDateString());
        $listId = isset($parameters['list_id']) ? (int) $parameters['list_id'] : null;
        $dataPage = (bool) ($parameters['data_page'] ?? true);
        $withEphemerides = (bool) ($parameters['ephemerides'] ?? true);
        $withYearEphemerides = (bool) ($parameters['year_ephemerides'] ?? true);
        $indexPage = (bool) ($parameters['index_page'] ?? true);

        $specs = array_values($parameters['objects'] ?? []);
        $total = count($specs);
        $pages = [];
        $tempFiles = [];

        try {
            foreach ($specs as $i => $spec) {
                $object = DeepskyObject::find((string) ($spec['name'] ?? ''));

                if ($object !== null) {
                    if ($onProgress !== null) {
                        $onProgress($i, $total, (string) $object->name);
                    }

                    array_push($pages, ...$this->objectPages(
                        $object,
                        $spec,
                        $user,
                        $date,
                        $listId,
                        $dataPage,
                        $withEphemerides,
                        $withYearEphemerides,
                        $indexPage,
                        $tempFiles
                    ));
                }
            }

            if ($onProgress !== null) {
                $onProgress($total, $total, '');
            }

            if ($pages === []) {
                throw new \RuntimeException('None of the requested objects could be drawn.');
            }

            $html = view('pdf.object_sets', [
                'pages' => $pages,
                'pageWidthMm' => self::PAGE_WIDTH_MM,
                'pageHeightMm' => self::PAGE_HEIGHT_MM,
            ])->render();

            $options = new \Dompdf\Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', false);
            // Charts and photos are referenced as files in the temp directory,
            // which Dompdf only reads from inside its chroot.
            $options->set('chroot', array_unique([sys_get_temp_dir(), base_path(), storage_path()]));

            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper([0, 0, (self::PAGE_WIDTH_MM / 25.4) * 72.0, (self::PAGE_HEIGHT_MM / 25.4) * 72.0]);
            $dompdf->render();

            return (string) $dompdf->output();
        } finally {
            foreach ($tempFiles as $file) {
                @unlink($file);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $spec
     * @param  array<int, string>  $tempFiles
     * @return array<int, array<string, mixed>>
     */
    private function objectPages(
        DeepskyObject $object,
        array $spec,
        User $user,
        Carbon $date,
        ?int $listId,
        bool $dataPage,
        bool $withEphemerides,
        bool $withYearEphemerides,
        bool $indexPage,
        array &$tempFiles
    ): array {
        $pages = [];
        $data = $this->dataBuilder->objectData($object, $user, $listId);
        $observer = trim(($user->name ?? '') !== '' ? (string) $user->name : (string) ($user->username ?? ''));

        if ($dataPage) {
            $pages[] = [
                'kind' => 'data',
                'object' => $data,
                'observer' => $observer,
                'night' => $withEphemerides ? $this->dataBuilder->nightEphemerides($object, $user, $date) : null,
                'year' => $withYearEphemerides ? $this->dataBuilder->yearEphemerides($object, $user, $date) : null,
            ];
        }

        $photos = [];
        foreach (array_slice(array_values($spec['photos'] ?? []), 0, 2) as $size) {
            $size = (int) round(min(self::MAX_PHOTO_ARCMIN, max(1.0, (float) $size)));
            $photos[] = [
                'size' => $size,
                'path' => $this->dssPhoto($object, $size, $tempFiles),
            ];
        }

        if ($photos !== []) {
            $pages[] = [
                'kind' => 'photos',
                'object' => $data,
                'photos' => $photos,
                'with_summary' => ! $dataPage,
            ];
        }

        $fovs = array_values($spec['fovs'] ?? []);
        $dsos = array_values($spec['dsos'] ?? []);
        $stars = array_values($spec['stars'] ?? []);
        $raDeg = $object->raHours() * 15.0;
        $decDeg = $object->declDegrees();
        $index = [];

        foreach ($fovs as $k => $fov) {
            $fov = min(3600.0, max(1.0, (float) $fov));
            // A missing magnitude takes the one before it, so "10 12" for
            // three charts draws the last one to magnitude 12 as well.
            $dsoMag = $this->valueAt($dsos, $k, 15.0, 1.0, 20.0);
            $starMag = $this->valueAt($stars, $k, 15.0, 1.0, 18.0);

            $chart = $this->starChartService->build(
                $object,
                $raDeg,
                $decDeg,
                $fov / 2.0,
                null,
                $dsoMag,
                $starMag,
                true,
                true,
                false,
                null,
                true
            );

            $pages[] = [
                'kind' => 'chart',
                'title' => $object->name,
                'svg_path' => $this->writeTemp($chart['svg'], '.svg', $tempFiles),
            ];

            $index[] = [
                'fov' => $fov,
                'dso_mag' => $dsoMag,
                'star_mag' => $starMag,
                'objects' => collect($chart['objects'])
                    ->sort(fn (array $a, array $b) => strnatcasecmp((string) $a['name'], (string) $b['name']))
                    ->map(fn (array $row) => [
                        'name' => (string) $row['name'],
                        'type' => $this->dataBuilder->typeNameFor($row['type'] ?? null),
                        'mag' => ObjectSetDataBuilder::knownValue($row['mag'] ?? null),
                        'size' => ObjectSetDataBuilder::formatSize($row['diam1'] ?? null, $row['diam2'] ?? null),
                        'is_target' => (bool) ($row['is_target'] ?? false),
                    ])
                    ->values()
                    ->all(),
            ];

            unset($chart);
        }

        if ($indexPage && $index !== []) {
            $pages[] = [
                'kind' => 'index',
                'object' => $data,
                'charts' => $index,
            ];
        }

        return $pages;
    }

    /**
     * @param  array<int, float>  $values
     */
    private function valueAt(array $values, int $k, float $default, float $min, float $max): float
    {
        if ($values === []) {
            return $default;
        }

        $value = $values[$k] ?? $values[count($values) - 1];

        return min($max, max($min, (float) $value));
    }

    /**
     * A DSS photo of the object, negated for print like the old DeepskyLog
     * object sets, or null when the survey could not be reached.
     *
     * @param  array<int, string>  $tempFiles
     */
    private function dssPhoto(DeepskyObject $object, int $sizeArcMin, array &$tempFiles): ?string
    {
        try {
            $response = Http::timeout(120)
                ->retry(2, 2000, throw: false)
                ->get('https://archive.stsci.edu/cgi-bin/dss_search', self::dssQuery($object->raHours(), $object->declDegrees(), $sizeArcMin));

            if (! $response->successful() || ! str_starts_with(strtolower((string) $response->header('Content-Type')), 'image/')) {
                Log::warning('ObjectSetPdfService: DSS image unavailable', ['object' => $object->name, 'status' => $response->status()]);

                return null;
            }

            $image = @imagecreatefromstring($response->body());
            if ($image === false) {
                return null;
            }

            $width = imagesx($image);
            $height = imagesy($image);
            $scaled = imagecreatetruecolor(self::PHOTO_PIXELS, self::PHOTO_PIXELS);
            imagecopyresampled($scaled, $image, 0, 0, 0, 0, self::PHOTO_PIXELS, self::PHOTO_PIXELS, $width, $height);
            imagedestroy($image);
            imagefilter($scaled, IMG_FILTER_GRAYSCALE);
            imagefilter($scaled, IMG_FILTER_NEGATE);

            $tmp = tempnam(sys_get_temp_dir(), 'dsl_dss_');
            if ($tmp === false) {
                imagedestroy($scaled);

                return null;
            }
            $path = $tmp . '.jpg';
            rename($tmp, $path);
            imagejpeg($scaled, $path, 88);
            imagedestroy($scaled);
            $tempFiles[] = $path;

            return $path;
        } catch (\Throwable $ex) {
            Log::warning('ObjectSetPdfService: DSS download failed', ['object' => $object->name, 'error' => $ex->getMessage()]);

            return null;
        }
    }

    /**
     * Query string for the STScI DSS cutout service.
     *
     * @return array<string, string>
     */
    public static function dssQuery(float $raHours, float $decDeg, int $sizeArcMin): array
    {
        $ra = fmod($raHours, 24.0);
        if ($ra < 0) {
            $ra += 24.0;
        }
        $raSeconds = (int) round($ra * 3600.0) % 86400;
        $raText = sprintf('%02d %02d %02d', intdiv($raSeconds, 3600), intdiv($raSeconds % 3600, 60), $raSeconds % 60);

        $decSeconds = (int) round(abs($decDeg) * 3600.0);
        $decText = sprintf('%s%02d %02d %02d', $decDeg < 0 ? '-' : '+', intdiv($decSeconds, 3600), intdiv($decSeconds % 3600, 60), $decSeconds % 60);

        return [
            'v' => 'poss2ukstu_red',
            'r' => $raText,
            'd' => $decText,
            'e' => 'J2000',
            'h' => (string) $sizeArcMin,
            'w' => (string) $sizeArcMin,
            'f' => 'gif',
            'c' => 'none',
            'fov' => 'NONE',
            'v3' => '',
        ];
    }

    /**
     * @param  array<int, string>  $tempFiles
     */
    private function writeTemp(string $content, string $extension, array &$tempFiles): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'dsl_set_');
        if ($tmp === false) {
            throw new \RuntimeException('Could not create a temporary file for the object set.');
        }

        // Some renderers go by the extension, so give them one.
        $path = $tmp . $extension;
        rename($tmp, $path);
        file_put_contents($path, $content);
        $tempFiles[] = $path;

        return $path;
    }

    /**
     * File name the set is offered under, e.g. object-sets-my-list.pdf, or
     * m-1.pdf for the set of a single object.
     *
     * @param  array<int, array<string, mixed>>  $objects
     */
    public static function filenameFor(string $base, array $objects): string
    {
        if (count($objects) === 1) {
            $base = (string) ($objects[0]['name'] ?? $base);
        }

        $slug = Str::slug($base);

        return ($slug !== '' ? $slug : 'object-sets') . '.pdf';
    }
}
