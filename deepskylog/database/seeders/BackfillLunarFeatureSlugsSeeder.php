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

        DB::table('lunar_features')->orderBy('name')->chunk(500, function ($rows) {
            foreach ($rows as $r) {
                $base = Str::slug((string) $r->name, '-');
                $slug = $base;
                $i = 1;
                while (DB::table('lunar_features')->where('slug', $slug)->exists()) {
                    $slug = $base . '-' . $i;
                    $i++;
                }
                DB::table('lunar_features')->where('id', $r->id)->update(['slug' => $slug]);
            }
        });
    }
}
