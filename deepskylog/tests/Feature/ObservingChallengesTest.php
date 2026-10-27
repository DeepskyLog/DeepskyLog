<?php

use App\Livewire\ObservingGoals;
use App\Models\NewsItem;
use App\Models\Observation;
use App\Models\ObservingChallenge;
use App\Models\ObservingGoal;
use App\Models\ObservingList;
use App\Models\User;
use App\Services\ChallengeStandings;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

// The observing challenges: set by an administrator, announced in the news,
// joined by observers as an observing goal, with a leaderboard and the
// objects the participants saw together.

function challengeCatalog(string $catalog, int $count): void
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

function challengeObservation(User $observer, string $object, string $date, array $attributes = []): Observation
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

function hicksonMonth(array $attributes = []): ObservingChallenge
{
    $challenge = new ObservingChallenge(array_merge([
        'name' => 'Hickson month',
        'catalog' => 'HCG',
        'source_label' => 'HCG',
        'target_count' => 3,
        'mode' => 'observed',
        'starts_on' => '2026-10-01',
        'ends_on' => '2026-11-30',
    ], $attributes));
    $challenge->saveAndAnnounce();

    return $challenge;
}

beforeEach(function () {
    Carbon::setTestNow('2026-10-27 12:00:00');
    challengeCatalog('HCG', 5);
});

afterEach(fn () => Carbon::setTestNow());

it('lets the administrators and database experts set a challenge, announced in the news', function (string $team, int $status) {
    $user = $this->createUserAndAssignToTeam($team);

    $this->actingAs($user)->get(route('admin.challenges.create'))->assertStatus($status);
    $response = $this->actingAs($user)->post(route('admin.challenges.store'), [
        'name' => 'Arp galaxies this spring',
        'description' => '<p>Peculiar galaxies!</p><script>alert(1)</script>',
        'source' => 'catalog:hcg',
        'target' => '',
        'mode' => 'observed',
        'starts_on' => '2027-03-01',
        'ends_on' => '2027-05-31',
    ]);

    if ($status !== 200) {
        $response->assertForbidden();
        expect(ObservingChallenge::count())->toBe(0);

        return;
    }

    $response->assertRedirect(route('admin.challenges'));
    $challenge = ObservingChallenge::sole();
    expect($challenge->slug)->toBe('arp-galaxies-this-spring')
        ->and($challenge->target_count)->toBeNull()
        ->and($challenge->description)->not->toContain('script')
        ->and($challenge->task())->toBe('See all objects of HCG');

    $news = NewsItem::where('event_key', 'challenge:' . $challenge->id)->sole();
    expect($news->kind)->toBe('challenge')
        ->and($news->published_at->toDateString())->toBe('2027-02-22')
        ->and($news->expires_at->toDateString())->toBe('2027-06-01');

    // Announced at once when the week before the start has passed
    $this->actingAs($user)->put(route('admin.challenges.update', $challenge), [
        'name' => 'Arp galaxies this spring',
        'description' => $challenge->description,
        'source' => 'catalog:hcg',
        'mode' => 'observed',
        'starts_on' => '2026-10-28',
        'ends_on' => '2026-12-31',
    ])->assertRedirect();
    expect($news->fresh()->published_at->toDateTimeString())->toBe('2026-10-27 12:00:00');

    $this->get(route('news.show', $news))->assertOk()
        ->assertSee('Observing challenge: Arp galaxies this spring')
        ->assertSee('Peculiar galaxies!')
        ->assertSee(route('challenges.show', $challenge));
})->with([['Administrators', 200], ['Database Experts', 200], ['Observers', 403]]);

it('refuses a private observing list, an unknown catalog and a challenge of more than a year', function () {
    $admin = $this->createUserAndAssignToTeam('Administrators');
    $list  = ObservingList::query()->create(['owner_user_id' => $admin->id, 'name' => 'My secret list', 'public' => false]);

    $this->actingAs($admin)->post(route('admin.challenges.store'), [
        'name' => 'Secret', 'source' => 'list:' . $list->id, 'mode' => 'observed', 'starts_on' => '2026-11-01', 'ends_on' => '2026-11-30',
    ])->assertSessionHasErrors('source');
    $this->actingAs($admin)->post(route('admin.challenges.store'), [
        'name' => 'Unknown', 'source' => 'catalog:nothing', 'mode' => 'observed', 'starts_on' => '2026-11-01', 'ends_on' => '2026-11-30',
    ])->assertSessionHasErrors('source');
    $this->actingAs($admin)->post(route('admin.challenges.store'), [
        'name' => 'Long', 'source' => 'catalog:hcg', 'mode' => 'observed', 'starts_on' => '2026-11-01', 'ends_on' => '2028-11-30',
    ])->assertSessionHasErrors('ends_on');

    $list->update(['public' => true]);
    $this->actingAs($admin)->post(route('admin.challenges.store'), [
        'name' => 'Public list', 'source' => 'list:' . $list->id, 'mode' => 'observed', 'starts_on' => '2026-11-01', 'ends_on' => '2026-11-30',
    ])->assertSessionHasNoErrors();
    expect(ObservingChallenge::sole()->observing_list_id)->toBe($list->id);
});

it('makes a joined observer a participant with an observing goal that follows the challenge', function () {
    $challenge = hicksonMonth();
    $observer  = User::factory()->create(['name' => 'Joiner', 'username' => 'joiner', 'slug' => 'joiner']);

    $this->actingAs($observer)->post(route('challenges.join', $challenge))->assertRedirect(route('challenges.show', $challenge));
    $this->actingAs($observer)->post(route('challenges.join', $challenge));
    $goal = ObservingGoal::sole();
    expect($goal->user_id)->toBe($observer->id)
        ->and($goal->challenge_id)->toBe($challenge->id)
        ->and($goal->title())->toBe('Hickson month')
        ->and($goal->public)->toBeTrue();

    $challenge->fill(['target_count' => 4, 'ends_on' => '2026-12-31'])->saveAndAnnounce();
    expect($goal->fresh()->target_count)->toBe(4)
        ->and($goal->fresh()->ends_on->toDateString())->toBe('2026-12-31');

    // On the observer page as a goal of the challenge, which can't be edited
    $this->actingAs($observer)->get(route('observer.show', ['observer' => 'joiner']))
        ->assertOk()
        ->assertSee(route('challenges.show', $challenge));
    Livewire::actingAs($observer)->test(ObservingGoals::class, ['observer' => $observer])->call('edit', $goal->id)->assertNotFound();

    // Removing the goal is leaving the challenge
    Livewire::actingAs($observer)->test(ObservingGoals::class, ['observer' => $observer])->call('delete', $goal->id);
    expect($challenge->hasJoined($observer))->toBeFalse();

    $this->actingAs($observer)->post(route('challenges.join', $challenge));
    $this->actingAs($observer)->delete(route('challenges.leave', $challenge))->assertRedirect();
    expect(ObservingGoal::count())->toBe(0);
});

it('cannot be joined once it ended or while it is hidden', function () {
    $observer = User::factory()->create();

    $ended = hicksonMonth(['name' => 'Last year', 'starts_on' => '2025-10-01', 'ends_on' => '2025-10-31']);
    $this->actingAs($observer)->post(route('challenges.join', $ended))->assertNotFound();

    $hidden = hicksonMonth(['name' => 'Not yet', 'hidden' => true]);
    $this->actingAs($observer)->post(route('challenges.join', $hidden))->assertNotFound();
    expect($hidden->newsItem()->hidden)->toBeTrue();
    $this->get(route('challenges.show', $hidden))->assertNotFound();
    $this->get(route('challenges.index'))->assertOk()->assertSee('Last year')->assertDontSee('Not yet');
});

it('ranks the participants and counts what they saw together', function () {
    $challenge = hicksonMonth();
    $first  = User::factory()->create(['name' => 'Fast Observer', 'username' => 'fast', 'slug' => 'fast']);
    $second = User::factory()->create(['name' => 'Slow Observer', 'username' => 'slow', 'slug' => 'slow']);
    $third  = User::factory()->create(['name' => 'Later Observer', 'username' => 'later', 'slug' => 'later']);
    $guest  = User::factory()->create(['name' => 'Not Joined', 'username' => 'notjoined', 'slug' => 'not-joined']);
    foreach ([$first, $second, $third] as $observer) {
        $challenge->join($observer);
    }

    challengeObservation($first, 'HCG 1', '2026-10-02');
    challengeObservation($first, 'HCG 2', '2026-10-03');
    challengeObservation($first, 'HCG 3', '2026-10-04');
    challengeObservation($second, 'HCG 1', '2026-10-05');
    challengeObservation($second, 'HCG 4', '2026-10-06');
    challengeObservation($third, 'HCG 1', '2026-10-05');
    challengeObservation($third, 'HCG 5', '2026-10-20');
    // Before the challenge, not seen, or no participant: none of these count
    challengeObservation($second, 'HCG 2', '2026-09-30');
    $notSeen = challengeObservation($third, 'HCG 2', '2026-10-10');
    DB::table('deepsky_observation_details')->insert(['observation_id' => $notSeen->id, 'visibility' => Observation::VISIBILITY_NOT_SEEN]);
    challengeObservation($guest, 'HCG 2', '2026-10-10');

    $standings = ChallengeStandings::compute($challenge);
    expect(collect($standings['rows'])->map(fn ($row) => [$row['rank'], $row['name'], $row['count'], $row['reached_on']])->all())->toBe([
        [1, 'Fast Observer', 3, '2026-10-04'],
        [2, 'Slow Observer', 2, null],
        [3, 'Later Observer', 2, null],
    ])
        ->and($standings['total'])->toBe(5)
        ->and($standings['seen'])->toBe(5)
        ->and(collect($standings['objects'])->pluck('seen_by', 'object_name')->all())
        ->toBe(['HCG 1' => 3, 'HCG 2' => 1, 'HCG 3' => 1, 'HCG 4' => 1, 'HCG 5' => 1]);

    $this->actingAs($second)->get(route('challenges.show', $challenge))
        ->assertOk()
        ->assertSeeInOrder(['Fast Observer', 'Slow Observer', 'Later Observer'])
        ->assertSee('Leave the challenge')
        ->assertSee('2 of 3 objects');
    $this->actingAs($guest)->get(route('challenges.show', $challenge))
        ->assertOk()
        ->assertSee('Join the challenge')
        ->assertDontSee('Leave the challenge')
        ->assertSee('your observations count, but you are not on the leaderboard', false);
    auth()->logout();
    $this->get(route('challenges.show', $challenge))->assertOk()->assertSee('Log in to join the challenge');
});

it('makes an observing list of the objects the observer still needs', function () {
    $challenge = hicksonMonth();
    $observer  = $this->createUserAndAssignToTeam('Observers');
    challengeObservation($observer, 'HCG 2', '2026-10-02');
    challengeObservation($observer, 'HCG 4', '2026-09-02');

    $this->actingAs($observer)->post(route('challenges.observing-list', $challenge))->assertRedirect();

    $list = ObservingList::where('owner_user_id', $observer->id)->sole();
    expect($list->name)->toBe('Hickson month')
        ->and(DB::table('observing_list_items')->where('observing_list_id', $list->id)->orderBy('sort_order')->pluck('object_name')->all())
        ->toBe(['HCG 1', 'HCG 3', 'HCG 4', 'HCG 5']);
});

it('deletes the goals and the news item with the challenge', function () {
    $admin     = $this->createUserAndAssignToTeam('Administrators');
    $challenge = hicksonMonth();
    $challenge->join(User::factory()->create());

    $this->actingAs($admin)->get(route('admin.challenges'))->assertOk()->assertSee('Hickson month')->assertSee('See 3 objects of HCG');
    $this->actingAs($admin)->get(route('admin.challenges.edit', $challenge))->assertOk()->assertSee('value="catalog:hcg" selected', false);

    $this->actingAs($admin)->delete(route('admin.challenges.destroy', $challenge))->assertRedirect(route('admin.challenges'));

    expect(ObservingChallenge::count())->toBe(0)
        ->and(ObservingGoal::count())->toBe(0)
        ->and(NewsItem::count())->toBe(0);
});

it('shows the running challenges on the home page, the joined ones first with progress and place', function () {
    $joined   = hicksonMonth(['name' => 'Joined challenge', 'ends_on' => '2026-11-30']);
    $other    = hicksonMonth(['name' => 'Other challenge', 'ends_on' => '2026-11-15']);
    hicksonMonth(['name' => 'Coming challenge', 'starts_on' => '2026-12-01', 'ends_on' => '2026-12-31']);
    hicksonMonth(['name' => 'Hidden challenge', 'hidden' => true]);
    $observer = User::factory()->create(['username' => 'homeobserver', 'slug' => 'home-observer']);
    $rival    = User::factory()->create();
    $joined->join($observer);
    $joined->join($rival);
    challengeObservation($rival, 'HCG 1', '2026-10-02');
    challengeObservation($rival, 'HCG 2', '2026-10-03');
    challengeObservation($observer, 'HCG 3', '2026-10-04');

    $entries = ObservingChallenge::forHomePage($observer);
    expect($entries->map(fn ($entry) => [$entry['challenge']->name, $entry['goal'] !== null, $entry['rank']])->all())->toBe([
        ['Joined challenge', true, 2],
        ['Other challenge', false, null],
    ]);

    $this->actingAs($observer)->get('/')->assertOk()
        ->assertSeeInOrder(['Observing challenges', 'Joined challenge', '1 of 3 objects', 'Place 2 of 2', 'Other challenge', 'Join the challenge'])
        ->assertDontSee('Coming challenge')
        ->assertDontSee('Hidden challenge');
    expect(ObservingChallenge::forHomePage(null)->map(fn ($entry) => $entry['challenge']->name)->all())->toBe(['Other challenge', 'Joined challenge']);
});

it('shows a coming challenge on the home page once announced, or when the observer joined it', function () {
    $observer = User::factory()->create();
    $soon     = hicksonMonth(['name' => 'Starts soon', 'starts_on' => '2026-11-01', 'ends_on' => '2027-03-31']);
    $later    = hicksonMonth(['name' => 'Starts later', 'starts_on' => '2026-12-01', 'ends_on' => '2026-12-31']);

    $names = fn (?User $viewer) => ObservingChallenge::forHomePage($viewer)->map(fn ($entry) => $entry['challenge']->name)->all();
    expect($names($observer))->toBe(['Starts soon']);

    $later->join($observer);
    $entries = ObservingChallenge::forHomePage($observer);
    expect($names($observer))->toBe(['Starts later', 'Starts soon'])
        ->and($entries[0]['rank'])->toBeNull();

    $this->actingAs($observer)->get('/')->assertOk()
        ->assertSeeInOrder(['Starts later', '0 of 3 objects', 'Starts on December 1, 2026', 'Starts soon', 'Starts on November 1, 2026', 'Join the challenge']);
});

it('gives a trophy for every challenge whose target was reached, gold for the winners', function () {
    $winner = User::factory()->create(['name' => 'Gold Winner', 'username' => 'goldwinner', 'slug' => 'gold-winner']);
    $tied   = User::factory()->create(['name' => 'Tied Winner', 'username' => 'tiedwinner', 'slug' => 'tied-winner']);
    $second = User::factory()->create(['name' => 'Second Place', 'username' => 'secondplace', 'slug' => 'second-place']);

    $ended = hicksonMonth(['name' => 'Hickson spring', 'starts_on' => '2026-03-01', 'ends_on' => '2026-05-31', 'target_count' => 2]);
    $running = hicksonMonth(['name' => 'Hickson autumn', 'target_count' => 2]);
    $hidden = hicksonMonth(['name' => 'Hidden one', 'hidden' => true, 'target_count' => 1]);
    foreach ([$winner, $tied, $second] as $observer) {
        $ended->join($observer);
    }
    $running->join($winner);
    $hidden->join($winner);
    foreach ([$winner, $tied] as $observer) {
        challengeObservation($observer, 'HCG 1', '2026-03-02');
        challengeObservation($observer, 'HCG 2', '2026-03-03');
        challengeObservation($observer, 'HCG 3', '2026-03-04');
    }
    challengeObservation($second, 'HCG 1', '2026-04-02');
    challengeObservation($second, 'HCG 2', '2026-04-03');
    // The running challenge: reached, but not won yet
    challengeObservation($winner, 'HCG 4', '2026-10-05');
    challengeObservation($winner, 'HCG 5', '2026-10-06');

    $trophies = fn (User $observer) => ObservingChallenge::trophiesFor($observer)
        ->map(fn ($trophy) => [$trophy['challenge']->name, $trophy['reached_on']->toDateString(), $trophy['winner']])->all();
    expect($trophies($winner))->toBe([['Hickson autumn', '2026-10-06', false], ['Hickson spring', '2026-03-03', true]])
        ->and($trophies($tied))->toBe([['Hickson spring', '2026-03-03', true]])
        ->and($trophies($second))->toBe([['Hickson spring', '2026-04-03', false]]);

    $this->get(route('observer.show', ['observer' => 'gold-winner']))->assertOk()
        ->assertSee('Winner of Hickson spring')
        ->assertSee('Hickson autumn: target reached on')
        ->assertDontSee('Hidden one');

    $nobody = User::factory()->create(['slug' => 'nobody-here']);
    $running->join($nobody);
    expect(ObservingChallenge::trophiesFor($nobody))->toBeEmpty();
    $this->get(route('observer.show', ['observer' => 'nobody-here']))->assertOk()->assertSee('No trophies yet.');
});
