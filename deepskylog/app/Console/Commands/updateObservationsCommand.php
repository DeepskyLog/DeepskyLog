<?php

namespace App\Console\Commands;

use App\Models\Eyepiece;
use App\Models\Filter;
use App\Models\Instrument;
use App\Models\Lens;
use App\Models\Location;
use App\Models\Observation;
use Exception;
use Illuminate\Console\Command;

class updateObservationsCommand extends Command
{
    protected $signature = 'update:observations';

    protected $description = 'Updates the instrument, eyepiece, filter, lens and location table with the number of observations from the old version of DeepskyLog.';

    public function handle(): void
    {
        $this->info('Updating Instruments table...');

        // Get all instruments
        $instruments = Instrument::all();

        // Precompute counts grouped by instrument from the unified observations table
        $instrumentObsCounts = Observation::selectRaw('instrument_id, COUNT(*) as cnt')
            ->whereNotNull('instrument_id')
            ->groupBy('instrument_id')
            ->pluck('cnt', 'instrument_id')
            ->toArray();

        foreach ($instruments as $instrument) {
            $instrument->observations = $instrumentObsCounts[$instrument->id] ?? 0;
            $instrument->save();
        }

        $this->info('Updating Eyepieces table...');

        // Get all eyepieces
        $eyepieces = Eyepiece::all();

        // Precompute counts grouped by eyepiece from the unified observations table
        $eyepieceObsCounts = Observation::selectRaw('eyepiece_id, COUNT(*) as cnt')
            ->whereNotNull('eyepiece_id')
            ->groupBy('eyepiece_id')
            ->pluck('cnt', 'eyepiece_id')
            ->toArray();

        foreach ($eyepieces as $eyepiece) {
            $eyepiece->observations = $eyepieceObsCounts[$eyepiece->id] ?? 0;
            $eyepiece->save();
        }

        $this->info('Updating Lenses table...');

        // Get all lenses
        $lenses = Lens::all();

        // Precompute counts grouped by lens from the unified observations table
        $lensObsCounts = Observation::selectRaw('lens_id, COUNT(*) as cnt')
            ->whereNotNull('lens_id')
            ->groupBy('lens_id')
            ->pluck('cnt', 'lens_id')
            ->toArray();

        foreach ($lenses as $lens) {
            $lens->observations = $lensObsCounts[$lens->id] ?? 0;
            $lens->save();
        }

        $this->info('Updating Filters table...');

        // Get all filters
        $filters = Filter::all();

        // Precompute counts grouped by filter from the unified observations table
        $filterObsCounts = Observation::selectRaw('filter_id, COUNT(*) as cnt')
            ->whereNotNull('filter_id')
            ->groupBy('filter_id')
            ->pluck('cnt', 'filter_id')
            ->toArray();

        foreach ($filters as $filter) {
            $filter->observations = $filterObsCounts[$filter->id] ?? 0;
            $filter->save();
        }

        $this->info('Updating Locations table...');

        // Get all locations
        $locations = Location::all();

        // Precompute counts grouped by location from the unified observations table
        $locationObsCounts = Observation::selectRaw('location_id, COUNT(*) as cnt')
            ->whereNotNull('location_id')
            ->groupBy('location_id')
            ->pluck('cnt', 'location_id')
            ->toArray();

        foreach ($locations as $location) {
            $location->observations = $locationObsCounts[$location->id] ?? 0;
            $location->save();
        }

        $this->info('All observation counts updated successfully!');
    }
}
