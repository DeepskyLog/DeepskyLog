<?php

use App\Jobs\GenerateObjectSetsPdf;
use App\Livewire\ObjectSets;
use App\Models\ObservingList;
use App\Models\ObservingListItem;
use App\Models\User;
use App\Services\ObjectSetPdfService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

function insertObjectSetObject(string $name, string $slug, float $raHours, float $decl, float $diam1 = 480.0): void
{
    DB::table('objects')->insert([
        'name' => $name,
        'slug' => $slug,
        'type' => 'GALXY',
        'con' => 'AND',
        'ra' => $raHours,
        'decl' => $decl,
        'mag' => 8.4,
        'subr' => 12.0,
        'pa' => 35,
        'urano' => 0,
        'urano_new' => 0,
        'sky' => 0,
        'millenium' => '',
        'diam1' => $diam1,
        'diam2' => 240.0,
        'datasource' => null,
        'taki' => '',
        'SBObj' => 0.0,
        'description' => 'A test object',
        'psa' => '',
        'torresB' => '',
        'torresBC' => '',
        'torresC' => '',
        'milleniumbase' => '0',
        'DSLDL' => '0',
        'DSLDP' => '0',
        'DSLLL' => '0',
        'DSLLP' => '0',
        'DSLOL' => '0',
        'DSLOP' => '0',
        'DeepskyHunter' => '0',
        'Interstellarum' => '0',
        'timestamp' => now(),
    ]);
}

function objectSetList(User $owner, bool $public = false): ObservingList
{
    insertObjectSetObject('M 31', 'm-31', 0.712313, 41.26917, 11400.0);
    insertObjectSetObject('M 32', 'm-32', 0.711611, 40.86528);

    $list = ObservingList::create([
        'owner_user_id' => $owner->id,
        'name' => 'Andromeda',
        'description' => null,
        'public' => $public,
    ]);

    foreach (['M 32', 'M 31'] as $name) {
        ObservingListItem::create([
            'observing_list_id' => $list->id,
            'object_name' => $name,
            'item_description' => $name === 'M 31' ? 'My own note on M 31' : null,
            'added_by_user_id' => $owner->id,
        ]);
    }

    return $list;
}

it('parses the fields of a row into numbers', function () {
    expect(ObjectSetPdfService::parseNumbers('120 60 15'))->toBe([120.0, 60.0, 15.0])
        ->and(ObjectSetPdfService::parseNumbers(' 10,  12;15 x -3 '))->toBe([10.0, 12.0, 15.0])
        ->and(ObjectSetPdfService::parseNumbers(''))->toBe([]);
});

it('asks the DSS for the object position in sexagesimal J2000', function () {
    $query = ObjectSetPdfService::dssQuery(5.575, -22.0147, 60);

    expect($query['r'])->toBe('05 34 30')
        ->and($query['d'])->toBe('-22 00 53')
        ->and($query['h'])->toBe('60')
        ->and($query['w'])->toBe('60');
});

it('requires a login', function () {
    $this->get(route('object-sets.index', ['source' => 'list', 'list' => 'x']))
        ->assertRedirect(route('login'));
});

it('lists the objects of an observing list with the observer defaults', function () {
    $owner = User::factory()->create();
    $list = objectSetList($owner);

    $this->actingAs($owner)
        ->get(route('object-sets.index', ['source' => 'list', 'list' => $list->slug]))
        ->assertOk()
        ->assertSee('Andromeda')
        ->assertSee('M 31')
        ->assertSee('M 32')
        ->assertSee('Generating the charts can take a while.');
});

it('refuses a private list of somebody else', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $list = objectSetList($owner);

    $this->actingAs($other)
        ->get(route('object-sets.index', ['source' => 'list', 'list' => $list->slug]))
        ->assertForbidden();
});

it('shows the link on the observing list page', function () {
    $owner = User::factory()->create();
    $list = objectSetList($owner);

    $this->actingAs($owner)
        ->get(route('observing-list.show', $list))
        ->assertOk()
        ->assertSee(route('object-sets.index', ['source' => 'list', 'list' => $list->slug]));
});

it('copies the column value to the ticked rows only', function () {
    $owner = User::factory()->create();
    objectSetList($owner);
    $objects = App\Models\DeepskyObject::orderBy('name')->get();

    Livewire::actingAs($owner)
        ->test(ObjectSets::class, ['objects' => $objects, 'title' => 'Test', 'total' => 2])
        ->set('fovs', '30')
        ->set('rows.1.checked.fovs', true)
        ->call('applyColumn', 'fovs')
        ->assertSet('rows.0.fovs', '120 60 15')
        ->assertSet('rows.1.fovs', '30')
        ->set('photos', '15')
        ->set('rows.0.all', true)
        ->call('applyAll')
        ->assertSet('rows.0.fovs', '30')
        ->assertSet('rows.0.photos', '15')
        ->assertSet('rows.1.photos', '60 25')
        ->call('toggleColumn', 'stars')
        ->assertSet('rows.0.checked.stars', true)
        ->assertSet('rows.1.checked.stars', true);
});

it('queues the whole set, or the set of one object', function () {
    Queue::fake();
    $owner = User::factory()->create();
    $list = objectSetList($owner);
    $objects = App\Models\DeepskyObject::orderBy('name')->get();

    $component = Livewire::actingAs($owner)
        ->test(ObjectSets::class, ['objects' => $objects, 'title' => 'Test', 'total' => 2, 'listId' => $list->id, 'filename' => 'object-sets-andromeda'])
        ->call('generate')
        ->call('generate', 1);

    Queue::assertPushed(GenerateObjectSetsPdf::class, 2);
    Queue::assertPushed(GenerateObjectSetsPdf::class, function (GenerateObjectSetsPdf $job) use ($owner, $list) {
        return $job->userId === $owner->id
            && count($job->parameters['objects']) === 2
            && $job->parameters['objects'][0]['fovs'] === [120.0, 60.0, 15.0]
            && $job->parameters['objects'][0]['dsos'] === [10.0, 12.0, 15.0]
            && $job->parameters['objects'][0]['stars'] === [10.0, 12.0, 15.0]
            && $job->parameters['objects'][0]['photos'] === [60.0, 25.0]
            && $job->parameters['list_id'] === $list->id;
    });
    Queue::assertPushed(GenerateObjectSetsPdf::class, fn (GenerateObjectSetsPdf $job) => count($job->parameters['objects']) === 1
        && $job->parameters['objects'][0]['name'] === 'M 32');

    $tokens = $component->get('tokens');
    expect($tokens)->toHaveCount(2);
    expect(Cache::get(GenerateObjectSetsPdf::statusKey($tokens[0]))['filename'])->toBe('m-32.pdf');
    expect(Cache::get(GenerateObjectSetsPdf::statusKey($tokens[1]))['filename'])->toBe('object-sets-andromeda.pdf');

    $component->assertSee('Waiting for a worker...');
});

it('does not let the client change the observing list', function () {
    $owner = User::factory()->create();
    $objects = collect();

    Livewire::actingAs($owner)
        ->test(ObjectSets::class, ['objects' => $objects, 'listId' => 1])
        ->set('listId', 2);
})->throws(\Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException::class);

it('generates the pdf with charts, photos, data and index pages', function () {
    Storage::fake('local');
    // A 2x2 GIF stands in for the survey photo.
    $gif = base64_decode('R0lGODlhAgACAIAAAP///wAAACH5BAAAAAAALAAAAAACAAIAAAICRF4AOw==');
    Http::fake(['archive.stsci.edu/*' => Http::response($gif, 200, ['Content-Type' => 'image/gif'])]);

    $owner = User::factory()->create();
    $list = objectSetList($owner);
    $token = (string) Illuminate\Support\Str::uuid();
    Cache::put(GenerateObjectSetsPdf::statusKey($token), [
        'state' => 'queued',
        'user_id' => $owner->id,
        'label' => 'M 31',
        'filename' => 'm-31.pdf',
    ], now()->addHour());

    (new GenerateObjectSetsPdf($token, $owner->id, [
        'objects' => [[
            'name' => 'M 31',
            'fovs' => [120.0, 15.0],
            'dsos' => [10.0, 15.0],
            'stars' => [10.0, 15.0],
            'photos' => [60.0, 25.0],
        ]],
        'data_page' => true,
        'ephemerides' => true,
        'year_ephemerides' => true,
        'index_page' => true,
        'list_id' => $list->id,
    ]))->handle();

    $status = Cache::get(GenerateObjectSetsPdf::statusKey($token));
    expect($status['state'])->toBe('complete', $status['detail'] ?? '');
    expect($status['filename'])->toBe('m-31.pdf');

    Http::assertSentCount(2);
    Http::assertSent(fn ($request) => str_contains($request->url(), 'h=60') || str_contains($request->url(), 'h=25'));

    $pdf = Storage::disk('local')->get(GenerateObjectSetsPdf::path($token));
    expect($pdf)->toStartWith('%PDF');
    // Data page, photos page, two charts and the index.
    expect(preg_match_all('/\/Type\s*\/Page[^s]/', $pdf))->toBe(5);

    $this->actingAs($owner)
        ->get(route('object-sets.download', $token))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');

    $this->actingAs(User::factory()->create())
        ->get(route('object-sets.download', $token))
        ->assertNotFound();
});

it('always draws the object a chart is centred on, however faint', function () {
    insertObjectSetObject('M 31', 'm-31', 0.712313, 41.26917, 11400.0);
    insertObjectSetObject('M 32', 'm-32', 0.711611, 40.86528);
    $target = App\Models\DeepskyObject::find('M 31');

    // Both objects are magnitude 8.4; the limit of 5 only lets the target through.
    $chart = app(App\Services\StarChartService::class)->build(
        $target, $target->raHours() * 15.0, $target->declDegrees(), 60.0, null, 5.0, 5.0, true, true, false, null, false
    );

    expect(collect($chart['objects'])->pluck('name')->all())->toBe(['M 31']);
});

it('replaces an older set of the same list once the new one is ready', function () {
    Storage::fake('local');
    Http::fake(['archive.stsci.edu/*' => Http::response('', 503)]);
    $owner = User::factory()->create();
    objectSetList($owner);

    $make = function (string $filename, string $createdAt) use ($owner): string {
        $token = (string) Illuminate\Support\Str::uuid();
        Cache::put(GenerateObjectSetsPdf::statusKey($token), [
            'state' => 'queued',
            'user_id' => $owner->id,
            'label' => '2 objects',
            'filename' => $filename,
            'created_at' => $createdAt,
        ], now()->addHour());
        Cache::put(GenerateObjectSetsPdf::recentKey($owner->id), array_merge([$token], Cache::get(GenerateObjectSetsPdf::recentKey($owner->id), [])), now()->addHour());

        (new GenerateObjectSetsPdf($token, $owner->id, [
            'objects' => [['name' => 'M 31', 'fovs' => [60.0], 'dsos' => [10.0], 'stars' => [10.0], 'photos' => []]],
            'data_page' => false,
            'index_page' => false,
        ]))->handle();

        return $token;
    };

    $old = $make('object-sets-andromeda.pdf', '2026-09-23T08:00:00+00:00');
    $other = $make('m-31.pdf', '2026-09-23T08:30:00+00:00');
    $new = $make('object-sets-andromeda.pdf', '2026-09-23T09:00:00+00:00');

    expect(Cache::has(GenerateObjectSetsPdf::statusKey($old)))->toBeFalse()
        ->and(Storage::disk('local')->exists(GenerateObjectSetsPdf::path($old)))->toBeFalse()
        ->and(Storage::disk('local')->exists(GenerateObjectSetsPdf::path($new)))->toBeTrue()
        ->and(Storage::disk('local')->exists(GenerateObjectSetsPdf::path($other)))->toBeTrue()
        ->and(Cache::get(GenerateObjectSetsPdf::recentKey($owner->id)))->toBe([$new, $other]);
});

it('lists only the objects the chart draws, not those in the margin around it', function () {
    insertObjectSetObject('M 31', 'm-31', 0.712313, 41.26917, 11400.0);
    insertObjectSetObject('M 32', 'm-32', 0.711611, 40.86528);
    // 1.65 degrees east of the centre, just beyond the side of the chart, in
    // the margin loadObjects() takes around the page.
    insertObjectSetObject('NGC 9999', 'ngc-9999', 0.712313 + 1.65 / 15.0 / cos(deg2rad(40.56917)), 40.56917, 60.0);
    $target = App\Models\DeepskyObject::find('M 31');

    $chart = app(App\Services\StarChartService::class)->build(
        $target, $target->raHours() * 15.0, $target->declDegrees(), 60.0, null, 10.0, 10.0, true, true, false, null, true
    );

    expect(collect($chart['objects'])->pluck('name')->all())->toContain('NGC 9999')
        ->and(collect($chart['drawn_objects'])->pluck('name')->sort()->values()->all())->toBe(['M 31', 'M 32']);
});
