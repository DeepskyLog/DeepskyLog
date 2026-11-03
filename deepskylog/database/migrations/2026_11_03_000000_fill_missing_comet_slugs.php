<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration {
    /**
     * The comets without a slug, which their page (/object/{slug}) can't
     * find. 2025_11_17_120000_add_slug_to_cometobjects filled them, but it
     * swallowed its errors: on the server 156 of the 158 comets were left
     * without one. Same rule: the slug of the name, with the id appended
     * when that slug is taken. Comets that have a slug keep it.
     */
    public function up(): void
    {
        $taken = DB::table('cometobjects')->whereNotNull('slug')->where('slug', '<>', '')->pluck('slug')->flip()->all();

        DB::table('cometobjects')
            ->where(fn ($query) => $query->whereNull('slug')->orWhere('slug', ''))
            ->orderBy('id')
            ->get(['id', 'name'])
            ->each(function ($comet) use (&$taken) {
                $slug = Str::slug($comet->name ?? '', '-') ?: 'comet-'.$comet->id;
                if (isset($taken[$slug])) {
                    $slug .= '-'.$comet->id;
                }
                DB::table('cometobjects')->where('id', $comet->id)->update(['slug' => $slug]);
                $taken[$slug] = true;
            });
    }

    public function down(): void
    {
        // The slugs stay: old links to the comet pages use them.
    }
};
