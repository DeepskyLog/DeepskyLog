<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Equipment, locations and sets are found by user and slug, but their slugs
 * were not unique per user, so only one of the rows sharing a slug could be
 * opened. The oldest row keeps the slug, so existing links keep working; the
 * others get the suffix sluggable now gives a duplicate (-2, -3, ...).
 */
return new class extends Migration
{
    private const TABLES = ['instruments', 'eyepieces', 'filters', 'lenses', 'locations', 'instrument_sets'];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            $duplicates = DB::table($table)
                ->select('user_id', 'slug')
                ->whereNotNull('slug')
                ->groupBy('user_id', 'slug')
                ->havingRaw('COUNT(*) > 1')
                ->get();

            foreach ($duplicates as $duplicate) {
                $taken = DB::table($table)
                    ->where('user_id', $duplicate->user_id)
                    ->pluck('slug')
                    ->flip();

                $ids = DB::table($table)
                    ->where('user_id', $duplicate->user_id)
                    ->where('slug', $duplicate->slug)
                    ->orderBy('id')
                    ->pluck('id')
                    ->slice(1);

                $suffix = 2;
                foreach ($ids as $id) {
                    while ($taken->has($duplicate->slug.'-'.$suffix)) {
                        $suffix++;
                    }
                    $slug = $duplicate->slug.'-'.$suffix;
                    $taken[$slug] = true;

                    DB::table($table)->where('id', $id)->update(['slug' => $slug]);
                }
            }
        }
    }

    public function down(): void
    {
        // The old slugs are not kept; they could not all be opened anyway.
    }
};
