<?php

use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\Instrument;
use App\Models\Observation;
use App\Models\User;
use App\Services\Observations\IcqExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

function icqLine(array $values = []): string
{
    return IcqExport::line(array_merge([
        'designation' => IcqExport::designation('C/2020 F3 (NEOWISE)'),
        'date' => '2020-07-15',
        'time' => 2100,
        'method' => 'S',
        'fainter' => false,
        'magnitude' => 2.8,
        'uncertain' => false,
        'chart' => 'TK',
        'aperture' => 5.0,
        'instrument' => 'B',
        'focal_ratio' => null,
        'magnification' => 10,
        'coma' => 6,
        'dc' => '7',
        'tail' => 300,
        'pa' => 34,
        'observer' => 'abc01',
    ], $values));
}

it('writes the comet designation in columns 1 to 11', function (string $name, ?string $expected) {
    expect(IcqExport::designation($name))->toBe($expected);
})->with([
    ['12P/Pons-Brooks', ' 12        '],
    ['73P/Schwassmann-Wachmann 3-C', ' 73       c'],
    ['C/2020 F3 (NEOWISE)', '   2020F3  '],
    ['P/2010 H2 (Vales)', '  P2010H2  '],
    ['C/2013 US10 (Catalina)', '   2013US10'],
    ['C/2019 Y4-B (ATLAS)', '   2019Y4 b'],
    ['Nishimura', null],
]);

it('puts every field in its icq column', function () {
    $line = icqLine();

    //                1         2         3         4         5         6         7         8
    //       12345678901234567890123456789012345678901234567890123456789012345678901234567890
    expect($line)->toBe('   2020F3  2020 07 15.88  S  2.8 TK  5.0B    10   6    7   5.00  34 ICQ XX ABC01');
    expect(strlen($line))->toBe(80);
});

it('marks fainter and uncertain magnitudes and writes short tails in arcminutes', function () {
    $line = icqLine(['fainter' => true, 'uncertain' => true, 'tail' => 12, 'magnitude' => 11.25]);

    expect(substr($line, 27, 6))->toBe('[11.3:')
        ->and(substr($line, 58, 6))->toBe(' 12.0m');
});

it('moves the date when the time rounds up to the next day and leaves out an unknown time', function () {
    expect(substr(icqLine(['date' => '2020-07-31', 'time' => 2359]), 11, 13))->toBe('2020 08 01.00')
        ->and(substr(icqLine(['time' => null]), 11, 13))->toBe('2020 07 15   ');
});

it('exports the comet observations of a search with a magnitude', function () {
    $user = User::factory()->create(['username' => 'cometeer', 'slug' => 'cometeer', 'icqname' => 'COM01']);
    $cometId = DB::table('cometobjects')->insertGetId([
        'name' => 'C/2020 F3 (NEOWISE)',
        'slug' => 'c-2020-f3-neowise',
        'icqname' => '',
        'timestamp' => now(),
    ]);
    $instrument = Instrument::query()->create([
        'user_id' => $user->id,
        'make_id' => DB::table('instrument_makes')->value('id'),
        'name' => 'Newton',
        'aperture_mm' => 200,
        'instrument_type_id' => DB::table('instrument_types')->where('name', 'Reflector')->value('id'),
        'mount_type_id' => DB::table('mount_types')->value('id'),
        'focal_length_mm' => 1200,
        'active' => true,
        'observer' => '',
    ]);

    $comet = fn (string $date) => Observation::query()->create([
        'observer_username' => $user->username,
        'observer_user_id' => $user->id,
        'observed_on' => $date,
        'observed_time' => '20:24:00',
        'instrument_id' => $instrument->id,
        'target_type' => 'comet',
        'target_table' => 'cometobjects',
        'target_key' => (string) $cometId,
        'target_name' => 'C/2020 F3 (NEOWISE)',
        'has_drawing' => false,
    ]);
    $estimate = $comet('2020-07-20');
    $withoutMagnitude = $comet('2020-07-21');
    DB::table('comet_observation_details')->insert([
        ['observation_id' => $estimate->id, 'method' => 'S', 'magnitude' => 3.4, 'chart' => 'TK', 'magnification' => '48', 'dc' => '6', 'coma' => 5, 'tail' => null, 'position_angle' => null, 'magnitude_uncertain' => false, 'less_magnitude' => false],
        ['observation_id' => $withoutMagnitude->id, 'method' => null, 'magnitude' => null, 'chart' => null, 'magnification' => null, 'dc' => '5', 'coma' => 4, 'tail' => null, 'position_angle' => null, 'magnitude_uncertain' => false, 'less_magnitude' => false],
    ]);

    $response = $this->get(route('observations.search.icq', ['q' => 'C/2020 F3%']));

    $response->assertOk();
    expect($response->streamedContent())
        ->toBe("   2020F3  2020 07 20.85  S  3.4 TK 20.0L 6  48   5    6            ICQ XX COM01\n");
});

it('says so when the search has no comet estimates', function () {
    $this->from(route('observations.search'))
        ->get(route('observations.search.icq', ['q' => 'M 42']))
        ->assertRedirect(route('observations.search'))
        ->assertSessionHas('error');
});

it('saves the icq observer code of the profile in capitals', function () {
    $user = User::factory()->create();

    app(UpdateUserProfileInformation::class)->update($user, [
        'name' => $user->name,
        'email' => $user->email,
        'country' => 'BE',
        'about' => '',
        'fstOffset' => 0,
        'copyrightSelection' => '',
        'icqname' => ' abc01 ',
    ]);

    expect($user->fresh()->icqname)->toBe('ABC01');

    expect(fn () => app(UpdateUserProfileInformation::class)->update($user, [
        'name' => $user->name,
        'email' => $user->email,
        'country' => 'BE',
        'about' => '',
        'fstOffset' => 0,
        'copyrightSelection' => '',
        'icqname' => 'A1',
    ]))->toThrow(ValidationException::class);
});
