<?php

use App\Actions\Jetstream\DeleteUser;
use App\Console\Commands\PruneInactiveUsers;
use App\Http\Controllers\StarPartyController;
use App\Mail\MessageReceived;
use App\Mail\StarPartyInvitationMail;
use App\Models\Location;
use App\Models\Message;
use App\Models\MessageBlock;
use App\Models\Observation;
use App\Models\ObservationSession;
use App\Models\StarParty;
use App\Models\StarPartyInvitation;
use App\Models\StarPartyParticipant;
use App\Models\User;
use App\Services\StarParties\StarPartyActivity;
use App\Services\StarParties\StarPartyBest;
use App\Services\StarParties\StarPartyCalendar;
use App\Services\StarParties\StarPartyNights;
use Database\Factories\LocationFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;

// Starparties (#315): organised at a location of the organiser, public or
// for the invited observers only, with invitations, answers, the nights at
// the site and what was observed there.

function starPartyLocation(User $user, array $attributes = []): Location
{
    return LocationFactory::new()->create($attributes + [
        'user_id'   => $user->id,
        'observer'  => $user->username,
        'name'      => 'Ardennes field',
        'latitude'  => 50.1,
        'longitude' => 5.5,
        'timezone'  => 'Europe/Brussels',
        'country'   => 'Belgium',
        'elevation' => 400,
    ]);
}

function starParty(User $organiser, array $attributes = []): StarParty
{
    $party = new StarParty($attributes + [
        'user_id'     => $organiser->id,
        'name'        => 'Autumn starparty',
        'description' => '<p>Bring a warm coat.</p>',
        'language'    => 'en',
        'visibility'  => StarParty::PUBLIC,
        'start_date'  => today()->addDays(10)->toDateString(),
        'end_date'    => today()->addDays(11)->toDateString(),
    ]);
    if (! isset($attributes['latitude'])) {
        $party->useLocation(starPartyLocation($organiser));
    }
    $party->save();
    StarPartyParticipant::create(['star_party_id' => $party->id, 'user_id' => $organiser->id, 'status' => StarPartyParticipant::GOING]);

    return $party->fresh();
}

function starPartyForm(Location $location, array $overrides = []): array
{
    return $overrides + [
        'name'        => 'Dark sky weekend',
        'description' => '<p>Two nights under a dark sky.</p>',
        'language'    => 'en',
        'visibility'  => 'public',
        'start_date'  => today()->addDays(20)->toDateString(),
        'end_date'    => today()->addDays(21)->toDateString(),
        'location_id' => $location->id,
        'venue'       => 'Behind the farm',
        'website'     => 'https://example.org/starparty',
    ];
}

it('lets an observer organise a starparty at one of their locations', function () {
    $organiser = User::factory()->create();
    $location  = starPartyLocation($organiser);

    $response = $this->actingAs($organiser)->post(route('star-parties.store'), starPartyForm($location));

    $party = StarParty::firstOrFail();
    $response->assertRedirect(route('star-parties.show', $party));
    expect($party->user_id)->toBe($organiser->id)
        ->and($party->slug)->toBe('dark-sky-weekend')
        ->and($party->location_name)->toBe('Ardennes field')
        ->and($party->latitude)->toBe(50.1)
        ->and($party->timezone)->toBe('Europe/Brussels')
        ->and($party->country)->toBe('Belgium')
        ->and($party->nights())->toHaveCount(2)
        ->and($party->statusOf($organiser))->toBe(StarPartyParticipant::GOING);

    // The site stays when the location is deleted
    $location->delete();
    expect($party->fresh()->location_id)->toBeNull()
        ->and($party->fresh()->site()->latitude)->toBe(50.1);
});

it('refuses a location of another observer, a starparty in the past or one of too many nights', function () {
    $organiser = User::factory()->create();
    $other     = User::factory()->create();
    $location  = starPartyLocation($organiser);

    $this->actingAs($organiser)->post(route('star-parties.store'), starPartyForm(starPartyLocation($other)))
        ->assertSessionHasErrors('location_id');
    $this->actingAs($organiser)->post(route('star-parties.store'), starPartyForm($location, ['start_date' => today()->subDay()->toDateString()]))
        ->assertSessionHasErrors('start_date');
    $this->actingAs($organiser)->post(route('star-parties.store'), starPartyForm($location, [
        'end_date' => today()->addDays(20 + StarParty::MAX_NIGHTS)->toDateString(),
    ]))->assertSessionHasErrors('end_date');
    $this->actingAs($organiser)->post(route('star-parties.store'), starPartyForm($location, ['website' => 'javascript:alert(1)']))
        ->assertSessionHasErrors('website');

    expect(StarParty::count())->toBe(0);
    auth()->logout();
    $this->get(route('star-parties.create'))->assertRedirect(route('login'));
});

it('limits the starparties an observer organises that have not ended', function () {
    $organiser = User::factory()->create();
    for ($i = 0; $i < StarParty::MAX_UPCOMING_PER_ORGANISER; $i++) {
        starParty($organiser, ['name' => "Party {$i}"]);
    }

    $this->actingAs($organiser)->get(route('star-parties.create'))->assertRedirect(route('star-parties.index'));
    $this->actingAs($organiser)->post(route('star-parties.store'), starPartyForm(starPartyLocation($organiser)))
        ->assertSessionHas('error');
    expect(StarParty::count())->toBe(StarParty::MAX_UPCOMING_PER_ORGANISER);
});

it('shows a public starparty to everybody and a private one only to the invited observers', function () {
    $organiser = User::factory()->create();
    $invited   = User::factory()->create();
    $stranger  = User::factory()->create();
    $public    = starParty($organiser, ['name' => 'Open night']);
    $private   = starParty($organiser, ['name' => 'Club night', 'visibility' => StarParty::PRIVATE]);
    StarPartyParticipant::create(['star_party_id' => $private->id, 'user_id' => $invited->id, 'status' => StarPartyParticipant::INVITED]);

    $this->get(route('star-parties.show', $public))->assertOk()->assertSee('Open night')->assertSee('Bring a warm coat.');
    $this->get(route('star-parties.show', $private))->assertNotFound();
    $this->actingAs($stranger)->get(route('star-parties.show', $private))->assertNotFound();
    $this->actingAs($invited)->get(route('star-parties.show', $private))->assertOk()->assertSee('Club night');

    auth()->logout();
    $this->get(route('star-parties.index'))->assertOk()->assertSee('Open night')->assertDontSee('Club night');
    $this->actingAs($invited)->get(route('star-parties.index'))->assertOk()->assertSee('Club night');
});

it('invites observers with a message in their language, but not an observer who blocked the organiser', function () {
    Mail::fake();
    $organiser = User::factory()->create(['name' => 'Organiser']);
    $dutch     = User::factory()->create(['language' => 'nl', 'sendMail' => true]);
    $blocker   = User::factory()->create();
    MessageBlock::create(['user_id' => $blocker->id, 'blocked_user_id' => $organiser->id]);
    $party = starParty($organiser);

    $this->actingAs($organiser)->post(route('star-parties.invite', $party), [
        'receivers' => json_encode([$dutch->username, $blocker->username, $organiser->username]),
    ])->assertSessionHas('success');

    expect($party->participants()->where('user_id', $dutch->id)->value('status'))->toBe(StarPartyParticipant::INVITED)
        ->and($party->participants()->where('user_id', $blocker->id)->exists())->toBeFalse();
    $message = Message::where('receiver', $dutch->username)->firstOrFail();
    expect($message->sender)->toBe($organiser->username)
        ->and($message->subject)->toContain('nodigt je uit')
        ->and($message->message)->toContain(route('star-parties.show', $party));
    expect(Message::where('receiver', $blocker->username)->exists())->toBeFalse();
    Mail::assertQueued(MessageReceived::class, fn ($mail) => $mail->hasTo($dutch->email));

    // Inviting again does not invite twice
    $this->actingAs($organiser)->post(route('star-parties.invite', $party), ['receivers' => json_encode([$dutch->username])]);
    expect(Message::where('receiver', $dutch->username)->count())->toBe(1);
});

it('lets only the organiser invite to a private starparty, and those who come to a public one', function () {
    $organiser = User::factory()->create();
    $guest     = User::factory()->create();
    $friend    = User::factory()->create();
    $private   = starParty($organiser, ['visibility' => StarParty::PRIVATE]);
    $public    = starParty($organiser, ['name' => 'Public']);
    StarPartyParticipant::create(['star_party_id' => $private->id, 'user_id' => $guest->id, 'status' => StarPartyParticipant::GOING]);
    StarPartyParticipant::create(['star_party_id' => $public->id, 'user_id' => $guest->id, 'status' => StarPartyParticipant::GOING]);

    $this->actingAs($guest)->post(route('star-parties.invite', $private), ['receivers' => json_encode([$friend->username])])->assertForbidden();
    $this->actingAs($guest)->post(route('star-parties.invite', $public), ['receivers' => json_encode([$friend->username])])->assertSessionHas('success');
    expect($public->participants()->where('user_id', $friend->id)->value('invited_by_user_id'))->toBe($guest->id);
});

it('records the answers and tells the organiser', function () {
    $organiser = User::factory()->create();
    $observer  = User::factory()->create(['name' => 'Eager observer']);
    $party     = starParty($organiser, ['max_participants' => 2]);

    $this->actingAs($observer)->post(route('star-parties.answer', $party), ['status' => 'going'])->assertSessionHas('success');
    expect($party->fresh()->statusOf($observer))->toBe(StarPartyParticipant::GOING);
    expect(Message::where('receiver', $organiser->username)->value('subject'))->toContain('Eager observer');

    // The starparty is full: the organiser and the observer come
    $late = User::factory()->create();
    $this->actingAs($late)->post(route('star-parties.answer', $party), ['status' => 'going'])->assertSessionHas('error');
    $this->actingAs($late)->post(route('star-parties.answer', $party), ['status' => 'maybe'])->assertSessionHas('success');
    expect($party->fresh()->statusOf($late))->toBe(StarPartyParticipant::MAYBE);

    // Saying no frees the place
    $this->actingAs($observer)->post(route('star-parties.answer', $party), ['status' => 'declined']);
    $this->actingAs($late)->post(route('star-parties.answer', $party), ['status' => 'going'])->assertSessionHas('success');
    expect($party->fresh()->goingCount())->toBe(2);

    $this->actingAs($late)->post(route('star-parties.answer', $party), ['status' => 'organiser'])->assertSessionHas('error');
});

it('closes the answers after the deadline and for a private starparty one was not invited to', function () {
    $organiser = User::factory()->create();
    $observer  = User::factory()->create();
    $party     = starParty($organiser, ['registration_deadline' => today()->subDay()->toDateString(), 'start_date' => today()->addDay()->toDateString(), 'end_date' => today()->addDay()->toDateString()]);
    $private   = starParty($organiser, ['visibility' => StarParty::PRIVATE]);

    $this->actingAs($observer)->post(route('star-parties.answer', $party), ['status' => 'going'])->assertSessionHas('error');
    $this->actingAs($observer)->post(route('star-parties.answer', $private), ['status' => 'going'])->assertNotFound();
    expect(StarPartyParticipant::where('user_id', $observer->id)->exists())->toBeFalse();
});

it('invites an e-mail address, whose link makes the observer who opens it a participant', function () {
    Mail::fake();
    $organiser = User::factory()->create();
    $party     = starParty($organiser, ['visibility' => StarParty::PRIVATE, 'name' => 'Secret night']);

    $this->actingAs($organiser)->post(route('star-parties.invite', $party), ['emails' => "friend@example.org, not-an-address"])
        ->assertSessionHasErrors('emails');
    $this->actingAs($organiser)->post(route('star-parties.invite', $party), ['emails' => "friend@example.org\nFriend@example.org"])
        ->assertSessionHas('success');

    $invitation = StarPartyInvitation::sole();
    expect($invitation->email)->toBe('friend@example.org');
    Mail::assertQueued(StarPartyInvitationMail::class, fn ($mail) => $mail->hasTo('friend@example.org'));
    auth()->logout();

    // A guest with the link sees the private starparty
    $this->get(route('star-parties.invitation', $invitation->token))->assertRedirect(route('star-parties.show', $party));
    $this->get(route('star-parties.show', $party))->assertOk()->assertSee('Secret night');

    // Logged in, the link makes them an invited participant who can answer
    $friend = User::factory()->create();
    $this->actingAs($friend)->get(route('star-parties.show', $party))->assertOk();
    expect($party->fresh()->statusOf($friend))->toBe(StarPartyParticipant::INVITED)
        ->and($invitation->fresh()->accepted_by_user_id)->toBe($friend->id);
    $this->actingAs($friend)->post(route('star-parties.answer', $party), ['status' => 'going'])->assertSessionHas('success');

    // A forwarded link does not make somebody else a participant
    $other = User::factory()->create();
    $this->actingAs($other)->get(route('star-parties.invitation', $invitation->token));
    expect($party->fresh()->statusOf($other))->toBeNull();
});

it('tells the participants when the dates change and when the starparty is cancelled', function () {
    $organiser = User::factory()->create();
    $observer  = User::factory()->create();
    $declined  = User::factory()->create();
    $party     = starParty($organiser);
    StarPartyParticipant::create(['star_party_id' => $party->id, 'user_id' => $observer->id, 'status' => StarPartyParticipant::GOING]);
    StarPartyParticipant::create(['star_party_id' => $party->id, 'user_id' => $declined->id, 'status' => StarPartyParticipant::DECLINED]);
    $party->forceFill(['reminder_sent_at' => now()])->save();

    $this->actingAs($organiser)->put(route('star-parties.update', $party), [
        'name'       => $party->name, 'language' => 'en', 'visibility' => 'public',
        'start_date' => today()->addDays(12)->toDateString(), 'end_date' => today()->addDays(13)->toDateString(),
    ])->assertRedirect(route('star-parties.show', $party));

    expect(Message::where('receiver', $observer->username)->value('subject'))->toBe('The starparty Autumn starparty has changed')
        ->and(Message::where('receiver', $declined->username)->exists())->toBeFalse()
        ->and($party->fresh()->reminder_sent_at)->toBeNull()
        ->and($party->fresh()->location_name)->toBe('Ardennes field');

    $this->actingAs($organiser)->post(route('star-parties.cancel', $party))->assertSessionHas('success');
    expect($party->fresh()->isCancelled())->toBeTrue()
        ->and(Message::where('receiver', $observer->username)->where('subject', 'The starparty Autumn starparty is cancelled')->exists())->toBeTrue();

    // Others cannot edit, cancel or delete it
    $this->actingAs($observer)->get(route('star-parties.edit', $party))->assertForbidden();
    $this->actingAs($observer)->post(route('star-parties.cancel', $party))->assertForbidden();
    $this->actingAs($observer)->delete(route('star-parties.destroy', $party))->assertForbidden();

    $this->actingAs($organiser)->delete(route('star-parties.destroy', $party))->assertRedirect(route('star-parties.index'));
    expect(StarParty::count())->toBe(0)->and(StarPartyParticipant::count())->toBe(0);
});

it('lets the organiser take an observer off the starparty', function () {
    $organiser   = User::factory()->create();
    $observer    = User::factory()->create();
    $party       = starParty($organiser);
    $participant = StarPartyParticipant::create(['star_party_id' => $party->id, 'user_id' => $observer->id, 'status' => StarPartyParticipant::GOING]);
    $own         = $party->participants()->where('user_id', $organiser->id)->firstOrFail();

    $this->actingAs($observer)->delete(route('star-parties.participants.destroy', [$party, $participant]))->assertForbidden();
    $this->actingAs($organiser)->delete(route('star-parties.participants.destroy', [$party, $own]))->assertForbidden();
    $this->actingAs($organiser)->delete(route('star-parties.participants.destroy', [$party, $participant]))->assertSessionHas('success');
    expect($party->fresh()->statusOf($observer))->toBeNull();
});

it('exports the starparty for a calendar', function () {
    $organiser = User::factory()->create();
    $party     = starParty($organiser, [
        'name'       => 'Nights, stars; and more',
        'start_date' => '2026-11-13', 'end_date' => '2026-11-14',
    ]);

    $ics = $this->get(route('star-parties.calendar', $party))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/calendar; charset=utf-8')
        ->getContent();

    expect($ics)->toContain("BEGIN:VEVENT\r\n")
        ->toContain('DTSTART;VALUE=DATE:20261113')
        ->toContain('DTEND;VALUE=DATE:20261116')
        ->toContain('SUMMARY:Nights\, stars\; and more')
        ->toContain('GEO:50.1;5.5')
        ->toContain('STATUS:CONFIRMED');

    foreach (explode("\r\n", StarPartyCalendar::ics($party)) as $line) {
        expect(strlen($line))->toBeLessThanOrEqual(75);
    }
});

it('reminds the observers who come a few days before the first night, once', function () {
    $organiser = User::factory()->create();
    $going     = User::factory()->create();
    $invited   = User::factory()->create();
    $party     = starParty($organiser, ['start_date' => today()->addDays(2)->toDateString(), 'end_date' => today()->addDays(3)->toDateString()]);
    starParty($organiser, ['name' => 'Later', 'start_date' => today()->addDays(30)->toDateString(), 'end_date' => today()->addDays(30)->toDateString()]);
    StarPartyParticipant::create(['star_party_id' => $party->id, 'user_id' => $going->id, 'status' => StarPartyParticipant::GOING]);
    StarPartyParticipant::create(['star_party_id' => $party->id, 'user_id' => $invited->id, 'status' => StarPartyParticipant::INVITED]);

    $this->artisan('star-parties:send-reminders')->assertSuccessful();
    expect(Message::count())->toBe(0);

    $this->artisan('star-parties:send-reminders --apply')->assertSuccessful();
    expect(Message::where('receiver', $going->username)->value('subject'))->toStartWith('Reminder: the starparty Autumn starparty')
        ->and(Message::where('receiver', $organiser->username)->exists())->toBeTrue()
        ->and(Message::where('receiver', $invited->username)->exists())->toBeFalse()
        ->and($party->fresh()->reminder_sent_at)->not->toBeNull();

    $this->artisan('star-parties:send-reminders --apply');
    expect(Message::where('receiver', $going->username)->count())->toBe(1);
});

it('gives the dark hours and the Moon of each night at the site', function () {
    $organiser = User::factory()->create();
    // 2026-11-09 is two days before new Moon; in Belgium it is dark for hours.
    $party  = starParty($organiser, ['start_date' => '2026-11-09', 'end_date' => '2026-11-10']);
    $nights = StarPartyNights::for($party);

    expect($nights)->toHaveCount(2)
        ->and($nights[0]['night'])->toBe('2026-11-09')
        ->and($nights[0]['dark']['twilight'])->toBe('astronomical')
        ->and($nights[0]['dark']['from'])->toStartWith('18:')
        ->and($nights[0]['moonless_minutes'])->toBeGreaterThan(9 * 60);

    // Above the polar circle it does not get dark at midsummer
    $north = starParty($organiser, ['name' => 'Midnight sun', 'latitude' => 69.6, 'longitude' => 18.9, 'location_name' => 'Tromsø',
        'timezone'                         => 'Europe/Oslo', 'start_date' => '2026-06-21', 'end_date' => '2026-06-21']);
    expect(StarPartyNights::for($north)[0]['dark'])->toBeNull();
});

it('finds the sessions and observations at the site in the nights of the starparty', function () {
    $organiser = User::factory()->create();
    $observer  = User::factory()->create(['name' => 'Night owl']);
    $party     = starParty($organiser, ['start_date' => '2026-09-11', 'end_date' => '2026-09-12']);
    // The observer's own location 2 km from the site, and one far away
    $near = starPartyLocation($observer, ['name' => 'My spot at the party', 'latitude' => 50.115, 'longitude' => 5.51]);
    $far  = starPartyLocation($observer, ['name' => 'Home', 'latitude' => 51.0, 'longitude' => 4.4]);

    $observation = fn (Location $location, string $date, ?string $time, string $object) => Observation::create([
        'observer_username' => $observer->username, 'observer_user_id' => $observer->id, 'location_id' => $location->id,
        'observed_on'       => $date, 'observed_time' => $time, 'target_type' => 'deepsky', 'target_table' => 'objects',
        'target_key'        => $object, 'target_name' => $object, 'description' => 'Seen',
    ]);
    $observation($near, '2026-09-11', '21:30:00', 'M 31');      // the first night
    $observation($near, '2026-09-13', '02:00:00', 'M 33');      // the second night, after midnight
    $observation($near, '2026-09-11', '08:00:00', 'M 1');       // the morning before
    $observation($near, '2026-09-13', '11:00:00', 'M 2');       // the morning after the last night
    $observation($far, '2026-09-12', '22:00:00', 'M 13');       // at home

    $session = fn (Location $location, string $begin, string $name) => ObservationSession::create([
        'name'    => $name, 'observerid' => $observer->username, 'begindate' => $begin,
        'enddate' => Carbon::parse($begin)->addHours(5)->toDateTimeString(), 'locationid' => $location->id, 'active' => 1,
    ]);
    $atParty = $session($near, '2026-09-12 21:00:00', 'Second night at the party');
    $session($far, '2026-09-12 21:00:00', 'At home');
    $session($near, '2026-09-20 21:00:00', 'A week later');

    // Only the observers who come count, by default: a neighbour who does not come is left out
    expect((new StarPartyActivity($party))->observations())->toHaveCount(0)
        ->and(StarParty::atSiteOn($near, '2026-09-12', $observer->username))->toHaveCount(0);
    StarPartyParticipant::create(['star_party_id' => $party->id, 'user_id' => $observer->id, 'status' => StarPartyParticipant::MAYBE]);

    $activity = new StarPartyActivity($party);
    expect($activity->observations()->pluck('target_name')->sort()->values()->all())->toBe(['M 31', 'M 33'])
        ->and($activity->sessions()->pluck('name')->all())->toBe(['Second night at the party'])
        ->and((int) $activity->observers()->first()->total)->toBe(2);

    $this->travelTo(Carbon::parse('2026-09-15 12:00'));
    $this->get(route('star-parties.show', $party))->assertOk()
        ->assertSee('Observed at the starparty')
        ->assertSee('Second night at the party')
        ->assertSee('Night owl');

    // The session page links to the starparty
    $this->get(route('session.show', [$observer->slug, $atParty->slug]))->assertOk()
        ->assertSee(route('star-parties.show', $party), false);

    // The spot is about 1.8 km from the site: outside a distance of 1 km
    $party->forceFill(['activity_radius_km' => 1])->save();
    expect((new StarPartyActivity($party->fresh()))->observations())->toHaveCount(0)
        ->and(StarParty::atSiteOn($near, '2026-09-12', $observer->username))->toHaveCount(0);

    // Everybody's, when the organiser chooses so
    $party->participants()->where('user_id', $observer->id)->delete();
    $party->forceFill(['activity_radius_km' => 2, 'activity_participants_only' => false])->save();
    expect((new StarPartyActivity($party->fresh()))->observations())->toHaveCount(2);
});

it('ranks the objects by the number of observers who saw them', function () {
    $organiser = User::factory()->create();
    $party     = starParty($organiser, ['start_date' => '2026-09-11', 'end_date' => '2026-09-12']);
    $observers = User::factory()->count(3)->create();
    foreach ($observers as $observer) {
        StarPartyParticipant::create(['star_party_id' => $party->id, 'user_id' => $observer->id, 'status' => StarPartyParticipant::GOING]);
    }

    $observation = function (User $observer, string $object, array $attributes = []) {
        $location = Location::where('user_id', $observer->id)->first() ?? starPartyLocation($observer, ['latitude' => 50.101, 'longitude' => 5.501]);

        return Observation::create($attributes + [
            'observer_username' => $observer->username, 'observer_user_id' => $observer->id, 'location_id' => $location->id,
            'observed_on'       => '2026-09-11', 'observed_time' => '21:30:00', 'target_type' => 'deepsky', 'target_table' => 'objects',
            'target_key'        => $object, 'target_name' => $object, 'description' => 'Seen',
        ]);
    };
    [$a, $b, $c] = $observers->all();
    // M 13 by one observer twice, M 31 by all three (one with a drawing), M 57 by two
    $observation($a, 'M 13');
    $observation($a, 'M 13', ['observed_time' => '22:30:00']);
    $observation($a, 'M 31', ['has_drawing' => true]);
    $observation($b, 'M 31');
    $observation($c, 'M 31');
    $observation($b, 'M 57');
    $observation($c, 'M 57');
    // Not seen does not count
    $notSeen = $observation($a, 'M 57', ['observed_time' => '23:00:00']);
    DB::table('deepsky_observation_details')->insert(['observation_id' => $notSeen->id, 'visibility' => Observation::VISIBILITY_NOT_SEEN]);

    $ranking = (new StarPartyActivity($party))->objectsSeenTogether();
    expect($ranking->map(fn ($row) => [$row->observation->target_name, $row->observers, $row->observations, $row->drawings])->all())
        ->toBe([['M 31', 3, 3, 1], ['M 57', 2, 2, 0], ['M 13', 1, 2, 0]]);

    $this->travelTo(Carbon::parse('2026-09-15 12:00'));
    $this->get(route('star-parties.show', $party))->assertOk()
        ->assertSeeInOrder(['Objects seen by most participants', 'M 31', 'M 57', 'M 13']);
});

it('saves the distance and the observers whose observations count', function () {
    $organiser = User::factory()->create();
    $location  = starPartyLocation($organiser);

    $this->actingAs($organiser)->post(route('star-parties.store'), starPartyForm($location, ['activity_radius_km' => 3]))
        ->assertSessionHasErrors('activity_radius_km');
    $this->actingAs($organiser)->post(route('star-parties.store'), starPartyForm($location, ['activity_radius_km' => 2, 'activity_participants_only' => '0']));

    $party = StarParty::sole();
    expect($party->radiusKm())->toBe(2.0)->and($party->activity_participants_only)->toBeFalse();
    $this->actingAs($organiser)->get(route('star-parties.edit', $party))->assertOk()
        ->assertSee('<option value="2" selected', false);
});

it('gives each night of the starparty its own deep-sky objects', function () {
    DB::table('deepskytypes')->insertOrIgnore([['code' => 'GALXY', 'name' => 'Galaxy'], ['code' => 'OPNCL', 'name' => 'Open cluster'], ['code' => 'GLOCL', 'name' => 'Globular cluster']]);
    $types = ['GALXY', 'OPNCL', 'GLOCL'];
    for ($i = 0; $i < 60; $i++) {
        $name = "Object {$i}";
        DB::table('objects')->insert([
            'name'     => $name, 'slug' => 'object-'.$i, 'type' => $types[$i % 3], 'con' => 'AND', 'ra' => fmod($i * 0.4, 24.0), 'decl' => 35.0,
            'mag'      => 8.0, 'subr' => 13.0, 'pa' => 0, 'urano' => 0, 'urano_new' => 0, 'sky' => 0, 'millenium' => '', 'diam1' => 300.0,
            'diam2'    => 200.0, 'datasource' => null, 'taki' => '', 'SBObj' => 0.0, 'description' => '', 'psa' => '', 'torresB' => '',
            'torresBC' => '', 'torresC' => '', 'milleniumbase' => '0', 'DSLDL' => '0', 'DSLDP' => '0', 'DSLLL' => '0', 'DSLLP' => '0',
            'DSLOL'    => '0', 'DSLOP' => '0', 'DeepskyHunter' => '0', 'Interstellarum' => '0', 'timestamp' => now(),
        ]);
        DB::table('object_observation_stats')->insert(['object_name' => $name, 'observations' => 1000 - $i]);
    }
    $party = starParty(User::factory()->create(), ['start_date' => '2026-11-09', 'end_date' => '2026-11-11']);

    $deepsky = fn (string $night) => collect(StarPartyBest::for($party, null, $night)['objects'])->where('kind', 'deepsky')->pluck('name');
    $first   = $deepsky('2026-11-09');
    $second  = $deepsky('2026-11-10');
    $third   = $deepsky('2026-11-11');

    expect($first)->not->toBeEmpty()->and($second)->not->toBeEmpty()->and($third)->not->toBeEmpty()
        ->and($first->intersect($second))->toBeEmpty()
        ->and($third->intersect($first->merge($second)))->toBeEmpty();
});

it('shows the coming starparties on the home page and in the feed', function () {
    $organiser = User::factory()->create();
    starParty($organiser, ['name' => 'Soon under the stars']);
    starParty($organiser, ['name' => 'Members only', 'visibility' => StarParty::PRIVATE]);
    starParty($organiser, ['name' => 'Far ahead', 'start_date' => today()->addDays(200)->toDateString(), 'end_date' => today()->addDays(200)->toDateString()]);

    $this->get('/')->assertOk()->assertSee('Soon under the stars')->assertDontSee('Members only')->assertDontSee('Far ahead');
    $this->actingAs($organiser)->get('/')->assertOk()->assertSee('Members only');

    $this->get(route('feed.star-parties'))->assertOk()
        ->assertSee('Soon under the stars')
        ->assertSee('Far ahead')
        ->assertDontSee('Members only');
});

it('gives the observer\'s starparties in the token API', function () {
    $organiser = User::factory()->create();
    $observer  = User::factory()->create();
    $party     = starParty($organiser);
    starParty($organiser, ['name' => 'Not for me']);
    StarPartyParticipant::create(['star_party_id' => $party->id, 'user_id' => $observer->id, 'status' => StarPartyParticipant::MAYBE]);

    Sanctum::actingAs($observer, ['read']);
    $this->getJson(route('api.me.star-parties'))->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.name', 'Autumn starparty')
        ->assertJsonPath('0.status', 'maybe')
        ->assertJsonPath('0.organiser', $organiser->username)
        ->assertJsonPath('0.site.timezone', 'Europe/Brussels');
});

it('keeps an account that organised or answered a starparty, and deletes the starparties with the account', function () {
    $organiser = User::factory()->create(['created_at' => now()->subYears(3)]);
    $answered  = User::factory()->create(['created_at' => now()->subYears(3)]);
    $invited   = User::factory()->create(['created_at' => now()->subYears(3)]);
    // Without a location, which keeps an account too
    $party = new StarParty([
        'user_id'       => $organiser->id, 'name' => 'Old party', 'language' => 'en', 'visibility' => StarParty::PUBLIC,
        'start_date'    => today()->addDay()->toDateString(), 'end_date' => today()->addDay()->toDateString(),
        'location_name' => 'Field', 'latitude' => 50.0, 'longitude' => 5.0, 'timezone' => 'UTC',
    ]);
    $party->save();
    StarPartyParticipant::create(['star_party_id' => $party->id, 'user_id' => $answered->id, 'status' => StarPartyParticipant::DECLINED]);
    StarPartyParticipant::create(['star_party_id' => $party->id, 'user_id' => $invited->id, 'status' => StarPartyParticipant::INVITED]);

    $inactive = PruneInactiveUsers::inactive()->pluck('id')->all();
    expect($inactive)->not->toContain($organiser->id)
        ->not->toContain($answered->id)
        ->toContain($invited->id);

    app(DeleteUser::class)->delete($organiser);
    expect(StarParty::count())->toBe(0)->and(StarPartyParticipant::count())->toBe(0);
});

it('shows what to observe at the site in a night of the starparty and makes it an observing list', function () {
    $organiser = User::factory()->create();
    $party     = starParty($organiser);
    $second    = $party->nights()[1];

    $this->get(route('star-parties.show', $party).'?night='.$second)->assertOk()
        ->assertSee('What to observe')
        ->assertSee('value="'.$second.'" selected', false);

    // The planets of the night at the site, under the name of the starparty
    $organiser->forceFill(['current_team_id' => \App\Models\Team::OBSERVERS])->save();
    $this->actingAs($organiser)->post(route('star-parties.observing-list', $party), ['night' => $second])
        ->assertSessionHas('success');
    $list = \App\Models\ObservingList::where('owner_user_id', $organiser->id)->sole();
    expect($list->name)->toStartWith('Autumn starparty - night of')
        ->and($list->items()->count())->toBeGreaterThan(0);
    auth()->logout();

    // Not after the starparty
    $past = starParty($organiser, ['name' => 'Long ago', 'start_date' => '2020-04-21', 'end_date' => '2020-04-22']);
    $this->get(route('star-parties.show', $past))->assertOk()->assertDontSee('What to observe');
});

it('pages the starparties, with all coming ones on the map, and filters them by country', function () {
    $organiser = User::factory()->create();
    $site      = ['location_name' => 'Field', 'latitude' => 50.0, 'longitude' => 5.0, 'timezone' => 'UTC'];
    for ($i = 1; $i <= 25; $i++) {
        starParty($organiser, $site + ['name' => sprintf('Coming %02d', $i), 'country' => $i <= 3 ? 'Netherlands' : 'Belgium',
            'start_date'                      => today()->addDays($i)->toDateString(), 'end_date' => today()->addDays($i)->toDateString()]);
    }
    for ($i = 1; $i <= 22; $i++) {
        starParty($organiser, $site + ['name' => sprintf('Old %02d', $i), 'country' => 'Belgium',
            'start_date'                      => today()->subDays(10 + $i)->toDateString(), 'end_date' => today()->subDays(10 + $i)->toDateString()]);
    }

    $markers = fn ($response) => json_decode(html_entity_decode($response->viewData('markers')->toJson()), true);
    $first   = $this->get(route('star-parties.index'))->assertOk();
    expect($first->viewData('upcoming')->count())->toBe(StarPartyController::PER_PAGE)
        ->and($first->viewData('upcoming')->total())->toBe(25)
        ->and($markers($first))->toHaveCount(25)
        ->and($first->viewData('past')->count())->toBe(StarPartyController::PER_PAGE)
        ->and($first->viewData('past')->first()->name)->toBe('Old 01');
    expect($first->viewData('upcoming')->last()->name)->toBe('Coming 20');
    $first->assertSee('upcoming=2', false)->assertSee('past=2', false);

    // The pages of the lists do not reset each other
    $second = $this->get(route('star-parties.index', ['upcoming' => 2, 'past' => 2]))->assertOk();
    expect($second->viewData('upcoming')->pluck('name')->all())->toBe(['Coming 21', 'Coming 22', 'Coming 23', 'Coming 24', 'Coming 25'])
        ->and($second->viewData('past')->pluck('name')->all())->toBe(['Old 21', 'Old 22']);
    $second->assertSee('upcoming=2&amp;past=1', false);

    // The country filter, with the countries of the coming starparties
    $dutch = $this->get(route('star-parties.index', ['country' => 'Netherlands']))->assertOk();
    expect($dutch->viewData('countries'))->toHaveKeys(['Belgium', 'Netherlands'])
        ->and($dutch->viewData('upcoming')->total())->toBe(3)
        ->and($markers($dutch))->toHaveCount(3)
        ->and($dutch->viewData('past')->total())->toBe(0);
    // An unknown country shows them all
    expect($this->get(route('star-parties.index', ['country' => 'Atlantis']))->viewData('upcoming')->total())->toBe(25);
});

it('shows the observer\'s own coming starparties, and all of them on request', function () {
    $organiser = User::factory()->create();
    $observer  = User::factory()->create();
    $coming    = starParty($organiser, ['name' => 'Next month']);
    $ended     = starParty($organiser, ['name' => 'Last year', 'start_date' => today()->subYear()->toDateString(), 'end_date' => today()->subYear()->toDateString()]);
    $declined  = starParty($organiser, ['name' => 'Not for me']);
    foreach ([[$coming, 'going'], [$ended, 'going'], [$declined, 'declined']] as [$party, $status]) {
        StarPartyParticipant::create(['star_party_id' => $party->id, 'user_id' => $observer->id, 'status' => $status]);
    }

    $names = fn ($response) => $response->viewData('mine')->pluck('name')->all();
    expect($names($this->actingAs($observer)->get(route('star-parties.index'))))->toBe(['Next month'])
        ->and($names($this->actingAs($observer)->get(route('star-parties.index', ['mine' => 'all']))))->toBe(['Next month', 'Last year']);

    // With only ended ones, the section stays, with the link to all of them
    $coming->participants()->where('user_id', $observer->id)->delete();
    $this->actingAs($observer)->get(route('star-parties.index'))->assertOk()
        ->assertSee('None of your starparties is still to come.')
        ->assertSee('mine=all', false);
});
