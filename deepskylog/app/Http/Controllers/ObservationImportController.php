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
            'format' => ['required', 'in:csv,oal'],
        ]);

        $file = $request->file('import_file');
        $content = file_get_contents($file->getRealPath());

        if ($content === false || empty($content)) {
            return back()->withErrors(['import_file' => __('The uploaded file is empty or could not be read.')]);
        }

        $user = $request->user();
        $format = $request->input('format');

        if ($format === 'csv') {
            $importer = new CsvObservationImporter($user);
            $result = $importer->import($content);
        } else {
            $importer = new OalObservationImporter($user);
            $result = $importer->import($content);
        }

        return view('observations.import-result', [
            'result' => $result,
            'format' => $format,
            'userSlug' => Auth::user()->slug,
        ]);
    }
}
