<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTagTables extends Migration
{
    public function up()
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->json('name');
            $table->json('slug');
            $table->string('type')->nullable();
            $table->integer('order_column')->nullable();
            $table->timestamps();
        });

        Schema::create('taggables', function (Blueprint $table) {
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->morphs('taggable');

            $table->unique(['tag_id', 'taggable_id', 'taggable_type']);

            $tag = \Spatie\Tags\Tag::create(['name' => 'Deep-sky', 'type' => 'ObservationList']);
            $tag->setTranslation('name', 'de', 'Deep-sky');
            $tag->setTranslation('name', 'nl', 'Deep-sky');
            $tag->setTranslation('name', 'sv', 'Deep-sky');
            $tag->setTranslation('name', 'es', 'Cielo Profundo');
            $tag->setTranslation('name', 'fr', 'Ciel Profond');
            $tag->save();
            $tag = \Spatie\Tags\Tag::create(['name' => 'Beginners', 'type' => 'ObservationList']);
            $tag->setTranslation('name', 'de', 'Anfänger');
            $tag->setTranslation('name', 'nl', 'Beginners');
            $tag->setTranslation('name', 'sv', 'Nybörjare');
            $tag->setTranslation('name', 'es', 'Principiantes');
            $tag->setTranslation('name', 'fr', 'Débutants');
            $tag->save();
            $tag = \Spatie\Tags\Tag::create(['name' => 'Intermediate', 'type' => 'ObservationList']);
            $tag->setTranslation('name', 'de', 'Intermediate');
            $tag->setTranslation('name', 'nl', 'Gemiddeld');
            $tag->setTranslation('name', 'sv', 'Mellanliggande');
            $tag->setTranslation('name', 'es', 'Intermedio');
            $tag->setTranslation('name', 'fr', 'Intermédiaire');
            $tag->save();
            $tag = \Spatie\Tags\Tag::create(['name' => 'Experienced', 'type' => 'ObservationList']);
            $tag->setTranslation('name', 'de', 'Erfahren');
            $tag->setTranslation('name', 'nl', 'Ervaren');
            $tag->setTranslation('name', 'sv', 'Erfaren');
            $tag->setTranslation('name', 'es', 'Experimentado');
            $tag->setTranslation('name', 'fr', 'Expérimenté');
            $tag->save();
            $tag = \Spatie\Tags\Tag::create(['name' => 'Faint', 'type' => 'ObservationList']);
            $tag->setTranslation('name', 'de', 'Schwach');
            $tag->setTranslation('name', 'nl', 'Zwak');
            $tag->setTranslation('name', 'sv', 'Svag');
            $tag->setTranslation('name', 'es', 'Débil');
            $tag->setTranslation('name', 'fr', 'Flou');
            $tag->save();
            $tag = \Spatie\Tags\Tag::create(['name' => 'Bright', 'type' => 'ObservationList']);
            $tag->setTranslation('name', 'de', 'Hell');
            $tag->setTranslation('name', 'nl', 'Helder');
            $tag->setTranslation('name', 'sv', 'Ljus');
            $tag->setTranslation('name', 'es', 'Brillante');
            $tag->setTranslation('name', 'fr', 'Brillant');
            $tag->save();
            $tag = \Spatie\Tags\Tag::create(['name' => 'Solar System', 'type' => 'ObservationList']);
            $tag->setTranslation('name', 'de', 'Sonnensystem');
            $tag->setTranslation('name', 'nl', 'Zonnestelsel');
            $tag->setTranslation('name', 'sv', 'Solsystem');
            $tag->setTranslation('name', 'es', 'Sistema solar');
            $tag->setTranslation('name', 'fr', 'Système solaire');
            $tag->save();
            $tag = \Spatie\Tags\Tag::create(['name' => 'Moon', 'type' => 'ObservationList']);
            $tag->setTranslation('name', 'de', 'Mond');
            $tag->setTranslation('name', 'nl', 'Maan');
            $tag->setTranslation('name', 'sv', 'Måne');
            $tag->setTranslation('name', 'es', 'Luna');
            $tag->setTranslation('name', 'fr', 'Lune');
            $tag->save();
            $tag = \Spatie\Tags\Tag::create(['name' => 'Comets', 'type' => 'ObservationList']);
            $tag->setTranslation('name', 'de', 'Kometen');
            $tag->setTranslation('name', 'nl', 'Kometen');
            $tag->setTranslation('name', 'sv', 'Kometer');
            $tag->setTranslation('name', 'es', 'Cometas');
            $tag->setTranslation('name', 'fr', 'Comètes');
            $tag->save();
            $tag = \Spatie\Tags\Tag::create(['name' => 'Binoculars', 'type' => 'ObservationList']);
            $tag->setTranslation('name', 'de', 'Fernglas');
            $tag->setTranslation('name', 'nl', 'Verrekijker');
            $tag->setTranslation('name', 'sv', 'Kikare');
            $tag->setTranslation('name', 'es', 'Binoculares');
            $tag->setTranslation('name', 'fr', 'Jumelles');
            $tag->save();
        });
    }

    public function down()
    {
        Schema::drop('taggables');
        Schema::drop('tags');
    }
}
