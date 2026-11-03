<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Observing partners nearby (App\Services\ObservingPartners): whether the
     * observer can be found by the observers near their standard location,
     * and the short note they show them.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('partner_search')->default(false)->index();
            $table->string('partner_note', 300)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['partner_search']);
            $table->dropColumn(['partner_search', 'partner_note']);
        });
    }
};
