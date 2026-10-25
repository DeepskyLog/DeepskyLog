<?php

namespace App\Console\Commands;

use App\Services\ObjectSearchIndex;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Remove the object types that hardly any object uses (#409), giving their
 * objects one of the remaining types:
 *
 * - ENRNN (emission and reflection nebula: the Orion Nebula complex) becomes
 *   an H-II region;
 * - ENSTR (emission nebula around a star) an emission nebula;
 * - STNEB (star + nebula complex) an emission nebula, or a reflection nebula
 *   for the nebulae around a young star;
 * - RNHII (H-II region, nebula complex) an H-II region, or a reflection nebula;
 * - CLANB (cluster with nebulosity) an H-II region (M 17, M 20, IC 2944), an
 *   open cluster with nebulosity (NGC 1980, NGC 2264), or a cluster with
 *   nebulosity in a galaxy (in the Magellanic Clouds).
 *
 * OBJECT_TYPES holds the type of each object of these types; an object that is
 * not listed there (added later, or by an SQL import) gets the default type of
 * REMOVED_TYPES. The types themselves are deleted from deepskytypes and
 * target_types. REMOVED_OBJECTS are test objects that are deleted, unless they
 * have observations.
 *
 * Observations store no type, and the contrast reserve does not depend on it,
 * so only the objects and their search_index rows change. Safe to run more
 * than once; run it after an SQL import.
 */
class MergeObjectTypes extends Command
{
    /** The removed types, with the type their objects get by default. */
    public const REMOVED_TYPES = [
        'CLANB' => 'OPNCL',
        'ENRNN' => 'HII',
        'ENSTR' => 'EMINB',
        'RNHII' => 'HII',
        'STNEB' => 'EMINB',
    ];

    /** The new type of the objects of the removed types, where it is not the default. */
    public const OBJECT_TYPES = [
        // CLANB
        'M 17' => 'HII',
        'M 20' => 'HII',
        'IC 2944' => 'HII',
        'NGC 456' => 'GACAN',
        'NGC 1920' => 'GACAN',
        // ENSTR
        'IC 4592' => 'REFNB',
        // RNHII
        'IC 59' => 'REFNB',
        'NGC 1999' => 'REFNB',
        'NGC 6589' => 'REFNB',
        'vdB 93' => 'REFNB',
        // STNEB
        'Ced 33' => 'REFNB',
        'GN 08.37.7' => 'REFNB',
        'LKHA 233' => 'REFNB',
    ];

    /** Test objects that are deleted. */
    public const REMOVED_OBJECTS = ['Hickson 752'];

    protected $signature = 'objects:merge-types
        {--apply : Write the changes to the database}';

    protected $description = 'Give the objects of the rarely used object types another type, and remove those types';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        if (! $apply) {
            $this->info('Running in dry-run mode. Use --apply to write the changes.');
        }

        $removed = $this->removedObjects();
        $changes = $this->typeChanges(array_column($removed['delete'], 'name'));

        if ($changes !== []) {
            $this->table(['Object', 'Type', 'New type'], array_map(fn ($c) => [$c['name'], $c['type'], $c['new_type']], $changes));
        }
        foreach ($removed['keep'] as $object) {
            $this->warn(sprintf('%s has %d observation(s) and is not deleted.', $object['name'], $object['observations']));
        }

        if ($apply) {
            $this->deleteObjects($removed['delete']);
            $this->changeTypes($changes);
            DB::table('deepskytypes')->whereIn('code', array_keys(self::REMOVED_TYPES))->delete();
            DB::table('target_types')->whereIn('id', array_keys(self::REMOVED_TYPES))->delete();
            Cache::forget('search_index:source_types');
        }

        $this->info(sprintf('Objects given another type: %d', count($changes)));
        $this->info(sprintf('Test objects deleted: %d', count($removed['delete'])));

        return self::SUCCESS;
    }

    /**
     * The objects of the removed types, with their new type.
     *
     * @param  list<string>  $skip  objects that are deleted instead
     * @return list<array{name: string, type: string, new_type: string}>
     */
    private function typeChanges(array $skip): array
    {
        return DB::table('objects')
            ->whereIn('type', array_keys(self::REMOVED_TYPES))
            ->whereNotIn('name', $skip)
            ->orderBy('type')->orderBy('name')
            ->get(['name', 'type'])
            ->map(fn ($o) => [
                'name' => $o->name,
                'type' => $o->type,
                'new_type' => self::OBJECT_TYPES[$o->name] ?? self::REMOVED_TYPES[$o->type],
            ])
            ->all();
    }

    /**
     * The test objects in the database: those to delete, and those kept
     * because they have observations.
     *
     * @return array{delete: list<array{name: string, slug: ?string}>, keep: list<array{name: string, observations: int}>}
     */
    private function removedObjects(): array
    {
        $result = ['delete' => [], 'keep' => []];
        foreach (DB::table('objects')->whereIn('name', self::REMOVED_OBJECTS)->get(['name', 'slug']) as $object) {
            $observations = DB::table('observations')
                ->where('target_table', 'objects')
                ->where('target_key', $object->name)
                ->count();
            if ($observations > 0) {
                $result['keep'][] = ['name' => $object->name, 'observations' => $observations];
            } else {
                $result['delete'][] = ['name' => $object->name, 'slug' => $object->slug];
            }
        }

        return $result;
    }

    private function deleteObjects(array $objects): void
    {
        foreach ($objects as $object) {
            DB::transaction(function () use ($object) {
                // Before the objectnames rows go, so the lookups of the aliases are forgotten too
                ObjectSearchIndex::removeObject($object['name'], $object['slug']);
                DB::table('objectnames')->where('objectname', $object['name'])->delete();
                DB::table('objectpartof')->where('objectname', $object['name'])->orWhere('partofname', $object['name'])->delete();
                DB::table('observing_list_items')->where('object_name', $object['name'])->delete();
                DB::table('user_object_metrics')->where('object_name', $object['name'])->delete();
                DB::table('objects')->where('name', $object['name'])->delete();
            });
        }
    }

    private function changeTypes(array $changes): void
    {
        DB::transaction(function () use ($changes) {
            foreach ($changes as $change) {
                DB::table('objects')->where('name', $change['name'])->update(['type' => $change['new_type']]);
                // The object's own row; its aliases have source_type 'alias'
                DB::table('search_index')
                    ->where('source_table', 'objects')
                    ->where('source_type', $change['type'])
                    ->where('name', $change['name'])
                    ->update(['source_type' => $change['new_type']]);
            }
        });
    }
}
