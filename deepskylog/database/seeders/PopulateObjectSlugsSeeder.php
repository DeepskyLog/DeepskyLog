<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PopulateObjectSlugsSeeder extends Seeder
{
    public function run(): void
    {
        // Populate slugs for objects table (canonical deepsky objects)
        DB::table('objects')->orderBy('name')->chunk(200, function ($rows) {
            foreach ($rows as $r) {
                $base = Str::slug((string) $r->name, '-');
                $slug = $base;
                $i = 1;
                while (DB::table('objects')->where('slug', $slug)->exists()) {
                    $slug = $base . '-' . $i;
                    $i++;
                }
                DB::table('objects')->where('name', $r->name)->update(['slug' => $slug]);
            }
        });

        // Populate slugs for objectnames table (aliases / canonical names)
        DB::table('objectnames')->orderBy('objectname')->chunk(200, function ($rows) {
            foreach ($rows as $r) {
                $source = $r->altname ?: $r->objectname;
                $base = Str::slug((string) $source, '-');
                $slug = $base;
                $i = 1;
                while (DB::table('objectnames')->where('slug', $slug)->exists()) {
                    $slug = $base . '-' . $i;
                    $i++;
                }
                DB::table('objectnames')->where('id', $r->id)->update(['slug' => $slug]);
            }
        });
    }
}
