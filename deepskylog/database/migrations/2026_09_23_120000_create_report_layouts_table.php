<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Layouts of the object reports: where on the page each field of an object
 * goes. A layout without a user is one of the DeepskyLog defaults.
 *
 * The layouts of the old DeepskyLog are imported by LegacyReportLayoutsSeeder.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_layouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            // Page size, margins, font sizes, ... (ReportLayoutCatalog::SETTINGS)
            $table->json('settings');
            // Per field: line, x, width, style, text before and after, legend
            $table->json('fields');
            $table->timestamps();

            $table->unique(['user_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_layouts');
    }
};
