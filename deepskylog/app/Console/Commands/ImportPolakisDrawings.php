<?php

namespace App\Console\Commands;

use App\Models\Observation;
use App\Models\User;
use App\Services\ObservingListItemNameResolver;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Give Tom Polakis's observations the drawings of his pbase gallery
 * "Deep-sky Drawings: 1986 - 1999" (https://pbase.com/polakis/visual_observing,
 * used with his permission).
 *
 * The gallery only names the object; the date, telescope, magnification and
 * site are written on each drawing, and were read off them into
 * database/polakis_drawings.csv (one row per drawing, with the URL of the
 * original). A drawing belongs to his observation of the same object on its
 * night: the date written on it, or the day before or after (the drawings and
 * the log do not always use the same date for a night), the same telescope
 * first. The seven drawings without a date go to the only observation of the
 * object with that telescope. Most drawings are of nights his log in
 * DeepskyLog has no observation of the object for; --create adds those as new
 * observations with only the drawing.
 *
 * Safe to run more than once: an observation that has a drawing keeps it.
 */
class ImportPolakisDrawings extends Command
{
    protected $signature = 'observations:import-polakis-drawings
        {--apply : Download the drawings and link them to the observations}
        {--create : Add an observation for the drawings no observation matches}
        {--file= : The list of drawings to read instead of database/polakis_drawings.csv}
        {--delay=500 : Milliseconds to wait between two downloads from pbase}';

    protected $description = "Download Tom Polakis's deep-sky drawings from pbase and link them to his observations";

    public const OBSERVER = 'polakis';

    /** Names in the gallery the name resolver does not know, or would read wrongly. */
    private const ALIASES = [
        'Collinder 399' => 'Cr 399',
        // Jones-Emberson 1
        'JE 1' => 'PK 164+31.01',
    ];

    /**
     * Nebulae of the Magellanic Clouds (Henize 1956) DeepskyLog does not
     * have; the resolver would read "N 19" as NGC 19.
     */
    private const NOT_IN_DEEPSKYLOG = ['N 19', 'N 37', 'Henize 119'];

    /** The telescope written on the drawings => the name of his instrument. */
    private const INSTRUMENTS = [
        '8-inch' => '8" telescope',
        '13-inch' => '13" telescope',
        '20-inch' => '20" telescope',
        '80mm' => '11x80',
    ];

    /** The site written on the drawings => the name of his location, where they differ. */
    private const LOCATIONS = [
        'Vekol Rd.' => 'Vekol',
        'Zane Grey Hwy.' => 'Zane Gray',
    ];

    public function handle(ObservingListItemNameResolver $resolver): int
    {
        $apply = (bool) $this->option('apply');
        $create = (bool) $this->option('create');
        if (! $apply) {
            $this->info('Running in dry-run mode. Use --apply to download the drawings and link them.');
        }

        $observer = User::query()->where('username', self::OBSERVER)->first();
        if ($observer === null) {
            $this->error('There is no observer '.self::OBSERVER.'.');

            return self::FAILURE;
        }

        $drawings = $this->readDrawings($this->option('file') ?: database_path('polakis_drawings.csv'));
        $instruments = DB::table('instruments')->where('user_id', $observer->id)->pluck('id', 'name');
        $locations = DB::table('locations')->where('user_id', $observer->id)->pluck('id', 'name');

        $plan = ['link' => [], 'create' => [], 'done' => [], 'unmatched' => [], 'skipped' => []];
        $claimed = [];
        foreach ($drawings as $drawing) {
            $drawing['object'] = $this->objectName($drawing['title'], $resolver);
            $drawing['instrument_id'] = $instruments[self::INSTRUMENTS[$drawing['instrument']] ?? ''] ?? null;
            $drawing['location_id'] = $drawing['site'] === '' ? null : ($locations[self::LOCATIONS[$drawing['site']] ?? $drawing['site']] ?? null);

            if ($drawing['object'] === null) {
                $plan['skipped'][] = [$drawing, 'object not in DeepskyLog'];

                continue;
            }

            [$observation, $how] = $this->matchObservation($drawing, $claimed);
            if ($observation !== null) {
                $claimed[$observation->id] = true;
                if ($observation->has_drawing) {
                    $plan['done'][] = [$drawing, $observation];
                } else {
                    $plan['link'][] = [$drawing, $observation, $how];
                }

                continue;
            }

            if ($drawing['date'] === '') {
                $plan['skipped'][] = [$drawing, $how];
            } elseif ($drawing['instrument_id'] === null || $drawing['location_id'] === null) {
                $plan['skipped'][] = [$drawing, $how.'; no instrument or location for an observation of its own'];
            } else {
                $plan['unmatched'][] = [$drawing, $how];
            }
        }

        $this->report($plan, $create);

        if (! $apply) {
            return self::SUCCESS;
        }

        $linked = $created = $failed = 0;
        foreach ($plan['link'] as [$drawing, $observation]) {
            $this->attach($drawing, $observation) ? $linked++ : $failed++;
        }
        if ($create) {
            foreach ($plan['unmatched'] as [$drawing]) {
                $observation = $this->createObservation($drawing, $observer);
                $this->attach($drawing, $observation) ? $created++ : $failed++;
            }
        }

        $this->info(sprintf('Linked %d drawings, added %d observations with a drawing%s.', $linked, $created, $failed ? ", {$failed} downloads failed" : ''));

        return $failed ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @return list<array{pbase_id: string, title: string, instrument: string, magnification: string, filter: string, date: string, site: string, original: string}>
     */
    private function readDrawings(string $path): array
    {
        $handle = fopen($path, 'r');
        $header = fgetcsv($handle, escape: '');
        $drawings = [];
        while (($row = fgetcsv($handle, escape: '')) !== false) {
            if ($row !== [null]) {
                $drawings[] = array_combine($header, $row);
            }
        }
        fclose($handle);

        return $drawings;
    }

    private function objectName(string $title, ObservingListItemNameResolver $resolver): ?string
    {
        if (in_array($title, self::NOT_IN_DEEPSKYLOG, true)) {
            return null;
        }

        return $resolver->resolve(self::ALIASES[$title] ?? $title);
    }

    /**
     * The observer's observation the drawing belongs to, or null, and how it
     * was found (or why none was).
     *
     * @param  array<int, true>  $claimed  observations another drawing already belongs to
     * @return array{0: Observation|null, 1: string}
     */
    private function matchObservation(array $drawing, array $claimed): array
    {
        $observations = Observation::query()
            ->where('observer_username', self::OBSERVER)
            ->where('target_type', 'deepsky')
            ->where('target_name', $drawing['object'])
            ->orderBy('observed_on')
            ->get()
            ->reject(fn (Observation $o) => isset($claimed[$o->id]));

        if ($drawing['date'] === '') {
            $same = $observations->where('instrument_id', $drawing['instrument_id']);

            return $same->count() === 1
                ? [$same->first(), 'undated, the only observation with this telescope']
                : [null, "undated, {$same->count()} observations with this telescope"];
        }

        // With the same telescope (the drawing of the SMC in binoculars is not
        // that of his 13-inch the same night), the date on the drawing first.
        $date = Carbon::parse($drawing['date']);
        $night = $observations
            ->filter(fn (Observation $o) => $o->instrument_id == $drawing['instrument_id'] && abs($o->observed_on->diffInDays($date, false)) <= 1)
            ->sortBy(fn (Observation $o) => [$o->observed_on->isSameDay($date) ? 0 : 1, $o->id])
            ->values();

        if ($night->isEmpty()) {
            return [null, $observations->isEmpty() ? 'no observation of the object' : 'no observation that night with this telescope ('.$observations->map(fn (Observation $o) => $o->observed_on->format('Y-m-d'))->implode(', ').')'];
        }

        $observation = $night->first();

        return [$observation, $observation->observed_on->isSameDay($date) ? '' : 'observed on '.$observation->observed_on->format('Y-m-d')];
    }

    private function report(array $plan, bool $create): void
    {
        $describe = fn (array $d) => trim("{$d['title']}, {$d['date']} {$d['site']}, {$d['instrument']} {$d['magnification']}x");

        if ($this->output->isVerbose()) {
            if ($plan['link'] !== []) {
                $this->table(['Drawing', 'Observation', 'Note'], array_map(
                    fn (array $p) => [$describe($p[0]), $p[1]->id, $p[2]],
                    $plan['link'],
                ));
            }
            if ($plan['unmatched'] !== []) {
                $this->line('');
                $this->warn($create ? 'Drawings that get an observation of their own:' : 'Drawings without an observation (--create adds one):');
                $this->table(['Drawing', 'Why'], array_map(fn (array $p) => [$describe($p[0]), $p[1]], $plan['unmatched']));
            }
        }

        if ($plan['skipped'] !== []) {
            $this->line('');
            $this->warn('Drawings not imported, to check by hand ('.count($plan['skipped']).'):');
            $this->table(['Drawing', 'Why'], array_map(fn (array $p) => [$describe($p[0]), $p[1]], $plan['skipped']));
        }

        $this->line('');
        $this->info(sprintf(
            '%d drawings belong to an observation, %d have none%s, %d are linked already, %d are not imported.',
            count($plan['link']),
            count($plan['unmatched']),
            $create ? ' and get an observation of their own' : ' (use --create to add them)',
            count($plan['done']),
            count($plan['skipped']),
        ));
    }

    /**
     * Download the drawing and give it to the observation, with the
     * magnification written on it when the observation has none.
     */
    private function attach(array $drawing, Observation $observation): bool
    {
        $path = 'observations/polakis-'.$drawing['pbase_id'].'.jpg';
        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            usleep(max(0, (int) $this->option('delay')) * 1000);
            $response = Http::timeout(60)->retry(2, 2000, throw: false)->get($drawing['original']);
            $info = $response->successful() ? @getimagesizefromstring($response->body()) : false;
            if ($info === false || $info[2] !== IMAGETYPE_JPEG) {
                $this->error("Could not download the drawing of {$drawing['title']} ({$drawing['original']}).");

                return false;
            }
            $disk->put($path, $response->body());
        }

        $observation->forceFill(['has_drawing' => true, 'drawing_filename' => $path])->save();

        if ($drawing['magnification'] !== '') {
            $details = DB::table('deepsky_observation_details')->where('observation_id', $observation->id);
            if (! $details->exists()) {
                DB::table('deepsky_observation_details')->insert(['observation_id' => $observation->id, 'magnification' => (int) $drawing['magnification']]);
            } elseif ($details->value('magnification') === null) {
                $details->update(['magnification' => (int) $drawing['magnification']]);
            }
        }

        return true;
    }

    /**
     * An observation of the night of a drawing that his log does not have:
     * the date, site and telescope written on the drawing, no description.
     */
    private function createObservation(array $drawing, User $observer): Observation
    {
        return Observation::query()->create([
            'observer_username' => $observer->username,
            'observer_user_id' => $observer->id,
            'observed_on' => $drawing['date'],
            'instrument_id' => $drawing['instrument_id'],
            'location_id' => $drawing['location_id'],
            'language' => 'en',
            'target_type' => 'deepsky',
            'target_table' => 'objects',
            'target_key' => $drawing['object'],
            'target_name' => $drawing['object'],
        ]);
    }
}
