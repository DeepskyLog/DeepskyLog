<?php

use App\Models\Observation;
use App\Models\SketchOfTheWeek;
use App\Models\User;
use Carbon\Carbon;

beforeEach(function () {
    // A Wednesday: the next Sundays are 2026-10-25, 2026-11-01, ...
    Carbon::setTestNow('2026-10-21 12:00:00');
});

afterEach(fn () => Carbon::setTestNow());

function sketchObservation(array $attributes = []): Observation
{
    $observer = User::factory()->create(['name' => 'Ann Sketcher']);

    return Observation::create(array_merge([
        'observer_username' => $observer->username,
        'observer_user_id'  => $observer->id,
        'observed_on'       => '2026-03-01',
        'target_type'       => 'deepsky',
        'target_table'      => 'objects',
        'target_key'        => 'M 65',
        'target_name'       => 'M 65',
        'description'       => 'Seen',
        'has_drawing'       => true,
        'drawing_filename'  => 'sketches/m65.jpg',
    ], $attributes));
}

function addSketchOfTheWeek(Observation $observation, string $date): SketchOfTheWeek
{
    $sketch                 = new SketchOfTheWeek;
    $sketch->observation_id = $observation->id;
    $sketch->user_id        = $observation->observer_user_id;
    $sketch->date           = $date;
    $sketch->save();

    return $sketch;
}

it('offers the Sundays from today on that have no sketch of the week yet', function () {
    addSketchOfTheWeek(sketchObservation(), '2026-11-01');

    $sundays = array_map(fn ($d) => $d->toDateString(), SketchOfTheWeek::freeSundays(3));

    expect($sundays)->toBe(['2026-10-25', '2026-11-08', '2026-11-15']);
});

it('offers today when today is a free Sunday', function () {
    Carbon::setTestNow('2026-10-25 08:00:00');

    expect(SketchOfTheWeek::freeSundays(1)[0]->toDateString())->toBe('2026-10-25');
});

it('only accepts a free Sunday from today on as the date of a sketch of the week', function (string $date) {
    $admin = $this->createUserAndAssignToTeam('Database Experts');
    addSketchOfTheWeek(sketchObservation(), '2026-11-01');
    $observation = sketchObservation();

    $this->actingAs($admin)
        ->post(route('sketch-of-the-week.store'), ['observation_id' => $observation->id, 'date' => $date])
        ->assertSessionHasErrors('date');

    expect(SketchOfTheWeek::where('observation_id', $observation->id)->exists())->toBeFalse();
})->with([
    'not a Sunday'  => '2026-10-24',
    'a past Sunday' => '2026-10-18',
    'a taken Sunday' => '2026-11-01',
]);

it('adds a sketch of the week on the chosen Sunday', function () {
    $expert      = $this->createUserAndAssignToTeam('Database Experts');
    $observation = sketchObservation();

    $this->actingAs($expert)
        ->post(route('sketch-of-the-week.store'), ['observation_id' => $observation->id, 'date' => '2026-11-08'])
        ->assertOk();

    expect(SketchOfTheWeek::where('observation_id', $observation->id)->first()->date->toDateString())->toBe('2026-11-08');
});

it('shows the free Sundays in the admin actions of an observation', function () {
    $expert      = $this->createUserAndAssignToTeam('Database Experts');
    $observation = sketchObservation();
    addSketchOfTheWeek(sketchObservation(), '2026-11-01');

    $this->actingAs($expert)
        ->get($observation->detailUrl())
        ->assertOk()
        ->assertSee('name="date"', false)
        ->assertSee('value="2026-10-25"', false)
        ->assertDontSee('value="2026-11-01"', false)
        ->assertSee('value="2026-11-08"', false);
});

it('hides a sketch of the week until its Sunday', function () {
    $observation = sketchObservation();
    $sketch      = addSketchOfTheWeek($observation, '2026-11-01');
    $observer    = User::find($observation->observer_user_id);

    expect(SketchOfTheWeek::published()->count())->toBe(0)
        ->and($sketch->isPublished())->toBeFalse();

    $this->get(route('sketch-of-the-week'))->assertOk()->assertDontSee($observation->detailUrl(), false);
    $this->get($observation->detailUrl())->assertOk()->assertDontSee('DeepskyLog Sketch of the Week');
    $this->get(route('observer.show', $observer->slug))->assertOk()->assertDontSee('DeepskyLog sketches of the week');

    Carbon::setTestNow('2026-11-01 00:01:00');

    expect(SketchOfTheWeek::published()->count())->toBe(1)
        ->and($sketch->fresh()->isPublished())->toBeTrue();

    $this->get($observation->detailUrl())->assertOk()->assertSee('DeepskyLog Sketch of the Week');
    $this->get(route('observer.show', $observer->slug))->assertOk()->assertSee('DeepskyLog sketches of the week');
});
