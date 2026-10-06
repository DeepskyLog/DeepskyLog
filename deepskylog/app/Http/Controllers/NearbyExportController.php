<?php

namespace App\Http\Controllers;

use App\Livewire\NearbyObjectsTable;
use App\Models\DeepskyObject;
use App\Services\StarChartService;
use App\Support\SvgRasterizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NearbyExportController extends Controller
{
    /** Largest chart radius, in arcminutes: half the overview chart's width. */
    private const MAX_CHART_RADIUS_ARCMIN = 600;

    /** Magnitude range the atlas settings in the profile accept. */
    private const MIN_CHART_MAGNITUDE = 1.0;

    private const MAX_CHART_MAGNITUDE = 20.0;

    /**
     * Generate a PDF listing names of nearby objects based on RA/Dec and radius.
     * Accepts query params: ra (degrees or hours), dec (degrees), radius (arcminutes)
     */
    public function namesPdf(Request $request, $slug = null)
    {
        try {
            $ra = $request->query('ra');
            $dec = $request->query('dec');
            $radiusArcMin = NearbyObjectsTable::clampRadius($request->query('radius', 30));

            if (! is_numeric($ra) || ! is_numeric($dec)) {
                // Try to look up object by slug to get coords
                if ($slug) {
                    $obj = DeepskyObject::where('slug', $slug)->orWhere('name', $slug)->first();
                    if ($obj) {
                        $ra = $obj->ra;
                        $dec = $obj->decl;
                    }
                }
            }

            if (! is_numeric($ra) || ! is_numeric($dec)) {
                return redirect()->back()->with('error', __('Missing coordinates for nearby export'));
            }

            $radiusDeg = $radiusArcMin / 60.0;

            $centerRaDeg = floatval($ra);
            if ($centerRaDeg <= 24.0) {
                $centerRaDeg = $centerRaDeg * 15.0;
            }

            $expr = "DEGREES(ACOS(LEAST(1, GREATEST(-1, SIN(RADIANS(?))*SIN(RADIANS(`decl`)) + COS(RADIANS(?))*COS(RADIANS(`decl`))*COS(RADIANS((?)-(CASE WHEN `ra` <= 24 THEN `ra`*15 ELSE `ra` END))) ))))";

            $bindings = [$dec, $dec, $centerRaDeg, $radiusDeg];

            $q = DeepskyObject::query()->select('name')->whereRaw("{$expr} <= ?", $bindings)->orderBy('name', 'asc');

            $rows = $q->get();
            $names = $rows->pluck('name')->filter()->values();

            // Determine a friendly title that includes the main object's name when available
            $mainObjectName = null;
            if (!empty($slug)) {
                try {
                    $found = DeepskyObject::where('slug', $slug)->orWhere('name', $slug)->first();
                    if ($found) {
                        $mainObjectName = $found->name ?? null;
                    }
                } catch (\Throwable $_) {
                    // ignore lookup failures
                }
            }

            if ($mainObjectName) {
                // Use phrasing requested: "Nearby objects of M 31"
                $title = __('Nearby objects of :object', ['object' => $mainObjectName]);
            } else {
                $title = __('Nearby object names');
            }

            $html = view('pdf.nearby_names', ['names' => $names, 'title' => $title])->render();

            // Use barryvdh facade if available
            if (class_exists('\\Barryvdh\\DomPDF\\Facade\\Pdf')) {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper('a4', 'portrait');
                $base = $mainObjectName ? Str::slug($mainObjectName) : 'nearby_names_' . date('Ymd_His');
                $filename = $base . '.pdf';
                return response()->streamDownload(function () use ($pdf) {
                    echo $pdf->output();
                }, $filename, ['Content-Type' => 'application/pdf']);
            }

            if (class_exists('Dompdf\\Dompdf')) {
                $dompdf = new \Dompdf\Dompdf();
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                $output = $dompdf->output();
                $base = $mainObjectName ? Str::slug($mainObjectName) : 'nearby_names_' . date('Ymd_His');
                $filename = $base . '.pdf';
                return response()->streamDownload(function () use ($output) {
                    echo $output;
                }, $filename, ['Content-Type' => 'application/pdf']);
            }

            return redirect()->back()->with('error', __('PDF library not installed. Please run: composer require barryvdh/laravel-dompdf'));
        } catch (\Throwable $ex) {
            Log::error('NearbyExportController::namesPdf failed', ['error' => (string) $ex]);
            return redirect()->back()->with('error', __('Failed to generate names PDF'));
        }
    }

    /**
     * Export the full nearby objects table as a landscape PDF.
     * Delegates to the NearbyObjectsTable Livewire component logic where possible.
     */
    public function tablePdf(Request $request, $slug = null)
    {
        try {
            $ra = $request->query('ra');
            $dec = $request->query('dec');
            $radiusArcMin = NearbyObjectsTable::clampRadius($request->query('radius', 30));

            if (! is_numeric($ra) || ! is_numeric($dec)) {
                // Try to look up object by slug to get coords
                if ($slug) {
                    $obj = DeepskyObject::where('slug', $slug)->orWhere('name', $slug)->first();
                    if ($obj) {
                        $ra = $obj->ra;
                        $dec = $obj->decl;
                    }
                }
            }

            if (! is_numeric($ra) || ! is_numeric($dec)) {
                return redirect()->back()->with('error', __('Missing coordinates for nearby export'));
            }

            // Instantiate the Livewire component and run its lifecycle so any
            // auth-driven settings (like the user's standard atlas) are populated
            // before calling exportPdf(). Then set the RA/Dec inputs and delegate
            // to the component's export method which reuses the datasource.
            $comp = new NearbyObjectsTable();
            // Run boot/mount if available so the component picks up auth user info
            if (method_exists($comp, 'boot')) {
                try {
                    $comp->boot();
                } catch (\Throwable $_) {
                }
            }
            if (method_exists($comp, 'mount')) {
                try {
                    $comp->mount();
                } catch (\Throwable $_) {
                }
            }

            // Set query parameters after mount so mount-based defaults are preserved
            $comp->ra = $ra;
            $comp->decl = $dec;
            $comp->radiusArcMin = $radiusArcMin;

            // If the component's exportPdf() returns a Response, just return it.
            if (method_exists($comp, 'exportPdf')) {
                return $comp->exportPdf();
            }

            return redirect()->back()->with('error', __('PDF export not available'));
        } catch (\Throwable $ex) {
            Log::error('NearbyExportController::tablePdf failed', ['error' => (string) $ex]);
            return redirect()->back()->with('error', __('Failed to generate nearby objects PDF'));
        }
    }

    /**
     * Export a printable star chart PDF centered on the current object.
     */
    public function chartPdf(Request $request, StarChartService $starChartService, $slug = null)
    {
        try {
            [$centerRaDeg, $centerDecDeg, $target] = $this->resolveCenterAndTarget($request, $slug);

            if ($centerRaDeg === null || $centerDecDeg === null || $target === null) {
                return redirect()->back()->with('error', __('Missing coordinates for star chart export'));
            }

            // Atlas page field-of-view values are fixed full widths in arcminutes.
            $type = strtolower(trim((string) $request->query('type', 'detail')));
            $user = auth()->user();

            if ($type === 'overview') {
                $fov = 1200.0;
                $objectMag = is_numeric($user?->overviewdsos) ? (float) $user->overviewdsos : 10.0;
                $starMag = is_numeric($user?->overviewstars) ? (float) $user->overviewstars : 10.0;
            } elseif ($type === 'lookup') {
                $fov = 720.0;
                $objectMag = is_numeric($user?->lookupdsos) ? (float) $user->lookupdsos : 12.0;
                $starMag = is_numeric($user?->lookupstars) ? (float) $user->lookupstars : 12.0;
            } else {
                $fov = 360.0;
                $objectMag = is_numeric($user?->detaildsos) ? (float) $user->detaildsos : 15.0;
                $starMag = is_numeric($user?->detailstars) ? (float) $user->detailstars : 15.0;
            }

            // Objects with no recorded magnitude are plotted only on the
            // deepest charts, the same rule the printed atlas pages follow.
            $showUnknownMagnitudes = $type !== 'overview' && $type !== 'lookup';

            // Allow explicit overrides via query params: radius (arcmin), mag_limit (both), object_mag, star_mag
            if ($request->has('radius')) {
                $radiusArcMin = max(5, min(self::MAX_CHART_RADIUS_ARCMIN, (int) $request->query('radius', 30)));
            } else {
                // Field-of-view values are full widths; build expects radius.
                $radiusArcMin = max(5, min(self::MAX_CHART_RADIUS_ARCMIN, (int) round(((float) $fov) / 2.0)));
            }

            $magLimit = $request->query('mag_limit');
            if (is_numeric($magLimit)) {
                $objectMag = $starMag = (float) $magLimit;
            }
            $objMagReq = $request->query('object_mag');
            if (is_numeric($objMagReq)) {
                $objectMag = (float) $objMagReq;
            }
            $starMagReq = $request->query('star_mag');
            if (is_numeric($starMagReq)) {
                $starMag = (float) $starMagReq;
            }

            // Each fainter magnitude loads a few times more stars: keep the
            // limits within what the atlas settings in the profile allow.
            $objectMag = max(self::MIN_CHART_MAGNITUDE, min(self::MAX_CHART_MAGNITUDE, $objectMag));
            $starMag = max(self::MIN_CHART_MAGNITUDE, min(self::MAX_CHART_MAGNITUDE, $starMag));

            // Use atlas-mode projection (spherical) for generated charts
            $chart = $starChartService->build($target, $centerRaDeg, $centerDecDeg, $radiusArcMin, null, $objectMag, $starMag, false, true, false, null, $showUnknownMagnitudes);
            $title = __('Star chart for :object', ['object' => $target->name]);

            // A4 landscape: 297×210 mm
            $pw = 297.0;
            $ph = 210.0;
            $pageDpi = 144;
            $widthPx  = (int) round(($pw / 25.4) * $pageDpi);
            $heightPx = (int) round(($ph / 25.4) * $pageDpi);

            // Write SVG to a temp file for rasterization
            $tmpSvgPath = null;
            $tmp = tempnam(sys_get_temp_dir(), 'dsl_chart_');
            if ($tmp !== false) {
                $tmpSvgPath = $tmp . '.svg';
                rename($tmp, $tmpSvgPath);
                file_put_contents($tmpSvgPath, $chart['svg']);
            }

            // Rasterize SVG → PNG using ImageMagick (same approach as AtlasController)
            $imageDataUri = null;
            if ($tmpSvgPath !== null) {
                $imageDataUri = SvgRasterizer::fileToDataUri($tmpSvgPath, $widthPx, $heightPx, $pageDpi);
            }

            if ($imageDataUri !== null) {
                $chartData = ['title' => $title, 'image_data_uri' => $imageDataUri];
            } else {
                // Fall back to inline SVG with mm dimensions so DomPDF can size it
                $svgContent = preg_replace(
                    '/<svg([^>]*)\bwidth="[^"]*"\s*height="[^"]*"/',
                    '<svg$1 width="' . $pw . 'mm" height="' . $ph . 'mm"',
                    $chart['svg'],
                    1
                );
                $chartData = ['title' => $title, 'svg' => $svgContent];
            }

            if ($tmpSvgPath !== null && is_file($tmpSvgPath)) {
                @unlink($tmpSvgPath);
            }

            $html = view('pdf.star_chart_bundle', [
                'charts' => [$chartData],
                'pageWidthMm' => $pw,
                'pageHeightMm' => $ph,
            ])->render();

            $filename = Str::slug($target->name) . '-' . $type . '-star-chart.pdf';

            if (class_exists('Dompdf\\Dompdf')) {
                $options = new \Dompdf\Options();
                $options->set('isHtml5ParserEnabled', true);

                $dompdf = new \Dompdf\Dompdf($options);
                $dompdf->loadHtml($html);
                $dompdf->setPaper([0, 0, ($pw / 25.4) * 72.0, ($ph / 25.4) * 72.0]);
                $dompdf->render();
                $output = $dompdf->output();

                return response()->streamDownload(function () use ($output) {
                    echo $output;
                }, $filename, ['Content-Type' => 'application/pdf']);
            }

            return redirect()->back()->with('error', __('PDF library not installed. Please run: composer require barryvdh/laravel-dompdf'));
        } catch (\Throwable $ex) {
            Log::error('NearbyExportController::chartPdf failed', ['error' => (string) $ex]);

            return redirect()->back()->with('error', __('Failed to generate star chart PDF'));
        }
    }

    /**
     * Export nearby objects in Argo Navis format (plain text).
     * Streams a plain text file with one entry per object.
     */
    public function argoNavis(Request $request, $slug = null)
    {
        try {
            $ra = $request->query('ra');
            $dec = $request->query('dec');
            $radiusArcMin = NearbyObjectsTable::clampRadius($request->query('radius', 30));

            if (! is_numeric($ra) || ! is_numeric($dec)) {
                if ($slug) {
                    $obj = DeepskyObject::where('slug', $slug)->orWhere('name', $slug)->first();
                    if ($obj) {
                        $ra = $obj->ra;
                        $dec = $obj->decl;
                    }
                }
            }

            if (! is_numeric($ra) || ! is_numeric($dec)) {
                return redirect()->back()->with('error', __('Missing coordinates for nearby export'));
            }

            // Instantiate the Livewire component and run its lifecycle so any
            // auth-driven settings (like the user's standard atlas) are populated
            // before calling the export method.
            $comp = new NearbyObjectsTable();
            if (method_exists($comp, 'boot')) {
                try {
                    $comp->boot();
                } catch (\Throwable $_) {
                }
            }
            if (method_exists($comp, 'mount')) {
                try {
                    $comp->mount();
                } catch (\Throwable $_) {
                }
            }

            $comp->ra = $ra;
            $comp->decl = $dec;
            $comp->radiusArcMin = $radiusArcMin;

            if (method_exists($comp, 'exportArgo')) {
                return $comp->exportArgo();
            }

            return redirect()->back()->with('error', __('Argo export not available'));
        } catch (\Throwable $ex) {
            Log::error('NearbyExportController::argoNavis failed', ['error' => (string) $ex]);
            return redirect()->back()->with('error', __('Failed to generate Argo Navis export'));
        }
    }

    /**
     * Export nearby objects in SkySafari .skylist format (plain text).
     * Delegates to the NearbyObjectsTable Livewire component if available.
     */
    public function skylist(Request $request, $slug = null)
    {
        try {
            $ra = $request->query('ra');
            $dec = $request->query('dec');
            $radiusArcMin = NearbyObjectsTable::clampRadius($request->query('radius', 30));

            if (! is_numeric($ra) || ! is_numeric($dec)) {
                if ($slug) {
                    $obj = DeepskyObject::where('slug', $slug)->orWhere('name', $slug)->first();
                    if ($obj) {
                        $ra = $obj->ra;
                        $dec = $obj->decl;
                    }
                }
            }

            if (! is_numeric($ra) || ! is_numeric($dec)) {
                return redirect()->back()->with('error', __('Missing coordinates for nearby export'));
            }

            // Instantiate the Livewire component and run lifecycle
            $comp = new NearbyObjectsTable();
            if (method_exists($comp, 'boot')) {
                try {
                    $comp->boot();
                } catch (\Throwable $_) {
                }
            }
            if (method_exists($comp, 'mount')) {
                try {
                    $comp->mount();
                } catch (\Throwable $_) {
                }
            }

            $comp->ra = $ra;
            $comp->decl = $dec;
            $comp->radiusArcMin = $radiusArcMin;

            if (method_exists($comp, 'exportSkylist')) {
                return $comp->exportSkylist();
            }

            return redirect()->back()->with('error', __('Skylist export not available'));
        } catch (\Throwable $ex) {
            Log::error('NearbyExportController::skylist failed', ['error' => (string) $ex]);
            return redirect()->back()->with('error', __('Failed to generate SkySafari export'));
        }
    }

    /**
     * @return array{0: float|null, 1: float|null, 2: DeepskyObject|null}
     */
    private function resolveCenterAndTarget(Request $request, ?string $slug): array
    {
        $ra = $request->query('ra');
        $dec = $request->query('dec');

        $target = null;

        if ($slug) {
            $target = DeepskyObject::where('slug', $slug)->orWhere('name', $slug)->first();
        }

        if ((! is_numeric($ra) || ! is_numeric($dec)) && $target) {
            $ra = $target->ra;
            $dec = $target->decl;
        }

        if (! is_numeric($ra) || ! is_numeric($dec)) {
            return [null, null, $target];
        }

        $centerRaDeg = (float) $ra;
        if ($centerRaDeg <= 24.0) {
            $centerRaDeg *= 15.0;
        }

        return [$centerRaDeg, (float) $dec, $target];
    }

    /**
     * Export nearby objects as a plain TXT file (SkyTools simple list: one name per line).
     */
    public function stxt(Request $request, $slug = null)
    {
        try {
            $ra = $request->query('ra');
            $dec = $request->query('dec');
            $radiusArcMin = NearbyObjectsTable::clampRadius($request->query('radius', 30));

            if (! is_numeric($ra) || ! is_numeric($dec)) {
                if ($slug) {
                    $obj = DeepskyObject::where('slug', $slug)->orWhere('name', $slug)->first();
                    if ($obj) {
                        $ra = $obj->ra;
                        $dec = $obj->decl;
                    }
                }
            }

            if (! is_numeric($ra) || ! is_numeric($dec)) {
                return redirect()->back()->with('error', __('Missing coordinates for nearby export'));
            }

            $comp = new NearbyObjectsTable();
            if (method_exists($comp, 'boot')) {
                try {
                    $comp->boot();
                } catch (\Throwable $_) {
                }
            }
            if (method_exists($comp, 'mount')) {
                try {
                    $comp->mount();
                } catch (\Throwable $_) {
                }
            }

            $comp->ra = $ra;
            $comp->decl = $dec;
            $comp->radiusArcMin = $radiusArcMin;

            if (method_exists($comp, 'exportStxt')) {
                return $comp->exportStxt();
            }

            return redirect()->back()->with('error', __('TXT export not available'));
        } catch (\Throwable $ex) {
            Log::error('NearbyExportController::stxt failed', ['error' => (string) $ex]);
            return redirect()->back()->with('error', __('Failed to generate SkyTools TXT export'));
        }
    }

    /**
     * Export nearby objects as an AstroPlanner .apd file (SQLite database).
     * Delegates to NearbyObjectsTable::exportApd() when available.
     */
    public function apd(Request $request, $slug = null)
    {
        try {
            $ra = $request->query('ra');
            $dec = $request->query('dec');
            $radiusArcMin = NearbyObjectsTable::clampRadius($request->query('radius', 30));

            if (! is_numeric($ra) || ! is_numeric($dec)) {
                if ($slug) {
                    $obj = DeepskyObject::where('slug', $slug)->orWhere('name', $slug)->first();
                    if ($obj) {
                        $ra = $obj->ra;
                        $dec = $obj->decl;
                    }
                }
            }

            if (! is_numeric($ra) || ! is_numeric($dec)) {
                return redirect()->back()->with('error', __('Missing coordinates for nearby export'));
            }

            $comp = new NearbyObjectsTable();
            if (method_exists($comp, 'boot')) {
                try {
                    $comp->boot();
                } catch (\Throwable $_) {
                }
            }
            if (method_exists($comp, 'mount')) {
                try {
                    $comp->mount();
                } catch (\Throwable $_) {
                }
            }

            $comp->ra = $ra;
            $comp->decl = $dec;
            $comp->radiusArcMin = $radiusArcMin;

            if (method_exists($comp, 'exportApd')) {
                return $comp->exportApd();
            }

            return redirect()->back()->with('error', __('APD export not available'));
        } catch (\Throwable $ex) {
            Log::error('NearbyExportController::apd failed', ['error' => (string) $ex]);
            return redirect()->back()->with('error', __('Failed to generate AstroPlanner APD export'));
        }
    }
}
