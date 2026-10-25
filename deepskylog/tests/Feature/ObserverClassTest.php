<?php

use App\Livewire\AdvancedObservationSearch;
use App\Livewire\ObservationSearchTable;
use App\Models\Observation;
use App\Models\User;
use App\Services\Import\CsvObservationImporter;
use Database\Factories\InstrumentFactory;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

/**
 * The class observers give their own observations (#321): A for a top
 * observation, B for a good one, C for a record that the object was seen.
 */
function observerClassObject(string $name, float $ra, float $decl): void
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

function observerClassPost(User $user, array $extra = []): array
{
    return array_merge([
        'target_table' => 'objects',
        'target_slug' => 'm-81',
        'observed_on' => '2026-03-01',
        'observed_time' => '22:30',
        'instrument_id' => InstrumentFactory::new()->create(['observer' => '', 'user_id' => $user->id])->id,
        'description' => '<p>A long look at the spiral arms.</p>',
    ], $extra);
}

function observerClassObservation(User $observer, string $object, ?string $class, array $extra = []): Observation
{
    return Observation::query()->create(array_merge([
        'observer_username' => $observer->username, 'observer_user_id' => $observer->id, 'observed_on' => '2026-03-01',
        'language' => 'en', 'target_type' => 'deepsky', 'target_table' => 'objects', 'target_key' => $object,
        'target_name' => $object, 'has_drawing' => false, 'description' => 'Seen.', 'observer_class' => $class,
    ], $extra));
}

beforeEach(function () {
    observerClassObject('M 81', 9.9259, 69.0653);
    observerClassObject('M 82', 9.9319, 69.6797);
});

it('stores the class of a new observation and of the objects seen in the same field', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('observation.store'), observerClassPost($user, [
        'observer_class' => 'A',
        'also_seen' => ['M 82'],
    ]))->assertSessionHasNoErrors();

    expect(Observation::orderBy('id')->pluck('observer_class', 'target_name')->all())
        ->toBe(['M 81' => 'A', 'M 82' => 'A']);
});

it('stores an observation without a class and refuses an unknown class', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('observation.store'), observerClassPost($user, ['observer_class' => '']))
        ->assertSessionHasNoErrors();
    expect(Observation::first()->observer_class)->toBeNull();

    $this->actingAs($user)->post(route('observation.store'), observerClassPost($user, ['observer_class' => 'D']))
        ->assertSessionHasErrors('observer_class');
    expect(Observation::count())->toBe(1);
});

it('changes and clears the class when the observation is edited', function () {
    $user = User::factory()->create(['username' => 'classedit', 'slug' => 'class-edit']);
    $observation = observerClassObservation($user, 'M 81', 'C');

    $this->actingAs($user)
        ->get('/observation/class-edit/2026-03-01/m-81/1/edit')
        ->assertSessionHasInput('observer_class', 'C');

    $this->actingAs($user)->post(route('observation.store'), observerClassPost($user, [
        'edit_unified_id' => $observation->id, 'observer_class' => 'B',
    ]))->assertSessionHasNoErrors();
    expect($observation->fresh()->observer_class)->toBe('B');

    $this->actingAs($user)->post(route('observation.store'), observerClassPost($user, [
        'edit_unified_id' => $observation->id, 'observer_class' => '',
    ]))->assertSessionHasNoErrors();
    expect($observation->fresh()->observer_class)->toBeNull();
});

it('offers the class on the observation form', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('observation.create', ['slug' => 'm-81']))
        ->assertOk()
        ->assertSee('name="observer_class" value="A"', false)
        ->assertSee('Top observation: a lot of time and attention went into it')
        ->assertSee('Not graded');
});

it('shows the class on the card and the detail page', function () {
    $user = User::factory()->create(['username' => 'classcard', 'slug' => 'class-card']);
    $graded = observerClassObservation($user, 'M 81', 'A');
    $ungraded = observerClassObservation($user, 'M 82', null);

    expect(Blade::render('<x-observation-deepsky :observation="$o" />', ['o' => $graded]))->toContain('Class A')
        ->and(Blade::render('<x-observation-deepsky :observation="$o" />', ['o' => $ungraded]))->not->toContain('Class A');

    $this->get('/observation/class-card/2026-03-01/m-81')
        ->assertOk()
        ->assertSee('Class A')
        ->assertSee('Top observation: a lot of time and attention went into it');
});

it('filters the observation search on the class', function () {
    $user = User::factory()->create();
    $a = observerClassObservation($user, 'M 81', 'A');
    $b = observerClassObservation($user, 'M 82', 'B');
    observerClassObservation($user, 'M 81', null, ['observed_on' => '2026-03-02']);

    $ids = fn (array $filters) => Livewire::test(ObservationSearchTable::class, ['filters' => $filters])
        ->instance()->datasource()->pluck('observations.id')->sort()->values()->all();

    expect($ids(['observer_classes' => ['A']]))->toBe([$a->id])
        ->and($ids(['observer_classes' => ['A', 'B']]))->toBe([$a->id, $b->id])
        // An unknown class is ignored, not matched
        ->and($ids(['observer_classes' => ['X']]))->toHaveCount(3);
});

it('exports the class as the 17th csv column and imports it again', function () {
    $user = User::factory()->create(['name' => 'Class Observer', 'username' => 'classcsv']);
    observerClassObservation($user, 'M 81', 'B');

    Livewire::test(ObservationSearchTable::class, ['objectName' => 'M 81'])
        ->call('exportToCsv')
        ->assertFileDownloaded('export.csv', "\xEF\xBB\xBF\"M 81\";\"Class Observer\";01-03-2026;;;;;;;;0;0;;en;Seen.;;B\n");

    $importer = User::factory()->create(['username' => 'classimport']);
    InstrumentFactory::new()->create(['observer' => '', 'user_id' => $importer->id, 'name' => 'Class Scope']);
    $row = fn (string $date, string $tail) => "M 81;Observer;{$date};;;Class Scope;;;;3;;;;en;Seen.{$tail}\n";

    (new CsvObservationImporter($importer))->import($row('01-03-2026', ';;a').$row('02-03-2026', '').$row('03-03-2026', ';;Z'));
    expect(Observation::where('observer_user_id', $importer->id)->orderBy('observed_on')->pluck('observer_class')->all())
        ->toBe(['A', null, null]);

    // A row without a class keeps the class of the observation it updates
    (new CsvObservationImporter($importer))->import($row('01-03-2026', ''));
    expect(Observation::where('observer_user_id', $importer->id)->whereDate('observed_on', '2026-03-01')->value('observer_class'))
        ->toBe('A');
});

it('offers the class in the advanced observation search and passes it on', function () {
    $this->get(route('search.observations.advanced', ['observer_classes' => ['A']]))
        ->assertOk()
        ->assertSee('Class of the observation')
        ->assertSee('wire:model="observerClasses" value="A"', false);

    Livewire::test(AdvancedObservationSearch::class, ['filters' => ['observer_classes' => ['A', 'X']]])
        ->assertSet('activeFilters', ['observerClass'])
        ->assertSet('observerClasses', ['A'])
        ->call('search')
        ->assertRedirect(route('search.observations.advanced.results', ['observer_classes' => ['A'], 'activeFilters' => ['observerClass']]));
});

it('shows a changed class on the home page at once', function () {
    $user = User::factory()->create(['username' => 'classhome', 'slug' => 'class-home']);
    $observation = observerClassObservation($user, 'M 81', null, ['observed_on' => now()->toDateString()]);

    $this->get('/')->assertOk()->assertDontSee('Class B');

    $observation->update(['observer_class' => 'B']);

    $this->get('/')->assertOk()->assertSee('Class B');
});
