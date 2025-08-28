<?php

namespace App\Console\Commands;

use App\Models\CometObservationsOld;
use App\Models\Location;
use App\Models\ObservationsOld;
use Exception;
use Illuminate\Console\Command;

class updateObservationsCommand extends Command
{
    protected $signature = 'update:observations';

    protected $description = 'Updates the instrument, eyepiece, filter, lens and location table with the number of observations from the old version of DeepskyLog.';

    public function handle(): void
    {
        //        $this->info('Updating Instruments table...');
        //
        //        // Get all instruments
        //        $instruments = Instrument::all();
        //
        //        // Check if the user with the given username already exists in the new database
        //        // If not, create a new user with the given username
        //        foreach ($instruments as $instrument) {
        //            try {
        //                $observations = ObservationsOld::where('instrumentid', $instrument->id)->count();
        //                $cometObservations = CometObservationsOld::where('instrumentid', $instrument->id)->count();
        //
        //                $observations = $observations + $cometObservations;
        //            } catch (Exception $e) {
        //                $observations = 0;
        //            }
        //
        //            $instrument->observations = $observations;
        //            $instrument->save();
        //        }
        //
        //        $this->info('Updating Eyepieces table...');
        //
        //        // Get all eyepieces
        //        $eyepieces = Eyepiece::all();
        //
        //        // Check if the user with the given username already exists in the new database
        //        // If not, create a new user with the given username
        //        foreach ($eyepieces as $eyepiece) {
        //            try {
        //                $observations = ObservationsOld::where('eyepieceid', $eyepiece->id)->count();
        //            } catch (Exception $e) {
        //                $observations = 0;
        //            }
        //
        //            $eyepiece->observations = $observations;
        //            $eyepiece->save();
        //        }
        //
        //        $this->info('Updating Lens table...');
        //
        //        // Get all lenses
        //        $lenses = Lens::all();
        //
        //        foreach ($lenses as $lens) {
        //            try {
        //                $observations = ObservationsOld::where('lensid', $lens->id)->count();
        //            } catch (Exception $e) {
        //                $observations = 0;
        //            }
        //
        //            $lens->observations = $observations;
        //            $lens->save();
        //        }
        //
        //        $this->info('Updating Filters table...');
        //
        //        // Get all filters
        //        $filters = Filter::all();
        //
        //        foreach ($filters as $filter) {
        //            try {
        //                $observations = ObservationsOld::where('filterid', $filter->id)->count();
        //            } catch (Exception $e) {
        //                $observations = 0;
        //            }
        //
        //            $filter->observations = $observations;
        //            $filter->save();
        //        }

        $this->info('Updating Locations table...');

        // Get all locations
        $locations = Location::all();

        // Check if the user with the given username already exists in the new database
        // If not, create a new user with the given username
        foreach ($locations as $location) {
            try {
                $observations = ObservationsOld::where('locationid', $location->id)->count();
                $cometObservations = CometObservationsOld::where('locationid', $location->id)->count();

                $observations = $observations + $cometObservations;
            } catch (Exception $e) {
                $observations = 0;
            }

            $location->observations = $observations;
            $location->save();
        }
    }
}
