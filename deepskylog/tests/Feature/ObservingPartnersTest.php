<?php

use App\Livewire\ObservingPartners as ObservingPartnersPage;
use App\Models\Club;
use App\Models\ClubMember;
use App\Models\MessageBlock;
use App\Models\StarParty;
use App\Models\User;
use App\Services\ObservingPartners;
use Database\Factories\LocationFactory;
use Livewire\Livewire;

// Observing partners nearby: observers who opted in find each other by the
// cell of their standard location, never by the location itself.

function partner(string $username, float $latitude, float $longitude, bool $search = true, array $attributes = []): User
{
    $user     = User::factory()->create(['username' => $username, 'name' => ucfirst($username), 'slug' => $username, 'partner_search' => $search, ...$attributes]);
    $location = LocationFactory::new()->create(['user_id' => $user->id, 'observer' => $username, 'latitude' => $latitude, 'longitude' => $longitude]);
    $user->forceFill(['stdlocation' => $location->id])->save();

    return $user->fresh();
}

it('measures the distance between the centres of the cells, not the locations', function () {
    // Two points in the same cell of 0.25°, far apart within it
    expect(ObservingPartners::cellDistanceKm(50.76, 4.51, 50.99, 4.74))->toBe(0.0)
        // The neighbouring cell to the north: 0.25° of latitude, about 28 km
        ->and(round(ObservingPartners::cellDistanceKm(50.80, 4.60, 51.01, 4.60)))->toBe(28.0)
        ->and(ObservingPartners::shownDistance(12.0))->toBeNull()
        ->and(ObservingPartners::shownDistance(83.0))->toBe(80);
});

it('finds the observers who opted in within the radius, nearest first', function () {
    $viewer = partner('viewer', 50.80, 4.60);
    partner('far', 51.80, 4.60);          // about 111 km
    partner('near', 50.90, 4.70);         // the same cell
    partner('hidden', 50.85, 4.65, false); // did not opt in
    partner('north', 51.05, 4.60);        // the next cell

    $found = ObservingPartners::nearby($viewer, 100)->map(fn ($partner) => $partner['user']->username)->all();

    expect($found)->toBe(['near', 'north'])
        ->and(ObservingPartners::nearby($viewer, 250)->pluck('user.username')->all())->toBe(['near', 'north', 'far']);
});

it('leaves out observers who blocked the viewer or whom the viewer blocked', function () {
    $viewer   = partner('viewer', 50.80, 4.60);
    $blocking = partner('blocking', 50.80, 4.60);
    $blocked  = partner('blocked', 50.80, 4.60);
    partner('friend', 50.80, 4.60);
    MessageBlock::create(['user_id' => $blocking->id, 'blocked_user_id' => $viewer->id]);
    MessageBlock::create(['user_id' => $viewer->id, 'blocked_user_id' => $blocked->id]);

    expect(ObservingPartners::nearby($viewer, 100)->pluck('user.username')->all())->toBe(['friend']);
});

it('shows the partners only to an observer who opted in, without their location', function () {
    $viewer = partner('viewer', 50.80, 4.60, false);
    $other  = partner('other', 50.90, 4.70, true, ['partner_note' => 'Weekends with a 30 cm Dobsonian']);
    $other->standardLocation->forceFill(['name' => 'Secret backyard'])->save();

    Livewire::actingAs($viewer)->test(ObservingPartnersPage::class)
        ->assertSee('One observer within 100 km looks for partners.')
        ->assertDontSee('Weekends with a 30 cm Dobsonian')
        ->set('partnerSearch', true)
        ->set('note', "  I have a car\n and a <b>16-inch</b>  ")
        ->call('save')
        ->assertSee('Weekends with a 30 cm Dobsonian')
        ->assertSee('within 30 km')
        ->assertSee(route('messages.create', ['to' => 'other']), false)
        ->assertDontSee('Secret backyard');

    expect($viewer->fresh())->partner_search->toBeTrue()
        ->partner_note->toBe('I have a car and a 16-inch');
});

it('asks for a standard location first', function () {
    $viewer = User::factory()->create(['stdlocation' => null]);

    Livewire::actingAs($viewer)->test(ObservingPartnersPage::class)
        ->assertSee('Choose a standard location with coordinates first');
});

it('suggests the starparties near the viewer and the clubs of the partners', function () {
    $viewer = partner('viewer', 50.80, 4.60);
    $member = partner('member', 50.90, 4.70);
    $club   = Club::create(['name' => 'Urania', 'join_policy' => Club::OPEN, 'user_id' => $member->id, 'country' => 'BE']);
    ClubMember::create(['club_id' => $club->id, 'user_id' => $member->id, 'role' => ClubMember::ROLE_ADMIN]);

    $party = fn (string $name, float $latitude, array $attributes = []) => StarParty::create($attributes + [
        'user_id'       => $member->id, 'name' => $name, 'description' => '', 'language' => 'en', 'visibility' => StarParty::PUBLIC,
        'start_date'    => today()->addDays(10)->toDateString(), 'end_date' => today()->addDays(11)->toDateString(),
        'location_name' => 'Field', 'latitude' => $latitude, 'longitude' => 4.60, 'timezone' => 'Europe/Brussels',
    ]);
    $party('Nearby party', 51.0);
    $party('Far party', 53.0);
    $party('Cancelled party', 51.0, ['cancelled_at' => now()]);
    $party('Private party', 51.0, ['visibility' => StarParty::PRIVATE]);

    expect(ObservingPartners::starParties($viewer, 100)->map(fn ($row) => $row['party']->name)->all())->toBe(['Nearby party'])
        ->and(ObservingPartners::clubs(ObservingPartners::nearby($viewer, 100))->map(fn ($row) => [$row['club']->name, $row['partners']])->all())
        ->toBe([['Urania', 1]]);

    $this->actingAs($viewer)->get('/observing-partners')->assertOk()->assertSee('Nearby party')->assertSee('Urania');
});
