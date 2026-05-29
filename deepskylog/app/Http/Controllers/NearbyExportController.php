<?php

namespace App\Http\Controllers;

use App\Models\DeepskyObject;
use App\Services\StarChartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NearbyExportController extends Controller
{
    /**
     * Generate a PDF listing names of nearby objects based on RA/Dec and radius.
     * Accepts query params: ra (degrees or hours), dec (degrees), radius (arcminutes)
     */
    public function namesPdf(Request $request, $slug = null)
    {
        try {
            $ra = $request->query('ra');
            $dec = $request->query('dec');
            $radiusArcMin = intval($request->query('radius', 30));

            if ($ra === null || $dec === null) {
                // Try to look up object by slug to get coords
                if ($slug) {
                    $obj = DeepskyObject::where('slug', $slug)->orWhere('name', $slug)->first();
                    if ($obj) {
                        $ra = $obj->ra;
                        $dec = $obj->decl;
                    }
                }
            }

            if ($ra === null || $dec === null) {
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
            $radiusArcMin = intval($request->query('radius', 30));

            if ($ra === null || $dec === null) {
                // Try to look up object by slug to get coords
                if ($slug) {
                    $obj = DeepskyObject::where('slug', $slug)->orWhere('name', $slug)->first();
                    if ($obj) {
                        $ra = $obj->ra;
                        $dec = $obj->decl;
                    }
                }
            }

            if ($ra === null || $dec === null) {
                return redirect()->back()->with('error', __('Missing coordinates for nearby export'));
            }

            // Instantiate the Livewire component and run its lifecycle so any
            // auth-driven settings (like the user's standard atlas) are populated
            // before calling exportPdf(). Then set the RA/Dec inputs and delegate
            // to the component's export method which reuses the datasource.
            $comp = new \App\Livewire\NearbyObjectsTable();
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

            $radiusArcMin = max(5, min(180, (int) $request->query('radius', 30)));
            $magnitudeLimit = $request->query('mag_limit');
            $magnitudeLimit = is_numeric($magnitudeLimit) ? (float) $magnitudeLimit : null;

            $chart = $starChartService->build($target, $centerRaDeg, $centerDecDeg, $radiusArcMin, $magnitudeLimit);
            $title = __('Star chart for :object', ['object' => $target->name]);

            $html = view('pdf.star_chart', [
                'title' => $title,
                'target' => $target,
                'radius_arc_min' => $chart['radius_arc_min'],
                'magnitude_limit' => $chart['magnitude_limit'],
                'svg_data_uri' => 'data:image/svg+xml;base64,' . base64_encode($chart['svg']),
                'object_count' => $chart['objects']->count(),
                'star_count' => $chart['stars']->count(),
            ])->render();

            if (class_exists('\\Barryvdh\\DomPDF\\Facade\\Pdf')) {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper('a4', 'landscape');
                $filename = Str::slug($target->name) . '-star-chart.pdf';

                return response()->streamDownload(function () use ($pdf) {
                    echo $pdf->output();
                }, $filename, ['Content-Type' => 'application/pdf']);
            }

            if (class_exists('Dompdf\\Dompdf')) {
                $dompdf = new \Dompdf\Dompdf();
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'landscape');
                $dompdf->render();
                $output = $dompdf->output();
                $filename = Str::slug($target->name) . '-star-chart.pdf';

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
            $radiusArcMin = intval($request->query('radius', 30));

            if ($ra === null || $dec === null) {
                if ($slug) {
                    $obj = DeepskyObject::where('slug', $slug)->orWhere('name', $slug)->first();
                    if ($obj) {
                        $ra = $obj->ra;
                        $dec = $obj->decl;
                    }
                }
            }

            if ($ra === null || $dec === null) {
                return redirect()->back()->with('error', __('Missing coordinates for nearby export'));
            }

            // Instantiate the Livewire component and run its lifecycle so any
            // auth-driven settings (like the user's standard atlas) are populated
            // before calling the export method.
            $comp = new \App\Livewire\NearbyObjectsTable();
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
            $radiusArcMin = intval($request->query('radius', 30));

            if ($ra === null || $dec === null) {
                if ($slug) {
                    $obj = DeepskyObject::where('slug', $slug)->orWhere('name', $slug)->first();
                    if ($obj) {
                        $ra = $obj->ra;
                        $dec = $obj->decl;
                    }
                }
            }

            if ($ra === null || $dec === null) {
                return redirect()->back()->with('error', __('Missing coordinates for nearby export'));
            }

            // Instantiate the Livewire component and run lifecycle
            $comp = new \App\Livewire\NearbyObjectsTable();
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

        if (($ra === null || $dec === null) && $target) {
            $ra = $target->ra;
            $dec = $target->decl;
        }

        if ($ra === null || $dec === null) {
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
            $radiusArcMin = intval($request->query('radius', 30));

            if ($ra === null || $dec === null) {
                if ($slug) {
                    $obj = DeepskyObject::where('slug', $slug)->orWhere('name', $slug)->first();
                    if ($obj) {
                        $ra = $obj->ra;
                        $dec = $obj->decl;
                    }
                }
            }

            if ($ra === null || $dec === null) {
                return redirect()->back()->with('error', __('Missing coordinates for nearby export'));
            }

            $comp = new \App\Livewire\NearbyObjectsTable();
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
            $radiusArcMin = intval($request->query('radius', 30));

            if ($ra === null || $dec === null) {
                if ($slug) {
                    $obj = DeepskyObject::where('slug', $slug)->orWhere('name', $slug)->first();
                    if ($obj) {
                        $ra = $obj->ra;
                        $dec = $obj->decl;
                    }
                }
            }

            if ($ra === null || $dec === null) {
                return redirect()->back()->with('error', __('Missing coordinates for nearby export'));
            }

            $comp = new \App\Livewire\NearbyObjectsTable();
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
