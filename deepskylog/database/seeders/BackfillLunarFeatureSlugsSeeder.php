<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class BackfillLunarFeatureSlugsSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasColumn('lunar_features', 'slug')) {
            $this->command && $this->command->info('lunar_features.slug column missing; skipping lunar feature slug backfill.');
            return;
        }

        // Only the rows without a slug: observations refer to the slug, so an
        // existing one must never change (chunkById, as the filled rows drop out)
        DB::table('lunar_features')->where(fn ($q) => $q->whereNull('slug')->orWhere('slug', ''))->chunkById(500, function ($rows) {
            foreach ($rows as $r) {
                $base = Str::slug((string) $r->name, '-');
                $slug = $base;
                $i = 1;
                while (DB::table('lunar_features')->where('slug', $slug)->where('id', '!=', $r->id)->exists()) {
                    $slug = $base . '-' . $i;
                    $i++;
                }
                DB::table('lunar_features')->where('id', $r->id)->update(['slug' => $slug]);
            }
        });
    }
}
