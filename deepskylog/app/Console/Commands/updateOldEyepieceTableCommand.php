<?php

namespace App\Console\Commands;

use App\Models\Eyepiece;
use App\Models\EyepiecesOld;
use Illuminate\Console\Command;

class updateOldEyepieceTableCommand extends Command
{
    protected $signature = 'update:old-eyepiece-table';

    protected $description = 'Updates the old eyepiece table with the changes from the new version of DeepskyLog.';

    public function handle(): void
    {
        $this->info('Updating old Eyepiece table...');

        // Get all eyepieces from the new database
        $eyepieces = Eyepiece::all();

        // Preload all existing eyepiece IDs from old DB to avoid per-eyepiece queries
        $existingIds = EyepiecesOld::pluck('id')->flip()->all();

        // Check if the eyepiece with the given id already exists in the old database
        // If not, create a new eyepiece
        foreach ($eyepieces as $eyepiece) {
            $id = html_entity_decode($eyepiece->id);

            if (!isset($existingIds[$id])) {
                $this->info('Adding eyepiece: '.$id);
                $old_eyepiece = new EyepiecesOld;
                $old_eyepiece->id = $id;
                $old_eyepiece->name = html_entity_decode($eyepiece->fullName());
                $old_eyepiece->focalLength = $eyepiece->focal_length_mm;
                $old_eyepiece->apparentFOV = $eyepiece->apparentFOV;
                $old_eyepiece->maxFocalLength = $eyepiece->max_focal_length_mm;
                $old_eyepiece->observer = $eyepiece->observer;
                $old_eyepiece->eyepieceactive = $eyepiece->active;
                $old_eyepiece->save();
            }
        }
    }
}
