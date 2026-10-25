<?php

use App\Livewire\ObjectReviewTable;
use App\Models\DeepskyObject;
use App\Services\DeepskyObjectCreator;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

// Administrators and database experts review the objects (#526): new objects
// start as new, the edit form sets the status and a note, and
// /admin/object-reviews lists the objects per status.

function insertReviewTestObject(string $name, ?string $status = null, ?string $note = null): void
{
    DB::table('deepskytypes')->insertOrIgnore(['code' => 'GALXY', 'name' => 'Galaxy']);
    DB::table('objects')->insert([
        'name' => $name,
        'slug' => \Illuminate\Support\Str::slug($name),
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
        'review_status' => $status,
        'review_note' => $note,
    ]);
}

it('marks a new object as new', function () {
    DB::table('deepskytypes')->insertOrIgnore(['code' => 'GALXY', 'name' => 'Galaxy']);

    $object = app(DeepskyObjectCreator::class)->create([
        'name' => 'Wdm 1', 'type' => 'GALXY', 'ra' => 11.3, 'decl' => 13.1,
    ]);

    expect($object->fresh()->review_status)->toBe(DeepskyObject::REVIEW_NEW)
        ->and($object->fresh()->reviewed_by)->toBeNull();
});

it('saves the review of the edit form with who reviewed it', function () {
    insertReviewTestObject('M 65', DeepskyObject::REVIEW_NEW);
    $admin = $this->createUserAndAssignToTeam('Administrators');

    $this->actingAs($admin)->put(route('object.update', ['slug' => 'm-65']), [
        'name' => 'M 65',
        'ra' => '11.3155',
        'decl' => '13.0922',
        'type' => 'GALXY',
        'review_status' => DeepskyObject::REVIEW_ATTENTION,
        'review_note' => '  Position is 2\' off  ',
    ])->assertSessionHasNoErrors();

    $object = DeepskyObject::find('M 65');
    expect($object->review_status)->toBe(DeepskyObject::REVIEW_ATTENTION)
        ->and($object->review_note)->toBe('Position is 2\' off')
        ->and($object->reviewed_by)->toBe($admin->id)
        ->and($object->reviewed_at)->not->toBeNull();
});

it('keeps the reviewer when the review does not change', function () {
    insertReviewTestObject('M 65', DeepskyObject::REVIEW_CHECKED, 'Checked with DSS');
    DB::table('objects')->where('name', 'M 65')->update(['reviewed_by' => 99, 'reviewed_at' => '2026-01-01 00:00:00']);
    $admin = $this->createUserAndAssignToTeam('Database Experts');

    $this->actingAs($admin)->put(route('object.update', ['slug' => 'm-65']), [
        'name' => 'M 65',
        'ra' => '11.3155',
        'decl' => '13.0922',
        'type' => 'GALXY',
        'mag' => '9.4',
        'review_status' => DeepskyObject::REVIEW_CHECKED,
        'review_note' => 'Checked with DSS',
    ])->assertSessionHasNoErrors();

    $object = DeepskyObject::find('M 65');
    expect($object->reviewed_by)->toBe(99)
        ->and($object->reviewed_at->toDateString())->toBe('2026-01-01');
});

it('refuses an unknown review status', function () {
    insertReviewTestObject('M 65');
    $admin = $this->createUserAndAssignToTeam('Administrators');

    $this->actingAs($admin)->put(route('object.update', ['slug' => 'm-65']), [
        'name' => 'M 65',
        'ra' => '11.3155',
        'decl' => '13.0922',
        'type' => 'GALXY',
        'review_status' => 'ok',
    ])->assertSessionHasErrors('review_status');
});

it('counts the objects per review status', function () {
    insertReviewTestObject('M 65', DeepskyObject::REVIEW_NEW);
    insertReviewTestObject('M 66', DeepskyObject::REVIEW_NEW);
    insertReviewTestObject('NGC 3628', DeepskyObject::REVIEW_ATTENTION);
    insertReviewTestObject('NGC 3593');

    expect(DeepskyObject::reviewCounts())->toBe([
        'new' => 2, 'checked' => 0, 'attention' => 1, 'none' => 1,
    ]);
});

it('shows the review page to database experts', function () {
    insertReviewTestObject('M 65', DeepskyObject::REVIEW_NEW);
    $expert = $this->createUserAndAssignToTeam('Database Experts');

    $this->actingAs($expert)->get(route('admin.object-reviews'))
        ->assertOk()
        ->assertSee('Object reviews')
        ->assertSeeLivewire(ObjectReviewTable::class);
});

it('refuses the review page to observers', function () {
    $observer = $this->createUserAndAssignToTeam('Observers');

    $this->actingAs($observer)->get(route('admin.object-reviews'))->assertForbidden();
});

it('lists the objects of a status and marks them as checked', function () {
    insertReviewTestObject('M 65', DeepskyObject::REVIEW_NEW);
    insertReviewTestObject('M 66', DeepskyObject::REVIEW_ATTENTION, 'Size?');
    insertReviewTestObject('NGC 3628', DeepskyObject::REVIEW_NEW);
    insertReviewTestObject('NGC 3593');
    $admin = $this->createUserAndAssignToTeam('Administrators');
    $this->actingAs($admin);

    Livewire::test(ObjectReviewTable::class, ['status' => 'new'])
        ->assertSee('M 65')
        ->assertSee('NGC 3628')
        ->assertDontSee('NGC 3593')
        ->call('markChecked', 'M 65');

    expect(DeepskyObject::find('M 65')->review_status)->toBe(DeepskyObject::REVIEW_CHECKED)
        ->and(DeepskyObject::find('M 65')->reviewed_by)->toBe($admin->id);

    Livewire::test(ObjectReviewTable::class, ['status' => 'attention'])
        ->set('checkboxValues', ['M 66'])
        ->call('markSelectedChecked');

    $object = DeepskyObject::find('M 66');
    expect($object->review_status)->toBe(DeepskyObject::REVIEW_CHECKED)
        ->and($object->review_note)->toBe('Size?');
    expect(DeepskyObject::find('NGC 3628')->review_status)->toBe(DeepskyObject::REVIEW_NEW);
});

it('does not let observers mark objects as checked', function () {
    insertReviewTestObject('M 65', DeepskyObject::REVIEW_NEW);
    $this->actingAs($this->createUserAndAssignToTeam('Observers'));

    Livewire::test(ObjectReviewTable::class, ['status' => 'new'])
        ->call('markChecked', 'M 65')
        ->assertForbidden();

    expect(DeepskyObject::find('M 65')->review_status)->toBe(DeepskyObject::REVIEW_NEW);
});
