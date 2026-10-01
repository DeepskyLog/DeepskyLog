<?php

use App\Jobs\ComputeObserverStats;
use App\Livewire\PopularObservationsTable;
use App\Livewire\UserPopularObservationsTable;
use App\Models\Observation;
use App\Models\ObservationComment;
use App\Models\ObservationLike;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

/**
 * Likes and comments of an observation are stored as observation_type
 * 'observation' with the id of the observation in the `observations` table,
 * whether the observation was migrated from the old DeepskyLog or not.
 */
function socialTestObservation(User $user, array $extra = []): Observation
{
    return Observation::create(array_merge([
        'observer_username' => $user->username,
        'observer_user_id' => $user->id,
        'observed_on' => '2026-03-01',
        'target_type' => 'deepsky',
        'target_table' => 'objects',
        'target_key' => 'M 65',
        'target_name' => 'M 65',
        'description' => 'Seen',
    ], $extra));
}

function socialTestDetailParams(User $user): array
{
    return ['observer' => $user->slug, 'date' => '2026-03-01', 'object' => 'm-65'];
}

it('shows likes and comments of a migrated observation that were made on its detail page', function () {
    $owner = User::factory()->create();
    $viewer = User::factory()->create();
    $observation = socialTestObservation($owner, ['legacy_source' => 'deepsky', 'legacy_id' => 99999]);

    $this->actingAs($viewer)
        ->postJson(route('observation.like'), ['observation_type' => ObservationLike::TYPE_OBSERVATION, 'observation_id' => $observation->id])
        ->assertOk()
        ->assertJson(['status' => 'liked', 'count' => 1]);

    $this->actingAs($viewer)
        ->post(route('observation.comments.store'), ['observation_id' => $observation->id, 'body' => 'Nice one'])
        ->assertSessionHasNoErrors();

    $this->get(route('observations.detail', socialTestDetailParams($owner)))
        ->assertOk()
        ->assertViewHas('likesCount', 1)
        ->assertViewHas('liked', true)
        ->assertViewHas('commentsCount', 1)
        ->assertSee('Nice one');
});

it('stores a like sent with the target type by the observation id', function (string $sentType) {
    $owner = User::factory()->create();
    $viewer = User::factory()->create();
    $observation = socialTestObservation($owner, ['target_type' => $sentType, 'target_name' => 'Jupiter']);

    $this->actingAs($viewer)
        ->postJson(route('observation.like'), ['observation_type' => $sentType, 'observation_id' => $observation->id])
        ->assertOk()
        ->assertJson(['status' => 'liked', 'count' => 1]);

    expect(ObservationLike::sole()->only(['observation_type', 'observation_id']))
        ->toBe(['observation_type' => ObservationLike::TYPE_OBSERVATION, 'observation_id' => $observation->id]);
})->with(['planet', 'lunar', 'deepsky', 'comet']);

it('only deletes the likes and comments of the deleted observation', function () {
    $owner = User::factory()->create();
    $viewer = User::factory()->create();
    // The old id of the first observation is the new id of the second one.
    $other = socialTestObservation($owner, ['observed_on' => '2026-02-01', 'target_name' => 'M 66', 'target_key' => 'M 66']);
    $deleted = socialTestObservation($owner, ['legacy_source' => 'deepsky', 'legacy_id' => $other->id]);

    foreach ([$other, $deleted] as $observation) {
        ObservationLike::create(['user_id' => $viewer->id, 'observation_type' => ObservationLike::TYPE_OBSERVATION, 'observation_id' => $observation->id]);
        ObservationComment::create(['user_id' => $viewer->id, 'observation_type' => ObservationComment::TYPE_OBSERVATION, 'observation_id' => $observation->id, 'body' => 'Hi']);
    }

    $this->actingAs($owner)->delete(route('observations.destroy', socialTestDetailParams($owner)));

    expect(Observation::find($deleted->id))->toBeNull()
        ->and(ObservationLike::pluck('observation_id')->all())->toBe([$other->id])
        ->and(ObservationComment::pluck('observation_id')->all())->toBe([$other->id]);
});

it('knows an observer has popular observations when a new observation is liked', function () {
    $owner = User::factory()->withPersonalTeam()->create();
    $viewer = User::factory()->create();
    $observation = socialTestObservation($owner);
    $stats = function () use ($owner) {
        (new ComputeObserverStats($owner->id))->handle();

        return Cache::pull('observer_stats:'.$owner->id)['hasPopularObservations'] ?? null;
    };

    expect($stats())->toBeFalse();

    ObservationLike::create(['user_id' => $viewer->id, 'observation_type' => ObservationLike::TYPE_OBSERVATION, 'observation_id' => $observation->id]);
    expect($stats())->toBeTrue();
});

it('lists liked observations in the popular tables', function () {
    $owner = User::factory()->withPersonalTeam()->create();
    $viewer = User::factory()->create();
    $this->actingAs($viewer);
    $observation = socialTestObservation($owner);
    ObservationLike::create(['user_id' => $viewer->id, 'observation_type' => ObservationLike::TYPE_OBSERVATION, 'observation_id' => $observation->id]);

    Livewire::withoutLazyLoading();
    Livewire::test(PopularObservationsTable::class)->assertOk()->assertSee('M 65')->assertSee('Mar 1, 2026');
    Livewire::test(UserPopularObservationsTable::class, ['username' => $owner->username])->assertOk()->assertSee('M 65');
    Livewire::test(UserPopularObservationsTable::class, ['username' => $viewer->username])->assertOk()->assertDontSee('M 65');
});

it('moves old likes and comments to the observation id', function () {
    $owner = User::factory()->create();
    $viewer = User::factory()->create();
    $other = User::factory()->create();

    $new = socialTestObservation($owner);
    $migrated = socialTestObservation($owner, ['legacy_source' => 'deepsky', 'legacy_id' => $new->id]);
    $comet = socialTestObservation($owner, ['target_type' => 'comet', 'legacy_source' => 'comet', 'legacy_id' => 5]);
    $planet = socialTestObservation($owner, ['target_type' => 'planet']);

    $like = fn (User $user, string $type, int $id) => DB::table('observation_likes')->insertGetId(['user_id' => $user->id, 'observation_type' => $type, 'observation_id' => $id]);
    $like($viewer, 'deepsky', $new->id);           // old id of $migrated
    $like($viewer, 'comet', 5);                    // old id of $comet
    $like($viewer, 'planet', $planet->id);         // new id, sent by a list card
    $like($other, 'deepsky', $comet->id);          // no migrated observation has this old id
    $like($other, 'deepsky', 424242);              // observation is gone
    $like($viewer, 'observation', $planet->id + 1000); // already converted, left alone
    $like($viewer, 'session', 3);
    // Same user liked $comet once by its old and once by its new id.
    $like($other, 'comet', 5);
    $like($other, 'comet', $comet->id);
    DB::table('observation_comments')->insert(['user_id' => $viewer->id, 'observation_type' => 'deepsky', 'observation_id' => $new->id, 'body' => 'Hi']);

    $migration = require database_path('migrations/2026_09_28_000002_store_observation_likes_and_comments_by_observation_id.php');
    $migration->up();

    $likes = DB::table('observation_likes')->orderBy('id')->get()
        ->map(fn ($row) => [$row->user_id, $row->observation_type, (int) $row->observation_id])->all();
    expect($likes)->toBe([
        [$viewer->id, 'observation', $migrated->id],
        [$viewer->id, 'observation', $comet->id],
        [$viewer->id, 'observation', $planet->id],
        [$other->id, 'observation', $comet->id],
        [$viewer->id, 'observation', $planet->id + 1000],
        [$viewer->id, 'session', 3],
    ])->and((int) DB::table('observation_comments')->sole()->observation_id)->toBe($migrated->id);

    // Running it again changes nothing.
    $migration->up();
    expect(DB::table('observation_likes')->count())->toBe(6);
});

it('gives Facebook a preview of the observation, because it ignores the text of the share link', function () {
    $owner = User::factory()->create();
    socialTestObservation($owner, ['description' => '<p>Bright core</p><p>&amp; faint arms</p>']);

    $this->get(route('observations.detail', socialTestDetailParams($owner)))
        ->assertOk()
        ->assertSee('<meta property="og:title" content="Look at this observation of M 65 by '.e($owner->name).'" />', false)
        ->assertSee('<meta property="og:description" content="Bright core &amp; faint arms" />', false)
        ->assertSee('<meta property="og:image" content="'.url('/images/logo2.png').'" />', false)
        ->assertSee('data-share-text="Look at this observation of M 65', false);
});

it('hands the sketch to the X button and the share sheet, which take no image from a link', function () {
    $owner = User::factory()->create();
    socialTestObservation($owner, ['has_drawing' => true, 'drawing_filename' => 'sketches/m65.jpg']);

    $this->get(route('observations.detail', socialTestDetailParams($owner)))
        ->assertOk()
        ->assertSee('data-share-image="/images/sketches/m65.jpg"', false)
        ->assertSee('class="social-button native-share hover"', false);
});

it('gives the share buttons no image when the observation has no sketch', function () {
    $owner = User::factory()->create();
    socialTestObservation($owner);

    $this->get(route('observations.detail', socialTestDetailParams($owner)))
        ->assertOk()
        ->assertSee('data-share-image=""', false);
});
