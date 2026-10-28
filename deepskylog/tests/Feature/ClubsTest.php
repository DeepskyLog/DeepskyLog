<?php

use App\Actions\Jetstream\DeleteUser;
use App\Models\Club;
use App\Models\ClubMember;
use App\Models\Message;
use App\Models\Observation;
use App\Models\ObservingChallenge;
use App\Models\ObservingGoal;
use App\Models\ObservingList;
use App\Models\StarParty;
use App\Models\User;
use App\Services\ClubRanking;
use Carbon\Carbon;
use Database\Factories\LocationFactory;
use Illuminate\Support\Facades\DB;

// Clubs and groups: observers make a club, join several clubs (at once or
// after an administrator lets them in), share lists, and the club has its
// own challenges, starparties and ranking.

function clubObserver(string $username): User
{
    return User::factory()->create(['username' => $username, 'name' => ucfirst($username), 'slug' => $username]);
}

function makeClub(User $admin, array $attributes = []): Club
{
    $club = Club::create($attributes + ['name' => 'Urania', 'join_policy' => Club::APPROVAL, 'user_id' => $admin->id, 'country' => 'BE']);
    ClubMember::create(['club_id' => $club->id, 'user_id' => $admin->id, 'role' => ClubMember::ROLE_ADMIN]);

    return $club;
}

function clubObservation(User $observer, string $object, string $date, array $attributes = []): Observation
{
    return Observation::query()->create(array_merge([
        'observer_username' => $observer->username,
        'observer_user_id'  => $observer->id,
        'observed_on'       => $date,
        'observed_time'     => '22:00:00',
        'language'          => 'en',
        'description'       => '',
        'target_type'       => 'deepsky',
        'target_table'      => 'objects',
        'target_key'        => $object,
        'target_name'       => $object,
        'has_drawing'       => false,
    ], $attributes));
}

beforeEach(fn () => Carbon::setTestNow('2026-10-28 12:00:00'));
afterEach(fn () => Carbon::setTestNow());

it('lets an observer make a club and become its administrator', function () {
    $user = clubObserver('anna');

    $this->actingAs($user)->get(route('clubs.create'))->assertOk();
    $this->actingAs($user)->post(route('clubs.store'), [
        'name'        => 'Volkssterrenwacht Urania',
        'description' => '<p>Every Friday</p><script>alert(1)</script>',
        'place'       => 'Hove',
        'country'     => 'BE',
        'website'     => 'https://urania.be',
        'join_policy' => 'open',
    ])->assertRedirect(route('clubs.show', 'volkssterrenwacht-urania'));

    $club = Club::sole();
    expect($club->description)->not->toContain('script')
        ->and($club->isAdmin($user))->toBeTrue()
        ->and($club->isOpen())->toBeTrue();

    $this->get(route('clubs.index'))->assertOk()->assertSee('Volkssterrenwacht Urania')->assertSee('Hove');
    $this->get(route('clubs.show', $club))->assertOk()->assertSee('Every Friday')->assertSee('Anna');

    // A second club with the same name is refused
    $this->actingAs($user)->post(route('clubs.store'), ['name' => 'Volkssterrenwacht Urania', 'join_policy' => 'open'])
        ->assertSessionHasErrors('name');
});

it('lets an observer be a member of several clubs', function () {
    $anna  = clubObserver('anna');
    $bert  = clubObserver('bert');
    $open  = makeClub($anna, ['name' => 'Open club', 'join_policy' => Club::OPEN]);
    $other = makeClub($anna, ['name' => 'Second club', 'join_policy' => Club::OPEN]);

    $this->actingAs($bert)->post(route('clubs.join', $open))->assertRedirect();
    $this->actingAs($bert)->post(route('clubs.join', $other))->assertRedirect();

    expect($bert->clubs()->pluck('name')->sort()->values()->all())->toBe(['Open club', 'Second club']);

    $this->get(route('observer.show', $bert->slug))->assertOk()->assertSee('Open club')->assertSee('Second club');
    $this->actingAs($bert)->get(route('clubs.index'))->assertOk()->assertSee('My clubs');
});

it('lets the club administrators decide who joins a club with approval', function () {
    $anna = clubObserver('anna');
    $bert = clubObserver('bert');
    $club = makeClub($anna);

    $this->actingAs($bert)->post(route('clubs.join', $club))->assertRedirect();
    $row = ClubMember::where('user_id', $bert->id)->sole();
    expect($row->status)->toBe(ClubMember::REQUESTED)
        ->and($club->fresh()->isMember($bert))->toBeFalse()
        ->and(Message::where('receiver', 'anna')->where('subject', 'like', '%asks to join%')->exists())->toBeTrue();

    // Only a club administrator lets them in
    $this->actingAs($bert)->post(route('clubs.members.approve', [$club, $row]))->assertForbidden();
    $this->actingAs($anna)->get(route('clubs.show', $club))->assertSee('Asked to join')->assertSee('Bert');
    $this->actingAs($anna)->post(route('clubs.members.approve', [$club, $row]))->assertRedirect();

    expect($club->fresh()->isMember($bert))->toBeTrue()
        ->and(Message::where('receiver', 'bert')->where('subject', 'like', '%now a member%')->exists())->toBeTrue();
});

it('lets the club administrators invite observers, who accept or decline', function () {
    $anna = clubObserver('anna');
    $bert = clubObserver('bert');
    $carl = clubObserver('carl');
    $club = makeClub($anna);

    $this->actingAs($bert)->post(route('clubs.invite', $club), ['receivers' => json_encode(['carl'])])->assertForbidden();
    $this->actingAs($anna)->post(route('clubs.invite', $club), ['receivers' => json_encode(['bert', 'carl'])])->assertRedirect();

    expect(ClubMember::where('status', ClubMember::INVITED)->count())->toBe(2)
        ->and(Message::where('receiver', 'bert')->where('subject', 'like', '%invites you to the club%')->exists())->toBeTrue();

    $this->actingAs($bert)->get(route('clubs.index'))->assertSee('Invitations')->assertSee('Urania');
    // Accepting needs no approval
    $this->actingAs($bert)->post(route('clubs.join', $club))->assertRedirect();
    $this->actingAs($carl)->delete(route('clubs.leave', $club))->assertRedirect();

    expect($club->fresh()->isMember($bert))->toBeTrue()
        ->and(ClubMember::where('user_id', $carl->id)->exists())->toBeFalse();
});

it('keeps an administrator when the last one leaves, and deletes a club without members', function () {
    $anna = clubObserver('anna');
    $bert = clubObserver('bert');
    $club = makeClub($anna, ['join_policy' => Club::OPEN]);
    $club->join($bert);

    // The last administrator cannot become a plain member
    $annaRow = $club->membershipOf($anna);
    $this->actingAs($anna)->put(route('clubs.members.role', [$club, $annaRow]), ['role' => 'member'])->assertSessionHas('error');

    $this->actingAs($anna)->delete(route('clubs.leave', $club))->assertRedirect();
    expect($club->fresh()->isAdmin($bert))->toBeTrue();

    $this->actingAs($bert)->delete(route('clubs.leave', $club))->assertRedirect(route('clubs.index'));
    expect(Club::count())->toBe(0);
});

it('lets members share their public lists with the club', function () {
    $anna    = clubObserver('anna');
    $bert    = clubObserver('bert');
    $club    = makeClub($anna);
    $public  = ObservingList::create(['owner_user_id' => $anna->id, 'name' => 'Autumn galaxies', 'public' => true]);
    $private = ObservingList::create(['owner_user_id' => $anna->id, 'name' => 'My secret list', 'public' => false]);

    $this->actingAs($anna)->post(route('clubs.lists.store', $club), ['list_id' => $private->id])->assertSessionHas('error');
    $this->actingAs($bert)->post(route('clubs.lists.store', $club), ['list_id' => $public->id])->assertForbidden();
    $this->actingAs($anna)->post(route('clubs.lists.store', $club), ['list_id' => $public->id])->assertSessionHas('success');

    $this->actingAs($bert)->get(route('clubs.show', $club))->assertSee('Autumn galaxies')->assertDontSee('My secret list');

    $this->actingAs($bert)->delete(route('clubs.lists.destroy', $club), ['list_id' => $public->id])->assertForbidden();
    $this->actingAs($anna)->delete(route('clubs.lists.destroy', $club), ['list_id' => $public->id])->assertRedirect();
    expect($club->observingLists()->count())->toBe(0);
});

it('has club challenges that only members join and that are not in the news', function () {
    $anna = clubObserver('anna');
    $bert = clubObserver('bert');
    $carl = clubObserver('carl');
    $club = makeClub($anna, ['join_policy' => Club::OPEN]);
    $club->join($bert);
    DB::table('objectnames')->insert([
        'objectname' => 'M 1', 'catalog' => 'M', 'catindex' => '1', 'altname' => 'M 1', 'slug' => 'm-1', 'timestamp' => now(),
    ]);

    $this->actingAs($bert)->get(route('clubs.challenges.create', $club))->assertForbidden();
    $this->actingAs($anna)->get(route('clubs.challenges.create', $club))->assertOk();
    $this->actingAs($anna)->post(route('clubs.challenges.store', $club), [
        'name'      => 'Messier in November',
        'source'    => 'catalog:m',
        'mode'      => 'observed',
        'starts_on' => '2026-11-01',
        'ends_on'   => '2026-11-30',
    ])->assertRedirect(route('clubs.show', $club));

    $challenge = ObservingChallenge::sole();
    expect($challenge->club_id)->toBe($club->id)
        ->and($challenge->newsItem())->toBeNull()
        ->and(Message::where('receiver', 'bert')->where('subject', 'like', '%New challenge of the club%')->exists())->toBeTrue();

    $this->get(route('challenges.index'))->assertDontSee('Messier in November');
    $this->get(route('clubs.show', $club))->assertSee('Messier in November');

    $this->actingAs($carl)->post(route('challenges.join', $challenge))->assertNotFound();
    $this->actingAs($carl)->get(route('challenges.show', $challenge))->assertOk()->assertSee('Join the club to take part');
    $this->actingAs($bert)->post(route('challenges.join', $challenge))->assertRedirect();
    expect($challenge->hasJoined($bert))->toBeTrue();

    // On the home page box of the members only
    expect(ObservingChallenge::forHomePage($bert)->pluck('challenge.id')->all())->toBe([$challenge->id])
        ->and(ObservingChallenge::forHomePage($carl))->toBeEmpty();

    // Leaving the club leaves its challenges that have not ended
    $this->actingAs($bert)->delete(route('clubs.leave', $club))->assertRedirect();
    expect(ObservingGoal::where('user_id', $bert->id)->exists())->toBeFalse();

    // Only the club administrators edit it
    $this->actingAs($carl)->put(route('clubs.challenges.update', [$club, $challenge]), ['name' => 'x'])->assertForbidden();
});

it('ranks the members of a club in a period', function () {
    $anna = clubObserver('anna');
    $bert = clubObserver('bert');
    $carl = clubObserver('carl');
    $club = makeClub($anna, ['join_policy' => Club::OPEN]);
    $club->join($bert);
    DB::table('objectnames')->insert([
        ['objectname' => 'M 31', 'catalog' => 'M', 'catindex' => '31', 'altname' => 'M 31', 'slug' => 'm-31', 'timestamp' => now()],
        ['objectname' => 'M 33', 'catalog' => 'M', 'catindex' => '33', 'altname' => 'M 33', 'slug' => 'm-33', 'timestamp' => now()],
    ]);

    clubObservation($anna, 'M 31', '2026-09-01', ['has_drawing' => true]);
    clubObservation($anna, 'M 31', '2026-09-02');
    clubObservation($anna, 'M 33', '2025-09-02');
    clubObservation($bert, 'M 31', '2026-08-01');
    clubObservation($bert, 'M 33', '2026-08-02');
    clubObservation($bert, 'NGC 7000', '2026-08-03');
    // Not a member
    clubObservation($carl, 'M 31', '2026-08-01');

    $source  = ['key' => 'catalog:m', 'type' => 'catalog', 'catalog' => 'M', 'short_label' => 'M', 'label' => 'M'];
    $ranking = ClubRanking::for($club, 'year', $source, 'objects');
    expect(collect($ranking['rows'])->map(fn ($row) => [$row['name'], $row['rank'], $row['objects'], $row['observations'], $row['drawings'], $row['source']])->all())
        ->toBe([['Bert', 1, 3, 3, 0, 2], ['Anna', 2, 1, 2, 1, 1]])
        ->and($ranking['source_total'])->toBe(DB::table('objectnames')->where('catalog', 'M')->distinct('catindex')->count('catindex'));

    $all = ClubRanking::for($club, 'all', $source, 'source');
    expect(collect($all['rows'])->map(fn ($row) => [$row['name'], $row['rank'], $row['source']])->all())
        ->toBe([['Anna', 1, 2], ['Bert', 1, 2]]);

    $this->get(route('clubs.show', [$club, 'period' => 'all']))->assertOk()->assertSee('Club ranking')->assertDontSee('Carl');
});

it('shows a private starparty of a club to its members', function () {
    $anna = clubObserver('anna');
    $bert = clubObserver('bert');
    $carl = clubObserver('carl');
    $club = makeClub($anna, ['join_policy' => Club::OPEN]);
    $club->join($bert);
    $location = LocationFactory::new()->create([
        'user_id'  => $anna->id, 'observer' => $anna->username, 'name' => 'Club field',
        'latitude' => 50.1, 'longitude' => 5.5, 'timezone' => 'Europe/Brussels', 'country' => 'Belgium', 'elevation' => 400,
    ]);

    $this->actingAs($anna)->get(route('star-parties.create', ['club' => $club->slug]))->assertOk()->assertSee('Urania');
    $this->actingAs($anna)->post(route('star-parties.store'), [
        'name'        => 'Club night',
        'language'    => 'en',
        'visibility'  => StarParty::PRIVATE,
        'start_date'  => '2026-11-10',
        'end_date'    => '2026-11-10',
        'location_id' => $location->id,
        'club_id'     => $club->id,
    ])->assertRedirect();

    $party = StarParty::sole();
    expect($party->club_id)->toBe($club->id);

    $this->actingAs($bert)->get(route('star-parties.show', $party))->assertOk();
    $this->actingAs($carl)->get(route('star-parties.show', $party))->assertNotFound();
    $this->actingAs($bert)->get(route('clubs.show', $club))->assertSee('Club night');
    $this->actingAs($carl)->get(route('clubs.show', $club))->assertDontSee('Club night');
    $this->actingAs($bert)->post(route('star-parties.answer', $party), ['status' => 'going'])->assertSessionHas('success');

    // Not a club of the organiser
    $other = makeClub($carl, ['name' => 'Elsewhere']);
    $this->actingAs($anna)->put(route('star-parties.update', $party), [
        'name'       => 'Club night', 'language' => 'en', 'visibility' => StarParty::PRIVATE,
        'start_date' => '2026-11-10', 'end_date' => '2026-11-10', 'club_id' => $other->id,
    ])->assertSessionHasErrors('club_id');
});

it('hands a club over when its administrator deletes their account', function () {
    $anna = clubObserver('anna');
    $bert = clubObserver('bert');
    $club = makeClub($anna, ['join_policy' => Club::OPEN]);
    $club->join($bert);
    $alone = makeClub($anna, ['name' => 'Only me']);

    app(DeleteUser::class)->delete($anna);

    expect($club->fresh()->isAdmin($bert))->toBeTrue()
        ->and(Club::where('name', 'Only me')->exists())->toBeFalse();
});
