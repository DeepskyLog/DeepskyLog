<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateSearchIndexTable extends Migration
{
    public function up()
    {
        Schema::create('search_index', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 255)->index();
            $table->string('name_normalized', 255)->nullable();
            $table->string('source_table', 64)->index();
            $table->string('source_pk', 255)->index();
            $table->string('display_name', 255)->nullable();
            $table->string('source_type', 64)->nullable();
            $table->decimal('ra', 10, 6)->nullable();
            $table->decimal('decl', 10, 6)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        // add fulltext index via raw statement for compatibility (MySQL only)
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE search_index ADD FULLTEXT INDEX ft_name (name)');
        }
    }

    public function down()
    {
        Schema::dropIfExists('search_index');
    }
}
