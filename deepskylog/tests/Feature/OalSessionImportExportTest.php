<?php

use App\Models\Instrument;
use App\Models\Location;
use App\Models\Observation;
use App\Models\ObservationSession;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\Support\ObservationImportFixtures as Fixtures;

/*
 * Sessions in OAL files: the session and search exports write the sessions of
 * the observations, and an import creates (or updates) the sessions of the
 * file, which then hold the imported observations.
 */

beforeEach(function () {
    Fixtures::seedTargets();
    $this->owner = Fixtures::createObserver(['username' => 'session-owner', 'name' => 'Session Owner']);
    $this->site = Location::query()->where('user_id', $this->owner->id)->firstOrFail();
});

function oalSessionObservation(User $user, string $object, string $date, string $time, ?int $locationId): Observation
{
    return Observation::query()->create([
        'observer_username' => $user->username,
        'observer_user_id' => $user->id,
        'observed_on' => $date,
        'observed_time' => $time,
        'location_id' => $locationId,
        'instrument_id' => Instrument::query()->where('user_id', $user->id)->where('instrument_type_id', '<>', 1)->value('id'),
        'language' => 'en',
        'description' => "{$object} seen",
        'target_type' => 'deepsky',
        'target_table' => 'objects',
        'target_key' => $object,
        'target_name' => $object,
        'has_drawing' => false,
    ]);
}

function oalSession(User $user, array $attributes): ObservationSession
{
    return ObservationSession::query()->create($attributes + [
        'name' => 'A night in January',
        'observerid' => $user->username,
        'begindate' => '2024-01-12 19:30:00',
        'enddate' => '2024-01-13 00:45:00',
        'weather' => '',
        'equipment' => '',
        'language' => 'en',
        'active' => 1,
    ]);
}

it('imports the session of a session export into another account', function () {
    $coObserver = User::factory()->create(['username' => 'jane', 'name' => 'Jane Companion']);
    $session = oalSession($this->owner, [
        'locationid' => $this->site->id,
        'weather' => '<p>Clear &amp; frosty</p>',
        'equipment' => '<p>12 inch Dobson</p>',
        'comments' => '<p>Steady all night.</p>',
    ]);
    DB::table('sessionObservers')->insert(['sessionid' => $session->id, 'observer' => $coObserver->username]);
    oalSessionObservation($this->owner, 'M 42', '2024-01-12', '21:15:00', $this->site->id);

    $content = $this->get(route('session.export.oal', [$this->owner->slug, $session->slug]))->assertOk()->getContent();

    // The local time at the site (Brussels, UT+1 in January).
    expect($content)->toContain('<begin>2024-01-12T19:30:00+01:00</begin>')
        ->and($content)->toContain('<end>2024-01-13T00:45:00+01:00</end>');

    $importer = Fixtures::createObserver(['username' => 'session-importer']);
    $result = Fixtures::import($importer, 'oal/session.xml', false, $content);

    expect($result->errors)->toBe(0)->and($result->imported)->toBe(1);

    $imported = ObservationSession::query()->where('observerid', $importer->username)->sole();
    $importerSite = Location::query()->where('user_id', $importer->id)->where('name', 'Example Site')->sole();
    expect($imported->name)->toBe('Session at Example Site on 2024-01-12')
        ->and($imported->begindate->format('Y-m-d H:i:s'))->toBe('2024-01-12 19:30:00')
        ->and($imported->enddate->format('Y-m-d H:i:s'))->toBe('2024-01-13 00:45:00')
        ->and((int) $imported->locationid)->toBe($importerSite->id)
        ->and($imported->weather)->toBe('Clear &amp; frosty')
        ->and($imported->equipment)->toBe('12 inch Dobson')
        ->and($imported->comments)->toBe('Steady all night.')
        ->and($imported->language)->toBe('en')
        ->and((int) $imported->active)->toBe(1)
        ->and($imported->otherObservers())->toBe([]);

    $observationId = Observation::query()->where('observer_user_id', $importer->id)->value('id');
    expect(DB::table('sessionObservations')->where('sessionid', $imported->id)->pluck('observationid')->map(fn ($id) => (int) $id)->all())
        ->toBe([$observationId]);

    expect($result->sessions)->toBe([$imported->id => ['name' => $imported->name, 'slug' => $imported->slug, 'new' => true]])
        ->and(implode("\n", Fixtures::messages($result, 'info')))->toContain('co-observers in the file (Jane Companion)');

    // Importing the file again updates the session instead of adding one.
    $again = Fixtures::import($importer, 'oal/session.xml', false, $content);

    expect(ObservationSession::query()->where('observerid', $importer->username)->count())->toBe(1)
        ->and($again->sessions[$imported->id]['new'])->toBeFalse();
});

it('shows the imported sessions on the import result page', function () {
    $session = oalSession($this->owner, ['locationid' => $this->site->id]);
    oalSessionObservation($this->owner, 'M 42', '2024-01-12', '21:15:00', $this->site->id);
    $content = $this->get(route('session.export.oal', [$this->owner->slug, $session->slug]))->getContent();

    $importer = Fixtures::createObserver(['username' => 'page-importer']);
    $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('session.xml', $content);

    $this->actingAs($importer)
        ->post(route('observation.import.process'), ['import_file' => $file])
        ->assertOk()
        ->assertSee('Session at Example Site on 2024-01-12')
        ->assertSee(route('session.show', [$importer->slug, ObservationSession::query()->where('observerid', $importer->username)->value('slug')]), false);
});

it('imports the sessions of a file without observations', function () {
    $session = oalSession($this->owner, ['locationid' => $this->site->id]);
    $content = $this->get(route('session.export.oal', [$this->owner->slug, $session->slug]))->getContent();

    // The session export of an empty session still holds the session.
    expect($content)->toContain('<session id="session-'.$session->id.'"');

    $importer = Fixtures::createObserver(['username' => 'empty-importer']);
    $earlier = oalSessionObservation($importer, 'M 31', '2024-01-12', '19:45:00', null);
    $result = Fixtures::import($importer, 'oal/session.xml', false, $content);

    $imported = ObservationSession::query()->where('observerid', $importer->username)->sole();
    expect($result->sessions)->toHaveKey($imported->id)
        // The observations of the importer already in its dates join it.
        ->and(DB::table('sessionObservations')->where('sessionid', $imported->id)->pluck('observationid')->map(fn ($id) => (int) $id)->all())
        ->toBe([$earlier->id]);
});

it('writes the sessions of the observations in the search export', function () {
    $jane = User::factory()->create(['username' => 'jane']);
    $bob = Fixtures::createObserver(['username' => 'bob']);
    $bobSite = Location::query()->where('user_id', $bob->id)->firstOrFail();

    // Jane's copy of the shared session also holds the owner's observations;
    // they are written with the owner's session, although the copy is older.
    $copy = oalSession($jane, ['locationid' => $this->site->id]);
    DB::table('sessionObservers')->insert(['sessionid' => $copy->id, 'observer' => $this->owner->username]);
    $session = oalSession($this->owner, ['locationid' => $this->site->id]);
    DB::table('sessionObservers')->insert(['sessionid' => $session->id, 'observer' => $jane->username]);
    // A draft of somebody else is not exported.
    $draft = oalSession($bob, ['locationid' => $bobSite->id, 'active' => 0]);

    $inSession = oalSessionObservation($this->owner, 'M 42', '2024-01-12', '21:15:00', $this->site->id);
    $outside = oalSessionObservation($this->owner, 'M 31', '2024-02-02', '20:00:00', $this->site->id);
    $bobs = oalSessionObservation($bob, 'M 45', '2024-01-12', '22:00:00', $bobSite->id);

    expect(DB::table('sessionObservations')->where('observationid', $inSession->id)->count())->toBe(2);

    $this->actingAs($this->owner);
    $content = $this->get(route('observations.search.oal', ['q' => '%']))->assertOk()->getContent();

    $xml = simplexml_load_string($content);
    $sessionIds = collect($xml->xpath('//sessions/session'))->map(fn ($node) => (string) $node['id'])->all();
    $sessionOf = fn (Observation $o) => (string) ($xml->xpath('//observation[@id="observation-'.$o->id.'"]/session')[0] ?? '');

    expect($sessionIds)->toBe(['session-'.$session->id])
        ->and($sessionOf($inSession))->toBe('session-'.$session->id)
        ->and($sessionOf($outside))->toBe('')
        ->and($sessionOf($bobs))->toBe('')
        ->and($content)->toContain('<coObserver>observer-'.$jane->id.'</coObserver>')
        ->and($content)->not->toContain('session-'.$draft->id);

    // The draft is exported for its own observer.
    $this->actingAs($bob);
    $bobContent = $this->get(route('observations.search.oal', ['q' => '%']))->assertOk()->getContent();
    expect($bobContent)->toContain('<session id="session-'.$draft->id.'"');
});

it('round trips the sessions of a search export', function () {
    $session = oalSession($this->owner, ['locationid' => $this->site->id, 'comments' => "<p>First line<br>second line</p>"]);
    oalSessionObservation($this->owner, 'M 42', '2024-01-12', '21:15:00', $this->site->id);

    $this->actingAs($this->owner);
    $content = $this->get(route('observations.search.oal', ['q' => '%']))->assertOk()->getContent();

    $importer = Fixtures::createObserver(['username' => 'search-importer']);
    $result = Fixtures::import($importer, 'oal/search.xml', false, $content);

    $imported = ObservationSession::query()->where('observerid', $importer->username)->sole();
    expect($result->errors)->toBe(0)
        ->and($imported->begindate->format('Y-m-d H:i:s'))->toBe('2024-01-12 19:30:00')
        ->and($imported->enddate->format('Y-m-d H:i:s'))->toBe('2024-01-13 00:45:00')
        ->and(DB::table('sessionObservations')->where('sessionid', $imported->id)->count())->toBe(1);
});
