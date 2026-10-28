<?php

namespace App\Http\Controllers;

use App\Jobs\NotifyImportedObjects;
use App\Models\User;
use App\Services\Import\CsvObservationImporter;
use App\Services\Import\ImportDrawings;
use App\Services\Import\OalObservationImporter;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ObservationImportController extends Controller
{
    /** The most drawings uploaded next to the file (PHP's max_file_uploads is 20 by default). */
    public const MAX_LOOSE_DRAWINGS = 19;

    /** How long an upload is kept while the observer chooses what to do with its new targets. */
    public const KEEP_HOURS = 24;

    /** Where the uploads are kept, on the local disk. */
    public const DIRECTORY = 'observation-imports';

    private const CACHE_PREFIX = 'observation_import:';

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

        // The observer chooses what to do with the targets of an OAL file that
        // are not in the database before anything is imported (#221). A CSV
        // file never adds objects.
        if ($format === 'oal' && ($targets = (new OalObservationImporter($user))->newTargets($content)) !== []) {
            unset($drawings);
            $token = $this->keep($request, $isZip, $targets);

            return redirect()->route('observation.import.objects', $token);
        }

        return $this->run($user, $format, $content, $drawings, $request->boolean('import_below_horizon'));
    }

    /**
     * The targets of a kept upload that are not in the database, with the
     * objects near them, to choose what to do with them.
     */
    public function objects(Request $request, string $token)
    {
        $pending = $this->pending($request, $token);

        return view('observations.import-objects', [
            'token' => $token,
            'fileName' => $pending['file_name'],
            'targets' => $pending['targets'],
        ]);
    }

    /**
     * Import a kept upload with the choices for its new targets.
     */
    public function importWithObjects(Request $request, string $token)
    {
        $pending = $this->pending($request, $token);
        $targets = $pending['targets'];
        $user = $request->user();

        $request->validate([
            'choice' => ['required', 'array'],
            'choice.*' => ['required', 'string'],
            'other' => ['nullable', 'array'],
            'other.*' => ['nullable', 'string', 'max:255'],
        ]);

        $importer = new OalObservationImporter($user);
        $choices = [];
        $errors = [];
        foreach ($targets as $i => $target) {
            $choice = (string) $request->input("choice.{$i}", '');
            $nearby = array_column($target['nearby'], 'name');

            if ($choice === 'new' || $choice === 'skip') {
                $choices[$target['name']] = ['action' => $choice];
            } elseif (str_starts_with($choice, 'object:') && in_array($object = substr($choice, 7), $nearby, true)) {
                $choices[$target['name']] = ['action' => 'object', 'object' => $object];
            } elseif ($choice === 'other') {
                $other = trim((string) $request->input("other.{$i}", ''));
                $object = $other !== '' ? $importer->findObject($other) : null;
                if ($object === null) {
                    $errors["other.{$i}"] = $other === ''
                        ? __('Give the name of the object :target is, or choose another option.', ['target' => $target['name']])
                        : __(':name is not in the DeepskyLog database.', ['name' => $other]);
                } else {
                    $choices[$target['name']] = ['action' => 'object', 'object' => $object];
                }
            } else {
                $errors["choice.{$i}"] = __('Choose what to do with :target.', ['target' => $target['name']]);
            }
        }

        if ($errors !== []) {
            return back()->withErrors($errors)->withInput();
        }

        $directory = self::DIRECTORY . '/' . $token;
        $disk = Storage::disk('local');

        if ($pending['is_zip']) {
            try {
                [$drawings, $content] = ImportDrawings::fromZip($disk->path("{$directory}/import"));
            } catch (\RuntimeException $e) {
                $this->forget($token);

                return redirect()->route('observation.import')->withErrors(['import_file' => $e->getMessage()]);
            }
        } else {
            $content = (string) $disk->get("{$directory}/import");
            $drawings = ImportDrawings::fromUploads(array_map(
                fn (int $i, string $name) => new UploadedFile($disk->path("{$directory}/drawings/{$i}"), $name, null, null, true),
                array_keys($pending['drawings']),
                $pending['drawings'],
            ));
        }

        $response = $this->run($user, 'oal', $content, $drawings, $pending['below_horizon'], $choices);

        unset($drawings);
        $this->forget($token);

        return $response;
    }

    /**
     * Throw away a kept upload.
     */
    public function cancel(Request $request, string $token)
    {
        $this->pending($request, $token);
        $this->forget($token);

        return redirect()->route('observation.import')->with('status', __('The import was cancelled.'));
    }

    /**
     * Run the importer and show the result.
     *
     * @param  array<string, array{action: string, object?: string}>|null  $choices
     */
    private function run(User $user, string $format, string $content, ImportDrawings $drawings, bool $belowHorizon, ?array $choices = null)
    {
        $importer = $format === 'csv' ? new CsvObservationImporter($user) : new OalObservationImporter($user);
        if ($choices !== null) {
            $importer->withObjectChoices($choices);
        }
        $result = $importer->importBelowHorizon($belowHorizon)
            ->withDrawings($drawings)
            ->import($content);

        // The administrators and database experts check the objects an import added.
        if ($result->newObjects !== []) {
            NotifyImportedObjects::dispatch($user->username, $result->newObjects);
        }

        return view('observations.import-result', [
            'result' => $result,
            'format' => $format,
            'userSlug' => $user->slug,
        ]);
    }

    /**
     * Keep the uploaded files until the observer has chosen what to do with
     * the new targets, at most KEEP_HOURS, and return the token they are
     * kept under. Uploads kept longer are removed.
     *
     * @param  list<array<string, mixed>>  $targets
     */
    private function keep(Request $request, bool $isZip, array $targets): string
    {
        $disk = Storage::disk('local');
        foreach ($disk->directories(self::DIRECTORY) as $old) {
            if (@filemtime($disk->path($old)) < now()->subHours(self::KEEP_HOURS)->getTimestamp()) {
                $disk->deleteDirectory($old);
            }
        }

        $token = Str::random(40);
        $directory = $disk->path(self::DIRECTORY . '/' . $token);
        $file = $request->file('import_file');
        $fileName = $file->getClientOriginalName();
        $file->move($directory, 'import');

        $drawings = [];
        foreach (array_values($request->file('drawings', [])) as $i => $drawing) {
            $drawings[$i] = $drawing->getClientOriginalName();
            $drawing->move("{$directory}/drawings", (string) $i);
        }

        Cache::put(self::CACHE_PREFIX . $token, [
            'user_id' => $request->user()->id,
            'file_name' => $fileName,
            'is_zip' => $isZip,
            'drawings' => $drawings,
            'below_horizon' => $request->boolean('import_below_horizon'),
            'targets' => $targets,
        ], now()->addHours(self::KEEP_HOURS));

        return $token;
    }

    /**
     * The kept upload of a token, if it is the observer's.
     *
     * @return array{user_id: int, file_name: string, is_zip: bool, drawings: array<int, string>, below_horizon: bool, targets: list<array<string, mixed>>}
     */
    private function pending(Request $request, string $token): array
    {
        $pending = Cache::get(self::CACHE_PREFIX . $token);
        if (! is_array($pending) || $pending['user_id'] !== $request->user()->id
            || ! Storage::disk('local')->exists(self::DIRECTORY . "/{$token}/import")) {
            abort(404);
        }

        return $pending;
    }

    private function forget(string $token): void
    {
        Cache::forget(self::CACHE_PREFIX . $token);
        Storage::disk('local')->deleteDirectory(self::DIRECTORY . '/' . $token);
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
