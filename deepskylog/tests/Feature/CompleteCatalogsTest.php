<?php

use App\Livewire\CompleteCatalogs;
use App\Models\CompleteCatalog;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\Support\ObservationImportFixtures as Fixtures;

// Observers can't add objects to a complete catalog (#754); administrators and
// database experts can, and manage the list on /admin/complete-catalogs.

function storeCompleteCatalogObject($test, $user, string $name)
{
    DB::table('deepskytypes')->insertOrIgnore(['code' => 'GALXY', 'name' => 'Galaxy']);

    return $test->actingAs($user)->post(route('object.store'), [
        'name' => $name,
        'ra' => '11 18 55.8',
        'decl' => '13 05 32',
        'type' => 'GALXY',
    ]);
}

it('starts with the catalogs known to be complete', function () {
    expect(CompleteCatalog::codes())->toBe(['Arp', 'Caldwell', 'H400', 'H400-II', 'Hickson', 'IC', 'M', 'NGC']);
});

it('finds the complete catalog of a name', function (string $name, ?string $catalog) {
    expect(CompleteCatalog::catalogOf($name))->toBe($catalog);
})->with([
    ['NGC 9999', 'NGC'],
    ['ngc9999', 'NGC'],
    ['M 111', 'M'],
    ['H400-II 5', 'H400-II'],
    ['H400 5', 'H400'],
    ['Hickson 17B', 'Hickson'],
    ['Mrk 1', null],
    ['Messier', null],
    ['NGC', null],
    ['Wdm 1', null],
]);

it('refuses a new object in a complete catalog for an observer', function () {
    $observer = $this->createUserAndAssignToTeam('Observers');

    $this->actingAs($observer)
        ->get(route('object.nameSearch', ['catalog' => 'NGC', 'number' => '9999']))
        ->assertRedirect(route('object.create'))
        ->assertSessionHasErrors(['catalog' => 'The NGC catalog is complete: all its objects are already in DeepskyLog. Check the name of the object.']);

    storeCompleteCatalogObject($this, $observer, 'NGC 9999')
        ->assertRedirect(route('object.create'))
        ->assertSessionHasErrors('catalog');

    expect(DB::table('objects')->where('name', 'NGC 9999')->exists())->toBeFalse();
});

it('names the object an observer looks for when it is in a complete catalog already', function () {
    $observer = $this->createUserAndAssignToTeam('Observers');
    DB::table('objectnames')->insert([
        'objectname' => 'Arp 321', 'catalog' => 'Hickson', 'catindex' => '40',
        'altname' => 'Hickson 40', 'slug' => 'hickson-40', 'timestamp' => now(),
    ]);

    $this->actingAs($observer)
        ->get(route('object.nameSearch', ['catalog' => 'Hickson', 'number' => '40']))
        ->assertSessionHasErrors(['catalog' => 'Hickson 40 is already in DeepskyLog as Arp 321.']);
});

it('lets an observer add an object to another catalog', function () {
    $observer = $this->createUserAndAssignToTeam('Observers');

    storeCompleteCatalogObject($this, $observer, 'Wdm 1')->assertSessionHasNoErrors();

    expect(DB::table('objects')->where('name', 'Wdm 1')->exists())->toBeTrue();
});

it('leaves the complete catalogs out of the create form of an observer only', function (string $team, bool $listed) {
    DB::table('objectnames')->insert([
        ['objectname' => 'NGC 1', 'catalog' => 'NGC', 'catindex' => '1', 'altname' => 'NGC 1', 'slug' => 'ngc-1', 'timestamp' => now()],
        ['objectname' => 'Mrk 1', 'catalog' => 'Mrk', 'catindex' => '1', 'altname' => 'Mrk 1', 'slug' => 'mrk-1', 'timestamp' => now()],
    ]);

    $this->actingAs($this->createUserAndAssignToTeam($team))
        ->get(route('object.create'))
        ->assertOk()
        ->assertViewHas('catalogs', fn ($catalogs) => $catalogs->contains('Mrk') && $catalogs->contains('NGC') === $listed);
})->with([
    ['Observers', false],
    ['Database Experts', true],
]);

it('lets a database expert add an object to a complete catalog after a warning', function () {
    $expert = $this->createUserAndAssignToTeam('Database Experts');

    $this->actingAs($expert)
        ->get(route('object.nameSearch', ['catalog' => 'NGC', 'number' => '9999']))
        ->assertOk()
        ->assertSee('Only add NGC 9999 if it is missing by mistake.');

    storeCompleteCatalogObject($this, $expert, 'NGC 9999')->assertSessionHasNoErrors();

    expect(DB::table('objects')->where('name', 'NGC 9999')->exists())->toBeTrue();
});

it('adds no object in a complete catalog from the oal file of an observer', function () {
    Fixtures::seedTargets();
    $user = Fixtures::createObserver(['username' => 'example-observer']);
    $content = str_replace('Example Nebula 123', 'NGC 9999', Fixtures::content('oal/valid-new-targets.xml'));

    $result = Fixtures::import($user, 'oal/valid-new-targets.xml', content: $content);

    expect($result->newObjects)->toBe([])
        ->and(DB::table('objects')->where('name', 'NGC 9999')->exists())->toBeFalse()
        ->and(implode("\n", Fixtures::messages($result, 'error')))
        ->toContain("Target 'NGC 9999' is not in the DeepskyLog database, although the NGC catalog is complete in DeepskyLog");
});

it('lets only administrators and database experts manage the complete catalogs', function (string $team, int $status) {
    $this->actingAs($this->createUserAndAssignToTeam($team))
        ->get(route('admin.complete-catalogs'))
        ->assertStatus($status);
})->with([
    ['Observers', 403],
    ['Database Experts', 200],
    ['Administrators', 200],
]);

it('marks a catalog as complete and removes it again', function () {
    DB::table('objectnames')->insert([
        'objectname' => 'Mrk 1', 'catalog' => 'Mrk', 'catindex' => '1', 'altname' => 'Mrk 1', 'slug' => 'mrk-1', 'timestamp' => now(),
    ]);
    $this->actingAs($this->createUserAndAssignToTeam('Database Experts'));

    Livewire::test(CompleteCatalogs::class)
        ->set('catalog', 'Mrk')
        ->call('add')
        ->assertHasNoErrors();
    expect(CompleteCatalog::catalogOf('Mrk 2'))->toBe('Mrk');

    Livewire::test(CompleteCatalogs::class)
        ->set('catalog', 'Unknown')
        ->call('add')
        ->assertHasErrors('catalog');

    Livewire::test(CompleteCatalogs::class)
        ->call('remove', CompleteCatalog::where('catalog', 'Mrk')->value('id'));
    expect(CompleteCatalog::catalogOf('Mrk 2'))->toBeNull();
});

it('refuses the management actions to an observer', function () {
    $this->actingAs($this->createUserAndAssignToTeam('Observers'));

    Livewire::test(CompleteCatalogs::class)->assertForbidden();
});
