<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SearchExportController extends Controller
{
    /**
     * Generate a PDF listing names of search results based on query param q.
     */
    public function namesPdf(Request $request)
    {
        try {
            $q = $request->query('q', '');

            $comp = new \App\Livewire\SearchResultsTable();
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

            $comp->q = $q;

            $query = $comp->datasource();
            $rows = $query ? $query->get() : collect();

            $names = $rows->pluck('display_name')->filter()->values();

            $title = __('Search results for ":q"', ['q' => $q]);
            $html = view('pdf.nearby_names', ['names' => $names, 'title' => $title])->render();

            if (class_exists('\\Barryvdh\\DomPDF\\Facade\\Pdf')) {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper('a4', 'portrait');
                $filename = 'search_names_' . date('Ymd_His') . '.pdf';
                return response()->streamDownload(function () use ($pdf) {
                    echo $pdf->output(); }, $filename, ['Content-Type' => 'application/pdf']);
            }

            if (class_exists('Dompdf\\Dompdf')) {
                $dompdf = new \Dompdf\Dompdf();
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                $output = $dompdf->output();
                $filename = 'search_names_' . date('Ymd_His') . '.pdf';
                return response()->streamDownload(function () use ($output) {
                    echo $output; }, $filename, ['Content-Type' => 'application/pdf']);
            }

            return redirect()->back()->with('error', __('PDF library not installed. Please run: composer require barryvdh/laravel-dompdf'));
        } catch (\Throwable $ex) {
            Log::error('SearchExportController::namesPdf failed', ['error' => (string) $ex]);
            return redirect()->back()->with('error', __('Failed to generate names PDF'));
        }
    }

    /**
     * Export the full search results table as a landscape PDF.
     */
    public function tablePdf(Request $request)
    {
        try {
            $q = $request->query('q', '');

            $comp = new \App\Livewire\SearchResultsTable();
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
            $comp->q = $q;

            $query = $comp->datasource();
            $rows = $query ? $query->get() : collect();

            $formatDiameter = function ($d1, $d2, $pa = null) {
                $hasD1 = is_numeric($d1) && floatval($d1) > 0;
                $hasD2 = is_numeric($d2) && floatval($d2) > 0;
                if (!$hasD1 && !$hasD2)
                    return '';
                $d1f = $hasD1 ? floatval($d1) : 0.0;
                $d2f = $hasD2 ? floatval($d2) : 0.0;
                $fmt = function ($v) {
                    return (floor($v) == $v) ? sprintf('%d', $v) : sprintf('%.1f', $v); };
                if ($hasD1 && $hasD2) {
                    if (max($d1f, $d2f) > 60.0) {
                        $d1m = $d1f / 60.0;
                        $d2m = $d2f / 60.0;
                        $d1m_fmt = $fmt($d1m);
                        $d2m_fmt = $fmt($d2m);
                        if ($d1m_fmt === '0' || $d1m_fmt === '0.0' || $d2m_fmt === '0' || $d2m_fmt === '0.0') {
                            $size = $fmt($d1f) . "''x" . $fmt($d2f) . "''";
                        } else {
                            $size = $d1m_fmt . "'x" . $d2m_fmt . "'";
                        }
                    } else {
                        $size = $fmt($d1f) . "''x" . $fmt($d2f) . "''";
                    }
                } else {
                    $single = $hasD1 ? $d1f : $d2f;
                    if ($single > 60.0) {
                        $single_fmt = $fmt($single / 60.0) . "'";
                    } else {
                        $single_fmt = $fmt($single) . "''";
                    }
                    $size = $single_fmt;
                }
                if (is_numeric($pa) && intval(round(floatval($pa))) !== 999) {
                    $size .= '/' . sprintf('%d', round(floatval($pa))) . '°';
                }
                return $size;
            };

            $data = $rows->map(function ($r) use ($formatDiameter, $comp) {
                $d1 = $r->diam1 ?? null;
                $d2 = $r->diam2 ?? null;
                $pa = $r->pa ?? null;
                return [
                    'name' => $r->display_name ?? $r->name ?? '',
                    'ra' => $this->formatRAColon($r->obj_ra ?? $r->ra ?? null),
                    'decl' => $this->formatDecColon($r->obj_decl ?? $r->decl ?? null),
                    'type' => $r->type_name ?? $r->source_type ?? '',
                    'constellation' => $r->constellation ?? '',
                    'mag' => (is_numeric($r->mag) && floatval($r->mag) != 99.9 && floatval($r->mag) != 0.0) ? (string) $r->mag : '-',
                    'sb' => (is_numeric($r->subr) && floatval($r->subr) != 99.9 && floatval($r->subr) != 0.0) ? (string) $r->subr : '-',
                    'diameter' => $formatDiameter($d1, $d2, $pa),
                    'atlas_page' => property_exists($comp, 'includeAtlas') && $comp->includeAtlas ? ($r->atlas_page ?? '-') : '-',
                    'cr' => isset($r->computed_contrast_reserve) && is_numeric($r->computed_contrast_reserve) ? number_format(round(floatval($r->computed_contrast_reserve), 2), 2) : ($r->computed_contrast_reserve_category ?? '-'),
                    'best_mag' => $this->computeBestMagForExport($r, $comp),
                    'seen' => isset($r->total_observations) ? (string) $r->total_observations : '-',
                    'last_seen' => $r->your_last_seen_date ?? '-',
                ];
            })->toArray();

            $title = __('DeepskyLog Objects');
            $html = view('pdf.nearby_objects_table', ['rows' => $data, 'title' => $title])->render();

            if (class_exists('\\Barryvdh\\DomPDF\\Facade\\Pdf')) {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper('a4', 'landscape');
                $filename = 'search_objects_' . date('Ymd_His') . '.pdf';
                return response()->streamDownload(function () use ($pdf) {
                    echo $pdf->output(); }, $filename, ['Content-Type' => 'application/pdf']);
            }

            if (class_exists('Dompdf\\Dompdf')) {
                $dompdf = new \Dompdf\Dompdf();
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'landscape');
                $dompdf->render();
                $output = $dompdf->output();
                $filename = 'search_objects_' . date('Ymd_His') . '.pdf';
                return response()->streamDownload(function () use ($output) {
                    echo $output; }, $filename, ['Content-Type' => 'application/pdf']);
            }

            return redirect()->back()->with('error', __('PDF library not installed. Please run: composer require barryvdh/laravel-dompdf'));
        } catch (\Throwable $ex) {
            Log::error('SearchExportController::tablePdf failed', ['error' => (string) $ex]);
            return redirect()->back()->with('error', __('Failed to generate search objects PDF'));
        }
    }

    /**
     * Export search results in Argo Navis plain text format.
     */
    public function argoNavis(Request $request)
    {
        try {
            $q = $request->query('q', '');

            $comp = new \App\Livewire\SearchResultsTable();
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
            $comp->q = $q;

            $query = $comp->datasource();
            $rows = $query ? $query->get() : collect();

            $formatDiameter = function ($d1, $d2, $pa = null) {
                $hasD1 = is_numeric($d1) && floatval($d1) > 0;
                $hasD2 = is_numeric($d2) && floatval($d2) > 0;
                if (!$hasD1 && !$hasD2)
                    return '';
                $d1f = $hasD1 ? floatval($d1) : 0.0;
                $d2f = $hasD2 ? floatval($d2) : 0.0;
                $fmt = function ($v) {
                    return (floor($v) == $v) ? sprintf('%d', $v) : sprintf('%.1f', $v); };
                if ($hasD1 && $hasD2) {
                    if (max($d1f, $d2f) > 60.0) {
                        $d1m = $d1f / 60.0;
                        $d2m = $d2f / 60.0;
                        $d1m_fmt = $fmt($d1m);
                        $d2m_fmt = $fmt($d2m);
                        if ($d1m_fmt === '0' || $d1m_fmt === '0.0' || $d2m_fmt === '0' || $d2m_fmt === '0.0') {
                            $size = $fmt($d1f) . "''x" . $fmt($d2f) . "''";
                        } else {
                            $size = $d1m_fmt . "'x" . $d2m_fmt . "'";
                        }
                    } else {
                        $size = $fmt($d1f) . "''x" . $fmt($d2f) . "''";
                    }
                } else {
                    $single = $hasD1 ? $d1f : $d2f;
                    if ($single > 60.0) {
                        $single_fmt = $fmt($single / 60.0) . "'";
                    } else {
                        $single_fmt = $fmt($single) . "''";
                    }
                    $size = $single_fmt;
                }
                if (is_numeric($pa) && intval(round(floatval($pa))) !== 999) {
                    $size .= '/' . sprintf('%d', round(floatval($pa))) . '°';
                }
                return $size;
            };

            $typeMap = [
                'ASTER' => 'ASTERISM',
                'BRTNB' => 'BRIGHT',
                'CLANB' => 'NEBULA',
                'DRKNB' => 'DARK',
                'EMINB' => 'NEBULA',
                'ENRNN' => 'NEBULA',
                'ENSTR' => 'NEBULA',
                'GALCL' => 'GALAXY CL',
                'GALXY' => 'GALAXY',
                'GLOCL' => 'GLOBULAR',
                'GXADN' => 'NEBULA',
                'GXAGC' => 'GLOBULAR',
                'GACAN' => 'NEBULA',
                'HII' => 'NEBULA',
                'LMCCN' => 'NEBULA',
                'LMCDN' => 'NEBULA',
                'LMCGC' => 'GLOBULAR',
                'LMCOC' => 'OPEN',
                'NONEX' => 'USER',
                'OPNCL' => 'OPEN',
                'PLNNB' => 'PLANETARY',
                'REFNB' => 'NEBULA',
                'RNHII' => 'NEBULA',
                'SMCCN' => 'OPEN',
                'SMCDN' => 'NEBULA',
                'SMCGC' => 'GLOBULAR',
                'SMCOC' => 'OPEN',
                'SNREM' => 'NEBULA',
                'STNEB' => 'NEBULA',
                'QUASR' => 'USER',
                'WRNEB' => 'NEBULA',
                'AA1STAR' => 'STAR',
                'DS' => 'DOUBLE',
                'AA3STAR' => 'TRIPLE',
                'AA4STAR' => 'ASTERISM',
                'AA8STAR' => 'ASTERISM',
            ];

            $planetNames = array_map('strtolower', ['Mercury', 'Venus', 'Earth', 'Mars', 'Jupiter', 'Saturn', 'Uranus', 'Neptune', 'Pluto', 'Moon', 'Sun']);

            $lines = $rows->map(function ($r) use ($formatDiameter, $typeMap, $planetNames) {
                $name = 'DSL ' . ($r->display_name ?? $r->name ?? '');
                $ra = $this->formatRAColon($r->obj_ra ?? $r->ra ?? null);
                $dec = $this->formatDecColon($r->obj_decl ?? $r->decl ?? null);

                $rawTypeCode = strtoupper(trim((string) ($r->obj_type ?? $r->source_type ?? '')));
                $type = '';
                if ($rawTypeCode !== '' && isset($typeMap[$rawTypeCode])) {
                    $type = $typeMap[$rawTypeCode];
                } else {
                    $tn = $r->type_name ?? '';
                    $type = $tn ? strtoupper(trim((string) $tn)) : 'USER';
                }

                $mag = (is_numeric($r->mag) && floatval($r->mag) != 99.9 && floatval($r->mag) != 0.0) ? (string) $r->mag : '';
                $size = $formatDiameter($r->diam1 ?? null, $r->diam2 ?? null, $r->pa ?? null);
                $atlas = ''; // atlas handled in table export only
                $cr = isset($r->computed_contrast_reserve) && is_numeric($r->computed_contrast_reserve) ? number_format(round(floatval($r->computed_contrast_reserve), 2), 2) : ($r->computed_contrast_reserve_category ?? '');
                $best = $this->computeBestMagForExport($r, null);

                $lowerName = strtolower(trim((string) ($r->display_name ?? $r->name ?? '')));
                // Default to deep-sky. Classify solar-system objects (planets, moons,
                // comets) as 1,-1,-1 and stars as 2,-1,-1 using both the source
                // table and the object's type hints.
                $objectId = '4,-1,-1';
                $sourceTable = strtolower(trim((string) ($r->source_table ?? '')));
                if (in_array($sourceTable, ['planets', 'moons', 'cometobjects', 'lunar_features'], true) || in_array($lowerName, $planetNames, true)) {
                    $objectId = '1,-1,-1';
                } else {
                    $rawTypeCodeUpper = strtoupper(trim((string) ($r->obj_type ?? $r->source_type ?? '')));
                    $typeNameUpper = strtoupper(trim((string) ($r->type_name ?? '')));
                    $starCodes = ['DS', 'AA1STAR', 'AA3STAR', 'AA4STAR', 'AA8STAR'];
                    if ($rawTypeCodeUpper !== '' && (in_array($rawTypeCodeUpper, $starCodes, true) || str_contains($rawTypeCodeUpper, 'STAR') || str_contains($typeNameUpper, 'STAR'))) {
                        $objectId = '2,-1,-1';
                    }
                }

                return implode('|', [$name, $ra, $dec, $type, $mag, implode(';', array_filter([$size, $atlas, 'CR ' . ($cr === '' ? '-' : $cr), $best]))]);
            })->values()->all();

            $content = implode("\n", $lines) . "\n";
            $filename = 'search_argo_' . date('Ymd_His') . '.argo';
            return response()->streamDownload(function () use ($content) {
                echo $content; }, $filename, ['Content-Type' => 'text/plain']);
        } catch (\Throwable $ex) {
            Log::error('SearchExportController::argoNavis failed', ['error' => (string) $ex]);
            return redirect()->back()->with('error', __('Failed to generate Argo Navis export'));
        }
    }

    /**
     * Export search results in SkySafari .skylist format.
     */
    public function skylist(Request $request)
    {
        try {
            $q = $request->query('q', '');

            $comp = new \App\Livewire\SearchResultsTable();
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
            $comp->q = $q;

            $query = $comp->datasource();
            $rows = $query ? $query->get() : collect();

            $formatDiameter = function ($d1, $d2, $pa = null) {
                $hasD1 = is_numeric($d1) && floatval($d1) > 0;
                $hasD2 = is_numeric($d2) && floatval($d2) > 0;
                if (!$hasD1 && !$hasD2)
                    return '';
                $d1f = $hasD1 ? floatval($d1) : 0.0;
                $d2f = $hasD2 ? floatval($d2) : 0.0;
                $fmt = function ($v) {
                    return (floor($v) == $v) ? sprintf('%d', $v) : sprintf('%.1f', $v); };
                if ($hasD1 && $hasD2) {
                    if (max($d1f, $d2f) > 60.0) {
                        $d1m = $d1f / 60.0;
                        $d2m = $d2f / 60.0;
                        $d1m_fmt = $fmt($d1m);
                        $d2m_fmt = $fmt($d2m);
                        if ($d1m_fmt === '0' || $d1m_fmt === '0.0' || $d2m_fmt === '0' || $d2m_fmt === '0.0') {
                            $size = $fmt($d1f) . "''x" . $fmt($d2f) . "''";
                        } else {
                            $size = $d1m_fmt . "'x" . $d2m_fmt . "'";
                        }
                    } else {
                        $size = $fmt($d1f) . "''x" . $fmt($d2f) . "''";
                    }
                } else {
                    $single = $hasD1 ? $d1f : $d2f;
                    if ($single > 60.0) {
                        $single_fmt = $fmt($single / 60.0) . "'";
                    } else {
                        $single_fmt = $fmt($single) . "''";
                    }
                    $size = $single_fmt;
                }
                if (is_numeric($pa) && intval(round(floatval($pa))) !== 999) {
                    $size .= '/' . sprintf('%d', round(floatval($pa))) . '°';
                }
                return $size;
            };

            $planetNames = array_map('strtolower', ['Mercury', 'Venus', 'Earth', 'Mars', 'Jupiter', 'Saturn', 'Uranus', 'Neptune', 'Pluto', 'Moon', 'Sun']);

            $blocks = $rows->map(function ($r) use ($formatDiameter, $planetNames) {
                $catalog = trim((string) ($r->display_name ?? $r->name ?? ''));
                $common = '';
                $date = null;
                $size = $formatDiameter($r->diam1 ?? null, $r->diam2 ?? null, $r->pa ?? null);
                $atlas = '';
                $cr = isset($r->computed_contrast_reserve) && is_numeric($r->computed_contrast_reserve) ? number_format(round(floatval($r->computed_contrast_reserve), 2), 2) : ($r->computed_contrast_reserve_category ?? '');
                $best = $this->computeBestMagForExport($r, null);

                $lowerName = strtolower($catalog);
                $objectId = '4,-1,-1';
                $sourceTable = strtolower(trim((string) ($r->source_table ?? '')));
                if (in_array($sourceTable, ['planets', 'moons', 'cometobjects', 'lunar_features'], true) || in_array($lowerName, $planetNames, true)) {
                    $objectId = '1,-1,-1';
                } else {
                    $rawType = strtoupper(trim((string) ($r->obj_type ?? $r->source_type ?? '')));
                    $typeName = strtoupper(trim((string) ($r->type_name ?? '')));
                    $starCodes = ['DS', 'AA1STAR', 'AA3STAR', 'AA4STAR', 'AA8STAR'];
                    if ($rawType !== '' && (in_array($rawType, $starCodes, true) || str_contains($rawType, 'STAR') || str_contains($typeName, 'STAR'))) {
                        $objectId = '2,-1,-1';
                    }
                }

                $lines = [];
                $lines[] = "SkyObject=BeginObject";
                $lines[] = "   ObjectID={$objectId}";
                if ($catalog !== '') {
                    $lines[] = "   CatalogNumber=" . str_replace("\n", "\\n", $catalog);
                }
                if ($common !== '') {
                    $lines[] = "   CommonName=" . str_replace("\n", "\\n", $common);
                }
                if ($date) {
                    $lines[] = "   DateObserved={$date}";
                }
                $lines[] = "EndObject=SkyObject";

                return implode("\n", $lines);
            })->values()->all();

            $content = "SkySafariObservingListVersion=3.0\n" . implode("\n\n", $blocks) . "\n";
            $filename = 'search_objects_' . date('Ymd_His') . '.skylist';
            return response()->streamDownload(function () use ($content) {
                echo $content; }, $filename, ['Content-Type' => 'text/plain']);
        } catch (\Throwable $ex) {
            Log::error('SearchExportController::skylist failed', ['error' => (string) $ex]);
            return redirect()->back()->with('error', __('Failed to generate SkySafari export'));
        }
    }

    /**
     * Export search results as a plain TXT file (one object name per line).
     */
    public function stxt(Request $request)
    {
        try {
            $q = $request->query('q', '');
            $comp = new \App\Livewire\SearchResultsTable();
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
            $comp->q = $q;

            $query = $comp->datasource();
            $rows = $query ? $query->get() : collect();
            $names = $rows->pluck('display_name')->filter()->values();
            $content = implode("\n", $names->all()) . "\n";
            $filename = 'search_objects_' . date('Ymd_His') . '.txt';
            return response()->streamDownload(function () use ($content) {
                echo $content; }, $filename, ['Content-Type' => 'text/plain']);
        } catch (\Throwable $ex) {
            Log::error('SearchExportController::stxt failed', ['error' => (string) $ex]);
            return redirect()->back()->with('error', __('Failed to generate SkyTools TXT export'));
        }
    }

    /**
     * Export search results as an AstroPlanner .apd (SQLite) file.
     */
    public function apd(Request $request)
    {
        try {
            $q = $request->query('q', '');
            $comp = new \App\Livewire\SearchResultsTable();
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
            $comp->q = $q;

            $query = $comp->datasource();
            $rows = $query ? $query->get() : collect();

            // Create temporary sqlite file
            $tmp = tempnam(sys_get_temp_dir(), 'dsl_apd_');
            if ($tmp === false)
                throw new \RuntimeException('Failed to create temporary file for APD export');
            $db = new \PDO('sqlite:' . $tmp);
            $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            $db->exec("CREATE TABLE Objects (Number integer PRIMARY KEY, ID varchar, Name varchar, Type varchar, RA double, Dec double, Magnitude double, Magnitude2 double, Separation double, PosAngle integer, Size varchar, Catalogue varchar, Notes varchar, Comp varchar, CatNotes varchar, CatIdx integer, SortOrder integer, Period double, Force integer, UserDefined varchar, ObsID varchar, Association integer, AssociationSeqNumber integer, AssociatedData varchar, Origin varchar, Spect varchar, Orbits VarChar);");
            $db->exec("CREATE TABLE Prefs (ID Integer Primary Key Autoincrement, Name text, Value text);");
            $db->exec("CREATE TABLE ID (UID Text);");

            $insert = $db->prepare('INSERT INTO Objects (Number, ID, Name, Type, RA, Dec, Magnitude, Size, Catalogue, Notes) VALUES (:num, :id, :name, :type, :ra, :dec, :mag, :size, :cat, :notes)');

            $i = 1;
            foreach ($rows as $r) {
                $name = $r->display_name ?? $r->name ?? '';
                $id = $r->search_index_id ?? $r->display_name ?? $name;
                $type = $r->type_name ?? $r->source_type ?? '';
                $ra = is_numeric($r->obj_ra ?? $r->ra) ? floatval($r->obj_ra ?? $r->ra) : null;
                $dec = is_numeric($r->obj_decl ?? $r->decl) ? floatval($r->obj_decl ?? $r->decl) : null;
                $mag = (is_numeric($r->mag) && floatval($r->mag) != 99.9) ? floatval($r->mag) : null;
                $size = '';
                try {
                    $d1 = $r->diam1 ?? null;
                    $d2 = $r->diam2 ?? null;
                    $pa = $r->pa ?? null;
                    $size = $this->formatSizeForApd((object) ['diam1' => $d1, 'diam2' => $d2, 'pa' => $pa]);
                } catch (\Throwable $_) {
                    $size = '';
                }
                $catalogue = '';
                $notes = '';

                $insert->execute([':num' => $i, ':id' => $id, ':name' => $name, ':type' => $type, ':ra' => $ra, ':dec' => $dec, ':mag' => $mag, ':size' => $size, ':cat' => $catalogue, ':notes' => $notes]);
                $i++;
            }

            $db = null;
            $filename = 'search_objects_' . date('Ymd_His') . '.apd';
            return response()->streamDownload(function () use ($tmp) {
                readfile($tmp); }, $filename, ['Content-Type' => 'application/x-sqlite3']);
        } catch (\Throwable $ex) {
            Log::error('SearchExportController::apd failed', ['error' => (string) $ex]);
            return redirect()->back()->with('error', __('Failed to generate AstroPlanner APD export'));
        }
    }

    // --- Helper utilities copied/adapted for exports ---

    private function formatRAColon($ra): string
    {
        if ($ra === null || $ra === '')
            return '';
        if (!is_numeric($ra))
            return (string) $ra;
        $v = floatval($ra);
        if ($v > 24.0) {
            $hours = $v / 15.0;
        } else {
            $hours = $v;
        }
        $hours = fmod($hours, 24.0);
        if ($hours < 0)
            $hours += 24.0;
        $h = floor($hours);
        $mFloat = ($hours - $h) * 60.0;
        $m = floor($mFloat);
        $s = round(($mFloat - $m) * 60.0);
        if ($s >= 60) {
            $s -= 60;
            $m += 1;
        }
        if ($m >= 60) {
            $m -= 60;
            $h += 1;
        }
        $h = $h % 24;
        return sprintf('%02d:%02d:%02d', $h, $m, $s);
    }

    private function formatDecColon($dec): string
    {
        if ($dec === null || $dec === '')
            return '';
        if (!is_numeric($dec))
            return (string) $dec;
        $v = floatval($dec);
        $sign = ($v < 0) ? '-' : '+';
        $abs = abs($v);
        $d = floor($abs);
        $mFloat = ($abs - $d) * 60.0;
        $m = floor($mFloat);
        $s = round(($mFloat - $m) * 60.0);
        if ($s >= 60) {
            $s -= 60;
            $m += 1;
        }
        if ($m >= 60) {
            $m -= 60;
            $d += 1;
        }
        return sprintf('%s%02d:%02d:%02d', $sign, $d, $m, $s);
    }

    private function computeBestMagForExport($row, $comp = null): string
    {
        try {
            if (isset($row->optimum_detection_magnification) && is_numeric($row->optimum_detection_magnification)) {
                return (int) $row->optimum_detection_magnification . 'x';
            }
            // Minimal fallback: prefer computed_best_mag if present
            if (isset($row->computed_best_mag) && is_numeric($row->computed_best_mag)) {
                return (int) $row->computed_best_mag . 'x';
            }
            return '-';
        } catch (\Throwable $_) {
            return '-';
        }
    }

    private function formatSizeForApd($r): string
    {
        $d1 = $r->diam1 ?? null;
        $d2 = $r->diam2 ?? null;
        $pa = $r->pa ?? null;
        $hasD1 = is_numeric($d1) && floatval($d1) > 0;
        $hasD2 = is_numeric($d2) && floatval($d2) > 0;
        if (!$hasD1 && !$hasD2)
            return '';
        $d1f = $hasD1 ? floatval($d1) : 0.0;
        $d2f = $hasD2 ? floatval($d2) : 0.0;
        $fmt = function ($v) {
            return (floor($v) == $v) ? sprintf('%d', $v) : sprintf('%.1f', $v); };
        if ($hasD1 && $hasD2) {
            if (max($d1f, $d2f) > 60.0) {
                $d1m = $d1f / 60.0;
                $d2m = $d2f / 60.0;
                $d1m_fmt = $fmt($d1m);
                $d2m_fmt = $fmt($d2m);
                if ($d1m_fmt === '0' || $d1m_fmt === '0.0' || $d2m_fmt === '0' || $d2m_fmt === '0.0') {
                    $size = $fmt($d1f) . "''x" . $fmt($d2f) . "''";
                } else {
                    $size = $d1m_fmt . "'x" . $d2m_fmt . "'";
                }
            } else {
                $size = $fmt($d1f) . "''x" . $fmt($d2f) . "''";
            }
        } else {
            $single = $hasD1 ? $d1f : $d2f;
            if ($single > 60.0) {
                $single_fmt = $fmt($single / 60.0) . "'";
            } else {
                $single_fmt = $fmt($single) . "''";
            }
            $size = $single_fmt;
        }
        if (is_numeric($pa) && intval(round(floatval($pa))) !== 999) {
            $size .= '/' . sprintf('%d', round(floatval($pa))) . '°';
        }
        return (string) $size;
    }
}
