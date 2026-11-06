<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Import\CsvObservationImporter;
use App\Services\Import\ImportDrawings;
use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Import a DeepskyLog CSV file of observations (the format of
 * /observation/import, CsvObservationImporter) for an observer, with the
 * drawings its 16th column names taken from a folder: what the observer would
 * do on the import page, for observations an administrator adds for them,
 * such as the planet and Moon drawings of astronomydrawings (Peter
 * Vercauteren) in database/astronomydrawings_planets.csv.
 *
 * The importer resolves the objects (deep-sky, planets, lunar features,
 * moons, asteroids, comets) and the observer's location and equipment by
 * name, skips observations below the horizon, and updates an observation of
 * the same object, moment and instrument instead of adding it again; an
 * observation that has a drawing keeps it. So a second run changes nothing.
 *
 * Without --apply the import runs in a transaction that is rolled back,
 * without drawings, and the drawings the file names are only looked up in
 * the folder.
 */
class ImportObservationsCsv extends Command
{
    protected $signature = 'observations:import-csv
        {observer : The username of the observer the observations are of}
        {file : The CSV file to import}
        {--drawings= : The folder with the drawings the file names}
        {--below-horizon : Also import observations of objects below the horizon}
        {--apply : Import the observations}';

    protected $description = 'Import a CSV file of observations with their drawings for an observer';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        if (! $apply) {
            $this->info('Running in dry-run mode. Use --apply to import the observations.');
        }

        $observer = User::query()->where('username', $this->argument('observer'))->first();
        if ($observer === null) {
            $this->error("There is no observer {$this->argument('observer')}.");

            return self::FAILURE;
        }

        $file = (string) $this->argument('file');
        if (! is_file($file) || ($content = file_get_contents($file)) === false) {
            $this->error("Cannot read {$file}.");

            return self::FAILURE;
        }

        $folder = $this->option('drawings');
        if ($folder !== null && ! is_dir($folder)) {
            $this->error("There is no folder {$folder}.");

            return self::FAILURE;
        }

        $missing = $this->missingDrawings($content, $folder);
        if ($missing !== []) {
            $this->error('Drawings named in the file that are not in '.($folder ?? 'the folder (no --drawings given)').':');
            foreach ($missing as $name) {
                $this->line("  {$name}");
            }

            return self::FAILURE;
        }

        $importer = (new CsvObservationImporter($observer))->importBelowHorizon((bool) $this->option('below-horizon'));
        if ($apply && $folder !== null) {
            $importer->withDrawings($this->drawingsIn($folder));
        }

        DB::beginTransaction();
        try {
            $result = $importer->import($content);
        } finally {
            $apply ? DB::commit() : DB::rollBack();
        }

        foreach ($result->log as $entry) {
            // The dry run leaves the drawings out on purpose.
            if (! $apply && str_starts_with($entry['message'], 'The file names ')) {
                continue;
            }
            match ($entry['type']) {
                'error' => $this->error($entry['message']),
                'warning' => $this->warn($entry['message']),
                default => $this->line($entry['message']),
            };
        }

        $this->line('');
        $this->info(sprintf(
            '%s %d observations (%d updated), %d drawings, %d skipped.',
            $apply ? 'Imported' : 'Would import',
            $result->imported,
            collect($result->importedObservations)->where('updated', true)->count(),
            $apply ? $result->drawings : count(array_unique(array_filter($this->drawingNames($content)))),
            $result->skipped,
        ));

        return $result->hasErrors() ? self::FAILURE : self::SUCCESS;
    }

    /**
     * The drawing named in each row (the 16th column), '' for none.
     *
     * @return list<string>
     */
    private function drawingNames(string $content): array
    {
        $names = [];
        foreach (explode("\n", str_replace(["\r\n", "\r"], "\n", $content)) as $line) {
            if (trim($line) === '') {
                continue;
            }
            $name = trim(str_getcsv(trim($line), ';', escape: '')[15] ?? '');
            $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $names[] = in_array($extension, ImportDrawings::IMAGE_EXTENSIONS, true) ? $name : '';
        }

        return $names;
    }

    /**
     * @return list<string>
     */
    private function missingDrawings(string $content, ?string $folder): array
    {
        $names = array_unique(array_filter($this->drawingNames($content)));

        return array_values(array_filter($names, fn (string $name) => $folder === null || ! is_file($folder.'/'.$name)));
    }

    private function drawingsIn(string $folder): ImportDrawings
    {
        $files = [];
        foreach (scandir($folder) as $name) {
            $path = $folder.'/'.$name;
            $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (is_file($path) && ! str_starts_with($name, '.') && in_array($extension, ImportDrawings::IMAGE_EXTENSIONS, true)) {
                $files[] = new UploadedFile($path, $name, null, null, true);
            }
        }

        return ImportDrawings::fromUploads($files);
    }
}
