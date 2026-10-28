<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

beforeEach(fn () => Storage::fake('local'));

test('check objects page requires administrator privileges', function () {
    $user = $this->createUserAndAssignToTeam('Observers');

    $this->actingAs($user)
        ->get(route('admin.objects.check'))
        ->assertForbidden();
});

test('repairing observation object names requires administrator privileges', function () {
    $user = $this->createUserAndAssignToTeam('Observers');

    $this->actingAs($user)
        ->post(route('admin.objects.check.repair-observation-objectnames'))
        ->assertForbidden();
});

test('administrator can view the local check objects page', function () {
    $user = $this->createUserAndAssignToTeam('Administrators');

    DB::table('objects')->insert([
        'name' => 'M 31',
        'type' => 'GX',
        'con' => 'AND',
        'ra' => 0.712,
        'decl' => 41.269,
        'mag' => 3.4,
        'subr' => 13.5,
        'pa' => 35,
        'urano' => 0,
        'urano_new' => 0,
        'sky' => 0,
        'millenium' => '',
        'diam1' => 190,
        'diam2' => 60,
        'datasource' => null,
        'taki' => '',
        'SBObj' => 0,
        'description' => '',
        'psa' => '',
        'torresB' => '',
        'torresBC' => '',
        'torresC' => '',
        'milleniumbase' => '',
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

    $this->actingAs($user)
        ->get(route('admin.objects.check'))
        ->assertOk()
        ->assertSee('Check Objects')
        ->assertSee('Objects constellation')
        ->assertSee('Objects and object names');
});

test('database expert can view the local check objects page', function () {
    $user = $this->createUserAndAssignToTeam('Database Experts');

    $this->actingAs($user)
        ->get(route('admin.objects.check'))
        ->assertOk()
        ->assertSee('Check Objects');
});

test('administrator can delete orphan object names', function () {
    $user = $this->createUserAndAssignToTeam('Administrators');

    DB::table('objects')->insert([
        'name' => 'M 31',
        'type' => 'GX',
        'con' => 'AND',
        'ra' => 0.712,
        'decl' => 41.269,
        'mag' => 3.4,
        'subr' => 13.5,
        'pa' => 35,
        'urano' => 0,
        'urano_new' => 0,
        'sky' => 0,
        'millenium' => '',
        'diam1' => 190,
        'diam2' => 60,
        'datasource' => null,
        'taki' => '',
        'SBObj' => 0,
        'description' => '',
        'psa' => '',
        'torresB' => '',
        'torresBC' => '',
        'torresC' => '',
        'milleniumbase' => '',
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

    DB::table('objectnames')->insert([
        [
            'objectname' => 'M 31',
            'catalog' => 'Messier',
            'catindex' => '31',
            'altname' => 'NGC 224',
            'timestamp' => now(),
        ],
        [
            'objectname' => 'UNKNOWN OBJECT',
            'catalog' => 'Test',
            'catindex' => '1',
            'altname' => 'Ghost',
            'timestamp' => now(),
        ],
    ]);

    $this->actingAs($user)
        ->post(route('admin.objects.check.cleanup'))
        ->assertRedirect(route('admin.objects.check'));

    $this->assertDatabaseHas('objectnames', [
        'objectname' => 'M 31',
        'altname' => 'NGC 224',
    ]);

    $this->assertDatabaseMissing('objectnames', [
        'objectname' => 'UNKNOWN OBJECT',
        'altname' => 'Ghost',
    ]);
});

test('administrator can trigger observation object-name repair', function () {
    $user = $this->createUserAndAssignToTeam('Administrators');

    $this->actingAs($user)
        ->post(route('admin.objects.check.repair-observation-objectnames'))
        ->assertRedirect(route('admin.objects.check'));
});

test('database expert can trigger observation object-name repair', function () {
    $user = $this->createUserAndAssignToTeam('Database Experts');

    $this->actingAs($user)
        ->post(route('admin.objects.check.repair-observation-objectnames'))
        ->assertRedirect(route('admin.objects.check'));
});

test('administrator can repair constellation mismatches', function () {
    $user = $this->createUserAndAssignToTeam('Administrators');

    // Insert an object with wrong constellation code
    DB::table('objects')->insert([
        'name' => 'M 31',
        'type' => 'GX',
        'con' => 'WRONG',  // Will be corrected to AND
        'ra' => 0.712,
        'decl' => 41.269,
        'mag' => 3.4,
        'subr' => 13.5,
        'pa' => 35,
        'urano' => 0,
        'urano_new' => 0,
        'sky' => 0,
        'millenium' => '',
        'diam1' => 190,
        'diam2' => 60,
        'datasource' => null,
        'taki' => '',
        'SBObj' => 0,
        'description' => '',
        'psa' => '',
        'torresB' => '',
        'torresBC' => '',
        'torresC' => '',
        'milleniumbase' => '',
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

    $this->actingAs($user)
        ->post(route('admin.objects.check.repair'))
        ->assertRedirect(route('admin.objects.check'));

    $this->assertDatabaseHas('objects', [
        'name' => 'M 31',
        'con' => 'AND',
    ]);

    // The page lists the corrected object as corrected, not as still wrong
    $this->get(route('admin.objects.check'))
        ->assertOk()
        ->assertSee('Corrected 1 constellation mismatch.')
        ->assertSee('Correct: 1.')
        ->assertSee('have been corrected')
        ->assertSee('Corrected to')
        ->assertDontSee('Repair all constellation mismatches');
});

test('administrator can export constellation mismatches', function () {
    $user = $this->createUserAndAssignToTeam('Administrators');

    DB::table('objects')->insert([
        'name' => 'M 31',
        'type' => 'GX',
        'con' => 'WRONG',
        'ra' => 0.712,
        'decl' => 41.269,
        'mag' => 3.4,
        'subr' => 13.5,
        'pa' => 35,
        'urano' => 0,
        'urano_new' => 0,
        'sky' => 0,
        'millenium' => '',
        'diam1' => 190,
        'diam2' => 60,
        'datasource' => null,
        'taki' => '',
        'SBObj' => 0,
        'description' => '',
        'psa' => '',
        'torresB' => '',
        'torresBC' => '',
        'torresC' => '',
        'milleniumbase' => '',
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

    $this->actingAs($user)
        ->post(route('admin.objects.check.constellations'))
        ->assertRedirect(route('admin.objects.check'));

    // The check didn't touch the object
    $this->assertDatabaseHas('objects', ['name' => 'M 31', 'con' => 'WRONG']);

    $this->get(route('admin.objects.check'))
        ->assertOk()
        ->assertSee('1 mismatch')
        ->assertSee('Repair');

    $response = $this->get(route('admin.objects.check.export-constellations'));

    $response->assertOk()
        ->assertHeader('Content-Type', 'text/csv; charset=utf-8');

    expect($response->headers->get('Content-Disposition'))->toMatch('/constellation-mismatches-.*\.csv/');
    expect($response->getContent())->toContain('M 31');
});

test('administrator can export orphan object names', function () {
    $user = $this->createUserAndAssignToTeam('Administrators');

    DB::table('objects')->insert([
        'name' => 'M 31',
        'type' => 'GX',
        'con' => 'AND',
        'ra' => 0.712,
        'decl' => 41.269,
        'mag' => 3.4,
        'subr' => 13.5,
        'pa' => 35,
        'urano' => 0,
        'urano_new' => 0,
        'sky' => 0,
        'millenium' => '',
        'diam1' => 190,
        'diam2' => 60,
        'datasource' => null,
        'taki' => '',
        'SBObj' => 0,
        'description' => '',
        'psa' => '',
        'torresB' => '',
        'torresBC' => '',
        'torresC' => '',
        'milleniumbase' => '',
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

    DB::table('objectnames')->insert([
        'objectname' => 'ORPHAN',
        'catalog' => 'Test',
        'catindex' => '1',
        'altname' => 'Ghost',
        'timestamp' => now(),
    ]);

    $response = $this->actingAs($user)
        ->get(route('admin.objects.check.export-orphans'));

    $response->assertOk()
        ->assertHeader('Content-Type', 'text/csv; charset=utf-8');

    expect($response->headers->get('Content-Disposition'))->toMatch('/orphan-objectnames-.*\.csv/');
    expect($response->getContent())->toContain('ORPHAN');
});

test('administrator can export alias-fixable observation mappings', function () {
    $user = $this->createUserAndAssignToTeam('Administrators');

    $response = $this->actingAs($user)
        ->get(route('admin.objects.check.export-observation-alias-mappings'));

    $response->assertOk()
        ->assertHeader('Content-Type', 'text/csv; charset=utf-8');

    expect($response->headers->get('Content-Disposition'))->toMatch('/observation-alias-mappings-.*\.csv/');
    expect($response->getContent())->toContain('Observed Name');
    expect($response->getContent())->toContain('Primary Name');
    expect($response->getContent())->toContain('Observation Count');
});

test('observations stored under an alias are reported, exported and repaired', function () {
    $user = $this->createUserAndAssignToTeam('Administrators');

    DB::table('objects')->insert([
        'name' => 'M 31',
        'type' => 'GX',
        'con' => 'AND',
        'ra' => 0.712,
        'decl' => 41.269,
        'mag' => 3.4,
        'subr' => 13.5,
        'pa' => 35,
        'urano' => 0,
        'urano_new' => 0,
        'sky' => 0,
        'millenium' => '',
        'diam1' => 190,
        'diam2' => 60,
        'datasource' => null,
        'taki' => '',
        'SBObj' => 0,
        'description' => '',
        'psa' => '',
        'torresB' => '',
        'torresBC' => '',
        'torresC' => '',
        'milleniumbase' => '',
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

    DB::table('objectnames')->insert([
        'objectname' => 'M 31',
        'catalog' => 'NGC',
        'catindex' => '224',
        'altname' => 'NGC 224',
        'timestamp' => now(),
    ]);

    foreach (['NGC 224', 'NGC 224', 'Ghost 1'] as $name) {
        DB::table('observations')->insert([
            'observer_username' => $user->username,
            'observer_user_id' => $user->id,
            'observed_on' => '2026-03-01',
            'target_type' => 'deepsky',
            'target_table' => 'objectnames',
            'target_key' => $name,
            'target_name' => $name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    $report = app(\App\Services\AdminObjectCheckService::class)->buildReport()['unknown_observations'];
    expect($report['available'])->toBeTrue()
        ->and($report['alias_count'])->toBe(1)
        ->and($report['alias_observation_total'])->toBe(2)
        ->and($report['count'])->toBe(1);

    $this->actingAs($user)
        ->get(route('admin.objects.check.export-observation-alias-mappings'))
        ->assertOk()
        ->assertSee('"NGC 224","M 31","2"', false);

    $this->actingAs($user)
        ->post(route('admin.objects.check.repair-observation-objectnames'))
        ->assertRedirect(route('admin.objects.check'));

    expect(DB::table('observations')->where('target_key', 'M 31')->where('target_name', 'M 31')->where('target_table', 'objects')->count())->toBe(2)
        ->and(DB::table('observations')->where('target_key', 'Ghost 1')->count())->toBe(1);
});

function checkObjectRow(string $name): array
{
    return [
        'name' => $name,
        'slug' => \Illuminate\Support\Str::slug($name),
        'type' => 'GALXY',
        'con' => 'AND',
        'ra' => 0.712,
        'decl' => 41.269,
        'mag' => 9.0,
        'subr' => 13.0,
        'pa' => 999,
        'urano' => 0,
        'urano_new' => 0,
        'sky' => 0,
        'millenium' => '',
        'diam1' => 0.0,
        'diam2' => 0.0,
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
    ];
}

function checkObservationRow(\App\Models\User $user, string $name, string $date = '2026-03-01'): array
{
    return [
        'observer_username' => $user->username,
        'observer_user_id' => $user->id,
        'observed_on' => $date,
        'target_type' => 'deepsky',
        'target_table' => 'objects',
        'target_key' => $name,
        'target_name' => $name,
        'created_at' => now(),
        'updated_at' => now(),
    ];
}

test('observations of a name that differs only in spaces are repaired', function () {
    $user = $this->createUserAndAssignToTeam('Administrators');

    // "AB 12" and "A B12" both compact to "AB12": that spelling is left alone
    DB::table('objects')->insert([checkObjectRow('NGC 1741B'), checkObjectRow('AB 12'), checkObjectRow('A B12')]);
    DB::table('observations')->insert([
        checkObservationRow($user, 'NGC1741B'),
        checkObservationRow($user, 'NGC  1741B', '2026-03-02'),
        checkObservationRow($user, 'AB12'),
    ]);

    $report = app(\App\Services\AdminObjectCheckService::class)->buildReport()['unknown_observations'];
    expect($report['alias_count'])->toBe(2)
        ->and($report['alias_observation_total'])->toBe(2)
        ->and($report['count'])->toBe(1);

    $this->actingAs($user)
        ->post(route('admin.objects.check.repair-observation-objectnames'))
        ->assertRedirect(route('admin.objects.check'));

    expect(DB::table('observations')->where('target_key', 'NGC 1741B')->count())->toBe(2)
        ->and(DB::table('observations')->where('target_key', 'AB12')->count())->toBe(1);
});

test('unknown targets link to their observations and to the objects they may stand for', function () {
    $user = $this->createUserAndAssignToTeam('Administrators');

    DB::table('objects')->insert([checkObjectRow('IC 2554A'), checkObjectRow('IC 2554B'), checkObjectRow('IC 25540')]);
    DB::table('observations')->insert(checkObservationRow($user, 'IC 2554'));

    $row = collect(app(\App\Services\AdminObjectCheckService::class)->buildReport()['unknown_observations']['examples'])
        ->firstWhere('objectname', 'IC 2554');

    expect($row['status'])->toBe('unknown')
        ->and(collect($row['candidates'])->pluck('name')->all())->toBe(['IC 2554A', 'IC 2554B'])
        ->and($row['candidates'][0]['url'])->toBe(route('object.show', ['slug' => 'ic-2554a']))
        ->and($row['observations'])->toHaveCount(1)
        ->and($row['observations'][0]['date'])->toBe('2026-03-01')
        ->and($row['observations'][0]['url'])->toContain('/observation/');

    $this->actingAs($user)
        ->get(route('admin.objects.check'))
        ->assertOk()
        ->assertSee('Possibly:')
        ->assertSee(route('object.show', ['slug' => 'ic-2554b']), false)
        ->assertSee($row['observations'][0]['url'], false);
});

test('an observation of an unknown object can be moved to another object from its edit page', function () {
    $admin = $this->createUserAndAssignToTeam('Administrators');
    $observer = \App\Models\User::factory()->create(['id' => 3]);

    DB::table('objects')->insert([checkObjectRow('IC 2554A'), checkObjectRow('IC 2554B')]);
    $id = DB::table('observations')->insertGetId(checkObservationRow($observer, 'IC 2554'));
    $editUrl = route('observations.edit', ['observer' => $observer->slug, 'date' => '2026-03-01', 'object' => 'ic-2554', 'index' => 1]);

    $this->actingAs($admin)->get($editUrl)->assertRedirect(route('observation.create', ['slug' => 'IC 2554']));
    $this->get(route('observation.create', ['slug' => 'IC 2554']))
        ->assertOk()
        ->assertSee('Choose the object this observation belongs to.')
        ->assertSee($editUrl . '?object=ic-2554a', false)
        ->assertSee($editUrl . '?object=ic-2554b', false);

    $this->get($editUrl . '?object=ic-2554b')->assertRedirect(route('observation.create', ['slug' => 'ic-2554b']));
    $this->get(route('observation.create', ['slug' => 'ic-2554b']))
        ->assertOk()
        ->assertSee('IC 2554B')
        ->assertSee('(was IC 2554)')
        ->assertSee('name="edit_unified_id" value="' . $id . '"', false);

    // A name that matches no object says so
    $this->get($editUrl . '?object=Nothing 1')->assertSessionHas('error', "No object found for 'Nothing 1'.");
});

test('objects without a type, with an unknown type or without a position are listed', function () {
    $admin = $this->createUserAndAssignToTeam('Administrators');
    DB::table('deepskytypes')->insertOrIgnore(['code' => 'GALXY', 'name' => 'Galaxy']);

    DB::table('objects')->insert([
        checkObjectRow('NGC 1'),
        array_merge(checkObjectRow('Bochum 2026'), ['type' => '']),
        array_merge(checkObjectRow('PA Test Object'), ['type' => 'G', 'ra' => 0, 'decl' => 0]),
        // A position on one axis only is a real position
        array_merge(checkObjectRow('NGC 2'), ['ra' => 0]),
    ]);

    $report = app(\App\Services\AdminObjectCheckService::class)->buildReport()['incomplete_objects'];
    expect($report['count'])->toBe(2)
        ->and($report['examples']->pluck('problems', 'name')->all())->toBe([
            'Bochum 2026' => ['no type'],
            'PA Test Object' => ['unknown type G', 'no position'],
        ]);

    $this->actingAs($admin)
        ->get(route('admin.objects.check'))
        ->assertOk()
        ->assertSee('2 incomplete objects found.')
        ->assertSee(route('object.edit', 'pa-test-object'), false)
        ->assertSee('unknown type G, no position');
});
