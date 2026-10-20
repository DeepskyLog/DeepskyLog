<?php

namespace App\Http\Controllers;

use App\Services\Import\CsvObservationImporter;
use App\Services\Import\OalObservationImporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ObservationImportController extends Controller
{
    /**
     * Show the import form.
     */
    public function showForm()
    {
        return view('observations.import');
    }

    /**
     * Handle the uploaded file and run the appropriate importer.
     */
    public function import(Request $request)
    {
        $request->validate([
            'import_file' => [
                'required',
                'file',
                'max:51200', // 50 MB
                'mimes:csv,txt,xml,application/xml,text/xml,text/plain,text/csv',
            ],
            'import_below_horizon' => ['nullable', 'boolean'],
        ]);

        $file = $request->file('import_file');
        $content = file_get_contents($file->getRealPath());

        if ($content === false || empty($content)) {
            return back()->withErrors(['import_file' => __('The uploaded file is empty or could not be read.')]);
        }

        $user = $request->user();
        $format = self::detectFormat($content);

        $importer = $format === 'csv' ? new CsvObservationImporter($user) : new OalObservationImporter($user);
        $result = $importer->importBelowHorizon($request->boolean('import_below_horizon'))->import($content);

        return view('observations.import-result', [
            'result' => $result,
            'format' => $format,
            'userSlug' => Auth::user()->slug,
        ]);
    }

    /**
     * The format of an uploaded file: an OAL file is XML, so after a byte
     * order mark and white space it starts with "<" (the XML declaration or
     * the root element); a CSV row starts with the name of an object.
     */
    public static function detectFormat(string $content): string
    {
        $start = ltrim((string) preg_replace('/^\xEF\xBB\xBF/', '', $content));

        return str_starts_with($start, '<') ? 'oal' : 'csv';
    }
}
