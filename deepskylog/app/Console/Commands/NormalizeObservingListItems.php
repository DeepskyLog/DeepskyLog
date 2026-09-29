<?php

namespace App\Console\Commands;

use App\Services\ObservingListItemNameResolver;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Store observing list items under the names the list tables match on.
 *
 * The list tables and exports match observing_list_items.object_name exactly
 * against objects.name and search_index.name. Items that were imported or
 * typed before names were resolved ("M31", "NGC 224", "andromeda galaxy")
 * therefore vanish from them. This renames each item to the name
 * ObservingListItemNameResolver gives it ("M 31"). When the list already
 * holds that object, the two items are merged: the one under the right name is
 * kept and the other's note is appended to it.
 *
 * Names that don't resolve to any object are reported, not deleted: they are
 * still shown on the list page, and the owner may want to correct them.
 *
 * Safe to run more than once: only items whose name changes are touched.
 */
class NormalizeObservingListItems extends Command
{
    protected $signature = 'observing-lists:normalize-items
        {--apply : Apply fixes to the database}
        {--list=* : Only these observing list ids}';

    protected $description = 'Rename observing list items to the canonical object names, and report unknown ones';

    public function handle(ObservingListItemNameResolver $resolver): int
    {
        $apply = (bool) $this->option('apply');

        if (! $apply) {
            $this->info('Running in dry-run mode. Use --apply to persist changes.');
        }

        $query = DB::table('observing_list_items')
            ->orderBy('observing_list_id')
            ->orderBy('id');
        $listIds = array_filter(array_map('intval', (array) $this->option('list')));
        if ($listIds !== []) {
            $query->whereIn('observing_list_id', $listIds);
        }
        $items = $query->get(['id', 'observing_list_id', 'object_name', 'item_description']);

        $resolved = $resolver->resolveMany($items->pluck('object_name'));

        $renames = [];
        $merges = [];
        $unknown = [];

        foreach ($items->groupBy('observing_list_id') as $listId => $listItems) {
            // The items already under their right name, by lower-cased name:
            // the unique index on (list, object_name) is case-insensitive on MySQL.
            $taken = [];
            foreach ($listItems as $item) {
                if (($resolved[$resolver->clean($item->object_name)] ?? null) === $item->object_name) {
                    $taken[mb_strtolower($item->object_name)] = $item;
                }
            }

            foreach ($listItems as $item) {
                $name = $resolved[$resolver->clean($item->object_name)] ?? null;

                if ($name === null) {
                    $unknown[] = [$listId, $item->id, $item->object_name];
                } elseif ($name !== $item->object_name) {
                    $key = mb_strtolower($name);
                    if (isset($taken[$key])) {
                        $merges[] = [$item, $taken[$key], $name];
                    } else {
                        $renames[] = [$item, $name];
                        $taken[$key] = $item;
                    }
                }
            }
        }

        if ($renames !== [] || $merges !== []) {
            $this->table(
                ['List', 'Item', 'Name', 'New name', 'Action'],
                array_merge(
                    array_map(fn ($r) => [$r[0]->observing_list_id, $r[0]->id, $r[0]->object_name, $r[1], 'rename'], $renames),
                    array_map(fn ($m) => [$m[0]->observing_list_id, $m[0]->id, $m[0]->object_name, $m[2], 'merge into item ' . $m[1]->id], $merges),
                )
            );
        }

        if ($unknown !== []) {
            $this->newLine();
            $this->warn('Items that match no object (left unchanged):');
            $this->table(['List', 'Item', 'Name'], $unknown);
        }

        if ($apply) {
            DB::transaction(function () use ($renames, $merges) {
                foreach ($renames as [$item, $name]) {
                    DB::table('observing_list_items')->where('id', $item->id)
                        ->update(['object_name' => $name, 'updated_at' => now()]);
                }

                foreach ($merges as [$item, $target]) {
                    $note = $this->mergedNote($target->item_description, $item->item_description);
                    if ($note !== $target->item_description) {
                        DB::table('observing_list_items')->where('id', $target->id)
                            ->update(['item_description' => $note, 'updated_at' => now()]);
                        // A later merge into the same item must see this note.
                        $target->item_description = $note;
                    }
                    DB::table('observing_list_items')->where('id', $item->id)->delete();
                }
            });
        }

        $this->newLine();
        $this->info('Items renamed: ' . count($renames));
        $this->info('Items merged into an existing item: ' . count($merges));
        $this->info('Items matching no object: ' . count($unknown));

        if (! $apply && ($renames !== [] || $merges !== [])) {
            $this->warn('Dry-run only: no changes were written. Re-run with --apply to apply fixes.');
        }

        return self::SUCCESS;
    }

    /**
     * The note of the kept item after merging another item's note into it.
     */
    private function mergedNote(?string $kept, ?string $merged): ?string
    {
        $kept = trim((string) $kept);
        $merged = trim((string) $merged);

        if ($merged === '' || $merged === $kept) {
            return $kept === '' ? null : $kept;
        }

        return $kept === '' ? $merged : $kept . "\n\n" . $merged;
    }
}
