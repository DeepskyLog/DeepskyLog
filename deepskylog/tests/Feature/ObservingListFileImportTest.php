<?php

use App\Models\ObservingList;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/*
 * The "Import objects" form of an observing list page
 * (POST /observing-list/{list}/import, ObservingListFileImportService):
 * deep-sky objects, comets, planets and lunar features in every format the
 * form accepts (.txt, .argo, .skylist, .apd, .csv).
 */

function olfiIndex(string $name, string $table, string $pk, string $type): void
{
    DB::table('search_index')->insert([
        'name' => $name, 'name_normalized' => mb_strtolower($name), 'source_table' => $table,
        'source_pk' => $pk, 'display_name' => $name, 'source_type' => $type,
        'created_at' => now(), 'updated_at' => now(),
    ]);
}

function olfiDeepsky(string $name, array $aliases = [], string $type = 'GALXY'): void
{
    $slug = str($name)->slug()->toString();
    DB::table('objects')->insert([
        'name' => $name, 'slug' => $slug, 'type' => $type, 'con' => 'AND', 'ra' => 0.71, 'decl' => 41.27,
        'mag' => 3.4, 'subr' => 13.5, 'pa' => 35, 'urano' => 0, 'urano_new' => 0, 'sky' => 0, 'millenium' => '',
        'diam1' => 10000.0, 'diam2' => 3000.0, 'datasource' => null, 'taki' => '', 'SBObj' => 0.0, 'description' => '',
        'psa' => '', 'torresB' => '', 'torresBC' => '', 'torresC' => '', 'milleniumbase' => '0', 'DSLDL' => '0',
        'DSLDP' => '0', 'DSLLL' => '0', 'DSLLP' => '0', 'DSLOL' => '0', 'DSLOP' => '0', 'DeepskyHunter' => '0',
        'Interstellarum' => '0', 'timestamp' => now(),
    ]);
    olfiIndex($name, 'objects', $slug, $type);
    foreach ($aliases as $alias) {
        olfiIndex($alias, 'objects', $slug, 'alias');
    }
}

/**
 * The search_index rows `search:reindex` writes for the other targets: the
 * resolver only looks at those, not at the source tables themselves.
 */
function olfiCatalogue(): void
{
    olfiDeepsky('M 31', ['NGC 224', 'Andromeda Galaxy']);
    olfiDeepsky('NGC 7000', ['North America Nebula'], 'EMINB');
    olfiDeepsky('NGC 1275');
    olfiDeepsky('B 33', ['Horsehead Nebula'], 'DRKNB');
    olfiDeepsky('Sh 2-91', [], 'SNREM');

    foreach (['1P/Halley', '12P/Pons-Brooks', 'C/2020 F3 (NEOWISE)', 'C/2023 A3 (Tsuchinshan-ATLAS)'] as $i => $comet) {
        olfiIndex($comet, 'cometobjects', (string) ($i + 1), 'comet');
    }
    foreach (['Mercury', 'Mars', 'Jupiter', 'Saturn', 'Neptune'] as $i => $planet) {
        olfiIndex($planet, 'planets', (string) ($i + 1), 'planet');
    }
    foreach (['Copernicus' => 'crater', 'Tycho' => 'crater', 'Mare Imbrium' => 'mare', 'Montes Apenninus' => 'mountain range', 'Rupes Recta' => 'scarp'] as $i => $type) {
        olfiIndex($i, 'lunar_features', (string) crc32($i), $type);
    }
}

function olfiList(User $owner): ObservingList
{
    return ObservingList::create(['owner_user_id' => $owner->id, 'name' => 'Test list', 'public' => true]);
}

function olfiItems(ObservingList $list): array
{
    return $list->items()->pluck('object_name')->sort()->values()->all();
}

/** A temporary file, removed when the test run ends. */
function olfiTempFile(): string
{
    $path = tempnam(sys_get_temp_dir(), 'apd');
    register_shutdown_function(fn () => @unlink($path));

    return $path;
}

/**
 * An AstroPlanner .apd file: an SQLite database with an Objects table.
 */
function olfiApd(array $names): UploadedFile
{
    $path = olfiTempFile();
    $db = new PDO('sqlite:' . $path);
    $db->exec('CREATE TABLE Objects (ID INTEGER PRIMARY KEY, Name TEXT, RA REAL, Dec REAL, Type TEXT)');
    $insert = $db->prepare('INSERT INTO Objects (Name, RA, Dec, Type) VALUES (?, 0, 0, ?)');
    foreach ($names as $name) {
        $insert->execute([$name, 'Galaxy']);
    }
    $db = null;

    return new UploadedFile($path, 'list.apd', null, null, true);
}

function olfiImport($test, User $owner, ObservingList $list, UploadedFile $file)
{
    return $test->actingAs($owner)->post(route('observing-list.import', $list), ['file' => $file]);
}

/** Every kind of target, with the name it must be stored under. */
const OLFI_EXPECTED = [
    '1P/Halley', '12P/Pons-Brooks', 'C/2020 F3 (NEOWISE)', 'C/2023 A3 (Tsuchinshan-ATLAS)',
    'Copernicus', 'Jupiter', 'M 31', 'Mare Imbrium', 'Mars', 'Montes Apenninus',
    'NGC 1275', 'NGC 7000', 'Saturn', 'Sh 2-91', 'Tycho',
];

beforeEach(function () {
    olfiCatalogue();
    $this->owner = User::factory()->create();
    $this->list = olfiList($this->owner);
});

it('imports every kind of object from a plain text file', function () {
    $content = implode("\n", [
        'M 31', 'NGC 7000', 'NGC 1275', 'Sh 2-91',
        '1P/Halley', '12P/Pons-Brooks', 'C/2020 F3 (NEOWISE)', 'C/2023 A3 (Tsuchinshan-ATLAS)',
        'Mars', 'Jupiter', 'Saturn',
        'Copernicus', 'Tycho', 'Mare Imbrium', 'Montes Apenninus',
    ]) . "\n";

    olfiImport($this, $this->owner, $this->list, UploadedFile::fake()->createWithContent('list.txt', $content))
        ->assertSessionHas('success', '15 object(s) imported, 0 skipped.');

    $sorted = OLFI_EXPECTED;
    sort($sorted);
    expect(olfiItems($this->list))->toBe($sorted);
    expect($this->list->items()->where('added_by_user_id', $this->owner->id)->where('source_mode', 'manual')->count())->toBe(15);
});

it('imports a text file with Windows line endings, blank lines and stray whitespace', function () {
    $content = "  M31  \r\n\r\nC/2020 F3 (NEOWISE)\r\n\tJupiter\r\nMare   Imbrium\r\n\r\n";

    olfiImport($this, $this->owner, $this->list, UploadedFile::fake()->createWithContent('list.txt', $content))
        ->assertSessionHas('success', '4 object(s) imported, 0 skipped.');

    expect(olfiItems($this->list))->toBe(['C/2020 F3 (NEOWISE)', 'Jupiter', 'M 31', 'Mare Imbrium']);
});

it('imports an Argo Navis file as DeepskyLog exports it', function () {
    $content = implode("\n", [
        'DSL M 31|00:42:44|+41:16:09|GALAXY|3.4|190.0\'x60.0\' @ 35;CR 1.20;56x',
        'DSL NGC 7000|20:58:48|+44:19:59|NEBULA|4|120.0\'x100.0\';CR -',
        'DSL 1P/Halley|||USER||CR -',
        'DSL C/2023 A3 (Tsuchinshan-ATLAS)|||USER||CR -',
        'DSL Saturn|||USER||CR -',
        'DSL Neptune|||USER||CR -',
        'DSL Copernicus|||USER||CR -',
        'DSL Rupes Recta|||USER||CR -',
    ]) . "\n";

    olfiImport($this, $this->owner, $this->list, UploadedFile::fake()->createWithContent('list.argo', $content))
        ->assertSessionHas('success', '8 object(s) imported, 0 skipped.');

    expect(olfiItems($this->list))->toBe([
        '1P/Halley', 'C/2023 A3 (Tsuchinshan-ATLAS)', 'Copernicus', 'M 31', 'NGC 7000', 'Neptune', 'Rupes Recta', 'Saturn',
    ]);
});

it('imports an Argo Navis file written by Argo Navis itself, with its own catalogue spellings', function () {
    // Argo Navis writes "N1275" and "Barnard 33", without the "DSL " prefix.
    $content = "N1275|03:19:48|+41:30:42|GALAXY|11.9||\nBarnard 33|05:40:59|-02:27:30|DARK||6'x4'|\nM31|00:42:44|+41:16:09|GALAXY|3.4||\nJUPITER|||USER|||\n";

    olfiImport($this, $this->owner, $this->list, UploadedFile::fake()->createWithContent('list.argo', $content))
        ->assertSessionHas('success', '4 object(s) imported, 0 skipped.');

    expect(olfiItems($this->list))->toBe(['B 33', 'Jupiter', 'M 31', 'NGC 1275']);
});

it('imports a SkySafari observing list', function () {
    $blocks = [];
    foreach (['M 31' => '4,-1,-1', 'Sh2-091' => '4,-1,-1', 'Horsehead Nebula' => '4,-1,-1', '12P/Pons-Brooks' => '1,-1,-1',
        'C/2020 F3 (NEOWISE)' => '1,-1,-1', 'Mercury' => '1,-1,-1', 'Mars' => '1,-1,-1', 'Tycho' => '1,-1,-1',
        'Montes Apenninus' => '1,-1,-1'] as $name => $id) {
        $blocks[] = "SkyObject=BeginObject\n   ObjectID={$id}\n   CatalogNumber={$name}\nEndObject=SkyObject";
    }
    $content = "SkySafariObservingListVersion=3.0\n" . implode("\n\n", $blocks) . "\n";

    olfiImport($this, $this->owner, $this->list, UploadedFile::fake()->createWithContent('list.skylist', $content))
        ->assertSessionHas('success', '9 object(s) imported, 0 skipped.');

    expect(olfiItems($this->list))->toBe([
        '12P/Pons-Brooks', 'B 33', 'C/2020 F3 (NEOWISE)', 'M 31', 'Mars', 'Mercury', 'Montes Apenninus', 'Sh 2-91', 'Tycho',
    ]);
});

it('imports an AstroPlanner file', function () {
    $file = olfiApd(['NGC 224', 'North America Nebula', '1P/Halley', 'C/2023 A3 (Tsuchinshan-ATLAS)', 'Jupiter', 'Saturn', 'Copernicus', 'Rupes Recta']);

    olfiImport($this, $this->owner, $this->list, $file)
        ->assertSessionHas('success', '8 object(s) imported, 0 skipped.');

    expect(olfiItems($this->list))->toBe([
        '1P/Halley', 'C/2023 A3 (Tsuchinshan-ATLAS)', 'Copernicus', 'Jupiter', 'M 31', 'NGC 7000', 'Rupes Recta', 'Saturn',
    ]);
});

it('imports a CSV file on its first column, with quoted names containing commas', function () {
    $content = implode("\n", [
        '"M 31",Andromeda,GALXY',
        'NGC7000,North America,EMINB',
        '"C/2023 A3 (Tsuchinshan-ATLAS)",comet,',
        '12P/Pons-Brooks,comet,',
        'Mercury,planet,',
        'Saturn,planet,',
        '"Mare Imbrium","Sea of Showers, the",mare',
        'Tycho,crater,',
    ]) . "\n";

    olfiImport($this, $this->owner, $this->list, UploadedFile::fake()->createWithContent('list.csv', $content))
        ->assertSessionHas('success', '8 object(s) imported, 0 skipped.');

    expect(olfiItems($this->list))->toBe([
        '12P/Pons-Brooks', 'C/2023 A3 (Tsuchinshan-ATLAS)', 'M 31', 'Mare Imbrium', 'Mercury', 'NGC 7000', 'Saturn', 'Tycho',
    ]);
});

it('ignores the byte order mark of a UTF-8 file', function (string $filename, string $content) {
    // Excel ("CSV UTF-8") and Notepad start the file with one.
    olfiImport($this, $this->owner, $this->list, UploadedFile::fake()->createWithContent($filename, "\xEF\xBB\xBF" . $content))
        ->assertSessionHas('success', '2 object(s) imported, 0 skipped.');

    expect(olfiItems($this->list))->toBe(['M 31', 'Mars']);
})->with([
    'txt' => ['list.txt', "M 31\nMars\n"],
    'csv' => ['list.csv', "M 31,galaxy\nMars,planet\n"],
    'argo' => ['list.argo', "DSL M 31|||GALAXY||\nDSL Mars|||USER||\n"],
    'skylist' => ['list.skylist', "SkySafariObservingListVersion=3.0\nSkyObject=BeginObject\n   CatalogNumber=M 31\nEndObject=SkyObject\n\nSkyObject=BeginObject\n   CatalogNumber=Mars\nEndObject=SkyObject\n"],
]);

it('stores names in any letter case under the canonical name', function () {
    $content = "m 31\nngc 1275\nc/2020 f3 (neowise)\n1p/halley\njupiter\nSATURN\ncopernicus\nMARE IMBRIUM\n";

    olfiImport($this, $this->owner, $this->list, UploadedFile::fake()->createWithContent('list.txt', $content))
        ->assertSessionHas('success', '8 object(s) imported, 0 skipped.');

    expect(olfiItems($this->list))->toBe([
        '1P/Halley', 'C/2020 F3 (NEOWISE)', 'Copernicus', 'Jupiter', 'M 31', 'Mare Imbrium', 'NGC 1275', 'Saturn',
    ]);
});

it('stores an object once when the file names it in several spellings', function () {
    $content = "M 31\nM31\nNGC 224\nAndromeda Galaxy\nJupiter\njupiter\n";

    olfiImport($this, $this->owner, $this->list, UploadedFile::fake()->createWithContent('list.txt', $content))
        ->assertSessionHas('success', '2 object(s) imported, 4 skipped.');

    expect(olfiItems($this->list))->toBe(['Jupiter', 'M 31']);
});

it('skips objects that are already on the list', function () {
    $content = "M 31\n1P/Halley\nMars\nTycho\n";

    olfiImport($this, $this->owner, $this->list, UploadedFile::fake()->createWithContent('list.txt', $content))
        ->assertSessionHas('success', '4 object(s) imported, 0 skipped.');
    olfiImport($this, $this->owner, $this->list, UploadedFile::fake()->createWithContent('list.csv', "M31\nMars\nCopernicus\n"))
        ->assertSessionHas('success', '1 object(s) imported, 2 skipped.');

    expect(olfiItems($this->list))->toBe(['1P/Halley', 'Copernicus', 'M 31', 'Mars', 'Tycho']);
});

it('imports the known objects and reports the unknown ones', function () {
    $content = "M 31\nNot an object\nC/2099 Z9 (Nobody)\nVulcan\nCopernicus\nCrater Nonexistent\n";

    olfiImport($this, $this->owner, $this->list, UploadedFile::fake()->createWithContent('list.txt', $content))
        ->assertSessionHas('warning', fn ($message) => str_starts_with($message, '2 object(s) imported, 4 skipped.')
            && str_contains($message, 'Unknown objects: Not an object, C/2099 Z9 (Nobody), Vulcan, Crater Nonexistent'));

    expect(olfiItems($this->list))->toBe(['Copernicus', 'M 31']);
});

it('lists at most twenty unknown objects in the warning', function () {
    $content = implode("\n", array_map(fn ($i) => "Unknown {$i}", range(1, 25))) . "\nMars\n";

    olfiImport($this, $this->owner, $this->list, UploadedFile::fake()->createWithContent('list.txt', $content))
        ->assertSessionHas('warning', fn ($message) => str_contains($message, 'Unknown 20 and 5 more')
            && ! str_contains($message, 'Unknown 21'));

    expect(olfiItems($this->list))->toBe(['Mars']);
});

it('imports nothing from an empty file', function () {
    olfiImport($this, $this->owner, $this->list, UploadedFile::fake()->createWithContent('list.txt', "\n\n"))
        ->assertSessionHas('success', '0 object(s) imported, 0 skipped.');

    expect($this->list->items()->count())->toBe(0);
});

it('reports an AstroPlanner file without an Objects table', function () {
    $path = olfiTempFile();
    $db = new PDO('sqlite:' . $path);
    $db->exec('CREATE TABLE Other (Name TEXT)');
    $db = null;

    olfiImport($this, $this->owner, $this->list, new UploadedFile($path, 'list.apd', null, null, true))
        ->assertSessionHas('warning', fn ($message) => str_contains($message, 'Objects table not found'));

    expect($this->list->items()->count())->toBe(0);
});

it('rejects a file in an unsupported format', function () {
    olfiImport($this, $this->owner, $this->list, UploadedFile::fake()->createWithContent('list.xml', "<objects><object>M 31</object></objects>"))
        ->assertSessionHasErrors('file')
        ->assertSessionHas('error');

    expect($this->list->items()->count())->toBe(0);
});

it('requires a file', function () {
    $this->actingAs($this->owner)->post(route('observing-list.import', $this->list))
        ->assertSessionHasErrors('file');
});

it('only lets the owner import into a list', function () {
    $other = User::factory()->create();

    olfiImport($this, $other, $this->list, UploadedFile::fake()->createWithContent('list.txt', "M 31\n"))
        ->assertForbidden();

    expect($this->list->items()->count())->toBe(0);
});

it('sends guests to the login page', function () {
    $this->post(route('observing-list.import', $this->list), ['file' => UploadedFile::fake()->createWithContent('list.txt', "M 31\n")])
        ->assertRedirect(route('login'));

    expect($this->list->items()->count())->toBe(0);
});
