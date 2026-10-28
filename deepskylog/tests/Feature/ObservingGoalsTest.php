<?php

use App\Livewire\ObservingGoals;
use App\Models\Observation;
use App\Models\ObservingGoal;
use App\Models\ObservingList;
use App\Models\User;
use App\Services\ObserverBlog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

function goalCatalog(string $catalog, int $count): void
{
    foreach (range(1, $count) as $index) {
        DB::table('objectnames')->insert([
            'objectname' => $catalog . ' ' . $index,
            'catalog' => $catalog,
            'catindex' => (string) $index,
            'altname' => $catalog . ' ' . $index,
            'slug' => strtolower($catalog) . '-' . $index,
            'timestamp' => now(),
        ]);
    }
}

function goalObservation(User $observer, string $object, string $date, array $attributes = []): Observation
{
    return Observation::query()->create(array_merge([
        'observer_username' => $observer->username,
        'observer_user_id' => $observer->id,
        'observed_on' => $date,
        'observed_time' => '22:00:00',
        'language' => 'en',
        'description' => '',
        'target_type' => 'deepsky',
        'target_table' => 'objects',
        'target_key' => $object,
        'target_name' => $object,
        'has_drawing' => false,
    ], $attributes));
}

function goalFor(User $observer, array $attributes = []): ObservingGoal
{
    return ObservingGoal::query()->create(array_merge([
        'user_id' => $observer->id,
        'catalog' => 'TG',
        'source_label' => 'TG',
        'target_count' => 3,
        'mode' => 'observed',
        'starts_on' => '2026-01-01',
        'ends_on' => '2026-12-31',
    ], $attributes));
}

beforeEach(function () {
    Carbon::setTestNow('2026-10-26 12:00:00');
    $this->observer = User::factory()->create(['name' => 'Goal Setter', 'username' => 'goalsetter', 'slug' => 'goal-setter']);
    goalCatalog('TG', 5);
});

afterEach(fn () => Carbon::setTestNow());

it('counts the different objects seen in the period and the day the target was reached', function () {
    goalObservation($this->observer, 'TG 1', '2025-12-30');
    goalObservation($this->observer, 'TG 2', '2026-02-01');
    goalObservation($this->observer, 'TG 2', '2026-02-03');
    goalObservation($this->observer, 'TG 3', '2026-03-10');
    goalObservation($this->observer, 'NGC 7000', '2026-03-11');
    $notSeen = goalObservation($this->observer, 'TG 4', '2026-04-01');
    DB::table('deepsky_observation_details')->insert(['observation_id' => $notSeen->id, 'visibility' => Observation::VISIBILITY_NOT_SEEN]);

    $goal = goalFor($this->observer);
    expect($goal->progress())->toMatchArray(['count' => 2, 'target' => 3, 'total' => 5, 'status' => 'active', 'reached_on' => null, 'days_left' => 67]);

    goalObservation($this->observer, 'TG 5', '2026-05-20');
    goalObservation($this->observer, 'TG 1', '2026-06-01');
    $goal = $goal->fresh();
    expect($goal->progress()['status'])->toBe('reached')
        ->and($goal->progress()['count'])->toBe(4)
        ->and($goal->progress()['reached_on']->toDateString())->toBe('2026-05-20');
});

it('counts all objects, only drawings or only new objects when asked', function () {
    goalObservation($this->observer, 'TG 1', '2025-12-30');
    goalObservation($this->observer, 'TG 1', '2026-01-10');
    goalObservation($this->observer, 'TG 2', '2026-01-11', ['has_drawing' => true]);

    expect(goalFor($this->observer, ['target_count' => null])->progress())->toMatchArray(['count' => 2, 'target' => 5]);
    expect(goalFor($this->observer, ['target_count' => null, 'mode' => 'drawn'])->progress())->toMatchArray(['count' => 1, 'target' => 5]);
    // TG 1 was seen before the start: 4 objects left, of which TG 2 is seen
    expect(goalFor($this->observer, ['target_count' => null, 'new_only' => true])->progress())->toMatchArray(['count' => 1, 'target' => 4, 'total' => 4]);
    // A target above the number of objects is all of them
    expect(goalFor($this->observer, ['target_count' => 200])->progress()['target'])->toBe(5);
});

it('counts the objects of an observing list and tells when the period is over or still to come', function () {
    $list = ObservingList::query()->create(['owner_user_id' => $this->observer->id, 'name' => 'Autumn favourites', 'public' => true]);
    foreach (['TG 1', 'NGC 7000'] as $order => $name) {
        DB::table('observing_list_items')->insert(['observing_list_id' => $list->id, 'object_name' => $name, 'sort_order' => $order, 'added_by_user_id' => $this->observer->id, 'created_at' => now(), 'updated_at' => now()]);
    }
    goalObservation($this->observer, 'NGC 7000', '2025-08-01');

    $ended = goalFor($this->observer, ['catalog' => null, 'observing_list_id' => $list->id, 'source_label' => 'Autumn favourites', 'target_count' => null, 'starts_on' => '2025-01-01', 'ends_on' => '2025-12-31']);
    expect($ended->progress())->toMatchArray(['count' => 1, 'target' => 2, 'status' => 'ended']);

    $upcoming = goalFor($this->observer, ['starts_on' => '2027-01-01', 'ends_on' => '2027-12-31']);
    expect($upcoming->progress()['status'])->toBe('upcoming');

    $list->delete();
    expect($ended->fresh()->progress())->toMatchArray(['count' => 0, 'target' => 0, 'status' => 'ended']);
});

it('shows the public goals on the observer page and the private ones only to the observer', function () {
    goalFor($this->observer, ['name' => 'Two hundred Herschels']);
    goalFor($this->observer, ['name' => 'Secret Messier run', 'public' => false]);

    $this->get(route('observer.show', ['observer' => 'goal-setter']))
        ->assertOk()
        ->assertSee('Observing goals')
        ->assertSee('Two hundred Herschels')
        ->assertDontSee('Secret Messier run');

    $this->actingAs($this->observer)->get(route('observer.show', ['observer' => 'goal-setter']))
        ->assertOk()
        ->assertSee('Secret Messier run');
});

it('lets the observer add, edit and remove their goals', function () {
    Livewire::actingAs($this->observer)
        ->test(ObservingGoals::class, ['observer' => $this->observer])
        ->call('create')
        ->assertSet('startsOn', '2026-01-01')
        ->assertSet('endsOn', '2026-12-31')
        ->set('source', 'catalog:tg')
        ->set('target', '3')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSee('See 3 objects of TG');

    $goal = ObservingGoal::sole();
    expect($goal->catalog)->toBe('TG')->and($goal->target_count)->toBe(3)->and($goal->public)->toBeTrue();

    Livewire::actingAs($this->observer)
        ->test(ObservingGoals::class, ['observer' => $this->observer])
        ->call('edit', $goal->id)
        ->assertSet('target', '3')
        ->set('target', '')
        ->set('endsOn', '2025-01-01')
        ->call('save')
        ->assertHasErrors(['endsOn'])
        ->set('endsOn', '2027-03-31')
        ->set('source', 'catalog:unknown')
        ->call('save')
        ->assertHasErrors(['source'])
        ->set('source', 'catalog:tg')
        ->call('save')
        ->assertHasNoErrors();

    expect($goal->fresh()->target_count)->toBeNull()->and($goal->fresh()->ends_on->toDateString())->toBe('2027-03-31');

    Livewire::actingAs($this->observer)
        ->test(ObservingGoals::class, ['observer' => $this->observer])
        ->call('delete', $goal->id);
    expect(ObservingGoal::count())->toBe(0);
});

it('does not let others change the goals of an observer', function () {
    $goal  = goalFor($this->observer);
    $other = User::factory()->create();

    Livewire::actingAs($other)
        ->test(ObservingGoals::class, ['observer' => $this->observer])
        ->call('delete', $goal->id)
        ->assertForbidden();

    expect(ObservingGoal::count())->toBe(1);
});

it('limits the number of goals', function () {
    foreach (range(1, ObservingGoal::MAX_PER_USER) as $_) {
        goalFor($this->observer);
    }

    Livewire::actingAs($this->observer)
        ->test(ObservingGoals::class, ['observer' => $this->observer])
        ->call('create')
        ->call('save')
        ->assertHasErrors(['name']);
});

it('shows the running goals and the reached ones in the blog and its feed', function () {
    goalObservation($this->observer, 'TG 1', '2026-02-01');
    goalObservation($this->observer, 'TG 2', '2026-02-02');
    goalFor($this->observer, ['name' => 'Two Messiers', 'target_count' => 2]);
    goalFor($this->observer, ['name' => 'All the Messiers', 'target_count' => null]);
    goalFor($this->observer, ['name' => 'Hidden goal', 'target_count' => 1, 'public' => false]);

    $entries = (new ObserverBlog($this->observer))->page()['entries'];
    $goal    = $entries->firstWhere('kind', 'goal');
    expect($entries->where('kind', 'goal'))->toHaveCount(1)
        ->and($goal['goal']->name)->toBe('Two Messiers')
        ->and($goal['date']->toDateString())->toBe('2026-02-02');

    $this->get(route('observer.blog', ['observer' => 'goal-setter']))
        ->assertOk()
        ->assertSee('Observing goal reached')
        ->assertSee('Two Messiers')
        ->assertSee('All the Messiers')
        ->assertSee('2 of 5 objects')
        ->assertDontSee('Hidden goal');

    // Older pages leave out the running goals
    $this->get(route('observer.blog', ['observer' => 'goal-setter', 'before' => '2026-01-01']))
        ->assertOk()
        ->assertDontSee('All the Messiers')
        ->assertDontSee('Two Messiers');

    $this->get(route('feed.observer', ['observer' => 'goal-setter']))
        ->assertOk()
        ->assertSee('reached the observing goal', false)
        ->assertDontSee('Hidden goal');
});
