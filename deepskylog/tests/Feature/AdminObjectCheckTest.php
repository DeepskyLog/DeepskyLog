<?php

use Illuminate\Support\Facades\DB;

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

    $response = $this->actingAs($user)
        ->get(route('admin.objects.check.export-constellations'));

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