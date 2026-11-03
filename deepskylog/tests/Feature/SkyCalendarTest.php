<?php

use App\Models\Location;
use App\Models\NewsItem;
use App\Models\ObservingChallenge;
use App\Models\ObservingList;
use App\Models\StarParty;
use App\Models\StarPartyParticipant;
use App\Models\User;
use App\Services\SkyCalendar;
use Carbon\Carbon;
use Database\Factories\LocationFactory;

// The personal sky calendar: an iCalendar feed opened with a secret token,
// with the news events seen from the standard location, the starparties,
// the nights of planned lists and the challenges.

function skyCalendarObserver(): User
{
    $user     = User::factory()->create(['language' => 'en']);
    $location = LocationFactory::new()->create([
        'user_id'   => $user->id,
        'observer'  => $user->username,
        'name'      => 'Backyard',
        'latitude'  => 50.85,
        'longitude' => 4.35,
        'timezone'  => 'Europe/Brussels',
        'country'   => 'Belgium',
        'elevation' => 50,
    ]);
    $user->forceFill(['stdlocation' => $location->id])->save();

    return $user->fresh();
}

beforeEach(fn () => Carbon::setTestNow('2026-10-29 12:00:00'));
afterEach(fn () => Carbon::setTestNow());

it('serves the calendar only with the secret token', function () {
    $user  = skyCalendarObserver();
    $token = $user->calendarFeedToken();

    $this->get(route('sky-calendar.show', ['token' => 'wrong']))->assertNotFound();
    $ics = $this->get(route('sky-calendar.show', ['token' => $token]))
        ->assertOk()
        ->assertHeader('Content-Type', 'text/calendar; charset=utf-8')
        ->getContent();

    expect($ics)->toStartWith("BEGIN:VCALENDAR\r\n")
        ->and($ics)->toContain('X-WR-CALNAME:DeepskyLog sky calendar')
        ->and($ics)->toEndWith("END:VCALENDAR\r\n")
        ->and($user->fresh()->toArray())->not->toHaveKey('calendar_feed_token');
});

it('gives the calendar a new address', function () {
    $user = skyCalendarObserver();
    $old  = $user->calendarFeedToken();

    $this->actingAs($user)->post(route('sky-calendar.regenerate'))->assertRedirect();

    $new = $user->fresh()->calendar_feed_token;
    expect($new)->not->toBe($old);
    $this->get(route('sky-calendar.show', ['token' => $old]))->assertNotFound();
    $this->get(route('sky-calendar.show', ['token' => $new]))->assertOk();
});

it('shows the address on the profile page', function () {
    $user = skyCalendarObserver();

    $this->actingAs($user)->get(route('profile.show'))
        ->assertOk()
        ->assertSee(route('sky-calendar.show', ['token' => $user->fresh()->calendarFeedToken()]), false);
});

it('holds the news events, with the local times of those that are seen there', function () {
    $user = skyCalendarObserver();
    // A day event, in the location's time zone
    NewsItem::create(['kind' => 'opposition', 'event_key' => 'opposition:mars:2026-11-20', 'event_at' => '2026-11-20 23:30:00', 'published_at' => '2026-11-13 00:00:00', 'data' => ['bodies' => ['mars'], 'decl' => 20.0, 'magnitude' => -1.2]]);
    // Hidden, written by hand or no moment in the sky: left out
    NewsItem::create(['kind' => 'opposition', 'event_key' => 'opposition:saturn:2026-11-21', 'event_at' => '2026-11-21 00:00:00', 'published_at' => '2026-11-14 00:00:00', 'hidden' => true, 'data' => ['bodies' => ['saturn']]]);
    NewsItem::create(['kind' => NewsItem::MANUAL, 'title' => 'Hickson', 'body' => '<p>x</p>', 'language' => 'en', 'event_at' => '2026-11-02 00:00:00', 'published_at' => now()]);
    // Regulus covered by the Moon, seen from Brussels (NewsTest)
    Carbon::setTestNow('2026-03-20 12:00:00');
    NewsItem::create([
        'kind'         => 'lunar_occultation',
        'event_key'    => 'lunar_occultation:alp-leo:2026-03-29',
        'event_at'     => '2026-03-29 19:58:00',
        'published_at' => now()->subDay(),
        'data'         => [
            'target' => 'star', 'bodies' => ['moon'], 'separation' => 0.32, 'elongation' => 142.0, 'magnitudes' => [], 'moon_illumination' => 0.89,
            'star'   => ['key' => 'alp-leo', 'name' => 'Regulus', 'designation' => 'α Leo', 'slug' => null, 'ra' => 152.09296, 'decl' => 11.96721, 'mag' => 1.4],
        ],
    ]);

    $ics = SkyCalendar::ics($user);
    expect($ics)->toContain('SUMMARY:The Moon covers Regulus on 29 March 2026')
        ->and($ics)->toMatch('/DTSTART:20260329T18\d{4}Z/');

    Carbon::setTestNow('2026-10-29 12:00:00');
    SkyCalendar::forget($user);
    $ics = SkyCalendar::ics($user);

    expect($ics)->toContain("DTSTART;VALUE=DATE:20261121\r\nDTEND;VALUE=DATE:20261122")
        ->and($ics)->toContain('SUMMARY:Mars at opposition on 21 November 2026')
        ->and($ics)->not->toContain('Saturn')
        ->and($ics)->not->toContain('Hickson')
        ->and($ics)->not->toContain('Regulus');
});

it('holds the starparties the observer comes to, the planned lists and the challenges', function () {
    $user      = skyCalendarObserver();
    $organiser = User::factory()->create();
    $party     = new StarParty([
        'user_id'       => $organiser->id, 'name' => 'Autumn starparty', 'description' => '<p>Coats.</p>', 'language' => 'en',
        'visibility'    => StarParty::PUBLIC, 'start_date' => '2026-11-10', 'end_date' => '2026-11-11',
        'location_name' => 'Ardennes', 'latitude' => 50.1, 'longitude' => 5.5, 'timezone' => 'Europe/Brussels',
    ]);
    $party->save();
    $declined = $party->replicate()->fill(['name' => 'Winter starparty', 'slug' => null]);
    $declined->save();
    StarPartyParticipant::create(['star_party_id' => $party->id, 'user_id' => $user->id, 'status' => StarPartyParticipant::MAYBE]);
    StarPartyParticipant::create(['star_party_id' => $declined->id, 'user_id' => $user->id, 'status' => StarPartyParticipant::DECLINED]);

    $at = fn (string $moment) => Carbon::parse($moment, 'UTC')->timestamp;
    ObservingList::createWithObjects($user, 'Clusters tonight', ['M 31', 'M 42'], null, false, [
        'M 31' => ['at' => $at('2026-10-30 20:30'), 'from' => $at('2026-10-30 18:00'), 'until' => $at('2026-10-30 21:00')],
        'M 42' => ['at' => $at('2026-10-31 01:00')],
    ], ['night' => '2026-10-30', 'timezone' => 'Europe/Brussels', 'location_id' => $user->stdlocation]);
    ObservingList::createWithObjects($user, 'No times', ['M 13']);

    $joined = new ObservingChallenge(['name' => 'Hickson month', 'catalog' => 'HCG', 'source_label' => 'HCG', 'target_count' => 3, 'mode' => 'observed', 'starts_on' => '2026-11-01', 'ends_on' => '2026-11-30']);
    $joined->save();
    $joined->join($user);
    $open = new ObservingChallenge(['name' => 'Arp spring', 'catalog' => 'Arp', 'source_label' => 'Arp', 'target_count' => 3, 'mode' => 'observed', 'starts_on' => '2027-01-01', 'ends_on' => '2027-01-31']);
    $open->save();

    $ics = SkyCalendar::ics($user);

    expect($ics)->toContain('SUMMARY:Autumn starparty')
        ->and($ics)->not->toContain('Winter starparty')
        ->and($ics)->toContain("UID:list-")
        ->and($ics)->toContain("DTSTART:20261030T180000Z\r\nDTEND:20261031T010000Z\r\nSUMMARY:Observing list: Clusters tonight\r\nLOCATION:Backyard")
        ->and($ics)->not->toContain('No times')
        ->and($ics)->toContain('SUMMARY:Observing challenge starts: Hickson month')
        ->and($ics)->toContain('SUMMARY:Observing challenge ends: Hickson month')
        ->and($ics)->toContain('SUMMARY:Observing challenge starts: Arp spring')
        ->and($ics)->not->toContain('Observing challenge ends: Arp spring');
});

it('holds the nights forecast clear and dark at the standard location', function () {
    config(['services.open_meteo.enabled' => true]);
    Carbon::setTestNow('2026-11-08 12:00:00');
    $user  = skyCalendarObserver();
    $start = Carbon::parse('2026-11-08 00:00', 'UTC')->getTimestamp();
    $time  = array_map(fn (int $i) => $start + 3600 * $i, range(0, 7 * 24 - 1));
    // Clear only in the night of 9 November, around the new Moon
    $cover = array_map(fn (int $t) => $t > Carbon::parse('2026-11-09 12:00', 'UTC')->timestamp && $t < Carbon::parse('2026-11-10 08:00', 'UTC')->timestamp ? 0 : 100, $time);
    Http::fake(['api.open-meteo.com/*' => Http::response(['hourly' => [
        'time'                 => $time,
        'cloud_cover'          => $cover,
        'cloud_cover_low'      => $cover,
        'cloud_cover_mid'      => array_fill(0, count($time), 0),
        'cloud_cover_high'     => array_fill(0, count($time), 0),
        'relative_humidity_2m' => array_fill(0, count($time), 60),
        'wind_speed_10m'       => array_fill(0, count($time), 5),
    ]])]);
    App\Services\Weather\NightForecast::hourly(50.85, 4.35);

    $ics = SkyCalendar::ics($user);

    expect(substr_count($ics, 'SUMMARY:Clear and dark at Backyard'))->toBe(1)
        ->and($ics)->toContain('UID:forecast-'.$user->id.'-2026-11-09@')
        ->and($ics)->toMatch('/DTSTART:20261109T1[78]\d{4}Z\r\nDTEND:20261110T0[45]\d{4}Z/');
});
