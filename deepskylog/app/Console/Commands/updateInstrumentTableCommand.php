<?php

namespace App\Console\Commands;

use App\Models\InstrumentsOld;
use Illuminate\Console\Command;

class updateInstrumentTableCommand extends Command
{
    protected $signature = 'update:instrument-table';

    protected $description = 'Updates the instrument table with the changes from the old version of DeepskyLog.';

    public function handle(): void
    {
        $this->info('Updating instrument table...');

        // Get all DeepskyLog sketches of the week
        $instruments = InstrumentsOld::all();

        // Update the instrument table
        dd($instruments);
    }
}
