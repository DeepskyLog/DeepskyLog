<?php

namespace App\Console\Commands;

use App\Models\CometObservationsOld;
use App\Models\Eyepiece;
use App\Models\Filter;
use App\Models\Instrument;
use App\Models\Lens;
use App\Models\Location;
use App\Models\ObservationsOld;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class updateObservationsCommand extends Command
{
    protected $signature = 'update:observations';

    protected $description = 'Updates the instrument, eyepiece, filter, lens and location table with the number of observations from the old version of DeepskyLog.';

    public function handle(): void
    {
        $this->info('Updating Instruments table...');

        // Get all instruments
        $instruments = Instrument::all();

        // Precompute counts grouped by instrument in the old DB to avoid per-instrument queries
        $instrumentObsCounts = DB::connection('mysqlOld')->table('observations')
            ->select('instrumentid', DB::raw('COUNT(*) as cnt'))
            ->groupBy('instrumentid')
            ->pluck('cnt', 'instrumentid')
            ->toArray();

        $instrumentCometCounts = DB::connection('mysqlOld')->table('cometobservations')
            ->select('instrumentid', DB::raw('COUNT(*) as cnt'))
            ->groupBy('instrumentid')
            ->pluck('cnt', 'instrumentid')
            ->toArray();

        foreach ($instruments as $instrument) {
            $observations = ($instrumentObsCounts[$instrument->id] ?? 0) + ($instrumentCometCounts[$instrument->id] ?? 0);
            $instrument->observations = $observations;
            $instrument->save();
        }

        $this->info('Updating Eyepieces table...');

        // Get all eyepieces
        $eyepieces = Eyepiece::all();

        // Precompute counts grouped by eyepiece in the old DB to avoid per-eyepiece queries
        $eyepieceObsCounts = DB::connection('mysqlOld')->table('observations')
            ->select('eyepieceid', DB::raw('COUNT(*) as cnt'))
            ->groupBy('eyepieceid')
            ->pluck('cnt', 'eyepieceid')
            ->toArray();

        foreach ($eyepieces as $eyepiece) {
            $eyepiece->observations = $eyepieceObsCounts[$eyepiece->id] ?? 0;
            $eyepiece->save();
        }

        $this->info('Updating Lenses table...');

        // Get all lenses
        $lenses = Lens::all();

        // Precompute counts grouped by lens in the old DB to avoid per-lens queries
        $lensObsCounts = DB::connection('mysqlOld')->table('observations')
            ->select('lensid', DB::raw('COUNT(*) as cnt'))
            ->groupBy('lensid')
            ->pluck('cnt', 'lensid')
            ->toArray();

        foreach ($lenses as $lens) {
            $lens->observations = $lensObsCounts[$lens->id] ?? 0;
            $lens->save();
        }

        $this->info('Updating Filters table...');

        // Get all filters
        $filters = Filter::all();

        // Precompute counts grouped by filter in the old DB to avoid per-filter queries
        $filterObsCounts = DB::connection('mysqlOld')->table('observations')
            ->select('filterid', DB::raw('COUNT(*) as cnt'))
            ->groupBy('filterid')
            ->pluck('cnt', 'filterid')
            ->toArray();

        foreach ($filters as $filter) {
            $filter->observations = $filterObsCounts[$filter->id] ?? 0;
            $filter->save();
        }

        $this->info('Updating Locations table...');

        // Get all locations
        $locations = Location::all();

        // Precompute counts grouped by location in the old DB to avoid per-location queries
        $locationObsCounts = DB::connection('mysqlOld')->table('observations')
            ->select('locationid', DB::raw('COUNT(*) as cnt'))
            ->groupBy('locationid')
            ->pluck('cnt', 'locationid')
            ->toArray();

        $locationCometCounts = DB::connection('mysqlOld')->table('cometobservations')
            ->select('locationid', DB::raw('COUNT(*) as cnt'))
            ->groupBy('locationid')
            ->pluck('cnt', 'locationid')
            ->toArray();

        foreach ($locations as $location) {
            $observations = ($locationObsCounts[$location->id] ?? 0) + ($locationCometCounts[$location->id] ?? 0);
            $location->observations = $observations;
            $location->save();
        }

        $this->info('All observation counts updated successfully!');
    }
}
