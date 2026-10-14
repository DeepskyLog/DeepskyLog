<?php

use App\Models\DeepskyObject;
use App\Models\Observation;
use App\Models\User;
use App\Services\ObjectSetDataBuilder;
use Database\Factories\InstrumentFactory;
use Illuminate\Support\Facades\DB;

function insertTargetTestObject(string $name, string $slug): void
{
    DB::table('objects')->insert([
        'name' => $name,
        'slug' => $slug,
        'type' => 'GALXY',
        'con' => 'LEO',
        'ra' => 11.3155,
        'decl' => 13.0922,
        'mag' => 9.3,
        'subr' => 12.4,
        'pa' => 174,
        'urano' => 0,
        'urano_new' => 0,
        'sky' => 0,
        'millenium' => '',
        'diam1' => 600.0,
        'diam2' => 180.0,
        'datasource' => null,
        'taki' => '',
        'SBObj' => 0.0,
        'description' => '',
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

function insertTargetTestAlias(string $objectName, string $alias, string $slug): void
{
    DB::table('objectnames')->insert([
        'objectname' => $objectName,
        'catalog' => strtok($alias, ' '),
        'catindex' => trim((string) strstr($alias, ' ')),
        'altname' => $alias,
        'slug' => $slug,
        'timestamp' => now(),
    ]);
}

function insertTargetTestComet(string $name, string $slug): int
{
    return DB::table('cometobjects')->insertGetId([
        'name' => $name,
        'slug' => $slug,
        'icqname' => '',
        'timestamp' => now(),
    ]);
}

function targetTestObservation(User $user, array $target, array $extra = []): Observation
{
    return Observation::create(array_merge([
        'observer_username' => $user->username,
        'observer_user_id' => $user->id,
        'observed_on' => '2026-03-01',
        'description' => 'Seen',
    ], $target, $extra));
}

beforeEach(function () {
    insertTargetTestObject('M 65', 'm-65');
    insertTargetTestAlias('M 65', 'NGC 3623', 'ngc-3623');
});

it('finds the canonical name of a deep-sky object from its name, slug or alias', function () {
    expect(Observation::canonicalDeepskyName('M 65'))->toBe('M 65')
        ->and(Observation::canonicalDeepskyName('m-65'))->toBe('M 65')
        ->and(Observation::canonicalDeepskyName('NGC 3623'))->toBe('M 65')
        ->and(Observation::canonicalDeepskyName('ngc-3623'))->toBe('M 65')
        ->and(Observation::canonicalDeepskyName('NGC 99999'))->toBeNull()
        ->and(Observation::canonicalDeepskyName(''))->toBeNull();
});

it('stores a deep-sky observation picked by an alias under the canonical object name', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('observation.store'), [
        'target_table' => 'objectnames',
        'target_slug' => 'ngc-3623',
        'observed_on' => '2026-03-01',
        'instrument_id' => InstrumentFactory::new()->create(['observer' => '', 'user_id' => $user->id])->id,
        'description' => 'Seen.',
    ])->assertSessionHasNoErrors();

    $observation = Observation::latest('id')->first();
    expect($observation->target_type)->toBe('deepsky')
        ->and($observation->target_table)->toBe('objects')
        ->and($observation->target_key)->toBe('M 65')
        ->and($observation->target_name)->toBe('M 65');
});

it('stores a comet observation under the id of the comet', function () {
    $user = User::factory()->create();
    $cometId = insertTargetTestComet('C/2022 A2 (PANSTARRS)', 'c2022-a2-panstarrs');

    $this->actingAs($user)->post(route('observation.store'), [
        'target_table' => 'cometobjects',
        'target_slug' => 'c2022-a2-panstarrs',
        'observed_on' => '2026-03-01',
        'instrument_id' => InstrumentFactory::new()->create(['observer' => '', 'user_id' => $user->id])->id,
        'description' => 'Seen.',
    ])->assertSessionHasNoErrors();

    $observation = Observation::latest('id')->first();
    expect($observation->target_type)->toBe('comet')
        ->and($observation->target_table)->toBe('cometobjects')
        ->and($observation->target_key)->toBe((string) $cometId)
        ->and($observation->target_name)->toBe('C/2022 A2 (PANSTARRS)');
});

it('rejects an observation of an object that does not exist', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('observation.store'), [
        'target_table' => 'objects',
        'target_slug' => 'no-such-object',
        'observed_on' => '2026-03-01',
        'instrument_id' => InstrumentFactory::new()->create(['observer' => '', 'user_id' => $user->id])->id,
        'description' => 'Seen.',
    ])->assertSessionHasErrors('target_slug');

    expect(Observation::count())->toBe(0);
});

it('normalises targets stored the old way by the observation form', function () {
    $user = User::factory()->create();
    $cometId = insertTargetTestComet('C/2022 A2 (PANSTARRS)', 'c2022-a2-panstarrs');

    $alias = targetTestObservation($user, ['target_type' => 'deepsky', 'target_table' => 'objectnames', 'target_key' => 'ngc-3623', 'target_name' => 'M 65']);
    $comet = targetTestObservation($user, ['target_type' => 'comet', 'target_table' => 'cometobjects', 'target_key' => 'c2022-a2-panstarrs', 'target_name' => 'C/2022 A2 (Panstarrs)']);
    $unknown = targetTestObservation($user, ['target_type' => 'deepsky', 'target_table' => 'objects', 'target_key' => 'IC 2554', 'target_name' => 'IC 2554', 'legacy_source' => 'deepsky', 'legacy_id' => 7]);

    $migration = require database_path('migrations/2026_09_28_000001_normalize_observation_targets.php');
    $migration->up();

    expect($alias->fresh()->only(['target_table', 'target_key', 'target_name']))
        ->toBe(['target_table' => 'objects', 'target_key' => 'M 65', 'target_name' => 'M 65'])
        ->and($comet->fresh()->only(['target_table', 'target_key', 'target_name']))
        ->toBe(['target_table' => 'cometobjects', 'target_key' => (string) $cometId, 'target_name' => 'C/2022 A2 (PANSTARRS)'])
        ->and($unknown->fresh()->target_key)->toBe('IC 2554');
});

it('counts migrated and new observations of an object together', function () {
    $user = User::factory()->create();
    $deepsky = ['target_type' => 'deepsky', 'target_table' => 'objects', 'target_key' => 'M 65', 'target_name' => 'M 65'];

    targetTestObservation($user, $deepsky, ['legacy_source' => 'deepsky', 'legacy_id' => 12]);
    targetTestObservation($user, $deepsky, ['has_drawing' => true, 'observed_on' => '2026-03-02']);

    expect(Observation::ofDeepskyObject('M 65')->count())->toBe(2)
        ->and(Observation::getObservationsCountForUserByName($user, 'M 65', 'deepsky'))->toBe(2);

    $data = app(ObjectSetDataBuilder::class)->objectData(DeepskyObject::where('name', 'M 65')->firstOrFail(), $user);
    expect($data['seen'])->toBe(2)
        ->and($data['your_seen'])->toBe(2);
});

it('shows the deep-sky details of a migrated observation on its detail page', function () {
    $user = User::factory()->create();
    $observation = targetTestObservation($user, ['target_type' => 'deepsky', 'target_table' => 'objects', 'target_key' => 'M 65', 'target_name' => 'M 65', 'legacy_source' => 'deepsky', 'legacy_id' => 12]);
    DB::table('deepsky_observation_details')->insert(['observation_id' => $observation->id, 'visibility' => 2, 'stellar' => 1]);

    $this->get(route('observations.detail', ['observer' => $user->slug, 'date' => '2026-03-01', 'object' => 'm-65']))
        ->assertOk()
        ->assertViewHas('observation', fn($o) => (int) $o->visibility === 2 && (int) $o->stellar === 1);
});

it('shows a comet observation made in this version on its detail and compare pages', function () {
    $user = User::factory()->create();
    $cometId = insertTargetTestComet('C/2022 A2 (PANSTARRS)', 'c2022-a2-panstarrs');
    $target = ['target_type' => 'comet', 'target_table' => 'cometobjects', 'target_key' => (string) $cometId, 'target_name' => 'C/2022 A2 (PANSTARRS)'];
    targetTestObservation($user, $target, ['observed_on' => '2026-02-01']);
    targetTestObservation($user, $target);

    $params = ['observer' => $user->slug, 'date' => '2026-03-01', 'object' => 'c2022-a2-panstarrs'];

    $this->get(route('observations.detail', $params))
        ->assertOk()
        ->assertViewHas('observationType', 'comet');

    $this->actingAs($user)->get(route('observations.compare.my-last', $params + ['index' => 1]))
        ->assertOk()
        ->assertViewHas('myLastObservation', fn($o) => $o !== null && $o->observed_on->format('Y-m-d') === '2026-02-01');
});

it('lists observations made in this version on the session page', function () {
    $user = User::factory()->create();
    $migrated = targetTestObservation($user, ['target_type' => 'deepsky', 'target_table' => 'objects', 'target_key' => 'M 65', 'target_name' => 'M 65', 'legacy_source' => 'deepsky', 'legacy_id' => 12]);
    $new = targetTestObservation($user, ['target_type' => 'deepsky', 'target_table' => 'objects', 'target_key' => 'M 65', 'target_name' => 'M 65'], ['observed_time' => '22:15:00']);

    DB::table('observation_sessions')->insert([
        'id' => 1,
        'name' => 'Leo triplet',
        'slug' => 'leo-triplet',
        'observerid' => $user->username,
        'begindate' => '2026-03-01 20:00:00',
        'enddate' => '2026-03-02 02:00:00',
        'locationid' => 0,
        'weather' => '',
        'equipment' => '',
        'comments' => '',
        'language' => 'en',
        'active' => 1,
    ]);
    DB::table('sessionObservations')->insert([
        ['sessionid' => 1, 'observationid' => $migrated->id],
        ['sessionid' => 1, 'observationid' => $new->id],
    ]);

    $this->get('/session/' . $user->slug . '/leo-triplet')
        ->assertOk()
        ->assertViewHas('observations', fn($observations) => collect($observations->items())->pluck('id')->sort()->values()->all() === collect([$migrated->id, $new->id])->sort()->values()->all());
});

it('counts migrated and new observations on the comet page', function () {
    $user = User::factory()->create();
    $cometId = insertTargetTestComet('C/2022 A2 (PANSTARRS)', 'c2022-a2-panstarrs');
    $comet = ['target_type' => 'comet', 'target_table' => 'cometobjects', 'target_key' => (string) $cometId, 'target_name' => 'C/2022 A2 (PANSTARRS)'];
    targetTestObservation($user, $comet, ['legacy_source' => 'comet', 'legacy_id' => 3]);
    targetTestObservation($user, $comet);

    $this->get('/object/c2022-a2-panstarrs')
        ->assertOk()
        ->assertViewHas('totalObservations', 2);
});

it('moves the observations of a deleted object to the chosen object', function () {
    $admin = $this->createUserAndAssignToTeam('Administrators');
    insertTargetTestObject('NGC 3623 duplicate', 'ngc-3623-duplicate');
    $observation = targetTestObservation($admin, ['target_type' => 'deepsky', 'target_table' => 'objects', 'target_key' => 'NGC 3623 duplicate', 'target_name' => 'NGC 3623 duplicate']);

    $this->actingAs($admin)->delete('/object/ngc-3623-duplicate', ['move_to_slug' => 'm-65'])
        ->assertRedirect(route('dashboard'));

    expect($observation->fresh()->only(['target_table', 'target_key', 'target_name']))
        ->toBe(['target_table' => 'objects', 'target_key' => 'M 65', 'target_name' => 'M 65'])
        ->and(DB::table('objects')->where('name', 'NGC 3623 duplicate')->exists())->toBeFalse();
});
