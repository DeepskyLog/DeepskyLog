<?php

namespace App\Console\Commands;

use App\Models\Lens;
use App\Models\LensesOld;
use Illuminate\Console\Command;

class updateOldLensTableCommand extends Command
{
    protected $signature = 'update:old-lens-table';

    protected $description = 'Updates the old lens table with the changes from the new version of DeepskyLog.';

    public function handle(): void
    {
        $this->info('Updating old Lens table...');

        // Get all lens from the new database
        $lenses = Lens::all();

        // Check if the lens with the given id already exists in the old database
        // If not, create a new lens
        foreach ($lenses as $lens) {
            $id = html_entity_decode($lens->id);

            $old_lens = LensesOld::where('id', $id)->first();

            if (! $old_lens) {
                $this->info('Adding lens: '.$id);
                $old_lens = new LensesOld;
                $old_lens->id = $id;
                $old_lens->name = html_entity_decode($lens->name);
                $old_lens->factor = $lens->factor;
                $old_lens->observer = $lens->observer;
                $old_lens->lensactive = $lens->lensactive;
                $old_lens->save();
            }
        }
    }
}
