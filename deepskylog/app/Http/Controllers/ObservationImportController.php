<?php

namespace App\Http\Controllers;

use App\Jobs\NotifyImportedObjects;
use App\Services\Import\CsvObservationImporter;
use App\Services\Import\ImportDrawings;
use App\Services\Import\OalObservationImporter;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;

class ObservationImportController extends Controller
{
    /** The most drawings uploaded next to the file (PHP's max_file_uploads is 20 by default). */
    public const MAX_LOOSE_DRAWINGS = 19;

    /**
     * Show the import form.
     */
    public function showForm()
    {
        return view('observations.import');
    }

    /**
     * Handle the uploaded file and run the appropriate importer. The file is
     * a CSV or OAL file, with its drawings uploaded next to it, or a zip file
     * holding one of them and the drawings (#817).
     */
    public function import(Request $request)
    {
        $isZip = self::isZip($request->file('import_file'));

        $request->validate([
            'import_file' => [
                'required',
                'file',
                $isZip ? 'max:1048576' : 'max:51200', // 1 GB, 50 MB
                'mimes:csv,txt,xml,zip,application/xml,text/xml,text/plain,text/csv',
            ],
            'drawings' => ['nullable', 'array', 'max:' . self::MAX_LOOSE_DRAWINGS],
            'drawings.*' => ['file', 'mimes:jpg,jpeg,png,gif,webp', 'max:' . ImportDrawings::MAX_DRAWING_BYTES / 1024],
            'import_below_horizon' => ['nullable', 'boolean'],
        ]);

        $file = $request->file('import_file');
        $uploads = $request->file('drawings', []);

        if ($isZip) {
            if ($uploads !== []) {
                return back()->withErrors(['drawings' => __('Put the drawings in the zip file, next to the CSV or OAL file.')]);
            }

            try {
                [$drawings, $content] = ImportDrawings::fromZip($file->getRealPath());
            } catch (\RuntimeException $e) {
                return back()->withErrors(['import_file' => $e->getMessage()]);
            }
        } else {
            $content = file_get_contents($file->getRealPath());
            $drawings = ImportDrawings::fromUploads($uploads);
        }

        if ($content === false || empty($content)) {
            return back()->withErrors(['import_file' => __('The uploaded file is empty or could not be read.')]);
        }

        $user = $request->user();
        $format = self::detectFormat($content);

        $importer = $format === 'csv' ? new CsvObservationImporter($user) : new OalObservationImporter($user);
        $result = $importer->importBelowHorizon($request->boolean('import_below_horizon'))
            ->withDrawings($drawings)
            ->import($content);

        // The administrators and database experts check the objects an import added.
        if ($result->newObjects !== []) {
            NotifyImportedObjects::dispatch($user->username, $result->newObjects);
        }

        return view('observations.import-result', [
            'result' => $result,
            'format' => $format,
            'userSlug' => Auth::user()->slug,
        ]);
    }

    /**
     * Whether an uploaded file is a zip file: it starts with the signature of
     * a zip file's first entry.
     */
    public static function isZip(?UploadedFile $file): bool
    {
        if ($file === null || ! $file->isValid()) {
            return false;
        }

        $handle = @fopen($file->getRealPath(), 'rb');
        if ($handle === false) {
            return false;
        }
        $signature = fread($handle, 4);
        fclose($handle);

        return $signature === "PK\x03\x04";
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
