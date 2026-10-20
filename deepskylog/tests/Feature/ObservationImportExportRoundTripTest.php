<?php

use App\Livewire\ObservationSearchTable;
use App\Models\Eyepiece;
use App\Models\Filter;
use App\Models\Instrument;
use App\Models\Lens;
use App\Models\Location;
use App\Models\Observation;
use App\Models\User;
use App\Support\SpreadsheetCell;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\Support\ObservationImportFixtures as Fixtures;

/*
 * Round trips between the CSV and OAL exports and imports: an example file
 * imported and exported again holds the same observations, and observations
 * exported and imported into another account (in either format) are the same
 * observations. The files themselves differ in what does not matter: the order,
 * the quoting, the ids, the observer and the target details an export adds.
 */

function roundTripExport(User $user, string $format): string
{
    test()->actingAs($user);

    if ($format === 'csv') {
        $download = Livewire::test(ObservationSearchTable::class)->call('exportToCsv')->effects['download'];

        return base64_decode($download['content']);
    }

    $response = test()->get(route('observations.search.oal', ['q' => '%']));
    $response->assertOk();

    return $response->getContent();
}

function roundTripText(?string $html): string
{
    return trim((string) preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) $html), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
}

function roundTripNumber(mixed $value): ?float
{
    $value = str_replace(',', '.', trim((string) $value));

    return is_numeric($value) && (float) $value > 0 ? round((float) $value, 2) : null;
}

/**
 * The observations of an account, as the fields both formats carry.
 *
 * @return array<string, array<string, mixed>>
 */
function roundTripObservations(User $user): array
{
    $name = fn (string $model, mixed $id) => $id === null ? null : $model::query()->whereKey($id)->value('name');

    return Observation::query()->where('observer_user_id', $user->id)->get()
        ->mapWithKeys(function (Observation $o) use ($name) {
            $instrument = $o->instrument_id === null ? null : Instrument::query()->find($o->instrument_id);
            $location = $o->location_id === null ? null : Location::query()->find($o->location_id);
            $key = "{$o->target_name} {$o->observed_on->format('Y-m-d')} ".($o->observed_time ?? '-');

            return [$key => [
                'target' => [$o->target_table, $o->target_key, $o->target_type],
                'location' => $location === null ? null : [$location->name, $location->timezone, (float) $location->latitude, (float) $location->longitude],
                // The naked eye is an instrument of its own in every account.
                'instrument' => $instrument === null ? null : ((int) $instrument->instrument_type_id === 1 ? 'naked eye' : [
                    $instrument->name, (int) $instrument->instrument_type_id, (int) $instrument->aperture_mm,
                    $instrument->focal_length_mm === null ? null : (float) $instrument->focal_length_mm,
                    $instrument->fixedMagnification === null ? null : (int) $instrument->fixedMagnification,
                    $instrument->field_of_view_deg === null ? null : (float) $instrument->field_of_view_deg,
                ]),
                'eyepiece' => $name(Eyepiece::class, $o->eyepiece_id),
                'filter' => $name(Filter::class, $o->filter_id),
                'lens' => $name(Lens::class, $o->lens_id),
                'seeing' => $o->seeing,
                'limiting_magnitude' => roundTripNumber($o->limiting_magnitude),
                'sqm' => roundTripNumber($o->sqm),
                'visibility' => DB::table('deepsky_observation_details')->where('observation_id', $o->id)->value('visibility'),
                'language' => $o->language,
                // OAL has plain text; the CSV export keeps the markup.
                'description' => roundTripText($o->description),
            ]];
        })
        ->sortKeys()
        ->all();
}

/**
 * The observations of a CSV file, by object, UT date and time.
 *
 * @return array<string, array<string, mixed>>
 */
function roundTripCsvRows(string $content): array
{
    $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
    $rows = [];
    foreach (preg_split('/\r\n|\n|\r/', trim($content)) as $line) {
        $f = array_map(fn (string $v) => SpreadsheetCell::unescape(trim($v)), array_pad(str_getcsv($line, ';'), 15, ''));
        $date = preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})$/', $f[2], $m) ? sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]) : $f[2];
        $time = preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/', $f[3], $m) ? sprintf('%02d:%02d:%02d', $m[1], $m[2], $m[3] ?? 0) : '-';
        $rows["{$f[0]} {$date} {$time}"] = [
            'location' => $f[4], 'instrument' => $f[5], 'eyepiece' => $f[6], 'filter' => $f[7], 'lens' => $f[8],
            'seeing' => $f[9], 'limiting_magnitude' => roundTripNumber($f[10]), 'sqm' => roundTripNumber($f[11]),
            'visibility' => $f[12], 'language' => $f[13] === '' ? 'en' : $f[13], 'description' => roundTripText($f[14]),
        ];
    }
    ksort($rows);

    return $rows;
}

/**
 * The observations of an OAL file, by target, UT date and time.
 *
 * @return array<string, array<string, mixed>>
 */
function roundTripOalObservations(string $content): array
{
    $document = new DOMDocument();
    $document->loadXML($content);
    $xpath = new DOMXPath($document);
    $text = fn (string $query, ?DOMNode $node = null) => trim($xpath->evaluate("string({$query})", $node));
    $byId = function (string $element, string $id) use ($xpath, $text) {
        $node = $id === '' ? null : $xpath->query("//{$element}[@id='{$id}']")->item(0);

        return $node === null ? null : (trim($text('vendor', $node).' '.$text('model', $node)) ?: $text('name', $node));
    };

    $observations = [];
    foreach ($xpath->query('//observation') as $node) {
        $begin = Carbon::parse($text('begin', $node), 'UTC')->utc();
        $observations["{$byId('target', $text('target', $node))} {$begin->format('Y-m-d H:i:s')}"] = [
            'site' => $byId('site', $text('site', $node)),
            'scope' => $byId('scope', $text('scope', $node)),
            'eyepiece' => $byId('eyepiece', $text('eyepiece', $node)),
            'filter' => $byId('filter', $text('filter', $node)),
            'lens' => $byId('lens', $text('lens', $node)),
            'seeing' => $text('seeing', $node),
            'faintestStar' => roundTripNumber($text('faintestStar', $node)),
            'sky-quality' => roundTripNumber($text('sky-quality', $node)),
            // The first result with a description, as the importer reads it.
            'rating' => $text("result[normalize-space(description) != ''][1]/rating", $node),
            'lang' => $text("result[normalize-space(description) != ''][1]/@lang", $node),
            'description' => roundTripText($text("result[normalize-space(description) != ''][1]/description", $node)),
        ];
    }
    ksort($observations);

    return $observations;
}

/**
 * An SQM the file leaves out is derived from the limiting magnitude when the
 * observation is saved, so the export has one; it is not compared.
 */
function withoutDerivedSqm(array $exported, array $original, string $field): array
{
    foreach ($original as $key => $observation) {
        if ($observation[$field] === null && isset($exported[$key])) {
            $exported[$key][$field] = null;
        }
    }

    return $exported;
}

beforeEach(function () {
    Fixtures::seedTargets();
    $this->user = Fixtures::createObserver(['username' => 'round-trip-source']);
});

it('exports the observations a csv file imported', function (string $file) {
    Fixtures::import($this->user, $file);

    $original = roundTripCsvRows(Fixtures::content($file));
    $exported = roundTripCsvRows(roundTripExport($this->user, 'csv'));

    expect(withoutDerivedSqm($exported, $original, 'sqm'))->toBe($original);
})->with(['csv/valid-all-types.csv', 'csv/valid-export-format.csv']);

it('exports the observations an oal file imported', function (string $file) {
    Fixtures::import($this->user, $file);

    $original = roundTripOalObservations(Fixtures::content($file));
    $exported = roundTripOalObservations(roundTripExport($this->user, 'oal'));

    expect(withoutDerivedSqm($exported, $original, 'sky-quality'))->toBe($original);
})->with(['oal/valid-all-types.xml', 'oal/valid-solar-system.xml']);

it('imports an export into another account as the same observations', function (string $file, string $format) {
    Fixtures::import($this->user, $file);
    $exported = roundTripExport($this->user, $format);

    // A CSV file only uses equipment the account has; an OAL file adds it.
    $other = $format === 'csv'
        ? Fixtures::createObserver(['username' => 'round-trip-target'])
        : User::factory()->create(['username' => 'round-trip-target']);
    $result = Fixtures::import($other, $format, content: $exported);

    // OAL requires a time: an observation without one is exported at midnight
    // UT, which another account cannot tell from an observation at 00:00 UT.
    $expected = collect(roundTripObservations($this->user))
        ->mapWithKeys(fn (array $o, string $key) => [$format === 'oal' ? preg_replace('/ -$/', ' 00:00:00', $key) : $key => $o])
        ->sortKeys()->all();

    expect($result->errors)->toBe(0)
        ->and(roundTripObservations($other))->toBe($expected);
})->with([
    'csv to csv, all types' => ['csv/valid-all-types.csv', 'csv'],
    'csv to csv, deep-sky' => ['csv/valid-observations.csv', 'csv'],
    'oal to oal, all types' => ['oal/valid-all-types.xml', 'oal'],
    'oal to oal, deep-sky' => ['oal/valid-observations.xml', 'oal'],
    'oal to oal, new objects' => ['oal/valid-new-targets.xml', 'oal'],
    'oal to oal, scopes' => ['oal/valid-scopes.xml', 'oal'],
    'csv to oal, all types' => ['csv/valid-all-types.csv', 'oal'],
    'csv to oal, deep-sky' => ['csv/valid-observations.csv', 'oal'],
    'oal to csv, all types' => ['oal/valid-all-types.xml', 'csv'],
    'oal to csv, solar system' => ['oal/valid-solar-system.xml', 'csv'],
]);

it('changes nothing when an export is imported into the same account', function (string $file, string $format) {
    Fixtures::import($this->user, $file);
    $before = roundTripObservations($this->user);

    $result = Fixtures::import($this->user, $format, content: roundTripExport($this->user, $format));

    expect($result->errors)->toBe(0)
        ->and($result->imported)->toBe(count($before))
        ->and(collect($result->importedObservations)->every(fn (array $row) => $row['updated']))->toBeTrue()
        ->and(roundTripObservations($this->user))->toBe($before);
})->with([
    'csv' => ['csv/valid-observations.csv', 'csv'],
    'oal' => ['oal/valid-observations.xml', 'oal'],
    'csv, all types' => ['csv/valid-all-types.csv', 'csv'],
    'oal, all types' => ['oal/valid-all-types.xml', 'oal'],
]);

it('exports the make of an instrument as the oal vendor and imports it again', function () {
    $make = fn (string $name) => DB::table('instrument_makes')->where('name', $name)->value('id');
    $scope = fn (string $name, int $makeId) => Instrument::query()->create([
        'user_id' => $this->user->id, 'observer' => $this->user->username, 'active' => true, 'name' => $name,
        'make_id' => $makeId, 'aperture_mm' => 200, 'focal_length_mm' => 1200, 'instrument_type_id' => 4,
        'mount_type_id' => DB::table('mount_types')->value('id'),
    ]);
    $scopes = [
        // No make, but a name that starts with one: the usual case.
        $scope('SkyWatcher Skyliner 200P', 1),
        // A make the name starts with too.
        $scope('Celestron C8', $make('Celestron')),
        // A make the name does not start with.
        $scope("18'' Classic", $make('Obsession')),
        // Neither.
        $scope('Home-made Dobson', 1),
    ];
    foreach ($scopes as $i => $instrument) {
        Observation::query()->create([
            'observer_username' => $this->user->username, 'observer_user_id' => $this->user->id, 'language' => 'en',
            'target_type' => 'deepsky', 'target_table' => 'objects', 'target_key' => 'M 31', 'target_name' => 'M 31',
            'has_drawing' => false, 'observed_on' => '2026-09-12', 'observed_time' => sprintf('21:%02d:00', $i),
            'instrument_id' => $instrument->id, 'description' => 'Seen.',
        ]);
    }

    $content = roundTripExport($this->user, 'oal');
    $document = new DOMDocument();
    $document->loadXML($content);
    $xpath = new DOMXPath($document);
    $written = collect($xpath->query('//scopes/scope'))
        ->map(fn (DOMElement $node) => [$xpath->evaluate('string(vendor)', $node) ?: null, $xpath->evaluate('string(model)', $node)])
        ->all();
    expect($written)->toBe([
        ['SkyWatcher', 'Skyliner 200P'],
        ['Celestron', 'C8'],
        ['Obsession', "18'' Classic"],
        [null, 'Home-made Dobson'],
    ]);

    $other = User::factory()->create(['username' => 'round-trip-target']);
    Fixtures::import($other, 'oal', content: $content);

    $imported = Instrument::query()->where('user_id', $other->id)->orderBy('id')->get()
        ->map(fn (Instrument $i) => [$i->name, DB::table('instrument_makes')->where('id', $i->make_id)->value('name')])
        ->all();
    expect($imported)->toBe([
        ['SkyWatcher Skyliner 200P', 'SkyWatcher'],
        ['Celestron C8', 'Celestron'],
        // The only name that changes: the make is put in front.
        ["Obsession 18'' Classic", 'Obsession'],
        ['Home-made Dobson', ''],
    ]);
});
