<?php

namespace App\Console\Commands;

use App\Services\ObjectSearchIndex;
use App\Services\StruveSigmaNames;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Give the double stars of the Struves their traditional names (#480): every
 * alternative name in STF, STFA, STFB, STT and STTA gets Σ 1, Σ I 1, Σ II 1,
 * OΣ 1 or OΣΣ 1 as another alternative name of the same object (see
 * StruveSigmaNames). New objects and edited names get them when they are
 * saved; this command adds them to the objects already in the database, and
 * after an SQL import. Safe to run more than once.
 */
class AddStruveSigmaNames extends Command
{
    protected $signature = 'objects:add-struve-sigma-names
        {--apply : Write the names to the database}';

    protected $description = 'Add the traditional Σ names (Σ 1, OΣ 1, ...) of the Struve double stars as alternative names';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        if (! $apply) {
            $this->info('Running in dry-run mode. Use --apply to write the names.');
        }

        $new = StruveSigmaNames::missing();

        if ($this->getOutput()->isVerbose() && $new !== []) {
            $this->table(['Object', 'Name', 'New name'], array_map(fn ($n) => [$n['objectname'], $n['from'], $n['altname']], $new));
        }

        $counts = array_count_values(array_column($new, 'catalog'));
        foreach (StruveSigmaNames::PREFIXES as $catalog => $prefix) {
            $this->line(sprintf('%-5s → %s: %d new names', $catalog, $prefix, $counts[$catalog] ?? 0));
        }
        $this->info(count($new).' new names.');

        if ($apply && $new !== []) {
            DB::transaction(fn () => StruveSigmaNames::insert($new));

            $objects = array_unique(array_column($new, 'objectname'));
            $bar     = $this->output->createProgressBar(count($objects));
            foreach ($objects as $name) {
                ObjectSearchIndex::syncObject($name);
                $bar->advance();
            }
            $bar->finish();
            $this->line('');
            $this->info('Done. The search index of the changed objects is updated.');
        }

        return self::SUCCESS;
    }
}
