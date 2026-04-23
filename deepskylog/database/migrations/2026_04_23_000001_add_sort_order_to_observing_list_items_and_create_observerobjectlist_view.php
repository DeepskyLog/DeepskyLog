<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * - Adds a sort_order column to observing_list_items so that the order
     *   of objects within a list can be preserved (mirrors objectplace from
     *   the legacy observerobjectlist table).
     * - Creates a compatibility VIEW named observerobjectlist that exposes
     *   the new normalised tables in the flat schema expected by the legacy
     *   PHP application.
     */
    public function up(): void
    {
        // 1. Add sort_order column
        Schema::table('observing_list_items', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->after('item_description');
            $table->index(['observing_list_id', 'sort_order'], 'idx_list_sort');
        });

        // 2. Populate sort_order based on insertion order within each list
        DB::statement('
            UPDATE observing_list_items oli
            JOIN (
                SELECT id,
                       ROW_NUMBER() OVER (PARTITION BY observing_list_id ORDER BY id) AS rn
                FROM observing_list_items
            ) ranked ON oli.id = ranked.id
            SET oli.sort_order = ranked.rn
        ');

        // 3. Create compatibility VIEW for the legacy PHP app
        DB::statement('
            CREATE OR REPLACE VIEW observerobjectlist AS
            -- List "header" rows: objectplace=0, objectname=\'\'
            SELECT
                u.username      AS observerid,
                \'\'            AS objectname,
                ol.name         AS listname,
                0               AS objectplace,
                \'\'            AS objectshowname,
                COALESCE(ol.description, \'\') AS description,
                COALESCE(DATE_FORMAT(ol.created_at, \'%Y%m%d%H%i%S\'), \'\') AS timestamp,
                ol.public       AS public
            FROM observing_lists ol
            JOIN users u ON u.id = ol.owner_user_id
            UNION ALL
            -- Item rows: objectplace = sort_order
            SELECT
                u.username      AS observerid,
                oli.object_name AS objectname,
                ol.name         AS listname,
                oli.sort_order  AS objectplace,
                oli.object_name AS objectshowname,
                COALESCE(oli.item_description, \'\') AS description,
                COALESCE(DATE_FORMAT(oli.created_at, \'%Y%m%d%H%i%S\'), \'\') AS timestamp,
                ol.public       AS public
            FROM observing_list_items oli
            JOIN observing_lists ol ON ol.id = oli.observing_list_id
            JOIN users u ON u.id = ol.owner_user_id
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS observerobjectlist');

        Schema::table('observing_list_items', function (Blueprint $table) {
            $table->dropIndex('idx_list_sort');
            $table->dropColumn('sort_order');
        });
    }
};
