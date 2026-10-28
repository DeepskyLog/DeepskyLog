<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * The news of the home page (#960): items written by administrators and
     * database experts (kind 'manual', with a title and body in the language
     * they wrote it in and Google translations), and items made by
     * news:astronomical-events for the coming eclipses, conjunctions, ...
     * (kind of the event, the event's key and data; title and body are
     * written when shown, in the reader's language).
     */
    public function up(): void
    {
        Schema::create('news_items', function (Blueprint $table) {
            $table->id();
            $table->string('kind', 32)->default('manual');
            $table->string('event_key', 191)->nullable()->unique();
            $table->timestamp('event_at')->nullable();
            $table->json('data')->nullable();
            $table->string('title')->nullable();
            $table->text('body')->nullable();
            $table->string('language', 5)->nullable();
            $table->json('translations')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('hidden')->default(false);
            $table->timestamp('published_at')->index();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_items');
    }
};
