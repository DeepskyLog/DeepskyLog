<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;

class BackfillPlanetSlugsSeeder extends Seeder
{
    public function run(): void
    {
    if (! Schema::hasColumn('planets', 'slug')) {
            $this->command && $this->command->info('planets.slug column missing; skipping planets slug backfill.');
            return;
        }

        DB::table('planets')->orderBy('name')->chunk(100, function ($rows) {
            foreach ($rows as $r) {
                $base = Str::slug((string) $r->name, '-');
                $slug = $base;
                $i = 1;
                while (DB::table('planets')->where('slug', $slug)->exists()) {
                    $slug = $base . '-' . $i;
                    $i++;
                }
                DB::table('planets')->where('id', $r->id)->update(['slug' => $slug]);
            }
        });
    }
}
