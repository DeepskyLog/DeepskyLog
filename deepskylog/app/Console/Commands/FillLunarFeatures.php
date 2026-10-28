<?php

namespace App\Console\Commands;

use App\Services\Moon\LunarGazetteer;
use Illuminate\Console\Command;

/**
 * Complete the lunar features from the IAU Gazetteer of Planetary
 * Nomenclature (App\Services\Moon\LunarGazetteer): their positions and
 * diameters, which the terminator list of the Moon page needs, the names the
 * old seed cut at an apostrophe, and the features that were missing. Safe to
 * run more than once: a second run changes nothing.
 */
class FillLunarFeatures extends Command
{
    protected $signature = 'lunar-features:fill-from-gazetteer
        {--apply : Write the changes to the database}';

    protected $description = 'Fill in the positions and diameters of the lunar features from the IAU gazetteer and add the missing ones';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        if (! $apply) {
            $this->info('Running in dry-run mode. Use --apply to write the changes.');
        }

        $plan = LunarGazetteer::plan();

        if ($plan['renamed'] !== []) {
            $this->info('Renamed ('.count($plan['renamed']).')');
            $this->table(['From', 'To'], array_map(fn ($r) => [$r['from'], $r['to']], $plan['renamed']));
        }
        if ($this->output->isVerbose() && $plan['updated'] !== []) {
            $this->info('Updated ('.count($plan['updated']).')');
            $this->table(['Feature', 'Values'], array_map(fn ($u) => [$u['name'], json_encode($u['values'])], $plan['updated']));
        }
        if ($this->output->isVerbose() && $plan['added'] !== []) {
            $this->info('Added ('.count($plan['added']).')');
            $this->table(['Feature', 'Type', 'Latitude', 'Longitude', 'Diameter'], array_map(fn ($f) => [$f['name'], $f['type'], $f['lat'], $f['lon'], $f['diameter']], $plan['added']));
        }
        if ($plan['unknown'] !== []) {
            $this->warn('Not in the gazetteer, left as they are: '.implode(', ', $plan['unknown']));
        }

        $this->info(sprintf(
            '%d renamed, %d with a new position or diameter, %d added, %d not in the gazetteer.',
            count($plan['renamed']),
            count($plan['updated']),
            count($plan['added']),
            count($plan['unknown'])
        ));

        if ($apply) {
            LunarGazetteer::apply($plan);
            $this->info('Done.');
        }

        return self::SUCCESS;
    }
}
