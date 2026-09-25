<?php

use App\Services\CometCoordinates;
use Carbon\Carbon;
use deepskylog\AstronomyLibrary\Coordinates\GeographicalCoordinates;
use deepskylog\AstronomyLibrary\Targets\Elliptic;
use deepskylog\AstronomyLibrary\Targets\NearParabolic;
use deepskylog\AstronomyLibrary\Targets\Parabolic;
use Illuminate\Support\Facades\DB;

// Elements from JPL's ELEMENTS.COMET
function insertComet(string $name, float $q, float $e, float $i, float $w, float $node, float $tp): void
{
    DB::table('comets_orbital_elements')->insert([
        'name' => $name, 'epoch' => 2460211.5, 'q' => $q, 'e' => $e, 'i' => $i, 'w' => $w,
        'node' => $node, 'Tp' => $tp, 'H' => 5.0, 'n' => 13.5, 'ref' => 'JPL',
    ]);
}

// Angular distance in arcminutes
function separationInArcminutes(float $ra1, float $dec1, float $ra2, float $dec2): float
{
    [$ra1, $dec1, $ra2, $dec2] = [deg2rad($ra1 * 15), deg2rad($dec1), deg2rad($ra2 * 15), deg2rad($dec2)];
    $cos = sin($dec1) * sin($dec2) + cos($dec1) * cos($dec2) * cos($ra1 - $ra2);

    return rad2deg(acos(min(1.0, $cos))) * 60;
}

it('reads the time of perihelion with the fraction of the day', function () {
    expect(CometCoordinates::perihelionDate(20240421.13116)->toDateTimeString())->toBe('2024-04-21 03:08:52')
        ->and(CometCoordinates::perihelionDate('20251108.53939')->toDateTimeString())->toBe('2025-11-08 12:56:43')
        ->and(CometCoordinates::perihelionDate(null))->toBeNull()
        ->and(CometCoordinates::perihelionDate(20241300.5))->toBeNull();
});

it('picks the orbit that matches the eccentricity', function () {
    $elements = (object) ['q' => 0.78, 'i' => 74.2, 'w' => 199.0, 'node' => 255.9, 'Tp' => 20240421.13];

    expect(CometCoordinates::target((object) ([...(array) $elements, 'e' => 0.95])))->toBeInstanceOf(Elliptic::class)
        ->and(CometCoordinates::target((object) ([...(array) $elements, 'e' => 1.0])))->toBeInstanceOf(Parabolic::class)
        ->and(CometCoordinates::target((object) ([...(array) $elements, 'e' => 1.0001])))->toBeInstanceOf(NearParabolic::class)
        ->and(CometCoordinates::target((object) ([...(array) $elements, 'e' => 0.95, 'Tp' => null])))->toBeNull();
});

it('finds the elements of a comet by name', function () {
    insertComet('12P/Pons-Brooks', 0.78086113, 0.95456124, 74.19091, 198.98800, 255.85535, 20240421.13116);
    insertComet('C/2023 A3 (Tsuchinshan-ATLAS)', 0.39143007, 1.00009537, 139.11211, 308.49176, 21.55948, 20240927.74085);

    expect(CometCoordinates::orbitalElements('12P/Pons-Brooks')->name)->toBe('12P/Pons-Brooks')
        ->and(CometCoordinates::orbitalElements('C/2023 A3 Tsuchinshan-ATLAS')->name)->toBe('C/2023 A3 (Tsuchinshan-ATLAS)')
        ->and(CometCoordinates::orbitalElements('Tsuchinshan-ATLAS')->name)->toBe('C/2023 A3 (Tsuchinshan-ATLAS)')
        ->and(CometCoordinates::orbitalElements('2P/Encke'))->toBeNull()
        ->and(CometCoordinates::orbitalElements(''))->toBeNull();
});

it('calculates the position of a comet without asking JPL Horizons', function () {
    insertComet('12P/Pons-Brooks', 0.78086113, 0.95456124, 74.19091, 198.98800, 255.85535, 20240421.13116);
    $date = Carbon::create(2026, 9, 25, 22, 0, 0, 'UTC');

    $coords = CometCoordinates::forName('12P/Pons-Brooks', $date, new GeographicalCoordinates(4.70, 50.88), 30.0);

    // JPL Horizons propagating the same elements, astrometric J2000 as seen
    // from Leuven, gives RA 16.201499 h and Dec -31.42003 deg.
    expect(separationInArcminutes($coords->getRA()->getCoordinate(), $coords->getDeclination()->getCoordinate(), 16.201499, -31.42003))
        ->toBeLessThan(1.0)
        // The date of the caller is left alone
        ->and($date->toDateTimeString())->toBe('2026-09-25 22:00:00');

    expect(CometCoordinates::forName('Unknown comet', $date, new GeographicalCoordinates(4.70, 50.88)))->toBeNull();
});
