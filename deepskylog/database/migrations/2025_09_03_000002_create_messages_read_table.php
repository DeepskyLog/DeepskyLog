<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages_read', function (Blueprint $table) {
            // id is the message id (preserve legacy semantics)
            $table->unsignedBigInteger('id')->index();
            $table->string('receiver')->index();
            $table->primary(['id', 'receiver']);
            // optional timestamp when the message was marked read
            $table->timestamp('read_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages_read');
    }
};
