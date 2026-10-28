<?php

use App\Models\Location;
use App\Models\Message;
use App\Models\Observation;
use App\Models\ObservationSession;
use App\Models\User;
use App\Services\SessionObservationReminders;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

function reminderLocation(User $owner): Location
{
    return Location::query()->create([
        'user_id'           => $owner->id,
        'name'              => 'Reminder Site',
        'longitude'         => 4.5,
        'latitude'          => 50.5,
        'timezone'          => 'UTC',
        'limitingMagnitude' => -999,
        'skyBackground'     => -999,
        'elevation'         => 120,
        'country'           => 'Belgium',
        'active'            => true,
        'observer'          => '',
        'observations'      => 0,
        'slug'              => 'reminder-site-' . $owner->id,
    ]);
}

function reminderSession(User $owner, string $name, int $daysAgo, array $attributes = []): ObservationSession
{
    return ObservationSession::query()->create(array_merge([
        'name'       => $name,
        'observerid' => $owner->username,
        'begindate'  => now()->subDays($daysAgo)->setTime(21, 0)->toDateTimeString(),
        'enddate'    => now()->subDays($daysAgo - 1)->setTime(2, 0)->toDateTimeString(),
        'locationid' => 0,
        'language'   => 'en',
        'active'     => 1,
    ], $attributes));
}

function reminderObservation(User $observer, ObservationSession $session): void
{
    $observation = Observation::query()->create([
        'observer_username' => $observer->username,
        'observer_user_id'  => $observer->id,
        'observed_on'       => $session->begindate->toDateString(),
        'observed_time'     => '2200',
        'language'          => 'en',
        'description'       => '',
        'target_type'       => 'deepsky',
        'target_table'      => 'objects',
        'target_key'        => 'M 31',
        'target_name'       => 'M 31',
        'has_drawing'       => false,
    ]);
    DB::table('sessionObservations')->insertOrIgnore(['sessionid' => $session->id, 'observationid' => $observation->id]);
}

it('reminds an observer once of their recent sessions without observations', function () {
    $user = User::factory()->create(['username' => 'forgetful', 'language' => 'en', 'sendMail' => false]);
    $other = User::factory()->create(['username' => 'companion']);

    $empty = reminderSession($user, 'Empty night', 3);
    $logged = reminderSession($user, 'Logged night', 4);
    reminderObservation($user, $logged);
    // Only observations of the session's own observer count.
    $companionOnly = reminderSession($user, 'Companion night', 5);
    reminderObservation($other, $companionOnly);
    // Too recent, too old, a draft.
    reminderSession($user, 'Last night', 1, ['enddate' => now()->subHours(6)->toDateTimeString()]);
    reminderSession($user, 'Old night', 40);
    reminderSession($user, 'Draft night', 3, ['active' => 0]);

    $this->artisan('sessions:send-observation-reminders')->assertSuccessful();
    expect(Message::where('receiver', 'forgetful')->count())->toBe(0);

    $this->artisan('sessions:send-observation-reminders --apply')->assertSuccessful();

    $messages = Message::where('receiver', 'forgetful')->get();
    expect($messages)->toHaveCount(1)
        ->and($messages->first()->message)->toContain('Empty night')
        ->and($messages->first()->message)->toContain('Companion night')
        ->and($messages->first()->message)->toContain('session=' . $empty->id)
        ->and($messages->first()->message)->not->toContain('Logged night')
        ->and($messages->first()->message)->not->toContain('Last night')
        ->and($messages->first()->message)->not->toContain('Old night')
        ->and($messages->first()->message)->not->toContain('Draft night')
        ->and($empty->fresh()->observation_reminder_sent_at)->not->toBeNull();

    // A second run sends nothing.
    $this->artisan('sessions:send-observation-reminders --apply')->assertSuccessful();
    expect(Message::where('receiver', 'forgetful')->count())->toBe(1);
});

it('does not remind observers who turned the reminder off', function () {
    $user = User::factory()->create(['username' => 'quiet', 'session_reminder' => false]);
    reminderSession($user, 'Empty night', 3);

    $this->artisan('sessions:send-observation-reminders --apply')->assertSuccessful();

    expect(Message::where('receiver', 'quiet')->count())->toBe(0);
});

it('lists the sessions without observations of the observer', function () {
    $user = User::factory()->create(['username' => 'lister']);
    reminderSession($user, 'Empty night', 3);
    reminderSession($user, 'Tonight', 0, ['enddate' => now()->addHours(4)->toDateTimeString()]);
    $logged = reminderSession($user, 'Logged night', 4);
    reminderObservation($user, $logged);

    expect(SessionObservationReminders::forUser($user)->pluck('name')->all())->toBe(['Empty night'])
        ->and(SessionObservationReminders::lacksObservations($logged))->toBeFalse();
});

it('fills in the date and location of the session on the observation form', function () {
    $user = User::factory()->create(['username' => 'prefill']);
    $location = reminderLocation($user);
    $session = reminderSession($user, 'Empty night', 3, ['locationid' => $location->id]);

    // The object picker passes the session on to the form.
    $this->actingAs($user)
        ->get(SessionObservationReminders::addObservationUrl($session))
        ->assertOk()
        ->assertViewIs('observations.add_select');
    Livewire::actingAs($user)
        ->test(\App\Http\Livewire\QuickSearch::class, ['mode' => 'add_observation', 'session' => $session->id])
        ->call('selectSuggestion', 'm-31')
        ->assertRedirect(route('observation.create', ['slug' => 'm-31', 'session' => $session->id]));

    $this->actingAs($user)
        ->get(route('observation.create', ['slug' => 'M 31', 'session' => $session->id]))
        ->assertOk()
        ->assertViewHas('prefill', fn (array $prefill) => $prefill['observed_on'] === $session->begindate->toDateString()
            && (int) $prefill['location_id'] === $location->id);

    // Not with another observer's session.
    $stranger = User::factory()->create(['username' => 'stranger']);
    $this->actingAs($stranger)
        ->get(route('observation.create', ['slug' => 'M 31', 'session' => $session->id]))
        ->assertOk()
        ->assertViewHas('prefill', fn (array $prefill) => ! isset($prefill['observed_on']));
});

it('shows the sessions without observations to their observer', function () {
    $user = User::factory()->create(['username' => 'homeowner']);
    $session = reminderSession($user, 'Empty night', 3);

    $this->actingAs($user)->get('/')
        ->assertOk()
        ->assertSee('Sessions without observations')
        ->assertSee('Empty night');

    $this->actingAs($user)->get(route('session.show', [$user->slug, $session->slug]))
        ->assertOk()
        ->assertSee('You have not added any observations to this session yet.')
        ->assertSee('session=' . $session->id, false);
});
