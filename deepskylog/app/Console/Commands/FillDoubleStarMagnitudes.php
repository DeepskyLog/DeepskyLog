<?php

namespace App\Console\Commands;

use App\Services\WashingtonDoubleStars;
use App\Support\MemoryLimit;
use Illuminate\Console\Command;

/**
 * Fill in the magnitudes of the companions of the double stars, and correct
 * the primary magnitude, separation, position angle and position where they
 * are missing, belong to another pair or are off, from the Washington Double
 * Star Catalog. See App\Services\WashingtonDoubleStars. Safe to run more
 * than once: values that agree with the catalog are never touched.
 */
class FillDoubleStarMagnitudes extends Command
{
    protected $signature = 'objects:fill-double-star-magnitudes
        {--apply : Write the magnitudes to the database}
        {--wds= : A WDS summary file (wdsweb_summ2.txt) to read instead of database/wds_double_stars.txt}';

    protected $description = 'Fill in the companion magnitudes of the double stars, and correct their separations and positions, from the Washington Double Star Catalog';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        if (! $apply) {
            $this->info('Running in dry-run mode. Use --apply to write the magnitudes.');
        }
        // The full summary file holds about 160,000 pairs
        MemoryLimit::atLeast('1024M');

        ['matches' => $matches, 'rejected' => $rejected, 'unmatched' => $unmatched] = WashingtonDoubleStars::plan($this->option('wds') ?: null);

        $value = fn (mixed $v) => $v === null ? '—' : (string) $v;
        if ($this->output->isVerbose() && $matches !== []) {
            $this->table(
                ['Object', 'Found by', 'WDS', 'Changes'],
                array_map(fn (array $m) => [
                    $m['name'],
                    $m['how'],
                    $m['wds'],
                    implode(', ', array_map(fn (string $field, mixed $new) => $field.' '.$value($m['old'][$field]).' → '.$new, array_keys($m['changes']), $m['changes']))
                        .(isset($m['changes']['ra']) ? ' (moved '.$m['offset'].'′)' : ''),
                ], $matches)
            );
        }

        if ($rejected !== []) {
            $this->line('');
            $this->warn('Double stars not changed, to check by hand ('.count($rejected).'):');
            $this->table(
                ['Object', 'WDS', 'Why', 'Offset (\')', 'Separation (″)', 'Primary', 'Companion (WDS)'],
                array_map(fn (array $r) => [
                    $r['name'],
                    $r['wds'],
                    $r['reason'],
                    $r['offset'],
                    $value($r['old']['diam1']).' / '.$value($r['wds_pair']['diam1']),
                    $value($r['old']['mag']).' / '.$value($r['wds_pair']['mag']),
                    $value($r['wds_pair']['subr']),
                ], $rejected)
            );
        }

        if ($unmatched !== [] && $this->output->isVerbose()) {
            $this->line('');
            $this->warn('Double stars not found in the WDS ('.count($unmatched).'): '.implode(', ', $unmatched));
        }

        $count = fn (string $field) => count(array_filter($matches, fn (array $m) => array_key_exists($field, $m['changes'])));
        $this->line('');
        $this->info(sprintf(
            '%d double stars get a companion magnitude, %d a primary magnitude, %d a separation, %d a position angle and %d a new position; %d to check, %d not found.',
            $count('subr'), $count('mag'), $count('diam1'), $count('pa'), $count('ra'), count($rejected), count($unmatched),
        ));

        if ($apply) {
            WashingtonDoubleStars::apply($matches);
            $this->info('Written.');
        }

        return self::SUCCESS;
    }
}
