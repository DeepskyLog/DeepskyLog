<?php

namespace App\Http\Controllers;

use App\Models\DeepskyObject;
use App\Models\User;
use App\Services\AtlasIndexPageService;
use App\Services\AtlasLayout;
use App\Services\AtlasObjectIndexService;
use App\Jobs\GenerateAtlasPdf;
use App\Services\StarChartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AtlasController extends Controller
{
    public function index()
    {
        return view('layouts.downloads.dsl_atlas');
    }

    public function queue(Request $request)
    {
        $user = $request->user() ?? User::find((int) $request->input('_user_id', 0));
        abort_unless($user, 403);

        $parameters = $request->except(['_token', '_user_id']);
        $token = (string) Str::uuid();
        Cache::put('atlas_pdf:' . $token, [
            'state' => 'queued',
            'user_id' => $user->id,
        ], now()->addDay());

        GenerateAtlasPdf::dispatch($token, (int) $user->id, $parameters);

        return response()->json([
            'token' => $token,
            'status_url' => route('downloads.dsl-atlas.status', $token),
            'download_url' => route('downloads.dsl-atlas.download', $token),
        ], 202);
    }

    public function status(Request $request, string $token)
    {
        $status = Cache::get('atlas_pdf:' . $token);
        abort_unless($status && (int) ($status['user_id'] ?? 0) === (int) $request->user()->id, 404);

        return response()->json($status);
    }

    public function download(Request $request, string $token)
    {
        abort_unless((bool) preg_match('/^[0-9a-f-]{36}$/i', $token), 404);

        $path = 'atlas/' . $token . '.pdf';
        abort_unless(
            Storage::disk('local')->exists($path),
            404
        );

        // The job records the descriptive name it generated under; fall back
        // to the defaults if the status entry has expired out of the cache.
        $status = Cache::get('atlas_pdf:' . $token);
        $filename = is_array($status) && is_string($status['filename'] ?? null) && $status['filename'] !== ''
            ? $status['filename']
            : self::filenameFor('detail', 'A4', 'landscape');

        return Response::download(Storage::disk('local')->path($path), $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Descriptive file name for a generated atlas, e.g.
     * dsl-atlas-portrait-a4-detail.pdf, or, for a single page centred on an
     * object, dsl-atlas-portrait-a4-detail-m31.pdf.
     */
    public static function filenameFor(string $type, string $paper, string $orientation, ?string $target = null): string
    {
        $parts = array_map(
            fn (string $part): string => Str::slug($part),
            [strtolower($orientation), strtolower($paper), strtolower($type), (string) $target]
        );

        return 'dsl-atlas-' . implode('-', array_filter($parts)) . '.pdf';
    }

    public function generate(Request $request, ?StarChartService $starChartService = null)
    {
        try {
            return $this->renderAtlas($request, $starChartService);
        } catch (\Throwable $ex) {
            $this->safeLog('error', 'AtlasController::generate failed', ['error' => (string) $ex]);

            return redirect()->back()->with('error', __('Failed to generate atlas'));
        }
    }

    /**
     * Render the atlas PDF response, letting any failure bubble up.
     *
     * generate() is the web wrapper that turns a failure into a redirect; the
     * queue job calls this directly so it can report the real cause.
     *
     * A whole atlas runs to thousands of pages, so the caller may pass
     * $onProgress(string $phase, int $page, int $totalPages) to follow along.
     */
    public function renderAtlas(Request $request, ?StarChartService $starChartService = null, ?callable $onProgress = null)
    {
        $starChartService ??= app(StarChartService::class);
        $type = strtolower(trim((string) $request->input('type', 'detail')));
        $paper = strtoupper(trim((string) $request->input('paper', 'A4')));
        $orientation = strtolower(trim((string) $request->input('orientation', 'landscape')));
        $scope = strtolower(trim((string) $request->input('scope', 'single')));

        $user = $request->user() ?? User::find((int) $request->input('_user_id', 1));

        // Allow long-running atlas generation (remove PHP execution time limit)
        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }
        @ini_set('max_execution_time', '0');

        $rasterize = filter_var((string) $request->input('rasterize', '0'), FILTER_VALIDATE_BOOLEAN);

        $fastAtlas = true;

        // Atlas variant index (0..5), used for page numbering on every page.
        $atlasIndex = AtlasLayout::variant($type, $orientation);

        // Atlas page field-of-view values are fixed full widths in arcminutes.
        if ($type === 'overview') {
            $fov = 1200.0;
            $objectMag = is_numeric($user->overviewdsos) ? (float) $user->overviewdsos : 10.0;
            $starMag = is_numeric($user->overviewstars) ? (float) $user->overviewstars : 10.0;
        } elseif ($type === 'lookup') {
            $fov = 720.0;
            $objectMag = is_numeric($user->lookupdsos) ? (float) $user->lookupdsos : 12.0;
            $starMag = is_numeric($user->lookupstars) ? (float) $user->lookupstars : 12.0;
        } else {
            $fov = 360.0;
            $objectMag = is_numeric($user->detaildsos) ? (float) $user->detaildsos : 15.0;
            $starMag = is_numeric($user->detailstars) ? (float) $user->detailstars : 15.0;
        }

        // Atlas charts need the full atlas radius; do not clamp to the small
        // object-chart field size.
        $radiusArcMin = $request->has('radius')
            ? max(0.5, min(3600.0, (float) $request->input('radius')))
            : max(0.5, ((float) $fov) / 2.0);

        // Objects with no recorded magnitude cannot be held to a magnitude
        // limit, so only the detail atlas -- the one meant to show everything
        // it can -- plots them. On an overview page they swamped the handful
        // of objects that are genuinely brighter than its limit.
        $showUnknownMagnitudes = $type !== 'overview' && $type !== 'lookup';

        if ($scope === 'single') {
            $slug = $request->input('slug');
            $ra = $request->input('ra');
            $dec = $request->input('dec');

            $target = null;
            if (!empty($slug)) {
                $target = DeepskyObject::where('slug', $slug)->orWhere('name', $slug)->first();
            }

            if ($target === null) {
                // create a lightweight fake target for the chart
                $target = new DeepskyObject();
                $target->name = $request->input('title', 'Custom center');
                if (!empty($ra)) {
                    $target->ra = $ra;
                }
                if (!empty($dec)) {
                    $target->decl = $dec;
                }
            }

            // Allow ra/dec query style as in NearbyExportController
            $centerRaDeg = floatval($target->ra ?? $request->input('ra') ?? 0.0);
            if ($centerRaDeg <= 24.0) {
                $centerRaDeg *= 15.0;
            }
            $centerDecDeg = floatval($target->decl ?? $request->input('dec') ?? 0.0);

            // A single page is centred wherever the caller asked, so only the
            // atlas page it falls on is meaningful -- its neighbours are not.
            $atlasPage = [
                'page' => AtlasLayout::pageAt($atlasIndex, $centerRaDeg / 15.0, $centerDecDeg),
                'total' => AtlasLayout::totalPages($atlasIndex),
            ];

            $t0 = microtime(true);
            $chart = $starChartService->build($target, $centerRaDeg, $centerDecDeg, $radiusArcMin, null, $objectMag, $starMag, $fastAtlas, true, $orientation === 'portrait', $atlasPage, $showUnknownMagnitudes);
            $t1 = microtime(true);
            $this->safeLog('info', 'AtlasController: built single chart', [
                'target' => $target->name ?? null,
                'duration_ms' => round(($t1 - $t0) * 1000, 2),
                'memory_bytes' => memory_get_usage(true),
                'peak_bytes' => memory_get_peak_usage(true),
            ]);
            $title = __('Star chart for :object', ['object' => $target->name ?? '']);

            // compute page dimensions (mm) for selected paper/orientation
            $paperSizes = [
                'A4' => [210, 297],
                'A3' => [297, 420],
            ];
            [$pw, $ph] = $paperSizes[$paper] ?? [210, 297];
            if ($orientation === 'landscape') {
                [$pw, $ph] = [$ph, $pw];
            }

            // Hand the chart to Dompdf as an SVG data URI so the PDF stays
            // vector: crisp at any zoom, and no resolution baked in. Dompdf
            // cannot render *inline* <svg>, but it does render SVG images.
            // ?rasterize=1 opts into a PNG instead, as for the whole atlas.
            $charts = [];
            $svgDataUri = 'data:image/svg+xml;base64,' . base64_encode($chart['svg']);

            if ($rasterize) {
                $imageDataUri = null;
                $tmp = tempnam(sys_get_temp_dir(), 'dsl_svg_');
                if ($tmp !== false) {
                    $tmpSvg = $tmp . '.svg';
                    rename($tmp, $tmpSvg);
                    file_put_contents($tmpSvg, $chart['svg']);

                    $pageDpi = 144;
                    $widthPx = (int) round(($pw / 25.4) * $pageDpi);
                    $heightPx = (int) round(($ph / 25.4) * $pageDpi);
                    $imageDataUri = $this->rasterizeSvgToDataUri($tmpSvg, $widthPx, $heightPx, $pageDpi);

                    @unlink($tmpSvg);
                }

                if ($imageDataUri === null) {
                    $this->safeLog('warning', 'AtlasController: rasterization failed for single page, falling back to SVG');
                }

                $charts[] = [
                    'title' => $title,
                    'image_data_uri' => $imageDataUri,
                    'svg_data_uri' => $imageDataUri === null ? $svgDataUri : null,
                ];
            } else {
                $charts[] = [
                    'title' => $title,
                    'svg_data_uri' => $svgDataUri,
                ];
            }

            $html = view('pdf.star_chart_bundle', ['charts' => $charts, 'pageWidthMm' => $pw, 'pageHeightMm' => $ph])->render();

            // PDF generation
            // Prefer using Dompdf directly for single-page generation so we
            // can explicitly enable HTML5 parser and remote file access.
            if (class_exists('Dompdf\\Dompdf')) {
                $options = new \Dompdf\Options();
                $options->set('isHtml5ParserEnabled', true);
                $options->set('isRemoteEnabled', true);

                $dompdf = new \Dompdf\Dompdf($options);
                $dompdf->loadHtml($html);
                $dompdf->setPaper([0, 0, ($pw / 25.4) * 72.0, ($ph / 25.4) * 72.0]);
                $dompdf->render();
                $output = $dompdf->output();
                $filename = self::filenameFor($type, $paper, $orientation, (string) $target->name);

                return response()->streamDownload(function () use ($output) {
                    echo $output;
                }, $filename, ['Content-Type' => 'application/pdf']);
            }

            if (class_exists('Dompdf\\Dompdf')) {
                $dompdf = new \Dompdf\Dompdf();
                $dompdf->loadHtml($html);
                $dompdf->setPaper(strtoupper($paper), $orientation);
                $dompdf->render();
                $output = $dompdf->output();
                $filename = self::filenameFor($type, $paper, $orientation, (string) $target->name);

                return response()->streamDownload(function () use ($output) {
                    echo $output;
                }, $filename, ['Content-Type' => 'application/pdf']);
            }

            return redirect()->back()->with('error', __('PDF library not installed. Please run: composer require barryvdh/laravel-dompdf'));
        }

        // whole atlas
        $bands = AtlasLayout::bandCount($atlasIndex);
        $totalPages = AtlasLayout::totalPages($atlasIndex);

        // The whole atlas is generated by default. `max_pages` is there to cut
        // a run short while trying things out -- it used to be hard-coded to
        // 20, which silently truncated every atlas anyone asked for.
        $maxPages = max(0, (int) $request->input('max_pages', 0));
        $pageLimit = $maxPages > 0 ? min($maxPages, $totalPages) : $totalPages;

        $paperSizes = [
            'A4' => [210, 297],
            'A3' => [297, 420],
        ];
        [$pw, $ph] = $paperSizes[$paper] ?? [210, 297];
        if ($orientation === 'landscape') {
            [$pw, $ph] = [$ph, $pw];
        }
        $pageDpi = 144;
        $widthPx = (int) round(($pw / 25.4) * $pageDpi);
        $heightPx = (int) round(($ph / 25.4) * $pageDpi);

        $pageNumber = 1;
        $charts = [];
        $tempFiles = [];
        $totalStart = microtime(true);

        // Front page: the atlas title, which atlas it is, and the symbol
        // legend. It carries no page number of its own -- the numbering on
        // the charts is the atlas page numbering, not the PDF's.
        // Dompdf cannot render inline <svg>, so the page goes in as an SVG
        // image, exactly as the charts do.
        $this->addSvgPage($charts, $tempFiles, __('Deepskylog Atlas'), $starChartService->buildTitlePage($type, $paper, $orientation));

        // Index maps, between the title page and the charts: which page of
        // the atlas covers which patch of sky, drawn over the constellations
        // so a reader can find a page from the sky rather than from a number.
        foreach (app(AtlasIndexPageService::class)->build($type, $paper, $orientation) as $indexSvg) {
            $this->addSvgPage($charts, $tempFiles, __('Atlas index'), $indexSvg);
        }

        for ($i = 0; $i < $bands; $i++) {
            $cols = AtlasLayout::columnCount($atlasIndex, $i);

            for ($c = 0; $c < $cols; $c++) {
                if ($pageNumber > $pageLimit) {
                    break 2;
                }

                [$raHours, $decCenter] = AtlasLayout::pageCenter($atlasIndex, $i, $c);
                $centerRaDeg = $raHours * 15.0;

                $fake = new DeepskyObject();
                $fake->name = ucfirst($type);
                // Store RA as degrees or hours; starChartService will normalize
                $fake->ra = $centerRaDeg;
                $fake->decl = $decCenter;

                // Every page carries its own number and those of the charts
                // that adjoin it, so a reader can follow the sky across pages.
                $atlasPage = AtlasLayout::neighbours($atlasIndex, $i, $c) + [
                    'page' => AtlasLayout::pageNumber($atlasIndex, $i, $c),
                    'total' => $totalPages,
                ];

                $chart = $starChartService->build($fake, $centerRaDeg, $decCenter, $radiusArcMin, null, $objectMag, $starMag, $fastAtlas, true, $orientation === 'portrait', $atlasPage, $showUnknownMagnitudes);

                $title = __('Atlas :type', ['type' => $type]);
                // Write each page's SVG to a temp file and reference it from
                // the bundle by path. A full atlas runs to thousands of pages;
                // keeping every one of them base64-encoded in memory as well
                // is what used to make a complete run impossible.
                $tmp = tempnam(sys_get_temp_dir(), 'dsl_svg_');
                if ($tmp !== false) {
                    // ensure .svg extension to help some renderers
                    $tmpSvg = $tmp . '.svg';
                    rename($tmp, $tmpSvg);
                    file_put_contents($tmpSvg, $chart['svg']);
                    $tempFiles[] = $tmpSvg;

                    if ($rasterize) {
                        $imageDataUri = $this->rasterizeSvgToDataUri($tmpSvg, $widthPx, $heightPx, $pageDpi);
                        if ($imageDataUri !== null) {
                            $charts[] = [
                                'title' => $title,
                                'image_data_uri' => $imageDataUri,
                            ];
                        } else {
                            $this->safeLog('warning', 'AtlasController: rasterization failed for atlas page, falling back to SVG', [
                                'page' => $pageNumber,
                                'svg_path' => $tmpSvg,
                            ]);
                            $charts[] = [
                                'title' => $title,
                                'svg_path' => $tmpSvg,
                            ];
                        }
                    } else {
                        $charts[] = [
                            'title' => $title,
                            'svg_path' => $tmpSvg,
                        ];
                    }
                } else {
                    // fallback to base64 if temp file couldn't be created
                    $charts[] = [
                        'title' => $title,
                        'svg_data_uri' => 'data:image/svg+xml;base64,' . base64_encode($chart['svg']),
                    ];
                }

                unset($chart);

                if ($onProgress !== null) {
                    $onProgress('building', $pageNumber, $pageLimit);
                }

                $pageNumber++;
            }
        }

        if ($onProgress !== null) {
            $onProgress('indexing', $pageNumber - 1, $pageLimit);
        }

        // The index of objects closes the atlas: every object the pages plot,
        // under every name it carries, against the page it is on. A run cut
        // short by max_pages only indexes as far as it actually got.
        $indexPages = app(AtlasObjectIndexService::class)->build(
            $type,
            $orientation,
            $objectMag,
            $showUnknownMagnitudes,
            $pageLimit < $totalPages ? $pageNumber - 1 : null
        );

        foreach ($indexPages as $indexSvg) {
            $this->addSvgPage($charts, $tempFiles, __('Index'), $indexSvg);
        }

        unset($indexPages);

        if ($onProgress !== null) {
            $onProgress('rendering', $pageNumber - 1, $pageLimit);
        }

        // Render all charts into a single HTML document (bundle view)
        $html = view('pdf.star_chart_bundle', ['charts' => $charts, 'pageWidthMm' => $pw, 'pageHeightMm' => $ph])->render();

        $totalEnd = microtime(true);
        $this->safeLog('info', 'AtlasController: whole atlas generation complete', [
            'total_pages' => $pageNumber - 1,
            'total_pages_requested' => $totalPages,
            'page_limit' => $pageLimit,
            'total_duration_ms' => round(($totalEnd - $totalStart) * 1000, 2),
            'peak_memory_bytes' => memory_get_peak_usage(true),
        ]);

        $filename = self::filenameFor($type, $paper, $orientation);

        // Use the server-side PDF engine only so atlas generation does not
        // depend on any local browser binary being installed.
        if (class_exists('Dompdf\\Dompdf')) {
            $options = new \Dompdf\Options();
            // enable HTML5 parser and remote resources
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            // allow local file access when we inline temporary SVG files
            $options->set('isPhpEnabled', true);
            // Pages are referenced as file:// URIs under the temp directory,
            // which Dompdf refuses to read unless it is inside the chroot.
            $options->set('chroot', array_unique([sys_get_temp_dir(), base_path(), storage_path()]));

            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper([0, 0, ($pw / 25.4) * 72.0, ($ph / 25.4) * 72.0]);
            $dompdf->render();
            $output = $dompdf->output();

            foreach ($tempFiles as $f) {
                try {
                    @unlink($f);
                } catch (\Throwable $_) {
                }
            }

            return response()->streamDownload(function () use ($output) {
                echo $output;
            }, $filename, ['Content-Type' => 'application/pdf']);
        }

        // cleanup temp svg files
        foreach ($tempFiles as $f) {
            try {
                @unlink($f);
            } catch (\Throwable $_) {
            }
        }

        return redirect()->back()->with('error', __('PDF library not installed. Please run: composer require barryvdh/laravel-dompdf'));
    }

    /**
     * Add one ready-made SVG page to the bundle.
     *
     * Through a temp file rather than a data URI, the way the charts go in:
     * the index of a detail atlas runs to a few hundred pages, and holding
     * every one of them base64-encoded in memory -- and then again inside
     * the bundle's HTML -- is exactly the cost the charts already avoid.
     *
     * @param  array<int, array<string, string>>  $charts
     * @param  array<int, string>  $tempFiles
     */
    private function addSvgPage(array &$charts, array &$tempFiles, string $title, string $svg): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'dsl_svg_');

        if ($tmp === false) {
            $charts[] = [
                'title' => $title,
                'svg_data_uri' => 'data:image/svg+xml;base64,' . base64_encode($svg),
            ];

            return;
        }

        // Some renderers go by the extension, so give them one.
        $tmpSvg = $tmp . '.svg';
        rename($tmp, $tmpSvg);
        file_put_contents($tmpSvg, $svg);
        $tempFiles[] = $tmpSvg;

        $charts[] = [
            'title' => $title,
            'svg_path' => $tmpSvg,
        ];
    }

    /**
     * Log without ever letting a broken log channel abort atlas generation.
     *
     * An unwritable log file used to throw from inside the render path and take
     * the whole PDF down with it, long after the chart itself had succeeded.
     */
    private function safeLog(string $level, string $message, array $context = []): void
    {
        try {
            Log::log($level, $message, $context);
        } catch (\Throwable $_) {
            // Diagnostics are best-effort; never fail the atlas because of them.
        }
    }

    /**
     * Rasterize an SVG file into a PNG data URI using an available converter.
     */
    private function rasterizeSvgToDataUri(string $svgPath, int $widthPx, int $heightPx, int $pageDpi = 144): ?string
    {
        $pngPath = $svgPath . '.png';
        $candidates = [];

        $rsvg = trim((string) shell_exec('command -v rsvg-convert 2>/dev/null'));
        if ($rsvg !== '') {
            $candidates[] = [
                'command' => escapeshellcmd($rsvg) . ' -w ' . $widthPx . ' -h ' . $heightPx . ' ' . escapeshellarg($svgPath) . ' -o ' . escapeshellarg($pngPath),
            ];
        }

        $magick = trim((string) shell_exec('command -v magick 2>/dev/null'));
        if ($magick !== '') {
            $candidates[] = [
                'command' => escapeshellcmd($magick) . ' ' . escapeshellarg($svgPath) . ' -background white -flatten -density ' . $pageDpi . ' -resize ' . $widthPx . 'x' . $heightPx . ' ' . escapeshellarg($pngPath),
            ];
        }

        $convert = trim((string) shell_exec('command -v convert 2>/dev/null'));
        if ($convert !== '') {
            $candidates[] = [
                'command' => escapeshellcmd($convert) . ' ' . escapeshellarg($svgPath) . ' -background white -flatten -density ' . $pageDpi . ' -resize ' . $widthPx . 'x' . $heightPx . ' ' . escapeshellarg($pngPath),
            ];
        }

        foreach ($candidates as $candidate) {
            @unlink($pngPath);
            $output = [];
            $exitCode = 0;
            exec($candidate['command'] . ' 2>&1', $output, $exitCode);

            if ($exitCode === 0 && is_file($pngPath)) {
                $pngBytes = file_get_contents($pngPath);
                @unlink($pngPath);

                if ($pngBytes !== false && $pngBytes !== '') {
                    return 'data:image/png;base64,' . base64_encode($pngBytes);
                }
            }
        }

        @unlink($pngPath);

        return null;
    }
}
