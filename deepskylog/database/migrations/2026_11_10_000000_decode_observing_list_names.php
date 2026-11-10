<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The import of the old site's observing lists (April 2026) stored about a
 * hundred names HTML-encoded (`Ciel Extr&ecirc;me 7`), which the edit form
 * showed as such. Store them as plain text, as new lists are; a name whose
 * decoded form the owner already uses for another list keeps its encoding.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('observing_lists')
            ->where('name', 'like', '%&%;%')
            ->orderBy('id')
            ->get(['id', 'owner_user_id', 'name'])
            ->each(function ($list) {
                $name = html_entity_decode($list->name, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                if ($name === $list->name || mb_strlen($name) > 255) {
                    return;
                }

                $taken = DB::table('observing_lists')
                    ->where('owner_user_id', $list->owner_user_id)
                    ->where('id', '!=', $list->id)
                    ->where('name', $name)
                    ->exists();

                if (! $taken) {
                    DB::table('observing_lists')->where('id', $list->id)->update(['name' => $name]);
                }
            });
    }

    public function down(): void
    {
        // The encoded names are not worth restoring.
    }
};
