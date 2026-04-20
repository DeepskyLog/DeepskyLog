<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $missing = [
            ['objectname' => 'M 65', 'slug' => 'm-65', 'catalog' => 'M', 'catindex' => 65, 'altname' => 'M 65'],
            ['objectname' => 'M 111', 'slug' => 'm-111', 'catalog' => 'M', 'catindex' => 111, 'altname' => 'M 111'],
        ];

        foreach ($missing as $row) {
            $exists = DB::table('objectnames')
                ->where('objectname', $row['objectname'])
                ->where('catalog', 'M')
                ->exists();

            if (!$exists) {
                DB::table('objectnames')->insert(array_merge($row, [
                    'timestamp' => now(),
                ]));
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('objectnames')
            ->whereIn('objectname', ['M 65', 'M 111'])
            ->where('catalog', 'M')
            ->delete();
    }
};
