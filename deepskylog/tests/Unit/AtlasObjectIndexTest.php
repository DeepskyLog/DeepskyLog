<?php

use App\Services\AtlasLayout;
use App\Services\AtlasObjectIndexService;
use Illuminate\Support\Facades\DB;

/**
 * Put an object in the catalogue at a position, with the aliases it is also
 * known by.
 *
 * @param  array<int, string>  $aliases
 */
function catalogueObject(string $name, float $raHours, float $decDeg, float $magnitude = 8.0, array $aliases = [], string $type = 'GALXY'): void
{
    DB::table('objects')->insert([
        'name' => $name,
        'type' => $type,
        'ra' => $raHours,
        'decl' => $decDeg,
        'mag' => $magnitude,
        'milleniumbase' => '',
        'timestamp' => '2026-01-01 00:00:00',
    ]);

    foreach ($aliases as $alias) {
        DB::table('objectnames')->insert([
            'objectname' => $name,
            'catalog' => '',
            'catindex' => $alias,
            'altname' => $alias,
            'timestamp' => '2026-01-01 00:00:00',
        ]);
    }
}

function overviewIndex(?int $lastPage = null): array
{
    return app(AtlasObjectIndexService::class)->entries(
        AtlasLayout::variant('overview', 'portrait'),
        10.0,
        false,
        $lastPage
    );
}

/**
 * The names an index carries, in the order it sets them.
 *
 * @return array<int, string>
 */
function indexNames(array $entries): array
{
    return array_column($entries, 'name');
}

/**
 * Where the entries of an index page are set, one x per column.
 *
 * @return array<int, string>
 */
function indexColumns(string $svg): array
{
    // The entries, not the running heads, which are set larger.
    preg_match_all('/<text x="([0-9.]+)" y="[0-9.]+" font-family="[^"]*" font-size="7\.0"/', $svg, $matches);

    return array_values(array_unique($matches[1]));
}

it('indexes an object against the atlas page it falls on', function () {
    catalogueObject('NGC 7000', 20.98, 44.53);

    $variant = AtlasLayout::variant('overview', 'portrait');

    expect(overviewIndex())->toBe([
        ['name' => 'NGC 7000', 'page' => AtlasLayout::pageAt($variant, 20.98, 44.53)],
    ]);
});

it('indexes only the names the charts print beside an object', function () {
    // A chart label carries the object's own name and nothing else, so the
    // aliases would send a reader looking for a name that is on no page.
    catalogueObject('NGC 7000', 20.98, 44.53, 8.0, ['Caldwell 20', 'North America Nebula', 'LBN 373']);

    expect(indexNames(overviewIndex()))->toBe(['NGC 7000']);
});

it('sorts the index the way a printed one is sorted', function () {
    catalogueObject('ASCC 9', 1.0, 10.0);
    catalogueObject('ASCC 100', 2.0, 10.0);
    catalogueObject('ASCC 11', 3.0, 10.0);
    catalogueObject('Abell 85', 4.0, 10.0);
    catalogueObject('7 Sisters of the Pole', 5.0, 10.0);
    catalogueObject('13 Sge Group', 6.0, 10.0);
    catalogueObject('Alessi 1', 7.0, 10.0);
    catalogueObject('Alessi-Teutsch 17', 8.0, 10.0);

    // Numbers in numerical order rather than as text, so ASCC 9 comes before
    // ASCC 100; and case-sensitive, so a catalogue designation in capitals
    // comes before a name that merely starts with the same letter.
    expect(indexNames(overviewIndex()))->toBe([
        '7 Sisters of the Pole',
        '13 Sge Group',
        'ASCC 9',
        'ASCC 11',
        'ASCC 100',
        'Abell 85',
        'Alessi-Teutsch 17',
        'Alessi 1',
    ]);
});

it('keeps both objects when two of them print the same label', function () {
    // The catalogue holds both of these, and a label is set as SVG text,
    // which collapses the double space: on the pages they read alike.
    catalogueObject('H 4 99', 23.5, 89.0);
    catalogueObject('H 4  99', 1.0, 0.0);

    $entries = overviewIndex();

    expect(indexNames($entries))->toBe(['H 4 99', 'H 4 99']);
    expect($entries[0]['page'])->not->toBe($entries[1]['page']);
});

it('leaves a name as the chart labels it, entity and all', function () {
    // Chart labels are escaped rather than interpreted, so this one goes on
    // the page with the entity showing, and the index has to agree with it.
    catalogueObject('T&ouml;pler 2', 6.68, 9.88);

    expect(indexNames(overviewIndex()))->toBe(['T&ouml;pler 2']);
});

it('leaves out what the atlas does not plot', function () {
    catalogueObject('NGC 1', 1.0, 10.0, 8.0);
    catalogueObject('NGC 2', 2.0, 10.0, 14.0);
    // 99.9 is how the catalogue records "nobody has measured this".
    catalogueObject('NGC 3', 3.0, 10.0, 99.9);
    catalogueObject('STF 100', 5.0, 10.0, 8.0, [], 'DS');

    // An overview page stops at its magnitude limit and skips the objects
    // whose magnitude nobody has recorded; double stars belong to the star
    // layer, which draws them from the star catalogue instead.
    expect(indexNames(overviewIndex()))->toBe(['NGC 1']);

    // A detail page does plot the ones of unrecorded magnitude, and the
    // index follows it.
    $detail = app(AtlasObjectIndexService::class)->entries(
        AtlasLayout::variant('detail', 'portrait'),
        15.0,
        true
    );

    expect(indexNames($detail))->toBe(['NGC 1', 'NGC 2', 'NGC 3']);
});

it('indexes only as far as a run cut short actually got', function () {
    $variant = AtlasLayout::variant('overview', 'portrait');

    catalogueObject('Near pole', 1.0, 88.0);
    catalogueObject('Near equator', 1.0, 0.0);

    $polePage = AtlasLayout::pageAt($variant, 1.0, 88.0);
    $equatorPage = AtlasLayout::pageAt($variant, 1.0, 0.0);

    expect($polePage)->toBeLessThan($equatorPage);
    expect(indexNames(overviewIndex($polePage)))->toBe(['Near pole']);
});

it('sets the index in columns of dotted lines ending in a page number', function () {
    catalogueObject('NGC 7000', 20.98, 44.53);
    catalogueObject('NGC 7023', 21.02, 68.17);

    $pages = app(AtlasObjectIndexService::class)->build('overview', 'portrait', 10.0, false);

    expect($pages)->toHaveCount(1);
    expect($pages[0])->toContain('viewBox="0 0 756 1120"');

    // The running heads name the first and the last entry on the page.
    expect($pages[0])->toContain('>NGC 7000</text>')->toContain('>NGC 7023</text>');

    $page = AtlasLayout::pageAt(AtlasLayout::variant('overview', 'portrait'), 20.98, 44.53);
    expect($pages[0])->toMatch('/NGC 7000 \.{2,} ' . $page . '</');
});

it('writes the page numbers as they are printed on the pages', function () {
    // Page 1 of the atlas, not page 0001: the numbers are right-aligned by
    // the length of the dot leader, so they need no padding.
    catalogueObject('At the pole', 23.5, 89.0);

    $pages = app(AtlasObjectIndexService::class)->build('overview', 'portrait', 10.0, false);

    expect(AtlasLayout::pageAt(AtlasLayout::variant('overview', 'portrait'), 23.5, 89.0))->toBe(1);
    expect($pages[0])->toMatch('/At the pole \.{2,} 1</')->not->toContain('0001');
});

it('sets an index page in as many columns as it is allowed', function () {
    // Enough entries to reach the last column of a page, so what is counted
    // is the columns the page is set in and not the ones the entries filled.
    foreach (range(1, 400) as $index) {
        catalogueObject(sprintf('Object %03d', $index), $index % 24, 10.0);
    }

    $service = app(AtlasObjectIndexService::class);

    // Short names, so the only thing holding the columns back is the cap.
    expect(indexColumns($service->build('overview', 'portrait', 10.0, false)[0]))->toHaveCount(4);
    expect(indexColumns($service->build('overview', 'landscape', 10.0, false)[0]))->toHaveCount(4);
});

it('gives up a column rather than cut a long name short', function () {
    $name = trim(str_repeat('A very long object name ', 5));

    catalogueObject($name, 12.0, 10.0);

    $page = app(AtlasObjectIndexService::class)->build('overview', 'portrait', 10.0, false)[0];

    expect(indexColumns($page))->toHaveCount(1);
    expect($page)->toContain($name);
});

it('has no index pages when the atlas plots nothing', function () {
    expect(app(AtlasObjectIndexService::class)->build('overview', 'portrait', 10.0, false))->toBe([]);
});
