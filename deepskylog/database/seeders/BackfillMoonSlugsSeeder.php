<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class BackfillMoonSlugsSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasColumn('moons', 'slug')) {
            $this->command && $this->command->info('moons.slug column missing; skipping moons slug backfill.');
            return;
        }

        // Only the rows without a slug: observations refer to the slug, so an
        // existing one must never change (chunkById, as the filled rows drop out)
        DB::table('moons')->where(fn ($q) => $q->whereNull('slug')->orWhere('slug', ''))->chunkById(200, function ($rows) {
            foreach ($rows as $r) {
                $base = Str::slug((string) $r->name, '-');
                $slug = $base;
                $i = 1;
                while (DB::table('moons')->where('slug', $slug)->where('id', '!=', $r->id)->exists()) {
                    $slug = $base . '-' . $i;
                    $i++;
                }
                DB::table('moons')->where('id', $r->id)->update(['slug' => $slug]);
            }
        });
    }
}
