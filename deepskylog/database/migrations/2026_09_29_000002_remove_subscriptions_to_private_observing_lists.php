<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * A private observing list is only for its owner. Remove the
     * subscriptions to lists that were made private before that was enforced,
     * and clear them as active list of everybody but their owner.
     */
    public function up(): void
    {
        $privateListIds = DB::table('observing_lists')->where('public', false)->pluck('id');

        foreach ($privateListIds->chunk(500) as $chunk) {
            DB::table('observing_list_subscriptions')->whereIn('observing_list_id', $chunk->all())->delete();

            DB::table('users')
                ->whereIn('active_observing_list_id', $chunk->all())
                ->whereNotExists(function ($query) {
                    $query->selectRaw('1')
                        ->from('observing_lists')
                        ->whereColumn('observing_lists.id', 'users.active_observing_list_id')
                        ->whereColumn('observing_lists.owner_user_id', 'users.id');
                })
                ->update(['active_observing_list_id' => null]);
        }
    }

    public function down(): void
    {
        // The removed subscriptions cannot be restored.
    }
};
