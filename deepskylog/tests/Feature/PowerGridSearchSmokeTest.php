<?php

use App\Livewire\AdminEyepieceTable;
use App\Livewire\AdminEyepieceTypeTable;
use App\Livewire\AdminFilterTable;
use App\Livewire\AdminInstrumentTable;
use App\Livewire\AdminLensTable;
use App\Livewire\AdminUserTable;
use App\Livewire\AdvancedObjectSearchTable;
use App\Livewire\EyepieceTable;
use App\Livewire\FilterTable;
use App\Livewire\InstrumentSetTable;
use App\Livewire\InstrumentTable;
use App\Livewire\LensTable;
use App\Livewire\LocationTable;
use App\Livewire\NearbyObjectsTable;
use App\Livewire\ObservationSearchTable;
use App\Livewire\PopularObservationsTable;
use App\Livewire\PopularSessionsTable;
use App\Livewire\RemoveUserTable;
use App\Livewire\SearchResultsTable;
use App\Livewire\UserPopularObservationsTable;
use App\Models\FilterMake;
use App\Models\LensMake;
use App\Models\User;
use Database\Factories\FilterFactory;
use Database\Factories\LensFactory;
use Livewire\Livewire;

/**
 * PowerGrid qualifies every searchable column with the table of the query's base
 * model, so a column that is not a real column of that table (a computed field, a
 * joined column, a column of a subquery that is not selected) makes the search
 * blow up with an "Unknown column" error. These tests type a term into every
 * table's search box to make sure the query is valid.
 */
beforeEach(function () {
    $this->user = User::factory()->withPersonalTeam()->create(['username' => 'tester']);
    $this->actingAs($this->user);
    Livewire::withoutLazyLoading();
});

test('tables without parameters can be searched', function (string $component) {
    Livewire::test($component)
        ->set('search', 'zzz')
        ->assertOk();
})->with([
    AdminEyepieceTable::class,
    AdminEyepieceTypeTable::class,
    AdminFilterTable::class,
    AdminInstrumentTable::class,
    AdminLensTable::class,
    AdvancedObjectSearchTable::class,
    EyepieceTable::class,
    FilterTable::class,
    InstrumentSetTable::class,
    InstrumentTable::class,
    LensTable::class,
    LocationTable::class,
    NearbyObjectsTable::class,
    ObservationSearchTable::class,
    PopularObservationsTable::class,
    PopularSessionsTable::class,
    RemoveUserTable::class,
    SearchResultsTable::class,
]);

test('the team member table can be searched', function () {
    Livewire::test(AdminUserTable::class, ['team' => $this->user->currentTeam->id])
        ->set('search', 'zzz')
        ->assertOk();
});

test('the user popular observations table can be searched', function () {
    Livewire::test(UserPopularObservationsTable::class, ['username' => $this->user->username])
        ->set('search', 'zzz')
        ->assertOk();
});

test('the filter table finds a filter by the name of its make', function () {
    $make = FilterMake::firstOrCreate(['name' => 'Lumicon']);

    FilterFactory::new()->create([
        'user_id' => $this->user->id,
        'observer' => $this->user->username,
        'name' => 'Deep Sky',
        'make_id' => $make->id,
    ]);
    FilterFactory::new()->create([
        'user_id' => $this->user->id,
        'observer' => $this->user->username,
        'name' => 'Nebula',
    ]);

    $other = User::factory()->create(['username' => 'somebody', 'name' => 'Somebody Else']);
    FilterFactory::new()->create([
        'user_id' => $other->id,
        'observer' => $other->username,
        'name' => 'Somebody Elses Filter',
        'make_id' => $make->id,
    ]);

    Livewire::test(FilterTable::class)
        ->set('search', 'Lumicon')
        ->assertOk()
        ->assertSee('Deep Sky')
        ->assertDontSee('Nebula')
        ->assertDontSee('Somebody Elses Filter');
});

test('the lens table finds a lens by the name of its make', function () {
    $make = LensMake::firstOrCreate(['name' => 'Televue']);

    LensFactory::new()->create([
        'user_id' => $this->user->id,
        'observer' => $this->user->username,
        'name' => 'Powermate',
        'make_id' => $make->id,
    ]);
    LensFactory::new()->create([
        'user_id' => $this->user->id,
        'observer' => $this->user->username,
        'name' => 'Reducer',
    ]);

    Livewire::test(LensTable::class)
        ->set('search', 'Televue')
        ->assertOk()
        ->assertSee('Powermate')
        ->assertDontSee('Reducer');
});

test('the popular sessions table finds a session by name and by observer', function () {
    $session = App\Models\ObservationSession::create([
        'id' => 4711,
        'name' => 'Messier marathon',
        'slug' => 'messier-marathon',
        'observerid' => $this->user->username,
        'locationid' => 1,
        'active' => 1,
        'begindate' => '2026-03-14 20:00:00',
        'enddate' => '2026-03-15 04:00:00',
    ]);
    $other = App\Models\ObservationSession::create([
        'id' => 4712,
        'name' => 'Comet hunting',
        'slug' => 'comet-hunting',
        'observerid' => $this->user->username,
        'locationid' => 1,
        'active' => 1,
        'begindate' => '2026-03-16 20:00:00',
        'enddate' => '2026-03-17 04:00:00',
    ]);

    foreach ([$session, $other] as $liked) {
        App\Models\ObservationLike::create([
            'user_id' => $this->user->id,
            'observation_type' => 'session',
            'observation_id' => $liked->id,
        ]);
    }

    Livewire::test(App\Livewire\PopularSessionsTable::class)
        ->set('search', 'Messier')
        ->assertOk()
        ->assertSee('Messier marathon')
        ->assertDontSee('Comet hunting');

    Livewire::test(App\Livewire\PopularSessionsTable::class)
        ->set('search', $this->user->name)
        ->assertOk()
        ->assertSee('Messier marathon')
        ->assertSee('Comet hunting');
});
