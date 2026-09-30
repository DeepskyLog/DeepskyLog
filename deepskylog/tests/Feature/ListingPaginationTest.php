<?php

use App\Helpers\ListingPagination;
use App\Models\Observation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

function listingPaginationObservations(int $count): User
{
    $user = User::factory()->create();
    foreach (range(1, $count) as $i) {
        Observation::query()->create([
            'observer_username' => $user->username, 'observer_user_id' => $user->id,
            'observed_on' => sprintf('2026-03-%02d', min($i, 28)), 'target_type' => 'deepsky', 'target_table' => 'objects',
            'target_key' => 'M ' . $i, 'target_name' => 'M ' . $i, 'has_drawing' => false,
        ]);
    }

    return $user;
}

it('accepts a page number within the cap of the global lists', function (string $query, bool $valid) {
    expect(ListingPagination::isValidPage(Request::create('/observations?' . $query), 'deepsky'))->toBe($valid);
})->with([
    ['', true],
    ['deepsky=1', true],
    ['deepsky=100&obs_type=comet', true],
    ['deepsky=101', false],
    ['deepsky=0', false],
    ['deepsky=abc', false],
    ['deepsky[]=2', false],
]);

it('returns 404 for pages past the cap of the global lists', function (string $url) {
    $this->get($url)->assertNotFound();
})->with([
    '/observations?deepsky=101',
    '/observations?deepsky=abc',
    '/cometobservations?comet=101',
    '/drawings?page=101',
]);

it('caps the global paginator and keeps only the listing parameters', function () {
    $this->get('/observations?obs_type=comet&sort=id&deepsky=2&foo=bar');

    $paginator = new LengthAwarePaginator(collect(range(1, 20)), 100000, 20, 2, ['pageName' => 'deepsky', 'path' => url('/observations')]);
    $capped = ListingPagination::cap($paginator);

    expect($capped->lastPage())->toBe(ListingPagination::MAX_PAGES)
        ->and($capped->url(3))->toContain('obs_type=comet')
        ->and($capped->url(3))->toContain('sort=id')
        ->and($capped->url(3))->toContain('deepsky=3')
        ->and($capped->url(3))->not->toContain('foo=bar');
});

it('does not copy unknown query parameters into the links of /observations', function () {
    listingPaginationObservations(25);

    $this->get('/observations?sort=id&foo=bar&utm_source=spam')
        ->assertOk()
        ->assertSee('deepsky=2', false)
        ->assertSee('sort=id', false)
        ->assertDontSee('foo=bar', false)
        ->assertDontSee('utm_source', false);
});

it('does not copy unknown query parameters into the links of an observer list', function () {
    $user = listingPaginationObservations(25);

    $this->get('/observations/' . $user->slug . '?foo=bar')
        ->assertOk()
        ->assertSee('deepsky=2', false)
        ->assertDontSee('foo=bar', false);
});
