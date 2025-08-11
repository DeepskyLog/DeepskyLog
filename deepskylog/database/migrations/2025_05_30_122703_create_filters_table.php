<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('filters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('make_id')->constrained('filter_makes', 'id');
            $table->string('name', 255);
            $table->foreignId('type_id')->constrained('filter_types', 'id');
            $table->integer('type')->virtualAs('type_id - 1');
            $table->foreignId('color_id')->constrained('filter_colors', 'id');
            $table->unsignedInteger('color')->virtualAs('color_id - 1');
            $table->string('wratten', 5)->nullable();
            $table->string('schott', 5)->nullable();
            $table->boolean('active')->default(true);
            $table->boolean('filteractive')->virtualAs('active');
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
        Schema::dropIfExists('filters');
    }
};
