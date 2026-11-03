<?php

namespace App\Console\Commands;

use App\Models\Location;
use App\Models\ObservationSession;
use App\Services\Weather\NightWeather;
use App\Support\EphemerisDate;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Fetches the weather of the nights of the sessions and observations from
 * Open-Meteo's historical archive ahead (NightWeather), so their pages show
 * it at once: the days of each cell not stored yet, the most recent first,
 * NightWeather::WINDOW_DAYS per request and at most --limit requests a run
 * (Open-Meteo allows 10,000 a day without a key). A session needs the UTC
 * days of its nights, an observation those around its UT date. Stops when
 * Open-Meteo refuses (NightWeather pauses for an hour).
 */
class FillNightWeather extends Command
{
    protected $signature = 'weather:fill-nights
        {--apply : Fetch and store the weather}
        {--limit=250 : The most requests to Open-Meteo in this run}
        {--only= : sessions or observations}';

    protected $description = 'Fetch the weather of the nights of the sessions and observations from the historical archive of Open-Meteo';

    /** Pause between requests (ms): Open-Meteo allows 600 a minute. */
    private const DELAY_MS = 200;

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        if (! $apply) {
            $this->info('Running in dry-run mode. Use --apply to fetch the weather.');
        }
        $only = $this->option('only');

        /** @var array<string, array<string, int>> $needed days per cell "lat:lon", with their priority (sessions first) */
        $needed = [];
        $add    = function (float $latitude, float $longitude, array $days, int $priority) use (&$needed) {
            $cell = implode(':', NightWeather::cell($latitude, $longitude));
            foreach ($days as $day) {
                if (NightWeather::available($day)) {
                    $needed[$cell][$day] = max($needed[$cell][$day] ?? 0, $priority);
                }
            }
        };

        $locations = Location::query()->get(['id', 'latitude', 'longitude', 'timezone'])
            ->filter(fn (Location $location) => $location->hasValidCoordinates())
            ->keyBy('id');

        if ($only !== 'observations') {
            ObservationSession::query()
                ->where('active', 1)->whereNotNull('begindate')->whereNotNull('locationid')
                ->where('begindate', '>=', NightWeather::FIRST_DATE)
                ->get(['id', 'locationid', 'begindate', 'enddate'])
                ->each(function (ObservationSession $session) use ($locations, $add) {
                    $location = $locations->get($session->locationid);
                    if (! $location) {
                        return;
                    }
                    $timezone = EphemerisDate::timezone($location);
                    foreach (NightWeather::sessionNights($session, $location) as $night) {
                        try {
                            [$start, $end] = NightWeather::window((float) $location->latitude, (float) $location->longitude, $timezone, $night['night'], $night['hours']);
                        } catch (\Throwable) {
                            continue;
                        }
                        $add((float) $location->latitude, (float) $location->longitude, NightWeather::days($start, $end), 1);
                    }
                });
        }

        if ($only !== 'sessions') {
            DB::table('observations')
                ->whereNotNull('location_id')->whereNotNull('observed_on')
                ->where('observed_on', '>=', NightWeather::FIRST_DATE)
                ->distinct()
                ->select(['location_id', 'observed_on'])
                ->orderBy('location_id')->orderBy('observed_on')
                ->each(function ($row) use ($locations, $add) {
                    $location = $locations->get($row->location_id);
                    if (! $location) {
                        return;
                    }
                    $date = Carbon::parse($row->observed_on, 'UTC');
                    $add((float) $location->latitude, (float) $location->longitude, [
                        $date->copy()->subDay()->toDateString(), $date->toDateString(), $date->copy()->addDay()->toDateString(),
                    ], 0);
                }, 5000);
        }

        // The requests of the days not stored yet: those of sessions first, then the most recent
        $requests    = [];
        $missingDays = 0;
        foreach ($needed as $cell => $days) {
            [$cellLat, $cellLon] = array_map('intval', explode(':', $cell));
            $missing             = NightWeather::missing($cellLat, $cellLon, array_keys($days));
            $missingDays += count($missing);
            foreach (NightWeather::windows($missing) as [$from, $until]) {
                $priority   = max(array_map(fn (string $day) => $days[$day], array_filter($missing, fn (string $day) => $day >= $from && $day <= $until)));
                $requests[] = [$cellLat, $cellLon, $from, $until, $priority];
            }
        }
        usort($requests, fn (array $a, array $b) => [$b[4], $b[3]] <=> [$a[4], $a[3]]);

        $this->info('Cells: '.count($needed).', days to fetch: '.$missingDays.', requests: '.count($requests));
        if ($this->output->isVerbose()) {
            $this->table(['Latitude', 'Longitude', 'From', 'Until'], array_map(
                fn (array $request) => [$request[0] * NightWeather::GRID, $request[1] * NightWeather::GRID, $request[2], $request[3]],
                array_slice($requests, 0, (int) $this->option('limit'))
            ));
        }
        if (! $apply || ! NightWeather::enabled()) {
            return self::SUCCESS;
        }

        $done   = 0;
        $failed = 0;
        foreach (array_slice($requests, 0, max(0, (int) $this->option('limit'))) as [$cellLat, $cellLon, $from, $until]) {
            if (NightWeather::paused()) {
                $this->warn('Open-Meteo refused a request: paused for an hour.');

                break;
            }
            NightWeather::fetch($cellLat, $cellLon, $from, $until) ? $done++ : $failed++;
            usleep(self::DELAY_MS * 1000);
        }

        $this->info('Fetched: '.$done.', failed: '.$failed);

        return self::SUCCESS;
    }
}
