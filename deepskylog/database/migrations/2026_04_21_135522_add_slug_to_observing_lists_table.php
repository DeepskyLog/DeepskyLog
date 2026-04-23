<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('observing_lists', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
        });

        // Generate slugs for existing records
        $lists = DB::table('observing_lists')->orderBy('id')->get(['id', 'name']);
        foreach ($lists as $list) {
            $base = \Illuminate\Support\Str::slug(html_entity_decode($list->name, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            if (empty($base)) {
                $base = 'list';
            }
            $slug = $base;
            $i = 2;
            while (DB::table('observing_lists')->where('slug', $slug)->where('id', '<>', $list->id)->exists()) {
                $slug = $base . '-' . $i++;
            }
            DB::table('observing_lists')->where('id', $list->id)->update(['slug' => $slug]);
        }

        Schema::table('observing_lists', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('observing_lists', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
