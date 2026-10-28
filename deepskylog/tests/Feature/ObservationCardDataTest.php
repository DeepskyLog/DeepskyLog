<?php

use App\Models\Observation;
use App\Models\ObservationLike;
use App\Models\User;
use App\Services\Observations\ObservationCardData;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;

function cardTestObservation(User $user, array $extra = []): Observation
{
    return Observation::create(array_merge([
        'observer_username' => $user->username,
        'observer_user_id' => $user->id,
        'observed_on' => '2026-03-01',
        'observed_time' => '21:00:00',
        'target_type' => Observation::TYPE_DEEPSKY,
        'target_table' => 'objects',
        'target_key' => 'M 65',
        'target_name' => 'M 65',
        'description' => 'Seen',
    ], $extra));
}

/** Number of queries to render a deep-sky card for each observation, after preloading them. */
function cardTestQueriesToRender($observations): int
{
    ObservationCardData::instance()->preload($observations);
    DB::flushQueryLog();
    DB::enableQueryLog();
    foreach ($observations as $observation) {
        Blade::render('<x-observation-deepsky :observation="$observation" />', ['observation' => $observation]);
    }
    $count = count(DB::getQueryLog());
    DB::disableQueryLog();

    return $count;
}

it('renders preloaded cards without a query per card', function () {
    $owner = User::factory()->create();
    $viewer = User::factory()->create();
    $this->actingAs($viewer);
    $observations = collect(range(1, 6))->map(fn ($day) => cardTestObservation($owner, [
        'observed_on' => '2026-03-0'.$day,
        'magnification' => '120',
    ]));
    foreach ($observations as $observation) {
        ObservationLike::create(['user_id' => $viewer->id, 'observation_type' => ObservationLike::TYPE_OBSERVATION, 'observation_id' => $observation->id]);
    }

    $few = cardTestQueriesToRender(Observation::whereIn('id', $observations->take(2)->pluck('id'))->get());
    app()->forgetScopedInstances();
    $many = cardTestQueriesToRender(Observation::whereIn('id', $observations->pluck('id'))->get());

    expect($many)->toBe($few);
});

it('counts the likes of an observation and whether the viewer liked it', function () {
    $owner = User::factory()->create();
    $viewer = User::factory()->create();
    $liked = cardTestObservation($owner);
    $notLiked = cardTestObservation($owner, ['observed_on' => '2026-03-02']);
    ObservationLike::create(['user_id' => $viewer->id, 'observation_type' => ObservationLike::TYPE_OBSERVATION, 'observation_id' => $liked->id]);
    ObservationLike::create(['user_id' => $owner->id, 'observation_type' => ObservationLike::TYPE_OBSERVATION, 'observation_id' => $liked->id]);
    ObservationLike::create(['user_id' => $owner->id, 'observation_type' => ObservationLike::TYPE_OBSERVATION, 'observation_id' => $notLiked->id]);

    $this->actingAs($viewer);
    $cards = ObservationCardData::instance()->preload([$liked, $notLiked]);

    expect($cards->likes($liked->id))->toBe(['count' => 2, 'liked' => true])
        ->and($cards->likes($notLiked->id))->toBe(['count' => 1, 'liked' => false])
        ->and($cards->likes(null))->toBe(['count' => 0, 'liked' => false]);
});

it('numbers the observations of an object on the same day in the detail url', function () {
    $owner = User::factory()->create();
    $first = cardTestObservation($owner, ['observed_time' => '20:00:00']);
    cardTestObservation($owner, ['target_key' => 'M 66', 'target_name' => 'M 66', 'observed_time' => '20:30:00']);
    $second = cardTestObservation($owner, ['observed_time' => '21:00:00']);

    ObservationCardData::instance()->preload([$first, $second]);

    expect($first->detailIndex())->toBe(1)
        ->and($second->detailIndex())->toBe(2)
        ->and($second->detailUrl())->toEndWith('/2026-03-01/m-65/2');

    // A new, earlier observation of the object that day moves the index.
    cardTestObservation($owner, ['observed_time' => '19:00:00']);

    expect($second->detailIndex())->toBe(3);
});

it('finds the observer of a card whatever the case of the username', function () {
    $owner = User::factory()->create(['username' => 'CaseObserver']);

    expect(ObservationCardData::instance()->user('caseobserver')?->id)->toBe($owner->id)
        ->and(ObservationCardData::instance()->user(''))->toBeNull();
});

it('keeps the cached total of a deferred page up to date', function () {
    $owner = User::factory()->create();
    $total = fn () => Observation::where('target_type', Observation::TYPE_DEEPSKY)->orderByDesc('id')->deferredPaginate(20)->total();
    cardTestObservation($owner);

    expect($total())->toBe(1);

    // Served from the cache: no count query.
    DB::enableQueryLog();
    $total();
    expect(collect(DB::getQueryLog())->filter(fn ($q) => str_contains($q['query'], 'count('))->all())->toBe([]);
    DB::disableQueryLog();

    $second = cardTestObservation($owner, ['observed_on' => '2026-03-02']);
    expect($total())->toBe(2);

    $second->delete();
    expect($total())->toBe(1);
});
