<?php

namespace App\Http\Controllers;

use App\Livewire\AdvancedObjectSearchTable;
use App\Models\ObservingList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ObservingListExportController extends Controller
{
    public function namesPdf(Request $request, ObservingList $list)
    {
        try {
            $this->authorize('view', $list);

            [, $rows] = $this->resolveRows($list);

            $names = $rows->pluck('display_name')->filter()->values();
            $title = __('Objects in observing list ":name"', ['name' => $list->name]);
            $html = view('pdf.nearby_names', ['names' => $names, 'title' => $title])->render();

            return $this->streamPdf(
                $html,
                $this->buildExportFilename($list, 'pdf', true),
                'portrait',
                __('Failed to generate observing list names PDF')
            );
        } catch (\Throwable $ex) {
            Log::error('ObservingListExportController::namesPdf failed', ['list_id' => $list->id ?? null, 'error' => (string) $ex]);

            return redirect()->back()->with('error', __('Failed to generate observing list names PDF'));
        }
    }

    public function tablePdf(Request $request, ObservingList $list)
    {
        try {
            $this->authorize('view', $list);

            [$comp, $rows] = $this->resolveRows($list);

            $data = $rows->map(function ($row) use ($comp) {
                return [
                    'name' => $row->display_name ?? $row->name ?? '',
                    'ra' => $this->formatRAColon($row->obj_ra ?? $row->ra ?? null),
                    'decl' => $this->formatDecColon($row->obj_decl ?? $row->decl ?? null),
                    'type' => $row->type_name ?? $row->source_type ?? '',
                    'constellation' => $row->constellation ?? '',
                    'mag' => (is_numeric($row->mag) && floatval($row->mag) != 99.9 && floatval($row->mag) != 0.0) ? (string) $row->mag : '-',
                    'sb' => (is_numeric($row->subr) && floatval($row->subr) != 99.9 && floatval($row->subr) != 0.0) ? (string) $row->subr : '-',
                    'diameter' => $this->formatDiameter($row->diam1 ?? null, $row->diam2 ?? null, $row->pa ?? null),
                    'atlas_page' => $row->atlas_page ?? '-',
                    'cr' => isset($row->computed_contrast_reserve) && is_numeric($row->computed_contrast_reserve)
                        ? number_format(round(floatval($row->computed_contrast_reserve), 2), 2)
                        : ($row->computed_contrast_reserve_category ?? '-'),
                    'best_mag' => $this->computeBestMagForExport($row, $comp),
                    'seen' => isset($row->total_observations) ? (string) $row->total_observations : '-',
                    'last_seen' => $row->your_last_seen_date ?? '-',
                ];
            })->toArray();

            $title = __('Observing list ":name"', ['name' => $list->name]);
            $html = view('pdf.nearby_objects_table', ['rows' => $data, 'title' => $title])->render();

            return $this->streamPdf(
                $html,
                $this->buildExportFilename($list, 'pdf'),
                'landscape',
                __('Failed to generate observing list PDF')
            );
        } catch (\Throwable $ex) {
            Log::error('ObservingListExportController::tablePdf failed', ['list_id' => $list->id ?? null, 'error' => (string) $ex]);

            return redirect()->back()->with('error', __('Failed to generate observing list PDF'));
        }
    }

    public function argoNavis(Request $request, ObservingList $list)
    {
        try {
            $this->authorize('view', $list);

            [, $rows] = $this->resolveRows($list);

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

            $lines = $rows->map(function ($row) use ($typeMap, $planetNames) {
                $name = 'DSL ' . ($row->display_name ?? $row->name ?? '');
                $ra = $this->formatRAColon($row->obj_ra ?? $row->ra ?? null);
                $dec = $this->formatDecColon($row->obj_decl ?? $row->decl ?? null);

                $rawTypeCode = strtoupper(trim((string) ($row->obj_type ?? $row->source_type ?? '')));
                if ($rawTypeCode !== '' && isset($typeMap[$rawTypeCode])) {
                    $type = $typeMap[$rawTypeCode];
                } else {
                    $typeName = $row->type_name ?? '';
                    $type = $typeName ? strtoupper(trim((string) $typeName)) : 'USER';
                }

                $mag = (is_numeric($row->mag) && floatval($row->mag) != 99.9 && floatval($row->mag) != 0.0) ? (string) $row->mag : '';
                $size = $this->formatDiameter($row->diam1 ?? null, $row->diam2 ?? null, $row->pa ?? null);
                $cr = isset($row->computed_contrast_reserve) && is_numeric($row->computed_contrast_reserve)
                    ? number_format(round(floatval($row->computed_contrast_reserve), 2), 2)
                    : ($row->computed_contrast_reserve_category ?? '');
                $best = $this->computeBestMagForExport($row);

                $lowerName = strtolower(trim((string) ($row->display_name ?? $row->name ?? '')));
                $objectId = '4,-1,-1';
                $sourceTable = strtolower(trim((string) ($row->source_table ?? '')));
                if (in_array($sourceTable, ['planets', 'moons', 'cometobjects', 'lunar_features'], true) || in_array($lowerName, $planetNames, true)) {
                    $objectId = '1,-1,-1';
                } else {
                    $typeNameUpper = strtoupper(trim((string) ($row->type_name ?? '')));
                    $starCodes = ['DS', 'AA1STAR', 'AA3STAR', 'AA4STAR', 'AA8STAR'];
                    if ($rawTypeCode !== '' && (in_array($rawTypeCode, $starCodes, true) || str_contains($rawTypeCode, 'STAR') || str_contains($typeNameUpper, 'STAR'))) {
                        $objectId = '2,-1,-1';
                    }
                }

                return implode('|', [$name, $ra, $dec, $type, $mag, implode(';', array_filter([$size, 'CR ' . ($cr === '' ? '-' : $cr), $best]))]);
            })->values()->all();

            $content = implode("\n", $lines) . "\n";

            return response()->streamDownload(function () use ($content) {
                echo $content;
            }, $this->buildExportFilename($list, 'argo'), ['Content-Type' => 'text/plain']);
        } catch (\Throwable $ex) {
            Log::error('ObservingListExportController::argoNavis failed', ['list_id' => $list->id ?? null, 'error' => (string) $ex]);

            return redirect()->back()->with('error', __('Failed to generate Argo Navis export'));
        }
    }

    public function skylist(Request $request, ObservingList $list)
    {
        try {
            $this->authorize('view', $list);

            [, $rows] = $this->resolveRows($list);

            $planetNames = array_map('strtolower', ['Mercury', 'Venus', 'Earth', 'Mars', 'Jupiter', 'Saturn', 'Uranus', 'Neptune', 'Pluto', 'Moon', 'Sun']);

            $blocks = $rows->map(function ($row) use ($planetNames) {
                $catalog = trim((string) ($row->display_name ?? $row->name ?? ''));
                $lowerName = strtolower($catalog);
                $objectId = '4,-1,-1';
                $sourceTable = strtolower(trim((string) ($row->source_table ?? '')));

                if (in_array($sourceTable, ['planets', 'moons', 'cometobjects', 'lunar_features'], true) || in_array($lowerName, $planetNames, true)) {
                    $objectId = '1,-1,-1';
                } else {
                    $rawType = strtoupper(trim((string) ($row->obj_type ?? $row->source_type ?? '')));
                    $typeName = strtoupper(trim((string) ($row->type_name ?? '')));
                    $starCodes = ['DS', 'AA1STAR', 'AA3STAR', 'AA4STAR', 'AA8STAR'];
                    if ($rawType !== '' && (in_array($rawType, $starCodes, true) || str_contains($rawType, 'STAR') || str_contains($typeName, 'STAR'))) {
                        $objectId = '2,-1,-1';
                    }
                }

                $lines = [
                    'SkyObject=BeginObject',
                    '   ObjectID=' . $objectId,
                ];

                if ($catalog !== '') {
                    $lines[] = '   CatalogNumber=' . str_replace("\n", '\\n', $catalog);
                }

                $lines[] = 'EndObject=SkyObject';

                return implode("\n", $lines);
            })->values()->all();

            $content = "SkySafariObservingListVersion=3.0\n" . implode("\n\n", $blocks) . "\n";

            return response()->streamDownload(function () use ($content) {
                echo $content;
            }, $this->buildExportFilename($list, 'skylist'), ['Content-Type' => 'text/plain']);
        } catch (\Throwable $ex) {
            Log::error('ObservingListExportController::skylist failed', ['list_id' => $list->id ?? null, 'error' => (string) $ex]);

            return redirect()->back()->with('error', __('Failed to generate SkySafari export'));
        }
    }

    public function stxt(Request $request, ObservingList $list)
    {
        try {
            $this->authorize('view', $list);

            [, $rows] = $this->resolveRows($list);

            $names = $rows->pluck('display_name')->filter()->values();
            $content = implode("\n", $names->all()) . "\n";

            return response()->streamDownload(function () use ($content) {
                echo $content;
            }, $this->buildExportFilename($list, 'txt'), ['Content-Type' => 'text/plain']);
        } catch (\Throwable $ex) {
            Log::error('ObservingListExportController::stxt failed', ['list_id' => $list->id ?? null, 'error' => (string) $ex]);

            return redirect()->back()->with('error', __('Failed to generate SkyTools TXT export'));
        }
    }

    public function apd(Request $request, ObservingList $list)
    {
        try {
            $this->authorize('view', $list);

            [, $rows] = $this->resolveRows($list);

            $tmp = tempnam(sys_get_temp_dir(), 'dsl_observing_list_apd_');
            if ($tmp === false) {
                throw new \RuntimeException('Failed to create temporary file for APD export');
            }

            $db = new \PDO('sqlite:' . $tmp);
            $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $db->exec("CREATE TABLE Objects (Number integer PRIMARY KEY, ID varchar, Name varchar, Type varchar, RA double, Dec double, Magnitude double, Magnitude2 double, Separation double, PosAngle integer, Size varchar, Catalogue varchar, Notes varchar, Comp varchar, CatNotes varchar, CatIdx integer, SortOrder integer, Period double, Force integer, UserDefined varchar, ObsID varchar, Association integer, AssociationSeqNumber integer, AssociatedData varchar, Origin varchar, Spect varchar, Orbits VarChar);");
            $db->exec("CREATE TABLE Prefs (ID Integer Primary Key Autoincrement, Name text, Value text);");
            $db->exec("CREATE TABLE ID (UID Text);");

            $insert = $db->prepare('INSERT INTO Objects (Number, ID, Name, Type, RA, Dec, Magnitude, Size, Catalogue, Notes) VALUES (:num, :id, :name, :type, :ra, :dec, :mag, :size, :cat, :notes)');

            $index = 1;
            foreach ($rows as $row) {
                $name = $row->display_name ?? $row->name ?? '';
                $insert->execute([
                    ':num' => $index,
                    ':id' => $row->search_index_id ?? $row->display_name ?? $name,
                    ':name' => $name,
                    ':type' => $row->type_name ?? $row->source_type ?? '',
                    ':ra' => is_numeric($row->obj_ra ?? $row->ra) ? floatval($row->obj_ra ?? $row->ra) : null,
                    ':dec' => is_numeric($row->obj_decl ?? $row->decl) ? floatval($row->obj_decl ?? $row->decl) : null,
                    ':mag' => (is_numeric($row->mag) && floatval($row->mag) != 99.9) ? floatval($row->mag) : null,
                    ':size' => $this->formatSizeForApd((object) ['diam1' => $row->diam1 ?? null, 'diam2' => $row->diam2 ?? null, 'pa' => $row->pa ?? null]),
                    ':cat' => '',
                    ':notes' => '',
                ]);

                $index++;
            }

            $db = null;

            return response()->streamDownload(function () use ($tmp) {
                readfile($tmp);
            }, $this->buildExportFilename($list, 'apd'), ['Content-Type' => 'application/x-sqlite3']);
        } catch (\Throwable $ex) {
            Log::error('ObservingListExportController::apd failed', ['list_id' => $list->id ?? null, 'error' => (string) $ex]);

            return redirect()->back()->with('error', __('Failed to generate AstroPlanner APD export'));
        }
    }

    private function resolveRows(ObservingList $list): array
    {
        $component = new AdvancedObjectSearchTable();

        if (method_exists($component, 'boot')) {
            try {
                $component->boot();
            } catch (\Throwable $_) {
            }
        }

        if (method_exists($component, 'mount')) {
            try {
                $component->mount([
                    'observing_lists' => [(string) $list->id],
                    'observing_lists_mode' => 'in',
                ]);
            } catch (\Throwable $_) {
            }
        }

        $query = $component->datasource();
        $rows = $query ? $query->get() : collect();

        return [$component, $rows];
    }

    private function streamPdf(string $html, string $filename, string $orientation, string $fallbackMessage)
    {
        if (class_exists('\\Barryvdh\\DomPDF\\Facade\\Pdf')) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper('a4', $orientation);

            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $filename, ['Content-Type' => 'application/pdf']);
        }

        if (class_exists('Dompdf\\Dompdf')) {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', $orientation);
            $dompdf->render();
            $output = $dompdf->output();

            return response()->streamDownload(function () use ($output) {
                echo $output;
            }, $filename, ['Content-Type' => 'application/pdf']);
        }

        return redirect()->back()->with('error', $fallbackMessage);
    }

    private function formatDiameter($diam1, $diam2, $pa = null): string
    {
        $hasDiam1 = is_numeric($diam1) && floatval($diam1) > 0;
        $hasDiam2 = is_numeric($diam2) && floatval($diam2) > 0;

        if (!$hasDiam1 && !$hasDiam2) {
            return '';
        }

        $diam1Value = $hasDiam1 ? floatval($diam1) : 0.0;
        $diam2Value = $hasDiam2 ? floatval($diam2) : 0.0;
        $formatter = static function ($value) {
            return floor($value) == $value ? sprintf('%d', $value) : sprintf('%.1f', $value);
        };

        if ($hasDiam1 && $hasDiam2) {
            if (max($diam1Value, $diam2Value) > 60.0) {
                $diam1Minutes = $diam1Value / 60.0;
                $diam2Minutes = $diam2Value / 60.0;
                $diam1Formatted = $formatter($diam1Minutes);
                $diam2Formatted = $formatter($diam2Minutes);

                if (in_array($diam1Formatted, ['0', '0.0'], true) || in_array($diam2Formatted, ['0', '0.0'], true)) {
                    $size = $formatter($diam1Value) . "''x" . $formatter($diam2Value) . "''";
                } else {
                    $size = $diam1Formatted . "'x" . $diam2Formatted . "'";
                }
            } else {
                $size = $formatter($diam1Value) . "''x" . $formatter($diam2Value) . "''";
            }
        } else {
            $single = $hasDiam1 ? $diam1Value : $diam2Value;
            $size = $single > 60.0 ? $formatter($single / 60.0) . "'" : $formatter($single) . "''";
        }

        if (is_numeric($pa) && intval(round(floatval($pa))) !== 999) {
            $size .= '/' . sprintf('%d', round(floatval($pa))) . '°';
        }

        return $size;
    }

    private function formatRAColon($ra): string
    {
        if ($ra === null || $ra === '') {
            return '';
        }

        if (!is_numeric($ra)) {
            return (string) $ra;
        }

        $value = floatval($ra);
        $hours = $value > 24.0 ? $value / 15.0 : $value;
        $hours = fmod($hours, 24.0);

        if ($hours < 0) {
            $hours += 24.0;
        }

        $hourPart = floor($hours);
        $minuteFloat = ($hours - $hourPart) * 60.0;
        $minutePart = floor($minuteFloat);
        $secondPart = round(($minuteFloat - $minutePart) * 60.0);

        if ($secondPart >= 60) {
            $secondPart -= 60;
            $minutePart += 1;
        }
        if ($minutePart >= 60) {
            $minutePart -= 60;
            $hourPart += 1;
        }

        return sprintf('%02d:%02d:%02d', $hourPart % 24, $minutePart, $secondPart);
    }

    private function formatDecColon($dec): string
    {
        if ($dec === null || $dec === '') {
            return '';
        }

        if (!is_numeric($dec)) {
            return (string) $dec;
        }

        $value = floatval($dec);
        $sign = $value < 0 ? '-' : '+';
        $absolute = abs($value);
        $degreePart = floor($absolute);
        $minuteFloat = ($absolute - $degreePart) * 60.0;
        $minutePart = floor($minuteFloat);
        $secondPart = round(($minuteFloat - $minutePart) * 60.0);

        if ($secondPart >= 60) {
            $secondPart -= 60;
            $minutePart += 1;
        }
        if ($minutePart >= 60) {
            $minutePart -= 60;
            $degreePart += 1;
        }

        return sprintf('%s%02d:%02d:%02d', $sign, $degreePart, $minutePart, $secondPart);
    }

    private function computeBestMagForExport($row, $component = null): string
    {
        try {
            if (isset($row->optimum_detection_magnification) && is_numeric($row->optimum_detection_magnification)) {
                return (int) $row->optimum_detection_magnification . 'x';
            }
            if (isset($row->computed_best_mag) && is_numeric($row->computed_best_mag)) {
                return (int) $row->computed_best_mag . 'x';
            }

            return '-';
        } catch (\Throwable $_) {
            return '-';
        }
    }

    private function formatSizeForApd($row): string
    {
        return $this->formatDiameter($row->diam1 ?? null, $row->diam2 ?? null, $row->pa ?? null);
    }

    private function buildExportFilename(ObservingList $list, string $extension, bool $namesOnly = false): string
    {
        $baseName = trim(Str::ascii((string) $list->name));
        $baseName = preg_replace('/[^A-Za-z0-9]+/', '_', $baseName ?? '') ?? '';
        $baseName = trim($baseName, '_');

        if ($baseName === '') {
            $baseName = 'observing_list_' . $list->id;
        }

        if ($namesOnly) {
            $baseName .= '_names';
        }

        return $baseName . '.' . ltrim($extension, '.');
    }
}