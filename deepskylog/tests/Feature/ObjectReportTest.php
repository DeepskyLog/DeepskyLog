<?php

use App\Jobs\GenerateObjectReportPdf;
use App\Livewire\ObjectReport;
use App\Models\ObservingList;
use App\Models\ObservingListItem;
use App\Models\ReportLayout;
use App\Models\User;
use App\Services\ObjectReportDataBuilder;
use App\Services\ObjectReportPdfService;
use App\Services\ReportLayoutCatalog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

function insertReportObject(string $name, string $slug, string $con = 'AND', float $raHours = 0.712313, float $decl = 41.26917): void
{
    DB::table('objects')->insert([
        'name' => $name,
        'slug' => $slug,
        'type' => 'GALXY',
        'con' => $con,
        'ra' => $raHours,
        'decl' => $decl,
        'mag' => 8.4,
        'subr' => 12.0,
        'pa' => 35,
        'urano' => 60,
        'urano_new' => 0,
        'sky' => 0,
        'millenium' => '',
        'diam1' => 480.0,
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

function insertReportObservation(User $observer, string $targetName, bool $drawing = false, string $observedOn = '2024-01-05'): void
{
    DB::table('observations')->insert([
        'observer_username' => $observer->username ?? 'observer-' . $observer->id,
        'observer_user_id' => $observer->id,
        'observed_on' => $observedOn,
        'target_type' => 'deepsky',
        'target_table' => 'objects',
        'target_key' => $targetName,
        'target_name' => $targetName,
        'has_drawing' => $drawing,
        'language' => 'en',
        'legacy_source' => 'deepsky',
        'legacy_id' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

function reportList(User $owner, bool $public = false): ObservingList
{
    insertReportObject('M 31', 'm-31');
    insertReportObject('M 32', 'm-32');

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

function defaultReportLayout(string $name = '01 Basic A4', ?int $userId = null): ReportLayout
{
    return ReportLayout::create(['user_id' => $userId, 'name' => $name] + ReportLayoutCatalog::builtInDefault());
}

it('requires a login', function () {
    $this->get(route('object-report.index', ['source' => 'list', 'list' => 'x']))
        ->assertRedirect(route('login'));
});

it('shows the objects of an observing list with the layouts to choose from', function () {
    $owner = User::factory()->create();
    $list = reportList($owner);
    defaultReportLayout();
    defaultReportLayout('Mine', $owner->id);

    $this->actingAs($owner)
        ->get(route('object-report.index', ['source' => 'list', 'list' => $list->slug]))
        ->assertOk()
        ->assertSee('Report for observing list')
        ->assertSee('Andromeda')
        ->assertSee('01 Basic A4')
        ->assertSee('Mine')
        ->assertSee('Generate the report (2 objects)');
});

it('refuses a private list of somebody else', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $list = reportList($owner);

    $this->actingAs($other)
        ->get(route('object-report.index', ['source' => 'list', 'list' => $list->slug]))
        ->assertForbidden();
});

it('starts from the most recently saved layout of the observer', function () {
    $user = User::factory()->create();
    defaultReportLayout();
    $mine = defaultReportLayout('Mine', $user->id);

    Livewire::actingAs($user)
        ->test(ObjectReport::class, ['objects' => collect(), 'title' => 't'])
        ->assertSet('layoutId', $mine->id);
});

it('falls back to the built-in layout when no defaults were imported', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(ObjectReport::class, ['objects' => collect(), 'title' => 't'])
        ->assertSet('layoutId', ObjectReport::BUILT_IN)
        ->assertSet('settings.pagesize', 'A4')
        ->assertSet('fields.showname.style', 'bi');
});

it('saves a default layout as a copy of your own and deletes only your own', function () {
    $user = User::factory()->create();
    $default = defaultReportLayout();

    $component = Livewire::actingAs($user)
        ->test(ObjectReport::class, ['objects' => collect(), 'title' => 't'])
        ->assertSet('layoutId', $default->id)
        ->set('settings.fontSizeText', '9')
        ->set('fields.objectseen.width', '0')
        ->call('save');

    $copy = ReportLayout::where('user_id', $user->id)->sole();
    expect($copy->name)->toBe('01 Basic A4')
        ->and($copy->settings['fontSizeText'])->toBe('9')
        ->and($copy->fields['objectseen']['width'])->toEqual(0)
        ->and($default->fresh()->settings['fontSizeText'])->toBe('8');
    $component->assertSet('layoutId', $copy->id);

    $component->set('newName', 'Compact')->call('saveAs');
    $compact = ReportLayout::where('user_id', $user->id)->where('name', 'Compact')->sole();
    $component->assertSet('layoutId', $compact->id)
        ->call('delete');

    expect(ReportLayout::whereKey($compact->id)->exists())->toBeFalse();

    // A default layout cannot be deleted from the page.
    $component->set('layoutId', $default->id)->call('delete');
    expect(ReportLayout::whereKey($default->id)->exists())->toBeTrue();
});

it('only offers the layouts of other observers when asked to', function () {
    $user = User::factory()->create();
    $other = User::factory()->create(['name' => 'Other Observer']);
    defaultReportLayout();
    defaultReportLayout('Their layout', $other->id);
    insertReportObject('M 31', 'm-31');

    Livewire::actingAs($user)
        ->test(ObjectReport::class, ['objects' => \App\Models\DeepskyObject::where('name', 'M 31')->get(), 'title' => 't'])
        ->assertDontSee('Their layout')
        ->set('showAll', true)
        ->assertSee('Other Observer: Their layout');
});

it('queues the report with the layout as it is in the editor', function () {
    Queue::fake();
    Cache::flush();

    $owner = User::factory()->create();
    $list = reportList($owner);
    defaultReportLayout();

    $this->actingAs($owner);
    $objects = app(\App\Services\ObjectSetSourceService::class)
        ->resolve(\Illuminate\Http\Request::create('/object-report', 'GET', ['source' => 'list', 'list' => $list->slug]), $owner, 'report', ObjectReport::MAX_OBJECTS);

    Livewire::test(ObjectReport::class, [
        'objects' => $objects['objects'],
        'title' => $objects['title'],
        'listId' => $objects['list_id'],
        'filename' => $objects['filename'],
    ])
        ->set('fields.altname.width', '80')
        ->set('sort', true)
        ->set('reportTitle', 'My report')
        ->call('generate')
        ->assertHasNoErrors();

    Queue::assertPushed(GenerateObjectReportPdf::class, function (GenerateObjectReportPdf $job) use ($owner, $list) {
        return $job->userId === $owner->id
            // In the order of the list, not by name.
            && $job->parameters['objects'] === ['M 32', 'M 31']
            && $job->parameters['layout']['fields']['altname']['width'] == 80
            && $job->parameters['sort'] === true
            && $job->parameters['title'] === 'My report'
            && $job->parameters['list_id'] === $list->id;
    });

    $token = Cache::get(GenerateObjectReportPdf::recentKey($owner->id))[0];
    expect(Cache::get(GenerateObjectReportPdf::statusKey($token))['filename'])->toBe('report-andromeda.pdf');
});

it('refuses to queue a report without a single field', function () {
    Queue::fake();
    $user = User::factory()->create();
    insertReportObject('M 31', 'm-31');

    $component = Livewire::actingAs($user)
        ->test(ObjectReport::class, ['objects' => \App\Models\DeepskyObject::where('name', 'M 31')->get(), 'title' => 't']);

    foreach (array_keys(ReportLayoutCatalog::FIELDS) as $name) {
        $component->set("fields.{$name}.width", '0');
    }

    $component->call('generate')->assertHasErrors('generate');
    Queue::assertNothingPushed();
});

it('fills in the fields as the old reports did', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    reportList($user);
    insertReportObject('M 33', 'm-33', 'TRI');
    insertReportObservation($user, 'M 31', false, '2024-01-05');
    insertReportObservation($other, 'M 31', true);
    insertReportObservation($other, 'M 32');

    $rows = app(ObjectReportDataBuilder::class)->rows(
        \App\Models\DeepskyObject::whereIn('name', ['M 31', 'M 32', 'M 33'])->orderBy('name')->get(),
        $user,
        array_keys(ReportLayoutCatalog::FIELDS),
        Carbon::parse('2026-09-23'),
        ObservingList::first()->id
    );

    expect($rows[0]['objectseen'])->toBe('YS(2/1)')
        ->and($rows[0]['objectlastseen'])->toBe('20240105')
        ->and($rows[0]['objectdescription'])->toBe('My own note on M 31')
        ->and($rows[0]['objectsizepa'])->toBe("8.0'x4.0'/35°")
        ->and($rows[0]['objectuseratlaspage'])->toBe('60')
        ->and($rows[0]['objectmagnitude'])->toBe('8.4')
        ->and($rows[1]['objectseen'])->toBe('XZ(1/0)')
        ->and($rows[1]['objectdescription'])->toBe('A test object')
        ->and($rows[2]['objectseen'])->toBe('-')
        // Without a standard location there are no ephemerides.
        ->and($rows[0]['objectrise'])->toBe('-')
        ->and($rows[0]['objectmaxalt'])->toBe('-');
});

it('finds the stretch of the year an object stands highest', function () {
    // Highest (60°) from 15 March through 1 May.
    $altitudes = array_fill(0, 24, '40°');
    foreach ([5, 6, 7, 8] as $halfMonth) {
        $altitudes[$halfMonth] = '60°';
    }
    $dates = array_map(fn ($a) => ['max_alt_night' => $a], $altitudes);

    $fields = ObjectReportDataBuilder::yearFields(['dates' => $dates]);

    expect($fields['objectmaxalt'])->toBe('60°')
        ->and($fields['objectmaxaltstarttext'])->toBe('mid Mar')
        ->and($fields['objectmaxaltmidtext'])->toBe('begin Apr')
        ->and($fields['objectmaxaltendtext'])->toBe('start May');

    // Round the turn of the year.
    $altitudes = array_fill(0, 24, '20°');
    $altitudes[23] = $altitudes[0] = $altitudes[1] = '30°';
    $fields = ObjectReportDataBuilder::yearFields(['dates' => array_map(fn ($a) => ['max_alt_night' => $a], $altitudes)]);

    expect($fields['objectmaxaltstarttext'])->toBe('mid Dec')
        ->and($fields['objectmaxaltendtext'])->toBe('mid Jan');

    expect(ObjectReportDataBuilder::yearFields(null)['objectmaxalt'])->toBe('-');
});

it('cleans up a layout from the editor', function () {
    $layout = ReportLayoutCatalog::normalize([
        'settings' => ['pagesize' => 'a3', 'pageorientation' => 'sideways', 'top' => 'abc', 'showelements' => 'htxpz'],
        'fields' => ['showname' => ['line' => '2', 'x' => '100', 'width' => '-5', 'style' => 'bxq', 'legend' => 'Naam &amp; nr']],
    ]);

    expect($layout['settings']['pagesize'])->toBe('A3')
        ->and($layout['settings']['pageorientation'])->toBe('landscape')
        ->and($layout['settings']['top'])->toBe('550')
        ->and($layout['settings']['showelements'])->toBe('htp')
        ->and($layout['fields']['showname'])->toMatchArray(['line' => 2, 'x' => 100.0, 'width' => 0.0, 'style' => 'b', 'legend' => 'Naam & nr'])
        ->and($layout['fields'])->toHaveCount(count(ReportLayoutCatalog::FIELDS));
});

it('draws a report over several pages and columns, grouped by constellation', function () {
    $layout = ReportLayoutCatalog::builtInDefault();
    $layout['settings']['showelements'] = 'htplsi';

    $rows = [];
    foreach (range(1, 120) as $i) {
        $rows[] = [
            'objectseen' => '-',
            'showname' => 'NGC ' . $i,
            'objecttype' => 'GALXY',
            'objectconstellation' => $i % 2 ? 'AND' : 'CYG',
            'objectconstellationfull' => $i % 2 ? 'Andromeda' : 'Cygnus',
            'objectdescription' => str_repeat('A long description that runs over more than one line. ', 5),
            'objectradecl' => '00h42m44s +41°16\'09"',
            'objectmaxaltstarttext' => 'mid Ořt',
        ];
    }

    $pdf = app(ObjectReportPdfService::class)->render($layout, $rows, ['title' => 'Test', 'sort' => true, 'date' => '23/09/2026']);

    expect($pdf)->toStartWith('%PDF')
        ->and(preg_match_all('#/Type /Page\b#', $pdf))->toBeGreaterThan(3);
});

it('renders the queued report and offers it to its owner only', function () {
    Storage::fake('local');
    Cache::flush();

    $owner = User::factory()->create();
    $list = reportList($owner);
    $token = '11111111-2222-3333-4444-555555555555';

    Cache::put(GenerateObjectReportPdf::statusKey($token), [
        'state' => 'queued',
        'user_id' => $owner->id,
        'label' => 'Andromeda',
        'filename' => 'report-andromeda.pdf',
        'created_at' => now()->toIso8601String(),
    ], now()->addHour());

    (new GenerateObjectReportPdf($token, $owner->id, [
        'layout' => ReportLayoutCatalog::builtInDefault(),
        'objects' => ['M 31', 'M 32'],
        'title' => 'Andromeda',
        'sort' => true,
        'date' => '2026-09-23',
        'list_id' => $list->id,
    ], 'en'))->handle();

    $status = Cache::get(GenerateObjectReportPdf::statusKey($token));
    expect($status['state'])->toBe('complete');
    expect(Storage::disk('local')->get(GenerateObjectReportPdf::path($token)))->toStartWith('%PDF');

    $this->actingAs($owner)->get(route('object-report.download', $token))
        ->assertOk()
        ->assertHeader('content-disposition', 'attachment; filename=report-andromeda.pdf');

    $this->actingAs(User::factory()->create())->get(route('object-report.download', $token))
        ->assertNotFound();
});

it('survives the round trip through the queue', function () {
    // The worker unserializes the job; readonly properties declared in the
    // base class cannot be restored from the subclass.
    foreach ([
        new GenerateObjectReportPdf('11111111-2222-3333-4444-555555555555', 1, ['objects' => ['M 31']], 'nl'),
        new \App\Jobs\GenerateObjectSetsPdf('11111111-2222-3333-4444-555555555555', 1, ['objects' => []], 'nl'),
    ] as $job) {
        $restored = unserialize(serialize($job));

        expect($restored->token)->toBe($job->token)
            ->and($restored->userId)->toBe(1)
            ->and($restored->parameters)->toBe($job->parameters)
            ->and($restored->locale)->toBe('nl');
    }
});

it('removes an unfinished older report of the same list when it is made again', function () {
    Queue::fake();
    Storage::fake('local');
    Cache::flush();

    $owner = User::factory()->create();
    $list = reportList($owner);
    $objects = \App\Models\DeepskyObject::whereIn('name', ['M 31', 'M 32'])->get();

    // A report that never got made, one that did, and one of another list.
    $stuck = '11111111-1111-1111-1111-111111111111';
    $done = '22222222-2222-2222-2222-222222222222';
    $other = '33333333-3333-3333-3333-333333333333';
    foreach ([$stuck => ['queued', 'report-andromeda.pdf'], $done => ['complete', 'report-andromeda.pdf'], $other => ['failed', 'report-other.pdf']] as $token => [$state, $filename]) {
        Cache::put(GenerateObjectReportPdf::statusKey($token), [
            'state' => $state, 'user_id' => $owner->id, 'label' => 'x', 'filename' => $filename, 'created_at' => now()->subHour()->toIso8601String(),
        ], now()->addHour());
    }
    Storage::disk('local')->put(GenerateObjectReportPdf::path($done), '%PDF old');
    Cache::put(GenerateObjectReportPdf::recentKey($owner->id), [$stuck, $done, $other], now()->addHour());

    Livewire::actingAs($owner)
        ->test(ObjectReport::class, ['objects' => $objects, 'title' => 'Andromeda', 'listId' => $list->id, 'filename' => 'report-andromeda'])
        ->call('generate');

    $tokens = Cache::get(GenerateObjectReportPdf::recentKey($owner->id));
    expect($tokens)->toHaveCount(3)
        ->and($tokens)->not->toContain($stuck)
        ->and(array_slice($tokens, 1))->toBe([$done, $other])
        ->and(Cache::has(GenerateObjectReportPdf::statusKey($stuck)))->toBeFalse();

    // The finished report stays until the new one is ready, which replaces it.
    $new = $tokens[0];
    (new GenerateObjectReportPdf($new, $owner->id, [
        'layout' => ReportLayoutCatalog::builtInDefault(), 'objects' => ['M 31'], 'title' => 't', 'date' => '2026-09-23',
    ], 'en'))->handle();

    expect(Cache::get(GenerateObjectReportPdf::statusKey($new))['state'])->toBe('complete')
        ->and(Cache::has(GenerateObjectReportPdf::statusKey($done)))->toBeFalse()
        ->and(Storage::disk('local')->exists(GenerateObjectReportPdf::path($done)))->toBeFalse()
        ->and(Cache::get(GenerateObjectReportPdf::recentKey($owner->id)))->toBe([$new, $other]);
});

it('throws away a report that was asked for again while it was being made', function () {
    Storage::fake('local');
    Cache::flush();

    $owner = User::factory()->create();
    reportList($owner);
    $superseded = '44444444-4444-4444-4444-444444444444';
    Cache::put(GenerateObjectReportPdf::statusKey($superseded), [
        'state' => 'queued', 'user_id' => $owner->id, 'label' => 'x', 'filename' => 'report-andromeda.pdf', 'created_at' => now()->toIso8601String(),
    ], now()->addHour());
    Cache::put(GenerateObjectReportPdf::recentKey($owner->id), ['55555555-5555-5555-5555-555555555555'], now()->addHour());

    (new GenerateObjectReportPdf($superseded, $owner->id, [
        'layout' => ReportLayoutCatalog::builtInDefault(), 'objects' => ['M 31'], 'title' => 't', 'date' => '2026-09-23',
    ], 'en'))->handle();

    expect(Storage::disk('local')->exists(GenerateObjectReportPdf::path($superseded)))->toBeFalse()
        ->and(Cache::has(GenerateObjectReportPdf::statusKey($superseded)))->toBeFalse();
});
