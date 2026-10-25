<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * The catalogs that are complete in DeepskyLog (#754): observers can't add
     * objects to them, administrators and database experts manage the list on
     * /admin/complete-catalogs. Arp (1-338) and Hickson (the 100 groups) were
     * checked to be complete; the 8 Hickson members without a name share
     * their galaxy with another member.
     */
    public function up(): void
    {
        Schema::create('complete_catalogs', function (Blueprint $table) {
            $table->id();
            $table->string('catalog', 50)->unique();
            $table->timestamps();
        });

        $now = now();
        DB::table('complete_catalogs')->insert(array_map(
            fn (string $catalog) => ['catalog' => $catalog, 'created_at' => $now, 'updated_at' => $now],
            ['M', 'NGC', 'IC', 'Caldwell', 'H400', 'H400-II', 'Arp', 'Hickson']
        ));
    }

    public function down(): void
    {
        Schema::dropIfExists('complete_catalogs');
    }
};
