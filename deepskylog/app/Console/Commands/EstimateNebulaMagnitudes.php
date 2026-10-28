<?php

namespace App\Console\Commands;

use App\Services\NebulaBrightness;
use Illuminate\Console\Command;

/**
 * Give the bright nebulae without a magnitude one estimated from the
 * brightness class of their Lynds (LBN) or Sharpless (Sh 2) name, so they get
 * a contrast reserve. See App\Services\NebulaBrightness. Safe to run more than
 * once: earlier estimates are recalculated, measured magnitudes never touched.
 */
class EstimateNebulaMagnitudes extends Command
{
    protected $signature = 'objects:estimate-nebula-magnitudes
        {--apply : Write the magnitudes to the database}';

    protected $description = 'Estimate the magnitudes of bright nebulae from the brightness classes of the LBN and Sharpless catalogues';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        if (! $apply) {
            $this->info('Running in dry-run mode. Use --apply to write the magnitudes.');
        }

        ['estimates' => $estimates, 'rejected' => $rejected] = NebulaBrightness::plan();

        $changed = array_filter($estimates, fn (array $estimate) => $estimate['changed']);

        if ($this->output->isVerbose() && $changed !== []) {
            $this->table(
                ['Object', 'Type', 'Source', 'SB', 'Size (\')', 'Magnitude'],
                array_map(fn (array $e) => [
                    $e['name'],
                    $e['type'],
                    $e['source'],
                    $e['sb'],
                    round($e['diam1'] / 60, 1).' × '.round($e['diam2'] / 60, 1).($e['size_from_catalogue'] ? ' (catalogue)' : ''),
                    ($e['old'] !== null ? $e['old'].' → ' : '').$e['mag'],
                ], $changed)
            );
        }

        if ($rejected !== []) {
            $this->line('');
            $this->warn('Names whose catalogue entry lies too far from the object ('.count($rejected).'):');
            $this->table(['Object', 'Name', 'Offset (\')'], array_map(fn (array $r) => [$r['name'], $r['source'], $r['offset']], $rejected));
        }

        $this->line('');
        $this->info(sprintf(
            '%d nebulae get an estimated magnitude (%d already have this one), %d with the size of the catalogue.',
            count($changed),
            count($estimates) - count($changed),
            count(array_filter($changed, fn (array $e) => $e['size_from_catalogue'])),
        ));

        if ($apply) {
            NebulaBrightness::apply($estimates);
            $this->info('Written; the contrast reserves of these objects are calculated again when needed.');
        }

        return self::SUCCESS;
    }
}
