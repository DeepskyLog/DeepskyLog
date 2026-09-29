<?php

use App\Helpers\HomepagePagination;
use App\Models\Observation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

it('accepts at most one homepage page parameter within the page cap', function (string $query, bool $valid) {
    expect(HomepagePagination::isValidRequest(Request::create('/?' . $query)))->toBe($valid);
})->with([
    ['', true],
    ['observations=3', true],
    ['sketches=10&sketch_type=comet', true],
    ['observations=11', false],
    ['sessions=0', false],
    ['sketches=abc', false],
    ['sketches=2&observations=2', false],
]);

it('returns 404 for crawler homepage urls', function () {
    $this->get('/?sketches=2&observations=2')->assertNotFound();
    $this->get('/?observations=11')->assertNotFound();
    $this->get('/?observations=2&obs_type=deepsky')->assertOk();
});

it('caps the homepage paginator and keeps only the filter parameters', function () {
    $this->get('/?obs_type=comet&observations=2&foo=bar');

    $paginator = new LengthAwarePaginator(collect(range(1, 20)), 1000, 20, 2, ['pageName' => 'observations']);
    $capped = HomepagePagination::cap($paginator);

    expect($capped->lastPage())->toBe(HomepagePagination::MAX_PAGES)
        ->and($capped->url(3))->toContain('obs_type=comet')
        ->and($capped->url(3))->toContain('observations=3')
        ->and($capped->url(3))->not->toContain('foo=bar');
});

it('paginates observations with a deferred join in the requested order', function () {
    $user = User::factory()->create();
    foreach (['2026-01-01', '2026-03-01', '2026-02-01'] as $date) {
        Observation::create([
            'observer_username' => $user->username,
            'observer_user_id' => $user->id,
            'observed_on' => $date,
            'description' => 'Seen',
        ]);
    }

    $page = Observation::orderByDesc('observed_on')->deferredPaginate(2, 'observations', 1);

    expect($page->total())->toBe(3)
        ->and($page->lastPage())->toBe(2)
        ->and($page->getCollection()->map(fn ($o) => $o->observed_on instanceof \DateTimeInterface ? $o->observed_on->format('Y-m-d') : substr($o->observed_on, 0, 10))->all())
        ->toBe(['2026-03-01', '2026-02-01']);

    $second = Observation::orderByDesc('observed_on')->deferredPaginate(2, 'observations', 2);
    expect($second->getCollection())->toHaveCount(1);
});
