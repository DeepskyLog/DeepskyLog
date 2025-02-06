<?php

namespace App\Console\Commands;

use App\Models\Instrument;
use App\Models\InstrumentsOld;
use Illuminate\Console\Command;

class updateOldInstrumentTableCommand extends Command
{
    protected $signature = 'update:old-instrument-table';

    protected $description = 'Updates the old instrument table with the changes from the new version of DeepskyLog.';

    public function handle(): void
    {
        $this->info('Updating old Instrument table...');

        // Get all instrument from the new database
        $instruments = Instrument::all();

        // Check if the instrument with the given id already exists in the old database
        // If not, create a new instrument
        foreach ($instruments as $instrument) {
            $id = html_entity_decode($instrument->id);

            $old_instrument = InstrumentsOld::where('id', $id)->first();
            if (! $old_instrument) {
                $this->info('Adding instrument: '.$id);
                $old_instrument = new InstrumentsOld;
                $old_instrument->id = $id;
                $old_instrument->name = html_entity_decode($instrument->fullName());
                $old_instrument->diameter = $instrument->diameter;
                $old_instrument->fd = $instrument->fd;
                $old_instrument->type = $instrument->type;
                if ($instrument->fixedMagnification) {
                    $old_instrument->fixedMagnification = $instrument->fixedMagnification;
                } else {
                    $old_instrument->fixedMagnification = 0;
                }
                $old_instrument->observer = $instrument->observer;
                $old_instrument->instrumentactive = $instrument->instrumentactive;
                $old_instrument->save();
            }
        }
    }
}
