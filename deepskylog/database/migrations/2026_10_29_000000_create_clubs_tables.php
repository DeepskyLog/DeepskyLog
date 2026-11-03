<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Clubs and groups of observers: an observer can be a member of several.
     * club_members holds the members (role admin or member) and the observers
     * who asked to join or were invited (status). The members share their
     * public observing lists with the club (club_observing_list), and the
     * club has its own challenges (observing_challenges.club_id) and
     * starparties (star_parties.club_id).
     */
    public function up(): void
    {
        Schema::create('clubs', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('website')->nullable();
            $table->string('place', 120)->nullable();
            $table->string('country', 2)->nullable();
            $table->string('join_policy', 16)->default('approval');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index('country');
        });

        Schema::create('club_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained('clubs')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role', 16)->default('member');
            $table->string('status', 16)->default('member');
            $table->foreignId('invited_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['club_id', 'user_id']);
            $table->index(['user_id', 'status']);
        });

        Schema::create('club_observing_list', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained('clubs')->cascadeOnDelete();
            $table->foreignId('observing_list_id')->constrained('observing_lists')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['club_id', 'observing_list_id']);
        });

        Schema::table('observing_challenges', function (Blueprint $table) {
            $table->foreignId('club_id')->nullable()->after('user_id')->constrained('clubs')->cascadeOnDelete();
        });

        Schema::table('star_parties', function (Blueprint $table) {
            $table->foreignId('club_id')->nullable()->after('user_id')->constrained('clubs')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('star_parties', function (Blueprint $table) {
            $table->dropConstrainedForeignId('club_id');
        });
        Schema::table('observing_challenges', function (Blueprint $table) {
            $table->dropConstrainedForeignId('club_id');
        });
        Schema::dropIfExists('club_observing_list');
        Schema::dropIfExists('club_members');
        Schema::dropIfExists('clubs');
    }
};
