<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class BackfillAsteroidSlugsSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasColumn('asteroids', 'slug')) {
            $this->command && $this->command->info('asteroids.slug column missing; skipping asteroid slug backfill.');
            return;
        }

        // Only the rows without a slug: observations refer to the slug, so an
        // existing one must never change (chunkById, as the filled rows drop out)
        DB::table('asteroids')->where(fn ($q) => $q->whereNull('slug')->orWhere('slug', ''))->chunkById(200, function ($rows) {
            foreach ($rows as $r) {
                $base = Str::slug((string) ($r->name ?: $r->designation), '-');
                $slug = $base;
                $i = 1;
                while (DB::table('asteroids')->where('slug', $slug)->where('id', '!=', $r->id)->exists()) {
                    $slug = $base . '-' . $i;
                    $i++;
                }
                DB::table('asteroids')->where('id', $r->id)->update(['slug' => $slug]);
            }
        });
    }
}
