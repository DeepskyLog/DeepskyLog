<?php

namespace Database\Seeders;

use App\Models\Target;
use Illuminate\Database\Seeder;

class SBObjSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Loop over the targets
        $targets = Target::all();

        foreach ($targets as $target) {
            // Calculate the SBObj
            $libraryTarget = new \deepskylog\AstronomyLibrary\Targets\Target();
            $libraryTarget->setDiameter($target->diam1, $target->diam2);
            $libraryTarget->setMagnitude($target->mag);
            $target->SBObj = $libraryTarget->calculateSBObj();
            // Set the diam to null if diam = 0.0
            if ($target->diam1 == 0.0) {
                $target->diam1 = null;
            }
            if ($target->diam2 == 0.0) {
                $target->diam2 = null;
            }
            $target->save();
        }
    }
}
