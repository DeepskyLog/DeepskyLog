<?php

namespace App\Console\Commands;

use App\Models\Location;
use App\Models\LocationsOld;
use Illuminate\Console\Command;

class updateOldLocationTableCommand extends Command
{
    protected $signature = 'update:old-location-table';

    protected $description = 'Updates the old location table with the changes from the new version of DeepskyLog.';

    public function handle(): void
    {
        $this->info('Updating old Location table...');

        // Get all locations from the new database
        $locations = Location::all();

        // Check if the location with the given id already exists in the old database
        // If not, create a new location
        foreach ($locations as $location) {
            $id = html_entity_decode($location->id);

            $old_location = LocationsOld::where('id', $id)->first();

            if (! $old_location) {
                $this->info('Adding location: '.$id);
                $old_location = new LocationsOld;
                $old_location->id = $id;
                $old_location->name = html_entity_decode($location->name);
                $old_location->longitude = $location->longitude;
                $old_location->latitude = $location->latitude;
                $old_location->timezone = $location->timezone;
                $old_location->limitingMagnitude = $location->limitingMagnitude;
                $old_location->skyBackground = $location->skyBackground;
                $old_location->elevation = $location->elevation;
                $old_location->country = $location->country;
                $old_location->observer = $location->observer;
                $old_location->locationactive = $location->locationactive;
                $old_location->save();
            }
        }
    }
}
