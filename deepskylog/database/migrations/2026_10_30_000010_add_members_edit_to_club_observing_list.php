<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A list shared with a club can be one the members edit together: they
     * add and remove its objects and edit their notes (members_edit).
     */
    public function up(): void
    {
        Schema::table('club_observing_list', function (Blueprint $table) {
            $table->boolean('members_edit')->default(false)->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('club_observing_list', function (Blueprint $table) {
            $table->dropColumn('members_edit');
        });
    }
};
