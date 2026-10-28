<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * The message a message answers, so the message page can show the
     * conversation. Without a foreign key: the inbox of a deleted account is
     * removed, and the replies to it keep their (dangling) id.
     */
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->unsignedBigInteger('reply_to_id')->nullable()->after('message')->index();
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['reply_to_id']);
            $table->dropColumn('reply_to_id');
        });
    }
};
