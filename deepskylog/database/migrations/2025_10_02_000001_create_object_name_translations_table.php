<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateObjectNameTranslationsTable extends Migration
{
    public function up()
    {
        Schema::create('object_name_translations', function (Blueprint $table) {
            $table->bigIncrements('id');
            // reference to canonical object name (matches objectnames.objectname / objects.name)
            $table->string('objectname', 128)->index();
            // locale code, eg. 'fr', 'nl', 'de'
            $table->string('locale', 10)->index();
            // localized form of the name
            $table->string('name', 128)->index();
            $table->timestamps();

            $table->unique(['objectname', 'locale', 'name'], 'uniq_object_locale_name');
            $table->index(['locale', 'name'], 'idx_locale_name');
        });
    }

    public function down()
    {
        Schema::dropIfExists('object_name_translations');
    }
}
