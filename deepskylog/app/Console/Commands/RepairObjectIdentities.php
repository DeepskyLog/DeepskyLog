<?php

namespace App\Console\Commands;

use App\Models\Observation;
use App\Services\ObjectMerger;
use App\Services\ObjectSearchIndex;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Repair objects that are another object, another kind of object, or no
 * object at all, found by objects:repair-measurements as far too bright for
 * their size and checked against SIMBAD and the PGC (VizieR VII/237):
 *
 * - MERGES: duplicates, merged into the object they are (ObjectMerger), so
 *   their names become its alternative names;
 * - TYPES: objects of the wrong type;
 * - DELETES: objects that do not exist, deleted unless they have
 *   observations.
 *
 * Safe to run more than once; run it after an SQL import.
 */
class RepairObjectIdentities extends Command
{
    /** Duplicate => the object it is. */
    public const MERGES = [
        // the planetary nebula, which already has IC 1108 as alternative name
        'IC 1108' => 'NGC 5882',
        // 2' from NGC 3450 (PGC 32270)
        'MCG -03-28-004' => 'NGC 3450',
        // 2' from UGC 12889 (PGC 2), across 0h
        'MCG +08-01-018' => 'UGC 12889',
        // the same galaxy in the PGC (54470) and SIMBAD, stored as 14.5 mag and 6"
        'NGC 5906' => 'NGC 5907',
    ];

    /** Object => its type. */
    public const TYPES = [
        // a galactic nebula in SIMBAD (1.4' × 1.2'), in the Milky Way in Serpens
        'MCG +01-48-001' => 'BRTNB',
    ];

    /** Objects that do not exist. */
    public const DELETES = [
        // in neither SIMBAD nor the PGC
        'KAZ 50',
    ];

    protected $signature = 'objects:repair-identities
        {--apply : Write the changes to the database}';

    protected $description = 'Merge duplicate objects, correct wrong types and delete objects that do not exist';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        if (! $apply) {
            $this->info('Running in dry-run mode. Use --apply to write the changes.');
        }

        $exists = fn (string $name) => DB::table('objects')->where('name', $name)->exists();
        $rows   = [];

        foreach (self::MERGES as $duplicate => $into) {
            if (! $exists($duplicate)) {
                continue;
            }
            if (! $exists($into)) {
                $this->warn("{$into} is not there: {$duplicate} is not merged.");

                continue;
            }
            $rows[] = ['Merge', $duplicate, $into, $this->references($duplicate)];
            if ($apply) {
                ObjectMerger::merge($duplicate, $into);
            }
        }

        foreach (self::TYPES as $name => $type) {
            $current = DB::table('objects')->where('name', $name)->value('type');
            if ($current === null || $current === $type) {
                continue;
            }
            $rows[] = ['Type', $name, "{$current} → {$type}", ''];
            if ($apply) {
                DB::table('objects')->where('name', $name)->update(['type' => $type]);
                ObjectSearchIndex::syncObject($name);
            }
        }

        foreach (self::DELETES as $name) {
            if (! $exists($name)) {
                continue;
            }
            $observations = Observation::ofDeepskyObject($name)->count();
            if ($observations > 0) {
                $this->warn("{$name} has {$observations} observation(s) and is not deleted.");

                continue;
            }
            $rows[] = ['Delete', $name, '', $this->references($name)];
            if ($apply) {
                ObjectMerger::delete($name);
            }
        }

        if ($rows !== []) {
            $this->table(['Change', 'Object', 'Into / type', 'Observations, list items'], $rows);
        }
        $this->info(count($rows).' objects to repair.'.($apply && $rows !== [] ? ' Written.' : ''));

        return self::SUCCESS;
    }

    private function references(string $name): string
    {
        return Observation::ofDeepskyObject($name)->count()
            .', '.DB::table('observing_list_items')->where('object_name', $name)->count();
    }
}
