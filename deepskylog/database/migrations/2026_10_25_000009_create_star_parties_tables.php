<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Starparties (#315): an observer organises a starparty for a few nights
     * at one of their locations, public (anyone may come) or private (only
     * the invited observers see it). The site is copied from the location, so
     * the starparty keeps it when the location changes or is deleted.
     *
     * The sessions and observations at a location within activity_radius_km
     * of the site in the nights belong to the starparty: with
     * activity_participants_only, only those of the observers who come.
     *
     * star_party_participants holds the observers invited or answering
     * (going, maybe, declined); star_party_invitations the invitations sent
     * to e-mail addresses, accepted with their token after logging in.
     */
    public function up(): void
    {
        Schema::create('star_parties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('language', 5)->default('en');
            $table->json('translations')->nullable();
            $table->string('visibility', 16)->default('public');
            $table->date('start_date');
            $table->date('end_date');
            $table->date('registration_deadline')->nullable();
            $table->unsignedSmallInteger('max_participants')->nullable();
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $table->string('location_name');
            $table->double('latitude');
            $table->double('longitude');
            $table->float('elevation')->nullable();
            $table->string('timezone', 64)->default('UTC');
            $table->string('country')->nullable();
            $table->string('venue')->nullable();
            $table->string('website')->nullable();
            $table->float('activity_radius_km')->default(5);
            $table->boolean('activity_participants_only')->default(true);
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('reminder_sent_at')->nullable();
            $table->timestamps();
            $table->index(['start_date', 'end_date']);
        });

        Schema::create('star_party_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('star_party_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status', 16)->default('invited');
            $table->foreignId('invited_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['star_party_id', 'user_id']);
            $table->index(['user_id', 'status']);
        });

        Schema::create('star_party_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('star_party_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->string('token', 64)->unique();
            $table->foreignId('invited_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('accepted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
            $table->unique(['star_party_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('star_party_invitations');
        Schema::dropIfExists('star_party_participants');
        Schema::dropIfExists('star_parties');
    }
};
