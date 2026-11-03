<?php

use App\Services\CometCoordinates;
use App\Services\Comets\HorizonsElements;
use Carbon\Carbon;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

// C/2006 VZ13 (LINEAR): the MPC's elements of 2025 and JPL's of July 2007
function vz13Current(): void
{
    DB::table('comets_orbital_elements')->insert([
        'name' => 'C/2006 VZ13 (LINEAR)', 'epoch' => 2461000, 'q' => 0.996019, 'e' => 0.999995,
        'i' => 134.3876, 'w' => 174.0889, 'node' => 66.338, 'Tp' => 20070810.3966,
        'H' => 8, 'n' => 14, 'ref' => 'MPC194112',
    ]);
}

function vz13Epoch(): void
{
    DB::table('comet_epoch_elements')->insert([
        'designation' => 'C/2006 VZ13', 'epoch' => 2454290.5, 'q' => 1.015252, 'e' => 1.000262,
        'i' => 134.79321, 'w' => 174.11508, 'node' => 66.02708, 'Tp' => 20070810.8911,
        'source' => 'JPL Horizons test', 'created_at' => now(), 'updated_at' => now(),
    ]);
}

function horizonsAnswer(): string
{
    return "header\n\$\$SOE\n2454300.500000000, A.D. 2007-Jul-19 00:00:00.0000,  1.000270211142362E+00,  1.015252346564924E+00,  1.347932094660709E+02,  6.602708809605269E+01,  1.741150814517806E+02,  2.454323391085448E+06,  4.279548486759042E-06, -9.796351008778374E-05,  3.302152256791179E+02, -3.757255669366518E+03,  9.999999999999998E+99,  9.999999999999998E+99,\n\$\$EOE\n";
}

it('reads the elements of a Horizons answer', function () {
    $elements = HorizonsElements::parse(horizonsAnswer(), 'DES=C/2006 VZ13;CAP<2008;NOFRAG;');

    expect($elements['epoch'])->toBe(2454300.5)
        ->and($elements['e'])->toEqualWithDelta(1.00027, 1e-5)
        ->and($elements['q'])->toEqualWithDelta(1.015252, 1e-6)
        ->and($elements['i'])->toEqualWithDelta(134.7932, 1e-4)
        ->and($elements['node'])->toEqualWithDelta(66.0271, 1e-4)
        ->and($elements['w'])->toEqualWithDelta(174.1151, 1e-4)
        // JD 2454323.3911 is 2007 August 10.8911
        ->and($elements['Tp'])->toEqualWithDelta(20070810.8911, 1e-4)
        ->and(HorizonsElements::parse("No matches found.\n", 'DES=X'))->toBeString();
});

it('takes the elements whose epoch is nearest to the date', function () {
    vz13Current();
    vz13Epoch();

    expect(CometCoordinates::orbitalElements('C/2006 VZ13 (LINEAR)')->ref)->toBe('MPC194112')
        ->and(CometCoordinates::orbitalElements('C/2006 VZ13 (LINEAR)', Carbon::parse('2025-01-01'))->ref)->toBe('MPC194112');

    $old = CometCoordinates::orbitalElements('C/2006 VZ13 (LINEAR)', Carbon::parse('2007-07-19 23:00', 'UTC'));
    expect($old->ref)->toBe('JPL Horizons test')
        ->and((float) $old->q)->toBe(1.015252)
        // The photometry stays that of the current elements
        ->and((float) $old->H)->toBe(8.0);
});

it('puts C/2006 VZ13 in Boötes in July 2007, as Horizons does', function () {
    vz13Current();
    $geo = new GeographicalCoordinates(6.12864, 44.1907);
    $moment = Carbon::create(2007, 7, 19, 23, 0, 0, 'UTC');

    // With today's elements it lands almost 2 degrees away
    $current = CometCoordinates::forName('C/2006 VZ13 (LINEAR)', $moment, $geo, 1070);
    expect(abs($current->getDeclination()->getCoordinate() - 36.62))->toBeGreaterThan(1.0);

    vz13Epoch();
    $right = CometCoordinates::forName('C/2006 VZ13 (LINEAR)', $moment, $geo, 1070);
    expect($right->getRA()->getCoordinate())->toEqualWithDelta(14.020, 0.01)
        ->and($right->getDeclination()->getCoordinate())->toEqualWithDelta(36.62, 0.1);
});

it('fetches the elements for the observations the elements do not cover', function () {
    vz13Current();
    $cometId = DB::table('cometobjects')->insertGetId(['name' => 'C/2006 VZ13 (LINEAR)', 'slug' => 'c2006-vz13-linear', 'icqname' => '', 'timestamp' => now()]);
    foreach (['2007-07-10', '2007-07-19', '2007-08-20'] as $date) {
        DB::table('observations')->insert([
            'observer_username' => 'wim', 'observed_on' => $date, 'description' => '',
            'target_type' => 'comet', 'target_table' => 'cometobjects', 'target_key' => (string) $cometId,
            'target_name' => 'C/2006 VZ13 (LINEAR)', 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    $this->artisan('comets:fetch-epoch-elements')
        ->expectsOutputToContain('Would fetch C/2006 VZ13 (LINEAR) at 2007-07-31 (3 observations)')
        ->assertSuccessful();
    expect(DB::table('comet_epoch_elements')->count())->toBe(0);

    config(['services.horizons.enabled' => true]);
    Http::fake(['ssd.jpl.nasa.gov/*' => Http::response(['result' => horizonsAnswer()])]);

    $this->artisan('comets:fetch-epoch-elements --apply')->assertSuccessful();
    expect(DB::table('comet_epoch_elements')->where('designation', 'C/2006 VZ13')->count())->toBe(1);
    Http::assertSent(fn ($request) => str_contains(urldecode($request->url()), "DES=C/2006 VZ13;CAP<2008;NOFRAG;"));

    // Covered now: nothing left to fetch
    $this->artisan('comets:fetch-epoch-elements')
        ->expectsOutputToContain('All comet observations are covered')
        ->assertSuccessful();
});

it('solves an orbit with an eccentricity just below 1 as near-parabolic', function () {
    // JPL's elements of C/2007 N3 (Lulin) of March 2009; Horizons puts it at
    // RA 15h35.0m, Dec -18.42 on 23 January 2009 at 22:15 UT from the Netherlands
    $elements = (object) ['q' => 1.2122648818879, 'e' => 0.99999931201477, 'i' => 178.37362987245,
        'w' => 136.86369262406, 'node' => 338.53517316337, 'Tp' => 20090110.641308];
    $target = CometCoordinates::target($elements);
    expect($target)->toBeInstanceOf(\deepskylog\AstronomyLibrary\Targets\NearParabolic::class);

    $position = CometCoordinates::calculate($target, Carbon::parse('2009-01-23 22:15', 'UTC'), new GeographicalCoordinates(5.1, 52.6833));
    expect($position->getRA()->getCoordinate())->toEqualWithDelta(15.583, 0.01)
        ->and($position->getDeclination()->getCoordinate())->toEqualWithDelta(-18.42, 0.1);

    // Far from 1 it stays elliptic
    $elements->e = 0.9;
    expect(CometCoordinates::target($elements))->toBeInstanceOf(\deepskylog\AstronomyLibrary\Targets\Elliptic::class);
});
