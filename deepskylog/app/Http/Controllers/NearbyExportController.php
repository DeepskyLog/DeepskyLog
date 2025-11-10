<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\DeepskyObject;

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
            if (! empty($slug)) {
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
            Log::error('NearbyExportController::namesPdf failed', ['error' => (string)$ex]);
            return redirect()->back()->with('error', __('Failed to generate names PDF'));
        }
    }
}
