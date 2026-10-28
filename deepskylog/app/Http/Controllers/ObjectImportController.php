<?php

namespace App\Http\Controllers;

use App\Livewire\ObjectImport;
use App\Services\Import\ObjectCsvImport;
use App\Support\SpreadsheetCell;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Checks a CSV file of objects for /admin/objects/import (#131, #136) and
 * downloads the report of the check. The checked rows are kept in the cache
 * (ObjectImport::CACHE_PREFIX . token) for the page, which shows them and
 * imports them. The file is posted to this controller, as the Livewire
 * uploads only take images.
 */
class ObjectImportController extends Controller
{
    public function check(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'max:10240', 'mimes:csv,txt'],
            'position' => ['required', 'numeric', 'min:0', 'max:3600'],
            'magnitude' => ['required', 'numeric', 'min:0', 'max:10'],
            'size' => ['required', 'numeric', 'min:0', 'max:1000'],
            'duplicate' => ['required', 'numeric', 'min:0', 'max:3600'],
        ]);

        $import = new ObjectCsvImport();
        $read = $import->read((string) file_get_contents($request->file('file')->getRealPath()));
        if ($read['errors'] !== []) {
            return back()->withErrors(['file' => implode(' ', $read['errors'])])->withInput();
        }

        $settings = array_map('floatval', array_intersect_key($validated, ObjectCsvImport::DEFAULT_SETTINGS));
        $token = (string) Str::uuid();
        Cache::put(ObjectImport::CACHE_PREFIX . $token, [
            'user_id' => Auth::id(),
            'file' => $request->file('file')->getClientOriginalName(),
            'settings' => $settings,
            'rows' => $import->check($read['rows'], $settings),
        ], now()->addHours(ObjectImport::KEEP_HOURS));

        return redirect()->route('admin.objects.import', ['token' => $token]);
    }

    /**
     * Every row of a checked file with its status, differences, new names
     * and nearby objects, as CSV.
     */
    public function report(string $token): StreamedResponse
    {
        $checked = ObjectImport::checkedFile($token);
        abort_if($checked === null, 404);

        $filename = pathinfo((string) $checked['file'], PATHINFO_FILENAME) . '-report.csv';

        return response()->streamDownload(function () use ($checked) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['line', 'name', 'status', 'object', 'differences', 'new_names', 'possible_duplicate', 'nearby', 'errors', 'warnings'], ',', '"', '');
            foreach ($checked['rows'] as $row) {
                $differences = [];
                foreach ($row['differences'] as $field => $difference) {
                    $differences[] = "{$field}: {$difference['file']} / {$difference['deepskylog']}" . ($difference['difference'] !== '' ? " ({$difference['difference']})" : '');
                }
                $nearby = array_map(fn (array $o) => "{$o['name']} ({$o['type']}, {$o['separation']}')", $row['nearby']);

                fputcsv($out, SpreadsheetCell::escapeRow([
                    $row['line'],
                    $row['name'],
                    $row['status'],
                    $row['object'] ?? '',
                    implode('; ', $differences),
                    implode('; ', $row['names']),
                    $row['suggestion'] ?? '',
                    implode('; ', $nearby),
                    implode(' ', $row['errors']),
                    implode(' ', $row['warnings']),
                ]), ',', '"', '');
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
