<?php

use App\Jobs\TranslateObservationDescriptions;
use App\Models\Observation;
use App\Models\ObservationTranslation;
use App\Models\User;
use App\Services\Observations\DescriptionTranslator;
use Database\Factories\InstrumentFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

// The observer writes the description in other languages of the site
// themselves (#321): stored in observation_translations with by_observer,
// shown instead of Google's translation to everybody who reads the site in
// that language, and never replaced by Google's.

function ownTranslationObject(string $name, float $ra, float $decl): void
{
    DB::table('objects')->insert([
        'name' => $name, 'slug' => \Illuminate\Support\Str::slug($name), 'type' => 'GALXY', 'con' => 'UMA', 'ra' => $ra, 'decl' => $decl,
        'mag' => 9.0, 'subr' => 13.0, 'pa' => 0, 'urano' => 0, 'urano_new' => 0, 'sky' => 0, 'millenium' => '',
        'diam1' => 300.0, 'diam2' => 120.0, 'datasource' => null, 'taki' => '', 'SBObj' => 0.0, 'description' => '',
        'psa' => '', 'torresB' => '', 'torresBC' => '', 'torresC' => '', 'milleniumbase' => '0', 'DSLDL' => '0',
        'DSLDP' => '0', 'DSLLL' => '0', 'DSLLP' => '0', 'DSLOL' => '0', 'DSLOP' => '0', 'DeepskyHunter' => '0',
        'Interstellarum' => '0', 'timestamp' => now(),
    ]);
}

function ownTranslationPost(User $user, array $extra = []): array
{
    return array_merge([
        'target_table' => 'objects',
        'target_slug' => 'm-81',
        'observed_on' => '2026-03-01',
        'observed_time' => '22:30',
        'instrument_id' => InstrumentFactory::new()->create(['observer' => '', 'user_id' => $user->id])->id,
        'description' => '<p>Prachtige spiraalarmen.</p>',
        'language' => 'nl',
        'observer_class' => 'A',
    ], $extra);
}

/** @return array<string, array{string|null, bool}> description and by_observer per language */
function ownTranslationRows(Observation $observation): array
{
    return ObservationTranslation::where('observation_id', $observation->id)->orderBy('language')->get()
        ->mapWithKeys(fn ($row) => [$row->language => [$row->description, $row->by_observer]])->all();
}

beforeEach(function () {
    Queue::fake();
    ownTranslationObject('M 81', 9.9259, 69.0653);
    ownTranslationObject('M 82', 9.9319, 69.6797);
});

it('stores the descriptions the observer wrote in other languages, also for the objects in the same field', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('observation.store'), ownTranslationPost($user, [
        'also_seen' => ['M 82'],
        'translations' => [
            'en' => '<p>Beautiful spiral arms.</p>',
            'de' => '<p>Herrliche Spiralarme.<script>alert(1)</script></p>',
            // The language of the observation itself, an empty one and one the site does not have are dropped
            'nl' => '<p>Dubbel</p>',
            'fr' => '<p> </p>',
            'it' => '<p>Bracci a spirale.</p>',
        ],
    ]))->assertSessionHasNoErrors();

    [$main, $copy] = Observation::orderBy('id')->get()->all();
    $expected = [
        'de' => ['<p>Herrliche Spiralarme.</p>', true],
        'en' => ['<p>Beautiful spiral arms.</p>', true],
    ];
    expect(ownTranslationRows($main))->toBe($expected)
        ->and(ownTranslationRows($copy))->toBe($expected);
});

it('edits and removes the descriptions in other languages with the observation', function () {
    $user = User::factory()->create(['username' => 'owntrans', 'slug' => 'own-trans']);
    $this->actingAs($user)->post(route('observation.store'), ownTranslationPost($user, [
        'translations' => ['en' => '<p>Beautiful spiral arms.</p>', 'de' => '<p>Herrliche Spiralarme.</p>'],
    ]))->assertSessionHasNoErrors();
    $observation = Observation::first();
    // A translation by Google next to them
    ObservationTranslation::create(['observation_id' => $observation->id, 'language' => 'fr', 'description' => 'Beaux bras spiraux.']);

    $this->actingAs($user)->get('/observation/own-trans/2026-03-01/m-81/1/edit')
        ->assertSessionHasInput('translations', ['de' => '<p>Herrliche Spiralarme.</p>', 'en' => '<p>Beautiful spiral arms.</p>']);

    // A new description: Google's translation goes, the observer's own stay as edited
    $this->actingAs($user)->post(route('observation.store'), ownTranslationPost($user, [
        'edit_unified_id' => $observation->id,
        'description' => '<p>Nog mooiere spiraalarmen.</p>',
        'translations' => ['en' => '<p>Even nicer spiral arms.</p>', 'de' => ''],
    ]))->assertSessionHasNoErrors();

    expect(ownTranslationRows($observation))->toBe(['en' => ['<p>Even nicer spiral arms.</p>', true]]);
});

it('shows the observer\'s own description to everybody who reads the site in that language', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->post(route('observation.store'), ownTranslationPost($user, [
        'translations' => ['en' => '<p>Beautiful spiral arms.</p>'],
    ]))->assertSessionHasNoErrors();
    $observation = Observation::first();
    ObservationTranslation::create(['observation_id' => $observation->id, 'language' => 'fr', 'description' => 'Beaux bras spiraux.']);
    auth()->logout();

    // A guest reading the site in English
    $this->withSession(['locale' => 'en'])->get('/observations')
        ->assertOk()
        ->assertSee('Beautiful spiral arms.')
        ->assertSee('Translated by the observer · original language: Dutch')
        ->assertDontSee('Translated by Google');

    // An English observer who did not turn on translations
    $this->actingAs(User::factory()->create(['translate' => false, 'language' => 'en']))->get('/observations')
        ->assertSee('Beautiful spiral arms.');

    // A French observer without translations reads the original; with them, Google's translation
    $this->actingAs(User::factory()->create(['translate' => false, 'language' => 'fr']))->get('/observations')
        ->assertSee('Prachtige spiraalarmen.')
        ->assertDontSee('Beaux bras spiraux.');
    $this->actingAs(User::factory()->create(['translate' => true, 'language' => 'fr']))->get('/observations')
        ->assertSee('Beaux bras spiraux.')
        ->assertSee('Traduit par Google');
});

it('lists the languages the observer wrote the description in on the detail page', function () {
    $user = User::factory()->create(['username' => 'owndetail', 'slug' => 'own-detail']);
    $this->actingAs($user)->post(route('observation.store'), ownTranslationPost($user, [
        'translations' => ['en' => '<p>Beautiful spiral arms.</p>', 'de' => '<p>Herrliche Spiralarme.</p>'],
    ]))->assertSessionHasNoErrors();

    $this->actingAs(User::factory()->create(['language' => 'en']))->get(Observation::first()->detailUrl())
        ->assertOk()
        ->assertSee('Also written by the observer in:')
        ->assertSee('Herrliche Spiralarme.')
        ->assertSee('Beautiful spiral arms.');
});

it('offers the other languages on the observation form', function () {
    $user = User::factory()->create(['observationlanguage' => 'nl']);

    $this->actingAs($user)->get(route('observation.create', ['slug' => 'm-81']))
        ->assertOk()
        ->assertSee('Description in other languages')
        ->assertSee('name="translations[en]"', false)
        ->assertDontSee('name="translations[nl]"', false);
});

it('never replaces the observer\'s own description with a translation by Google', function () {
    config(['app.translate_observations' => true]);
    $translator = new class extends DescriptionTranslator {
        public int $calls = 0;

        public function translate(string $text, ?string $source, string $target): ?string
        {
            $this->calls++;

            return "[{$target}] {$text}";
        }

        public function detectedSource(): ?string
        {
            return null;
        }
    };
    app()->instance(DescriptionTranslator::class, $translator);

    $user = User::factory()->create();
    $this->actingAs($user)->post(route('observation.store'), ownTranslationPost($user, [
        'also_seen' => ['M 82'],
        'translations' => ['en' => '<p>Beautiful spiral arms.</p>'],
    ]))->assertSessionHasNoErrors();
    $ids = Observation::orderBy('id')->pluck('id')->all();

    (new TranslateObservationDescriptions($ids, 'en'))->handle($translator);
    (new TranslateObservationDescriptions($ids, 'de'))->handle($translator);

    expect($translator->calls)->toBe(1)
        ->and(ObservationTranslation::whereIn('observation_id', $ids)->where('language', 'en')->pluck('description')->unique()->all())
        ->toBe(['<p>Beautiful spiral arms.</p>'])
        ->and(ObservationTranslation::whereIn('observation_id', $ids)->where('language', 'de')->where('by_observer', false)->count())
        ->toBe(2);
});
