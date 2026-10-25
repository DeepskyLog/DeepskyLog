<?php

use App\Livewire\ObjectImport;
use App\Services\Import\ObjectCsvImport;
use App\Services\ObjectSearchIndex;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;

// Administrators and database experts check a CSV file of objects against
// DeepskyLog and import it on /admin/objects/import (#131, #136).

function csvImportObject(string $name, string $type, float $ra, float $decl, array $values = [], array $altnames = []): void
{
    DB::table('objects')->insert(array_merge([
        'name' => $name,
        'slug' => Str::slug($name),
        'type' => $type,
        'con' => 'PEG',
        'ra' => $ra,
        'decl' => $decl,
        'mag' => 99.9,
        'subr' => 99.9,
        'pa' => 999,
        'urano' => 0,
        'urano_new' => 0,
        'sky' => 0,
        'millenium' => '',
        'diam1' => 0.0,
        'diam2' => 0.0,
        'datasource' => null,
        'taki' => '',
        'SBObj' => -999,
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
    ], $values));

    foreach ([$name, ...$altnames] as $altname) {
        [$catalog, $index] = App\Models\ObjectName::splitCatalogDesignation($altname);
        DB::table('objectnames')->insert([
            'objectname' => $name,
            'catalog' => $catalog,
            'catindex' => $index,
            'altname' => $altname,
            'slug' => Str::slug($altname),
            'timestamp' => now(),
        ]);
    }
    ObjectSearchIndex::syncObject($name);
}

/**
 * The checked rows of a file, keyed by name.
 */
function csvImportCheck(string $csv, array $settings = []): array
{
    $import = new ObjectCsvImport();
    $read = $import->read($csv);
    expect($read['errors'])->toBe([]);

    return collect($import->check($read['rows'], $settings))->keyBy('name')->all();
}

beforeEach(function () {
    DB::table('deepskytypes')->insertOrIgnore([
        ['code' => 'GALXY', 'name' => 'Galaxy'],
        ['code' => 'OPNCL', 'name' => 'Open cluster'],
    ]);
    // NGC 7331: 22h37m04.1s +34°24'56"
    csvImportObject('NGC 7331', 'GALXY', 22.617806, 34.415556, ['mag' => 9.5, 'diam1' => 630, 'diam2' => 222, 'pa' => 171], ['UGC 12113']);
    csvImportObject('NGC 7320', 'GALXY', 22.600969, 33.94844);
});

it('reads the columns of the file by their header, with any separator', function () {
    $import = new ObjectCsvImport();

    $read = $import->read("\xEF\xBB\xBFObject;Dec;RA;Magnitude;unknown\nNGC 1;+27 42 29;00 07 15.8;12,9;x\n\n");
    expect($read['errors'])->toBe([])
        ->and($read['rows'])->toBe([['line' => 2, 'name' => 'NGC 1', 'decl' => '+27 42 29', 'ra' => '00 07 15.8', 'mag' => '12,9']]);

    expect($import->read("name\ttype\nNGC 1\tGALXY")['rows'][0])->toBe(['line' => 2, 'name' => 'NGC 1', 'type' => 'GALXY']);
    expect($import->read("type,ra\nGALXY,1")['errors'])->not->toBe([]);
    expect($import->read("name,type\n")['errors'])->not->toBe([]);
});

it('reads positions in hours, degrees and sexagesimal notation', function (string $ra, string $decl) {
    $row = csvImportCheck("name,type,ra,decl\nWdm 1,OPNCL,{$ra},{$decl}")['Wdm 1'];

    expect($row['errors'])->toBe([])
        ->and($row['values']['ra'])->toEqualWithDelta(5.5875, 1e-4)
        ->and($row['values']['decl'])->toEqualWithDelta(-5.391111, 1e-4);
})->with([
    ['5.5875', '-5.391111'],
    ['83.8125', '-5.391111'],
    ['05 35 15', '-05 23 28'],
    ['05:35:15', '-05:23:28'],
    ['05h35m15s', '-05°23\'28"'],
    ['05h35.25m', '−05°23.4667\''],
]);

it('lists the fields of a recognised object that differ from the file', function () {
    $rows = csvImportCheck(<<<'CSV'
        name,type,ra,decl,mag,subr,diam1,diam2,pa,alternative_names
        NGC7331,GALXY,22 37 04.1,+34 24 56,9.55,13.1,10.5,4.0,175,Caldwell 30;UGC 12113
        NGC 7320,OPNCL,22 36 05,+33 56 53,12.6,,2.2,1.1,,
        CSV);

    // Within the tolerances, but the surface brightness is missing in DeepskyLog
    expect($rows['NGC7331']['status'])->toBe('recognised')
        ->and($rows['NGC7331']['object'])->toBe('NGC 7331')
        ->and(array_keys($rows['NGC7331']['differences']))->toBe(['subr'])
        // NGC7331 is a spelling of NGC 7331, UGC 12113 is its name already
        ->and($rows['NGC7331']['names'])->toBe(['Caldwell 30']);

    expect(array_keys($rows['NGC 7320']['differences']))->toBe(['position', 'type', 'mag', 'diam1', 'diam2'])
        ->and($rows['NGC 7320']['differences']['position']['difference'])->toBe('18.9"');

    $strict = csvImportCheck("name,mag,diam1\nNGC 7331,9.55,11", ['magnitude' => 0.01, 'size' => 1]);
    expect($strict['NGC 7331']['differences']['mag']['difference'])->toBe('+0.05')
        ->and($strict['NGC 7331']['differences']['diam1']['difference'])->toBe('+5%');
});

it('recognises an object by an alternative name of the file', function () {
    $row = csvImportCheck("name,alternative_names\nArp 999,UGC 12113")['Arp 999'];

    expect($row['status'])->toBe('recognised')
        ->and($row['object'])->toBe('NGC 7331')
        ->and($row['names'])->toBe(['Arp 999'])
        ->and($row['warnings'])->toBe(['Recognised by its alternative name UGC 12113.']);
});

it('finds the possible duplicates of the new objects', function () {
    $rows = csvImportCheck(<<<'CSV'
        name,type,ra,decl,diam1
        Foo 1,GALXY,22 37 05,+34 25 10,
        Foo 2,OPNCL,22 37 05,+34 25 10,
        Foo 3,GALXY,22 37 30,+34 25 10,
        Foo 4,GALXY,22 37 30,+34 25 10,12
        CSV);

    expect($rows['Foo 1']['status'])->toBe('duplicate')
        ->and($rows['Foo 1']['suggestion'])->toBe('NGC 7331')
        // Another type, or too far away
        ->and($rows['Foo 2']['status'])->toBe('new')
        ->and($rows['Foo 2']['nearby'][0]['name'])->toBe('NGC 7331')
        ->and($rows['Foo 3']['status'])->toBe('new')
        // An object that overlaps it
        ->and($rows['Foo 4']['status'])->toBe('duplicate');
});

it('does not import invalid rows', function () {
    $rows = csvImportCheck(<<<'CSV'
        name,type,ra,decl,mag
        Foo 1,,1,1,
        Foo 2,XXX,1,1,
        Foo 3,GALXY,25 00 00,1,
        Foo 4,GALXY,1,,
        Foo 5,GALXY,1,1,bright
        Foo 6,GALXY,1,1,
        Foo 6,GALXY,2,2,
        ,GALXY,1,1,
        NGC 9999,GALXY,3,3,
        CSV);

    expect($rows['Foo 1']['errors'])->toBe(['A new object needs a type.'])
        ->and($rows['Foo 2']['errors'])->toBe(['Unknown object type XXX.'])
        ->and($rows['Foo 3']['errors'])->toBe(['Invalid right ascension 25 00 00.'])
        ->and($rows['Foo 4']['errors'])->toBe(['A new object needs a right ascension and a declination.'])
        ->and($rows['Foo 5']['errors'])->toBe(['Invalid mag bright.'])
        ->and($rows['Foo 6']['errors'])->toBe(['The name is also on line 7 of the file.'])
        ->and($rows['']['errors'])->toBe(['The object has no name.'])
        ->and(collect($rows)->except('NGC 9999')->pluck('status')->unique()->all())->toBe(['invalid'])
        // An object in a complete catalog only gets a warning: only administrators and database experts import
        ->and($rows['NGC 9999']['status'])->toBe('new')
        ->and($rows['NGC 9999']['warnings'])->toBe(['The NGC catalog is complete in DeepskyLog: check the name of the object.']);
});

it('adds the new objects, names and differing fields as chosen', function () {
    $csv = <<<'CSV'
        name,type,ra,decl,mag,diam1,diam2,pa,alternative_names
        Wdm 1,OPNCL,05 35 15,-05 23 28,8.1,3,,,Wdm-A|Foo 9
        Arp 999,GALXY,22 37 05,+34 25 10,,,,,
        NGC 7331,GALXY,22 37 04.1,+34 24 56,9.0,10.5,3.7,171,Caldwell 30
        CSV;
    $import = new ObjectCsvImport();
    $rows = $import->check($import->read($csv)['rows']);

    $counts = $import->apply($rows, ['create' => true, 'duplicates' => 'alias', 'names' => true, 'update' => true, 'datasource' => 'Test catalog']);

    expect($counts)->toBe(['created' => 1, 'names' => 4, 'updated' => 1, 'skipped' => 0]);

    $new = DB::table('objects')->where('name', 'Wdm 1')->first();
    expect($new->type)->toBe('OPNCL')
        ->and((float) $new->ra)->toEqualWithDelta(5.5875, 1e-4)
        ->and((float) $new->diam1)->toEqual(180.0)
        ->and($new->datasource)->toBe('Test catalog')
        ->and($new->con)->toBe('ORI');
    expect(DB::table('objectnames')->where('objectname', 'Wdm 1')->orderBy('altname')->pluck('altname')->all())->toBe(['Foo 9', 'Wdm 1', 'Wdm-A']);

    // The possible duplicate became a name of NGC 7331, as did the new name of NGC 7331
    expect(DB::table('objects')->where('name', 'Arp 999')->exists())->toBeFalse()
        ->and(DB::table('objectnames')->where('objectname', 'NGC 7331')->orderBy('altname')->pluck('altname')->all())
        ->toBe(['Arp 999', 'Caldwell 30', 'NGC 7331', 'UGC 12113'])
        ->and(DB::table('search_index')->where('name', 'Arp 999')->value('source_pk'))->toBe('ngc-7331');

    $updated = DB::table('objects')->where('name', 'NGC 7331')->first();
    expect((float) $updated->mag)->toEqual(9.0)
        ->and((float) $updated->diam1)->toEqual(630.0)
        ->and((float) $updated->diam2)->toEqual(222.0)
        ->and((int) $updated->pa)->toBe(171);
});

it('leaves the objects alone unless asked', function () {
    $csv = "name,type,ra,decl,mag,alternative_names\nWdm 1,OPNCL,1,1,,\nArp 999,GALXY,22 37 05,+34 25 10,,\nNGC 7331,GALXY,22 37 04.1,+34 24 56,8.0,Caldwell 30";
    $import = new ObjectCsvImport();
    $rows = $import->check($import->read($csv)['rows']);

    $names = DB::table('objectnames')->count();

    $counts = $import->apply($rows, ['create' => false, 'duplicates' => 'skip', 'names' => false, 'update' => false]);

    expect($counts)->toBe(['created' => 0, 'names' => 0, 'updated' => 0, 'skipped' => 2])
        ->and(DB::table('objects')->count())->toBe(2)
        ->and(DB::table('objectnames')->count())->toBe($names)
        ->and((float) DB::table('objects')->where('name', 'NGC 7331')->value('mag'))->toEqual(9.5);
});

it('only lets administrators and database experts import objects', function (string $team, int $status) {
    $this->actingAs($this->createUserAndAssignToTeam($team))
        ->get(route('admin.objects.import'))
        ->assertStatus($status);
})->with([
    ['Observers', 403],
    ['Database Experts', 200],
    ['Administrators', 200],
]);

it('checks and imports a file on the page, with a report for the one who checked it', function () {
    $expert = $this->createUserAndAssignToTeam('Database Experts');
    $file = UploadedFile::fake()->createWithContent('my-catalog.csv', "name,type,ra,decl\nWdm 1,OPNCL,1,1\nNGC 7331,GALXY,1,1\nFoo,XXX,1,1");

    $response = $this->actingAs($expert)->post(route('admin.objects.import.check'), [
        'file' => $file, 'position' => 10, 'magnitude' => 0.1, 'size' => 10, 'duplicate' => 30,
    ]);
    $token = Str::after($response->headers->get('Location'), 'token=');
    $response->assertRedirect(route('admin.objects.import', ['token' => $token]));

    $this->get(route('admin.objects.import.report', ['token' => $token]))
        ->assertOk()
        ->assertDownload('my-catalog-report.csv');

    $component = Livewire::actingAs($expert)->withQueryParams(['token' => $token])->test(ObjectImport::class)
        ->assertSet('show', 'invalid')
        ->assertSet('datasource', 'my-catalog')
        ->assertSee('Unknown object type XXX.')
        ->call('import')
        ->assertSet('token', null)
        ->assertSet('applied', ['created' => 1, 'names' => 0, 'updated' => 0, 'skipped' => 1])
        ->assertSee('1 object added');

    expect(DB::table('objects')->where('name', 'Wdm 1')->value('datasource'))->toBe('my-catalog');

    // Imported once
    $this->get(route('admin.objects.import.report', ['token' => $token]))->assertNotFound();
});

it('keeps a checked file for the one who checked it', function () {
    $this->actingAs($this->createUserAndAssignToTeam('Database Experts'));
    Cache::put(ObjectImport::CACHE_PREFIX . 'abc', ['user_id' => 999, 'file' => 'x.csv', 'rows' => []], now()->addHour());

    $this->get(route('admin.objects.import.report', ['token' => 'abc']))->assertNotFound();
    $this->get(route('admin.objects.import', ['token' => 'abc']))
        ->assertOk()
        ->assertSee('The checked file is no longer available. Check it again.');
});

it('reports a file it cannot read', function () {
    $this->actingAs($this->createUserAndAssignToTeam('Administrators'))
        ->from(route('admin.objects.import'))
        ->post(route('admin.objects.import.check'), [
            'file' => UploadedFile::fake()->createWithContent('objects.csv', "type,ra\nGALXY,1"),
            'position' => 10, 'magnitude' => 0.1, 'size' => 10, 'duplicate' => 30,
        ])
        ->assertRedirect(route('admin.objects.import'))
        ->assertSessionHasErrors(['file' => 'The first line of the file has to name the columns, and one of them has to be name.']);
});
