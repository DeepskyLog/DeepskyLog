<?php

use App\Models\SearchIndex;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * The Moon was both a moon (moons, slug moon) and a lunar feature of type
     * "other" (lunar_features, slug moon), so a name lookup found a different
     * one depending on the order of the tables, and a Moon observation counted
     * as an observed lunar feature. The lunar features belong to the Moon of
     * the moons table (moon_id), which is kept; the lunar feature is removed.
     *
     * Observations of the lunar feature are moved to the Moon. Production had
     * none (it only allowed deep-sky and comet observations).
     */
    public function up(): void
    {
        $feature = DB::table('lunar_features')->where('name', 'Moon')->first(['id', 'slug']);
        if ($feature === null) {
            return;
        }

        $moon = DB::table('moons')->where('name', 'Moon')->first(['slug']);
        if ($moon !== null) {
            DB::table('observations')
                ->where('target_table', 'lunar_features')
                ->whereIn('target_key', array_filter([$feature->slug, 'Moon', (string) $feature->id]))
                ->update([
                    'target_table' => 'moons',
                    'target_key' => $moon->slug,
                    'target_type' => 'lunar',
                    'target_name' => 'Moon',
                ]);
        }

        DB::table('search_index')
            ->where('source_table', 'lunar_features')
            ->where('source_pk', (string) $feature->id)
            ->delete();
        DB::table('lunar_features')->where('id', $feature->id)->delete();

        // The Moon of the moons table is the only one with that name now.
        SearchIndex::markUniqueNames(['Moon']);
    }

    /**
     * The lunar feature is not added again: the code after this migration
     * reads the Moon from the moons table, as the code before it could.
     */
    public function down(): void
    {
    }
};
