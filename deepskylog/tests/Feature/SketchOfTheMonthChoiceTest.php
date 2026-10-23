<?php

use App\Livewire\ChooseSketchOfTheMonth;
use App\Models\Observation;
use App\Models\SketchOfTheMonth;
use App\Models\SketchOfTheWeek;
use App\Models\User;
use App\Services\ObservationSearchQuery;
use Carbon\Carbon;
use Livewire\Livewire;

beforeEach(function () {
    // A Wednesday in October: the first free 8th is 8 November.
    Carbon::setTestNow('2026-10-21 12:00:00');
});

afterEach(fn () => Carbon::setTestNow());

function monthSketchObservation(string $object = 'M 65', bool $drawing = true): Observation
{
    $observer = User::factory()->create(['name' => 'Ann Sketcher']);

    return Observation::create([
        'observer_username' => $observer->username,
        'observer_user_id'  => $observer->id,
        'observed_on'       => '2026-03-01',
        'target_type'       => 'deepsky',
        'target_table'      => 'objects',
        'target_key'        => $object,
        'target_name'       => $object,
        'description'       => 'Seen',
        'has_drawing'       => $drawing,
        'drawing_filename'  => $drawing ? 'sketches/' . md5($object . microtime()) . '.jpg' : null,
    ]);
}

function weekSketch(Observation $observation, string $date): SketchOfTheWeek
{
    $sketch                 = new SketchOfTheWeek;
    $sketch->observation_id = $observation->id;
    $sketch->user_id        = $observation->observer_user_id;
    $sketch->date           = $date;
    $sketch->save();

    return $sketch;
}

function monthSketch(Observation $observation, string $date): SketchOfTheMonth
{
    $sketch                 = new SketchOfTheMonth;
    $sketch->observation_id = $observation->id;
    $sketch->user_id        = $observation->observer_user_id;
    $sketch->date           = $date;
    $sketch->save();

    return $sketch;
}

it('offers the 8ths from today on of the months without a sketch of the month', function () {
    monthSketch(monthSketchObservation(), '2026-12-08');

    $dates = array_map(fn ($d) => $d->toDateString(), SketchOfTheMonth::freeDates(3));

    expect($dates)->toBe(['2026-11-08', '2027-01-08', '2027-02-08']);
});

it('offers the 8th of the current month up to that day', function () {
    Carbon::setTestNow('2026-10-08 09:00:00');

    expect(SketchOfTheMonth::freeDates(1)[0]->toDateString())->toBe('2026-10-08');
});

it('offers the sketches of the week of the month before as candidates', function () {
    $october1  = weekSketch(monthSketchObservation('M 65'), '2026-10-04');
    $october2  = weekSketch(monthSketchObservation('M 66'), '2026-10-25');
    $september = weekSketch(monthSketchObservation('M 31'), '2026-09-27');
    $november  = weekSketch(monthSketchObservation('M 33'), '2026-11-01');
    $chosen    = weekSketch(monthSketchObservation('M 42'), '2026-10-11');
    monthSketch($chosen->observation, '2026-06-08');

    expect(SketchOfTheMonth::candidates(Carbon::parse('2026-11-08'))->pluck('id')->all())
        ->toBe([$october1->id, $october2->id]);
});

it('shows the candidates of the chosen date on the page', function () {
    $expert = $this->createUserAndAssignToTeam('Database Experts');
    $october  = weekSketch(monthSketchObservation('M 65'), '2026-10-04');
    $november = weekSketch(monthSketchObservation('M 66'), '2026-11-15');

    $this->actingAs($expert)->get(route('sketch-of-the-month.create'))->assertOk();

    Livewire::actingAs($expert)->test(ChooseSketchOfTheMonth::class)
        ->assertSet('date', '2026-11-08')
        ->assertSee('value="' . $october->observation_id . '"', false)
        ->assertDontSee('value="' . $november->observation_id . '"', false)
        ->set('date', '2026-12-08')
        ->assertSee('value="' . $november->observation_id . '"', false)
        ->assertDontSee('value="' . $october->observation_id . '"', false)
        ->set('date', '2026-11-09')
        ->assertSet('date', '2026-11-08');
});

it('only accepts a sketch of the week of the month before on a free 8th', function (string $date, string $field) {
    $expert = $this->createUserAndAssignToTeam('Database Experts');
    $october   = weekSketch(monthSketchObservation('M 65'), '2026-10-04');
    monthSketch(monthSketchObservation('M 1'), '2027-01-08');

    $this->actingAs($expert)
        ->post(route('sketch-of-the-month.store'), ['observation_id' => $october->observation_id, 'date' => $date])
        ->assertSessionHasErrors($field);

    expect(SketchOfTheMonth::where('observation_id', $october->observation_id)->exists())->toBeFalse();
})->with([
    'not the 8th'            => ['2026-11-09', 'date'],
    'a past 8th'             => ['2026-10-08', 'date'],
    'a month with one'       => ['2027-01-08', 'date'],
    'not of the month before' => ['2026-12-08', 'observation_id'],
]);

it('adds the chosen sketch of the week as sketch of the month', function () {
    $expert  = $this->createUserAndAssignToTeam('Database Experts');
    $october = weekSketch(monthSketchObservation('M 65'), '2026-10-04');

    $this->actingAs($expert)
        ->post(route('sketch-of-the-month.store'), ['observation_id' => $october->observation_id, 'date' => '2026-11-08'])
        ->assertOk();

    expect(SketchOfTheMonth::where('observation_id', $october->observation_id)->first()->date->toDateString())->toBe('2026-11-08');
});

it('hides a sketch of the month until its 8th', function () {
    $observation = monthSketchObservation();
    weekSketch($observation, '2026-10-04');
    $sketch   = monthSketch($observation, '2026-11-08');
    $observer = User::find($observation->observer_user_id);

    $this->get($observation->detailUrl())->assertOk()->assertDontSee('DeepskyLog Sketch of the Month');
    $this->get(route('observer.show', $observer->slug))->assertOk()->assertDontSee('DeepskyLog sketches of the month');
    expect(SketchOfTheMonth::published()->count())->toBe(0);

    Carbon::setTestNow('2026-11-08 00:01:00');

    expect($sketch->fresh()->isPublished())->toBeTrue();
    $this->get($observation->detailUrl())->assertOk()->assertSee('DeepskyLog Sketch of the Month');
});

it('links to the choice from the sketch of the month page only for those who choose', function () {
    $this->get(route('sketch-of-the-month'))->assertOk()->assertDontSee(route('sketch-of-the-month.create'), false);

    $this->actingAs($this->createUserAndAssignToTeam('Database Experts'))
        ->get(route('sketch-of-the-month'))->assertOk()->assertSee(route('sketch-of-the-month.create'), false);
});

it('finds the drawings of an object that are no sketch of the week yet', function () {
    $expert    = $this->createUserAndAssignToTeam('Database Experts');
    $free      = monthSketchObservation('M 65');
    $published = monthSketchObservation('M 65');
    $later     = monthSketchObservation('M 65');
    monthSketchObservation('M 65', false);
    monthSketchObservation('M 66');
    weekSketch($published, '2026-10-18');
    weekSketch($later, '2026-11-01');

    $found = fn () => app(ObservationSearchQuery::class)
        ->observations('M 65', ['drawing_state' => 'not_sketch_of_the_week'])
        ->orderBy('observations.id')
        ->pluck('observations.id')
        ->all();

    // Those who choose the sketches also leave out the ones chosen for a later Sunday;
    // others do not learn about those before that Sunday.
    expect($found())->toBe([$free->id, $later->id]);

    $this->actingAs($expert);
    expect($found())->toBe([$free->id]);
});
