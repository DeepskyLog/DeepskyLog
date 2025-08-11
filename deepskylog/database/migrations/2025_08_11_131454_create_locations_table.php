<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->float('longitude', 10);
            $table->float('latitude', 10);
            $table->string('timezone', 255);
            $table->float('limitingMagnitude', 5)->default(-999);
            $table->float('skyBackground', 5)->default(-999);
            $table->integer('elevation')->default(0);
            $table->string('country', 255)->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('instrumentactive')->virtualAs('active');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')
                ->onDelete('cascade');
            $table->string('observer');
            $table->integer('observations', unsigned: true)->default(0);
            $table->string('picture')->nullable();
            $table->string('slug');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
