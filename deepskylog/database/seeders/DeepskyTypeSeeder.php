<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeepskyTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            // Authoritative mapping from legacy snippet + additional legacy codes discovered in the codebase
            ['code' => 'OPNCL', 'name' => 'Open cluster'],
            ['code' => 'GLOCL', 'name' => 'Globular cluster'],
            ['code' => 'GALXY', 'name' => 'Galaxy'],
            ['code' => 'PLNNB', 'name' => 'Planetary nebula'],
            ['code' => 'EMINB', 'name' => 'Emission nebula'],
            ['code' => 'REFNB', 'name' => 'Reflection nebula'],
            ['code' => 'HII',   'name' => 'H-II region'],
            ['code' => 'SNREM', 'name' => 'Supernova remnant'],
            ['code' => 'WRNEB', 'name' => 'Wolf-Rayet nebula / weird nebula'],
            ['code' => 'QUASR', 'name' => 'Quasar / Active nucleus'],
            ['code' => 'DS',    'name' => 'Double star / multiple system'],
            ['code' => 'GALCL', 'name' => 'Galaxy cluster / grouping'],
            ['code' => 'DRKNB', 'name' => 'Dark nebula'],
            ['code' => 'ASTER', 'name' => 'Asterism / informal grouping'],
            ['code' => 'AA1STAR', 'name' => '1 star'],
            ['code' => 'NONEX', 'name' => 'Non-existent / catalog error'],
            ['code' => 'BRTNB', 'name' => 'Bright nebula / emission/reflection'],
            ['code' => 'GXAGC', 'name' => 'Globular cluster in galaxy'],
            ['code' => 'GACAN', 'name' => 'Cluster with nebulosity in galaxy'],
            ['code' => 'SNOVA', 'name' => 'Supernova'],
            ['code' => 'GXADN', 'name' => 'Diffuse nebula in galaxy'],
        ];

        // Use upsert to be idempotent if seeder is re-run
        foreach ($data as $row) {
            DB::table('deepskytypes')->updateOrInsert(['code' => $row['code']], $row);
        }
    }
}
