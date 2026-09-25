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

it('ignores implausible photometric parameters', function () {
    expect(CometCoordinates::photometry((object) ['H' => 5.0, 'n' => 15.0]))->toBe([5.0, 15.0, null, null, null])
        // The mean motion and a julian day ended up in `n` with older imports
        ->and(CometCoordinates::photometry((object) ['H' => 14.5, 'n' => 0.1287])[1])->toBe(10.0)
        ->and(CometCoordinates::photometry((object) ['H' => 13.6, 'n' => 2451720.5])[1])->toBe(10.0)
        // The slope parameter G of the H-G system is not a phase coefficient
        ->and(CometCoordinates::photometry((object) ['H' => 9.0, 'n' => 5.0, 'phase_coeff' => 0.15])[2])->toBeNull()
        ->and(CometCoordinates::photometry((object) ['H' => null, 'n' => 10.0]))->toBeNull();
});

it('predicts the magnitude of a comet', function () {
    if (! method_exists(Elliptic::class, 'hasCometParams')) {
        $this->markTestSkipped('Comet photometry for elliptic orbits needs laravel-astronomy-library 6.8.1');
    }
    // Osculating elements of JPL Horizons for the date, with JPL's M1 and K1
    $comet = CometCoordinates::target((object) [
        'q' => 0.780788135653347, 'e' => 0.9546110184438342, 'i' => 74.19156435379394, 'w' => 198.988728245405,
        'node' => 255.8558161536839, 'Tp' => 20240421.124383, 'H' => 5.0, 'n' => 15.0,
    ]);
    $date = Carbon::create(2024, 4, 21, 22, 0, 0, 'UTC');

    // JPL Horizons: T-mag 4.416
    expect(CometCoordinates::magnitude($comet, $date))->toEqualWithDelta(4.416, 0.01);

    $curve = CometCoordinates::lightCurve($comet, $date->copy()->subDays(180), $date->copy()->addDays(180));
    expect($curve)->toHaveCount(73)
        ->and(min(array_column($curve, 'mag')))->toBeLessThan(4.5)
        ->and($curve[0]['mag'])->toBeGreaterThan(10.0);

    // Without an absolute magnitude there is no prediction
    $unknown = CometCoordinates::target((object) ['q' => 0.78, 'e' => 0.95, 'i' => 74.2, 'w' => 199.0, 'node' => 255.9, 'Tp' => 20240421.12]);
    expect(CometCoordinates::magnitude($unknown, $date))->toBeNull()
        ->and(CometCoordinates::lightCurve($unknown, $date->copy()->subDays(10), $date))->toBe([]);
});

it('draws the light curve with the observations of the same period', function () {
    $predicted = [['date' => '2024-03-01', 'mag' => 7.0], ['date' => '2024-04-21', 'mag' => 4.4], ['date' => '2024-06-01', 'mag' => 7.5]];
    $observed = [['date' => '2024-04-10', 'mag' => 4.6], ['date' => '2020-01-01', 'mag' => 12.0], ['date' => '2024-05-01', 'mag' => 99.9]];

    $svg = App\Services\CometLightCurveChart::svg($predicted, $observed, '2024-04-21');

    expect($svg)->toStartWith('<svg')
        ->and(substr_count($svg, '<polyline'))->toBe(1)
        // One observation in the period, plus the symbol in the legend
        ->and(substr_count($svg, '<circle'))->toBe(2)
        ->and($svg)->toContain('stroke-dasharray');

    expect(App\Services\CometLightCurveChart::svg([], $observed))->toBeNull();
});
