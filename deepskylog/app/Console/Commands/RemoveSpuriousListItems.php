<?php

namespace App\Console\Commands;

use App\Models\ObservingList;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\ResponseCache\Facades\ResponseCache;

/**
 * Remove the M 1 the import of the old site's observing lists put first in
 * lists it does not belong to.
 *
 * The import of April 2026 gave about 200 lists (NSOG Apus, NGC 201 - 300,
 * ...) an item M 1 in the first place, mostly with the same note ("De
 * Krabnevel: de eerste uit de lijst van Messier.") or the Clark description
 * of M 1, and created lists without a name holding only that item. Only the
 * M 1 in the first place of a list made by that import is removed, and not
 * in lists whose name says M 1 belongs there (Messier, the Crab Nebula,
 * supernova remnants): those are listed to check by hand. A list the removal
 * leaves empty is deleted, unless it has comments, likes or subscribers.
 *
 * Safe to run more than once.
 */
class RemoveSpuriousListItems extends Command
{
    /**
     * The import ran on 2026-04-21; observing lists went live after it.
     */
    public const IMPORTED_BEFORE = '2026-04-22 00:00:00';

    public const OBJECT_NAME = 'M 1';

    /**
     * List names in which M 1 belongs.
     */
    private const KEEP_PATTERN = '/messier|crab|krab|supernova|(^|[^a-z0-9])m ?1([^0-9]|$)/i';

    protected $signature = 'observing-lists:remove-spurious-items
        {--apply : Remove the items}';

    protected $description = 'Remove the M 1 the import of the old observing lists put first in lists it does not belong to';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');

        if (! $apply) {
            $this->info('Running in dry-run mode. Use --apply to persist changes.');
        }

        $candidates = DB::table('observing_list_items as i')
            ->join('observing_lists as l', 'l.id', '=', 'i.observing_list_id')
            ->leftJoin('users as u', 'u.id', '=', 'l.owner_user_id')
            ->where('i.object_name', self::OBJECT_NAME)
            ->where('i.sort_order', 1)
            ->where('i.created_at', '<', self::IMPORTED_BEFORE)
            ->orderBy('u.username')
            ->orderBy('l.name')
            ->get([
                'i.id', 'i.observing_list_id', 'l.name', 'l.public', 'u.username',
                DB::raw('(select count(*) from observing_list_items x where x.observing_list_id = l.id) as items'),
            ]);

        [$kept, $removed] = $candidates->partition(
            fn ($item) => preg_match(self::KEEP_PATTERN, html_entity_decode((string) $item->name, ENT_QUOTES | ENT_HTML5, 'UTF-8')) === 1
        );

        $emptied = $removed->filter(fn ($item) => (int) $item->items === 1);
        $engaged = $emptied->isEmpty() ? collect() : ObservingList::whereIn('id', $emptied->pluck('observing_list_id'))
            ->where(fn ($q) => $q->where('comments_count', '>', 0)->orWhere('likes_count', '>', 0)->orWhereHas('subscriptions'))
            ->pluck('id');
        $listsToDelete = $emptied->pluck('observing_list_id')->diff($engaged)->values();

        if ($kept->isNotEmpty()) {
            $this->line('Kept, the list name suggests M 1 belongs there:');
            $this->table(['Observer', 'List', 'Items'], $kept->map(fn ($i) => [$i->username, $this->listName($i), $i->items])->all());
        }

        if ($removed->isNotEmpty() && $this->getOutput()->isVerbose()) {
            $this->table(['Observer', 'List', 'Items', 'Public'], $removed->map(fn ($i) => [$i->username, $this->listName($i), $i->items, $i->public ? 'yes' : 'no'])->all());
        }

        if ($engaged->isNotEmpty()) {
            $this->line('Left empty, but kept because of their comments, likes or subscribers:');
            $this->table(['Observer', 'List'], $emptied->whereIn('observing_list_id', $engaged)->map(fn ($i) => [$i->username, $this->listName($i)])->all());
        }

        if ($apply && $removed->isNotEmpty()) {
            DB::transaction(function () use ($removed, $listsToDelete) {
                DB::table('observing_list_items')->whereIn('id', $removed->pluck('id'))->delete();
                // Through the model, so nobody keeps a deleted list as active list.
                ObservingList::whereIn('id', $listsToDelete)->get()->each->delete();
            });

            // Public lists changed; the deletes above bypass the model events.
            ResponseCache::clear();
        }

        $this->newLine();
        $this->info('Spurious items: '.$removed->count().' (in '.$removed->where('public', 1)->count().' public lists)');
        $this->info('Empty lists to delete: '.$listsToDelete->count());
        $this->info('Kept for checking: '.$kept->count());

        if (! $apply && $removed->isNotEmpty()) {
            $this->warn('Dry-run only: no changes were written. Re-run with --apply to apply fixes.');
        }

        return self::SUCCESS;
    }

    private function listName(object $item): string
    {
        return trim(html_entity_decode((string) $item->name, ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?: '(no name)';
    }
}
