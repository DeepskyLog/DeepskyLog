<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Items added after sort_order was introduced were never given one and
     * all have 0. Put them after the numbered items of their list, in the
     * order they were added.
     */
    public function up(): void
    {
        $listIds = DB::table('observing_list_items')
            ->where('sort_order', 0)
            ->distinct()
            ->pluck('observing_list_id');

        foreach ($listIds as $listId) {
            $ids = DB::table('observing_list_items')
                ->where('observing_list_id', $listId)
                ->orderByRaw('CASE WHEN sort_order = 0 THEN 1 ELSE 0 END')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->pluck('id');

            DB::transaction(function () use ($ids) {
                foreach ($ids as $position => $id) {
                    DB::table('observing_list_items')->where('id', $id)->update(['sort_order' => $position + 1]);
                }
            });
        }
    }

    public function down(): void
    {
        // Nothing to undo: the old values were all 0.
    }
};
