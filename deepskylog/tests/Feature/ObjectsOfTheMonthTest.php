<?php

use App\Mail\MessageReceived;
use App\Models\Location;
use App\Models\Message;
use App\Models\ObjectOfTheMonth;
use App\Models\Observation;
use App\Models\ObservingList;
use App\Models\User;
use App\Services\ObjectsOfTheMonth\AstroforumFeed;
use App\Services\ObjectsOfTheMonth\ObjectsInText;
use Database\Factories\LocationFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

// The objects of the month of Astroforum: the threads read from the forum's
// feed (astroforum:objects-of-the-month), the objects proposed from their
// first post, confirmed by an administrator, and shown with everyone's
// observations of them in their months.

beforeEach(function () {
    config(['services.astroforum.enabled' => true, 'services.astroforum.delay' => 0]);
    Carbon::setTestNow('2026-10-27 12:00:00');
});

afterEach(function () {
    Carbon::setTestNow();
});

function monthObject(string $name, string $type = 'GALXY', array $aliases = []): void
{
    $slug = Str::slug($name);
    DB::table('objects')->insert([
        'name' => $name, 'slug' => $slug, 'type' => $type, 'con' => 'ORI',
        'ra' => 5.5, 'decl' => 5.0, 'mag' => 9.0, 'subr' => 99.9, 'pa' => 999,
        'urano' => 0, 'urano_new' => 0, 'sky' => 0, 'millenium' => '', 'diam1' => 0, 'diam2' => 0,
        'datasource' => null, 'taki' => '', 'SBObj' => 0.0, 'description' => '', 'psa' => '',
        'torresB' => '', 'torresBC' => '', 'torresC' => '', 'milleniumbase' => '0',
        'DSLDL' => '0', 'DSLDP' => '0', 'DSLLL' => '0', 'DSLLP' => '0', 'DSLOL' => '0', 'DSLOP' => '0',
        'DeepskyHunter' => '0', 'Interstellarum' => '0', 'timestamp' => now(),
    ]);
    foreach (array_merge([$name], $aliases) as $index => $indexName) {
        DB::table('search_index')->insert([
            'name' => $indexName, 'name_normalized' => mb_strtolower($indexName), 'source_table' => 'objects',
            'source_pk' => $slug, 'display_name' => $name, 'source_type' => $index === 0 ? $type : 'alias',
        ]);
    }
}

function monthPlanet(string $name): void
{
    $id = DB::table('planets')->insertGetId(['name' => $name, 'slug' => Str::slug($name), 'body_type' => 'planet']);
    DB::table('search_index')->insert([
        'name' => $name, 'name_normalized' => mb_strtolower($name), 'source_table' => 'planets',
        'source_pk' => (string) $id, 'display_name' => $name, 'source_type' => 'planet',
    ]);
}

function monthObservation(User $observer, string $date, string $object, array $attributes = []): Observation
{
    $location = Location::where('user_id', $observer->id)->first() ?? LocationFactory::new()->create(['user_id' => $observer->id, 'observer' => $observer->username]);

    return Observation::create($attributes + [
        'observer_username' => $observer->username, 'observer_user_id' => $observer->id, 'location_id' => $location->id,
        'observed_on' => $date, 'observed_time' => '21:30:00', 'target_type' => 'deepsky', 'target_table' => 'objects',
        'target_key' => $object, 'target_name' => $object, 'description' => 'Seen',
    ]);
}

function monthItem(array $attributes = []): ObjectOfTheMonth
{
    return ObjectOfTheMonth::create($attributes + [
        'thread_id' => 1487192,
        'url' => 'https://www.astroforum.nl/threads/object-van-de-maand-oktober-2026.1487192/',
        'title' => 'Object van de Maand oktober 2026: Twee sterrenstelsels',
        'starts_on' => '2026-10-01',
        'ends_on' => '2026-10-31',
        'candidates' => [],
        'objects' => [
            ['name' => 'NGC 1662', 'table' => 'objects', 'slug' => 'ngc-1662', 'type' => 'deepsky', 'key' => 'NGC 1662', 'object_type' => 'OPNCL'],
            ['name' => 'Saturn', 'table' => 'planets', 'slug' => 'saturn', 'type' => 'planet', 'key' => 'saturn', 'object_type' => 'planets'],
        ],
        'confirmed_at' => now(),
    ]);
}

function astroforumFeed(array $threads): string
{
    $items = '';
    foreach ($threads as [$id, $title, $date]) {
        $items .= '<item><title>'.e($title).'</title><pubDate>'.$date.'</pubDate>'
            .'<link>https://www.astroforum.nl/threads/thread.'.$id.'/</link></item>';
    }

    return '<?xml version="1.0" encoding="utf-8"?><rss version="2.0"><channel><title>Objecten van de maand</title>'.$items.'</channel></rss>';
}

function astroforumThread(string $firstPost, string $reply = 'Ik zag ook M 31.'): string
{
    return '<html><body>'
        .'<article class="message message--post js-post"><div class="message-content"><article class="message-body"><div class="bbWrapper">'.$firstPost.'</div></article></div></article>'
        .'<article class="message message--post js-post"><div class="bbWrapper">'.$reply.'</div></article>'
        .'</body></html>';
}

it('takes the months from the title of the thread', function () {
    $period = fn (string $title, string $posted) => array_map(fn ($date) => $date->toDateString(), AstroforumFeed::period($title, Carbon::parse($posted)));

    expect($period('Object van de Maand April 2026 – Een schijfje citroen', '2026-05-04'))->toBe(['2026-04-01', '2026-04-30'])
        ->and($period('Object van de Maand juli/augustus 2026 - How low can you go?', '2026-08-24'))->toBe(['2026-07-01', '2026-08-31'])
        ->and($period('Object van de Maand februari - Twee sterrenhopen', '2026-02-28'))->toBe(['2026-02-01', '2026-02-28'])
        ->and($period('Object van de Maand december/januari 2027', '2026-12-02'))->toBe(['2026-12-01', '2027-01-31'])
        ->and($period('Object van de Maand: Saturnus', '2026-10-20'))->toBe(['2026-10-01', '2026-10-31']);
});

it('finds the names of objects written in a Dutch text', function () {
    monthObject('M 42', 'HII', ['Orion Nebula']);
    monthPlanet('Saturn');
    $names = (new ObjectsInText)->names('Tussen NGC 1973/75/77 en M42/43 staan Messiers 58, 59 en 60, de Orionnevel, Sh2-296, 17 Comae Berenices, C 20 en Saturnus.');

    expect($names)->toContain('NGC 1973', 'NGC 1975', 'NGC 1977', 'M 42', 'M 43', 'M 58', 'M 59', 'M 60', 'Orion Nebula', 'Sh 2-296', '17 Com', 'Caldwell 20', 'Saturn')
        ->not->toContain('NGC 75', 'M 2026');
});

it('proposes the objects of the title first, then those named most', function () {
    monthObject('NGC 1662', 'OPNCL');
    monthObject('M 42', 'HII', ['Orion Nebula']);
    monthObject('NGC 2537', 'GALXY');
    monthPlanet('Saturn');

    $found = (new ObjectsInText)->find(
        'Object van de Maand maart 2026: NGC 2537',
        'NGC 2537 ligt niet ver van de Orionnevel. Zoek eerst NGC 1662, dan NGC1662 opnieuw, en Saturnus. HJ 684 en LBN 1039 ken ik niet.',
    );

    expect(array_column($found['objects'], 'name'))->toBe(['NGC 2537', 'NGC 1662', 'M 42', 'Saturn'])
        ->and($found['objects'][0]['in_title'])->toBeTrue()
        ->and($found['objects'][1]['mentions'])->toBe(2)
        ->and($found['objects'][3])->toMatchArray(['table' => 'planets', 'type' => 'planet', 'key' => 'saturn'])
        ->and($found['unresolved'])->toBe(['HJ 684', 'LBN 1039']);
});

it('reads the new threads, proposes their objects and asks the administrators to confirm them', function () {
    Mail::fake();
    monthObject('NGC 1662', 'OPNCL');
    monthObject('M 31', 'GALXY');
    $admin = $this->createUserAndAssignToTeam('Administrators');
    $admin->forceFill(['sendMail' => true])->save();

    Http::fake([
        '*/index.rss' => Http::response(astroforumFeed([
            [1487192, 'Object van de Maand oktober 2026: NGC 1662', 'Tue, 20 Oct 2026 20:42:08 +0000'],
            [1484467, 'Object van de maand november 2025 - Planetoïde 433 Eros', 'Sat, 01 Nov 2025 10:00:00 +0000'],
        ])),
        '*/thread.1487192/' => Http::response(astroforumThread('<p>Deze maand NGC 1662.</p><p>Niet te verwarren met M 31.</p>')),
        '*/thread.1484467/' => Http::response(astroforumThread('Planetoïde 433 Eros staat dichtbij.')),
    ]);

    $this->artisan('astroforum:objects-of-the-month')->assertSuccessful();
    expect(ObjectOfTheMonth::count())->toBe(0);

    $this->artisan('astroforum:objects-of-the-month --apply')->assertSuccessful();

    $october = ObjectOfTheMonth::where('thread_id', 1487192)->first();
    expect($october->starts_on->toDateString())->toBe('2026-10-01')
        ->and(array_column($october->candidates, 'name'))->toBe(['NGC 1662', 'M 31'])
        ->and($october->proposedKeys())->toBe(['deepsky:NGC 1662'])
        ->and($october->confirmed_at)->toBeNull()
        ->and(ObjectOfTheMonth::where('thread_id', 1484467)->first()->unresolved)->toBe(['(433) Eros']);

    // Only the thread whose month has not ended is announced
    $message = Message::where('receiver', $admin->username)->sole();
    expect($message->message)->toContain(route('admin.objects-of-the-month.edit', $october))
        ->not->toContain('433 Eros');
    Mail::assertQueued(MessageReceived::class);

    // A thread is read once
    Http::assertSentCount(6);
    $this->artisan('astroforum:objects-of-the-month --apply')->assertSuccessful();
    Http::assertSentCount(7);
    expect(Message::count())->toBe(1);
});

it('lets the administrators and database experts confirm the objects', function (string $team, int $status) {
    $item = monthItem(['objects' => null, 'confirmed_at' => null]);

    $this->actingAs($this->createUserAndAssignToTeam($team))
        ->get(route('admin.objects-of-the-month.edit', $item))
        ->assertStatus($status);
})->with([['Administrators', 200], ['Database Experts', 200], ['Observers', 403]]);

it('publishes the chosen objects and the objects added by name', function () {
    monthObject('NGC 1662', 'OPNCL');
    monthObject('M 31', 'GALXY');
    monthObject('NGC 7000', 'EMINB', ['Caldwell 20']);
    $found = (new ObjectsInText)->find('Object van de Maand oktober 2026: NGC 1662', 'NGC 1662 en M 31');
    $item = monthItem(['objects' => null, 'confirmed_at' => null, 'candidates' => $found['objects']]);
    $admin = $this->createUserAndAssignToTeam('Database Experts');
    $this->actingAs($admin);

    $this->get(route('objects-of-the-month.show', $item))->assertOk();
    $this->get(route('admin.objects-of-the-month.edit', $item))->assertOk()->assertSee('NGC 1662')->assertSee('M 31');

    $form = ['objects' => ['deepsky:NGC 1662'], 'starts_on' => '2026-10-01', 'ends_on' => '2026-10-31'];
    $this->put(route('admin.objects-of-the-month.update', $item), $form + ['extra' => "C 20\nNo Such Object"])
        ->assertSessionHasErrors('extra');
    expect($item->fresh()->confirmed_at)->toBeNull();

    $this->put(route('admin.objects-of-the-month.update', $item), $form + ['extra' => 'C 20'])
        ->assertRedirect(route('admin.objects-of-the-month'));

    $item->refresh();
    expect(array_column($item->objects, 'name'))->toBe(['NGC 1662', 'NGC 7000'])
        ->and($item->objects[0])->not->toHaveKey('mentions')
        ->and($item->confirmed_by)->toBe($admin->id)
        ->and($item->isPublished())->toBeTrue();
});

it('hides an object of the month until its objects are confirmed', function () {
    $item = monthItem(['confirmed_at' => null]);

    $this->get(route('objects-of-the-month.show', $item))->assertNotFound();
    $this->get(route('objects-of-the-month.index'))->assertOk()->assertDontSee('Twee sterrenstelsels');
    expect(ObjectOfTheMonth::current())->toBeNull();

    $item->update(['confirmed_at' => now()]);
    $this->get(route('objects-of-the-month.index'))->assertOk()->assertSee('Twee sterrenstelsels');
    expect(ObjectOfTheMonth::current()->is($item))->toBeTrue();
});

it('shows everyone\'s observations of the objects in the months', function () {
    $item = monthItem();
    [$a, $b] = User::factory()->count(2)->create()->all();
    monthObservation($a, '2026-10-05', 'NGC 1662', ['has_drawing' => true]);
    monthObservation($b, '2026-10-06', 'NGC 1662');
    monthObservation($b, '2026-10-07', 'saturn', ['target_type' => 'planet', 'target_table' => 'planets', 'target_name' => 'Saturn']);
    // Before the month, and another object
    monthObservation($a, '2026-09-30', 'NGC 1662', ['description' => 'Too early']);
    monthObservation($a, '2026-10-05', 'M 31');

    expect($item->counts())->toBe([
        'deepsky:NGC 1662' => ['observations' => 2, 'observers' => 2, 'drawings' => 1],
        'planet:saturn' => ['observations' => 1, 'observers' => 1, 'drawings' => 0],
    ])->and($item->observerCount())->toBe(2)
        ->and($item->observationsQuery()->count())->toBe(3);

    $this->get(route('objects-of-the-month.show', $item))->assertOk()
        ->assertSee('Twee sterrenstelsels')
        ->assertSee($item->url)
        ->assertDontSee('Too early');

    $this->get('/')->assertOk()
        ->assertSee(__('Object of the month :period', ['period' => $item->periodLabel()]))
        ->assertSee(route('objects-of-the-month.show', $item));
});

it('makes an observing list of the objects', function () {
    monthObject('NGC 1662', 'OPNCL');
    monthPlanet('Saturn');
    $item = monthItem();
    $observer = $this->createUserAndAssignToTeam('Observers');

    $this->actingAs($observer)->post(route('objects-of-the-month.observing-list', $item))->assertRedirect();

    $list = ObservingList::where('owner_user_id', $observer->id)->sole();
    expect($list->items()->pluck('object_name')->all())->toBe(['NGC 1662', 'Saturn']);
});

it('shows a thread posted late on the home page during the next month', function () {
    $item = monthItem(['starts_on' => '2026-09-01', 'ends_on' => '2026-09-30']);
    expect(ObjectOfTheMonth::current()?->is($item))->toBeTrue();

    $this->travelTo(Carbon::parse('2026-11-05'));
    expect(ObjectOfTheMonth::current())->toBeNull();
});
