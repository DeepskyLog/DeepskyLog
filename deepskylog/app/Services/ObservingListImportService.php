<?php

namespace App\Services;

use App\Models\ObservingList;
use App\Models\ObservingListItem;
use App\Models\ObserverListOld;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class ObservingListImportService
{
    protected Collection $unmappedUsernames;
    protected int $listsCreated = 0;
    protected int $itemsCreated = 0;
    protected int $listNamesProcessed = 0;

    public function __construct()
    {
        $this->unmappedUsernames = collect();
    }

    /**
     * Execute the import of observing lists from legacy table.
     */
    public function execute(bool $dryRun = false): array
    {
        // Get all unique (observerid, listname) combinations from legacy table
        $legacyLists = ObserverListOld::select('observerid', 'listname', 'public')
            ->distinct()
            ->orderBy('observerid')
            ->orderBy('listname')
            ->get();

        $this->listNamesProcessed = $legacyLists->count();

        if ($legacyLists->isEmpty()) {
            return [
                'success' => true,
                'lists_created' => 0,
                'items_created' => 0,
                'unmapped_users' => [],
            ];
        }

        DB::beginTransaction();

        try {
            foreach ($legacyLists as $legacyList) {
                $this->importObservingList($legacyList, $dryRun);
            }

            if (!$dryRun) {
                DB::commit();
            } else {
                DB::rollBack();
            }

            return [
                'success' => true,
                'lists_created' => $this->listsCreated,
                'items_created' => $this->itemsCreated,
                'unmapped_users' => $this->unmappedUsernames->unique()->toArray(),
                'list_names_processed' => $this->listNamesProcessed,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Import a single observing list and its items.
     */
    protected function importObservingList($legacyList, bool $dryRun): void
    {
        // Skip lists with empty list names (they cannot generate a valid slug)
        if (empty(trim($legacyList->listname ?? ''))) {
            return;
        }

        // Map username to user_id
        $user = User::where('username', $legacyList->observerid)->first();

        if (!$user) {
            $this->unmappedUsernames->push($legacyList->observerid);
            return;
        }

        // Check if observing list already exists (idempotent check)
        $observingList = ObservingList::where('owner_user_id', $user->id)
            ->where('name', $legacyList->listname)
            ->first();

        if ($observingList && !$dryRun) {
            // List already exists, skip
            return;
        }

        // Create or get observing list
        if (!$observingList && !$dryRun) {
            $observingList = ObservingList::create([
                'owner_user_id' => $user->id,
                'name' => $legacyList->listname,
                'description' => null,
                'public' => (bool) $legacyList->public,
            ]);
            $this->listsCreated++;
        }

        // Get all objects in this list
        $legacyItems = ObserverListOld::where('observerid', $legacyList->observerid)
            ->where('listname', $legacyList->listname)
            ->get();

        // Import items if list was created/found
        if ($observingList && !$dryRun) {
            foreach ($legacyItems as $legacyItem) {
                $this->importObservingListItem($observingList, $legacyItem, $user);
            }
        } else if ($dryRun) {
            $this->itemsCreated += $legacyItems->count();
        }
    }

    /**
     * Import a single observing list item.
     */
    protected function importObservingListItem(
        ObservingList $observingList,
        $legacyItem,
        User $owner
    ): void {
        // Resolve object name: prefer objectname, fall back to objectshowname
        $objectName = !empty($legacyItem->objectname)
            ? $legacyItem->objectname
            : ($legacyItem->objectshowname ?? null);

        if (empty($objectName)) {
            // No usable name for this item, skip it
            return;
        }

        // Check if item already exists (idempotent check)
        $existingItem = ObservingListItem::where('observing_list_id', $observingList->id)
            ->where('object_name', $objectName)
            ->first();

        if ($existingItem) {
            // Item already exists, skip
            return;
        }

        // Create item with legacy description if available
        ObservingListItem::create([
            'observing_list_id' => $observingList->id,
            'object_name' => $objectName,
            'item_description' => $legacyItem->description ?? null,
            'source_mode' => 'manual', // Imported items are manual
            'source_observation_id' => null,
            'added_by_user_id' => $owner->id,
        ]);

        $this->itemsCreated++;
    }

    /**
     * Set initial active observing list for users who have owned lists.
     */
    public function setInitialActiveLists(): array
    {
        $updated = 0;

        // For each user who owns at least one observing list,
        // set their active list to the first one (by creation date)
        $usersWithLists = User::whereHas('observingLists')
            ->get();

        foreach ($usersWithLists as $user) {
            if ($user->active_observing_list_id === null) {
                $firstList = $user->observingLists()
                    ->orderBy('created_at')
                    ->first();

                if ($firstList) {
                    $user->update(['active_observing_list_id' => $firstList->id]);
                    $updated++;
                }
            }
        }

        return [
            'success' => true,
            'users_updated' => $updated,
        ];
    }

    /**
     * Get statistics about the import.
     */
    public function getStats(): array
    {
        return [
            'lists_created' => $this->listsCreated,
            'items_created' => $this->itemsCreated,
            'unmapped_users' => $this->unmappedUsernames->unique()->toArray(),
        ];
    }
}
