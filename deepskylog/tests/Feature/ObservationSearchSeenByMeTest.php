<?php

use App\Livewire\ObservationSearchTable;
use App\Models\Observation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

function seenByMeObservation(User $user, string $name, bool $drawing = false, ?int $cometId = null): Observation
{
    return Observation::query()->create([
        'observer_username' => $user->username,
        'observer_user_id' => $user->id,
        'observed_on' => '2026-05-01',
        'target_type' => $cometId ? 'comet' : 'deepsky',
        'target_table' => $cometId ? 'cometobjects' : 'objects',
        'target_key' => $cometId ? (string) $cometId : $name,
        'target_name' => $name,
        'has_drawing' => $drawing,
        'legacy_source' => null,
    ]);
}

beforeEach(function () {
    $this->me = User::factory()->create(['username' => 'seenme', 'slug' => 'seenme']);
    $other = User::factory()->create(['username' => 'seenother', 'slug' => 'seenother']);

    seenByMeObservation($this->me, 'M 31');
    seenByMeObservation($this->me, 'M 51', true);
    foreach (['M 31', 'M 51', 'M 81'] as $name) {
        seenByMeObservation($other, $name);
    }

    $halley = DB::table('cometobjects')->insertGetId(['name' => '1P/Halley', 'slug' => '1p-halley', 'icqname' => '', 'timestamp' => now()]);
    seenByMeObservation($this->me, '1P/Halley', false, $halley);
    seenByMeObservation($other, '1P/Halley', true, $halley);
});

it('links the seen and drawn marks to the observer\'s own observations and drawings of the object', function () {
    Livewire::actingAs($this->me)
        ->test(ObservationSearchTable::class, ['objectName' => '%'])
        ->assertSeeHtml('href="' . e(route('observations.user.object', ['observer' => 'seenme', 'object' => 'm-31'])) . '"')
        ->assertSeeHtml('href="' . e(route('observations.drawings.user.object', ['observer' => 'seenme', 'object' => 'm-51'])) . '"')
        ->assertSeeHtml('href="' . e(route('observations.comet.user.object', ['observer' => 'seenme', 'object' => '1p-halley'])) . '"')
        ->assertDontSeeHtml(route('observations.user.object', ['observer' => 'seenme', 'object' => 'm-81']));
});

it('makes the drawing icon a link to the drawing', function () {
    Livewire::test(ObservationSearchTable::class, ['objectName' => '1P/Halley'])
        ->assertSeeHtml('title="' . e(__('View drawing')) . '"')
        ->assertSeeHtml('href="/images/drawings/');
});

it('has the drawing lightbox on both pages with the observation search table', function () {
    $this->get(route('observations.search', ['q' => 'M 31']))
        ->assertOk()->assertSee('id="obs-drawing-modal"', false);
    $this->get(route('search.observations.advanced.results', ['drawing_state' => 'with_drawing']))
        ->assertOk()->assertSee('id="obs-drawing-modal"', false);
});

it('marks the objects the observer has seen or drawn in the observation search', function () {
    Livewire::actingAs($this->me)
        ->test(ObservationSearchTable::class, ['objectName' => 'M*'])
        ->assertSee(__('Seen by me'))
        ->assertSeeHtml('title="' . e(__('You have seen this object')) . '"')
        ->assertSeeHtml('title="' . e(__('You have seen and drawn this object')) . '"')
        ->assertSeeHtml('title="' . e(__('You have not seen this object')) . '"');
});

it('finds the seen status of a page in one query', function () {
    DB::enableQueryLog();
    Livewire::actingAs($this->me)
        ->test(ObservationSearchTable::class, ['objectName' => 'M*']);

    $statusQueries = collect(DB::getQueryLog())
        ->filter(fn($query) => str_contains($query['query'], 'as drawn'));

    expect($statusQueries)->toHaveCount(1);
});

it('does not show the seen column to guests', function () {
    Livewire::test(ObservationSearchTable::class, ['objectName' => 'M*'])
        ->assertDontSee(__('Seen by me'))
        ->assertDontSeeHtml(e(__('You have seen this object')));
});
