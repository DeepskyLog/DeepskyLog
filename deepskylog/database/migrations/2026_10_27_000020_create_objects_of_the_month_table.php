<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The objects of the month of Astroforum (one row per thread of
     * https://www.astroforum.nl/forums/objecten-van-de-maand.49/): the
     * months it covers, the objects proposed from its first post and those an
     * administrator or database expert confirmed.
     */
    public function up(): void
    {
        Schema::create('objects_of_the_month', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('thread_id')->unique();
            $table->string('url', 500);
            $table->string('title', 500);
            $table->date('starts_on')->index();
            $table->date('ends_on')->index();
            $table->json('candidates')->nullable();
            $table->json('unresolved')->nullable();
            $table->json('objects')->nullable();
            $table->boolean('hidden')->default(false);
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('objects_of_the_month');
    }
};
