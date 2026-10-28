<?php

use App\Models\DeepskyObject;
use App\Services\StarChartService;
use Illuminate\Support\Facades\DB;

// The edit page of an object shows a star chart of the values in the form
// (#526): the object is drawn where the form puts it, before it is saved.

function insertPreviewTestObject(string $name, string $slug, float $ra, float $decl, float $mag = 9.3): void
{
    DB::table('deepskytypes')->insertOrIgnore(['code' => 'GALXY', 'name' => 'Galaxy']);
    DB::table('objects')->insert([
        'name' => $name,
        'slug' => $slug,
        'type' => 'GALXY',
        'con' => 'LEO',
        'ra' => $ra,
        'decl' => $decl,
        'mag' => $mag,
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
        'SBObj' => 12.9,
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

it('draws the star chart of the values in the edit form', function () {
    insertPreviewTestObject('M 65', 'm-65', 11.3155, 13.0922);
    $admin = $this->createUserAndAssignToTeam('Administrators');

    $response = $this->actingAs($admin)->get(route('object.edit.preview-chart', [
        'slug' => 'm-65',
        'ra' => '11 18 55.8',
        'decl' => '13 05 32',
        'type' => 'GALXY',
        'diam1' => '10',
        'diam2' => '3',
        'pa' => '174',
    ]));

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toStartWith('image/svg+xml');
    expect($response->getContent())->toStartWith('<svg')->toContain('M 65');

    // Nothing is saved
    expect((float) DB::table('objects')->where('name', 'M 65')->value('ra'))->toBe(11.3155);
});

it('shows the preview on the edit page with the saved position and size', function () {
    insertPreviewTestObject('M 65', 'm-65', 11.3155, 13.0922);
    $admin = $this->createUserAndAssignToTeam('Administrators');

    $this->actingAs($admin)->get(route('object.edit', ['slug' => 'm-65']))
        ->assertOk()
        ->assertSee('id="preview-chart"', false)
        ->assertSee(route('object.edit.preview-chart', ['slug' => 'm-65']), false)
        ->assertSee('data-saved=\'{"ra":11.3155,"decl":13.0922,"diam1":10,"diam2":3,"pa":174}\'', false);
});

it('refuses the preview chart to observers', function () {
    insertPreviewTestObject('M 65', 'm-65', 11.3155, 13.0922);
    $observer = $this->createUserAndAssignToTeam('Observers');

    $this->actingAs($observer)
        ->get(route('object.edit.preview-chart', ['slug' => 'm-65', 'ra' => '11.3', 'decl' => '13']))
        ->assertForbidden();
});

it('refuses the preview chart for invalid coordinates', function () {
    insertPreviewTestObject('M 65', 'm-65', 11.3155, 13.0922);
    $admin = $this->createUserAndAssignToTeam('Administrators');

    $this->actingAs($admin)
        ->get(route('object.edit.preview-chart', ['slug' => 'm-65', 'ra' => '11.3', 'decl' => 'north']))
        ->assertStatus(422);
    $this->actingAs($admin)
        ->get(route('object.edit.preview-chart', ['slug' => 'm-65', 'ra' => '11.3', 'decl' => '95']))
        ->assertStatus(422);
});

it('plots a target with unsaved changes at its new position', function () {
    insertPreviewTestObject('M 65', 'm-65', 11.3155, 13.0922);
    insertPreviewTestObject('NGC 1', 'ngc-1', 0.1, 27.7);

    // M 65 moved in the form to the position of NGC 1, two hemispheres away
    $target = DeepskyObject::where('name', 'M 65')->firstOrFail();
    $target->fill(['ra' => 0.11, 'decl' => 27.72, 'diam1' => 120.0]);

    $chart = app(StarChartService::class)->build($target, 0.11 * 15.0, 27.72, 30);

    $plotted = $chart['objects']->firstWhere('name', 'M 65');
    expect($plotted)->not->toBeNull()
        ->and($plotted['is_target'])->toBeTrue()
        ->and($plotted['ra_degrees'])->toEqualWithDelta(1.65, 1e-6)
        ->and($plotted['decl_degrees'])->toEqualWithDelta(27.72, 1e-6)
        ->and($plotted['diam1'])->toBe(120.0);
    expect($chart['objects']->where('name', 'M 65'))->toHaveCount(1);
    expect($chart['objects']->pluck('name'))->toContain('NGC 1');
});

it('reads a declination of -00 30 00 as negative', function () {
    insertPreviewTestObject('M 65', 'm-65', 11.3155, 13.0922);
    $admin = $this->createUserAndAssignToTeam('Administrators');

    $this->actingAs($admin)->put(route('object.update', ['slug' => 'm-65']), [
        'name' => 'M 65',
        'ra' => '11.3155',
        'decl' => '-00 30 00',
        'type' => 'GALXY',
    ])->assertSessionHasNoErrors();

    expect((float) DB::table('objects')->where('name', 'M 65')->value('decl'))->toBe(-0.5);
});
