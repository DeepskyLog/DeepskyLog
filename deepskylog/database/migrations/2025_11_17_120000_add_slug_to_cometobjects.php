<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds a nullable unique `slug` column to `cometobjects` and
     * backfills values based on the `name` column.
     *
     * Note: running this migration will attempt to write slugs for existing
     * rows. If collisions occur, the migration appends the numeric id to make
     * the slug unique.
     */
    public function up(): void
    {
        if (! Schema::hasTable('cometobjects')) {
            return;
        }

        Schema::table('cometobjects', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
        });

        // Backfill slugs from name; ensure uniqueness by appending id on collisions
        try {
            $rows = DB::table('cometobjects')->select(['id', 'name'])->get();
            $seen = [];
            foreach ($rows as $r) {
                $base = Str::slug($r->name ?? '', '-');
                $slug = $base ?: 'comet-' . $r->id;
                if (empty($slug)) $slug = 'comet-' . $r->id;

                // ensure uniqueness
                if (isset($seen[$slug])) {
                    $slug = $slug . '-' . $r->id;
                }

                // also check DB for existing slug collisions (defensive)
                $exists = DB::table('cometobjects')->where('slug', $slug)->where('id', '!=', $r->id)->exists();
                if ($exists) {
                    $slug = $slug . '-' . $r->id;
                }

                DB::table('cometobjects')->where('id', $r->id)->update(['slug' => $slug]);
                $seen[$slug] = true;
            }
        } catch (\Throwable $e) {
            // Fail silently during migration; admins can re-run a slug backfill if needed.
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('cometobjects')) {
            return;
        }

        Schema::table('cometobjects', function (Blueprint $table) {
            if (Schema::hasColumn('cometobjects', 'slug')) {
                $table->dropColumn('slug');
            }
        });
    }
};
