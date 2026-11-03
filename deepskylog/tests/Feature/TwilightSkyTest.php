<?php

use App\Services\AtmosphericExtinction;
use App\Services\MoonlightSky;
use App\Services\NightWindow;
use App\Services\TwilightSky;

// The nights that never get astronomically dark: in Brussels from late May
// to mid July the Sun stays above -18°, so the night runs between the ends
// of nautical twilight, and the twilight brightens the sky (TwilightSky,
// through MoonlightSky). The new Moon is on 15 June 2026.

it('uses the nautical night when the astronomical one is missing or shorter than two hours', function () {
    // 15 May: more than two hours of astronomical darkness
    $may15 = NightWindow::for(50.85, 4.35, '2026-05-15', 'Europe/Brussels');
    expect($may15->twilight)->toBe('astronomical')
        ->and($may15->end - $may15->start)->toBeGreaterThanOrEqual(NightWindow::MIN_ASTRONOMICAL_MINUTES * 60)
        ->and($may15->astronomical)->toBeNull();

    // 24 May: less than an hour of it, kept as the dark part of the nautical night
    $may24 = NightWindow::for(50.85, 4.35, '2026-05-24', 'Europe/Brussels');
    expect($may24->twilight)->toBe('nautical')
        ->and($may24->end - $may24->start)->toBeGreaterThan(4 * 3600)
        ->and($may24->astronomical[1] - $may24->astronomical[0])->toBeLessThan(3600)
        ->and($may24->astronomical[0])->toBeGreaterThan($may24->start)
        ->and($may24->astronomical[1])->toBeLessThan($may24->end);

    // Midsummer: no astronomical darkness at all
    $june = NightWindow::for(50.85, 4.35, '2026-06-21', 'Europe/Brussels');
    expect($june->twilight)->toBe('nautical')->and($june->astronomical)->toBeNull();

    // Only the nautical twilight asked for (the Messier marathon) or only the astronomical one: no fallback
    expect(NightWindow::for(50.85, 4.35, '2026-05-15', 'Europe/Brussels', ['nautical'])->twilight)->toBe('nautical')
        ->and(NightWindow::for(50.85, 4.35, '2026-05-24', 'Europe/Brussels', ['astronomical'])->twilight)->toBe('astronomical');

    // Stockholm does not get darker than nautical twilight at midsummer
    expect(NightWindow::for(59.33, 18.07, '2026-06-21', 'Europe/Stockholm'))->toBeNull();
});

it('finds the Sun at the altitudes of the twilight times', function () {
    $info = date_sun_info(strtotime('2026-12-21 12:00 UTC'), 50.85, 4.35);

    expect(TwilightSky::sunAltitude($info['civil_twilight_end'], 50.85, 4.35))->toEqualWithDelta(-6.0, 0.1)
        ->and(TwilightSky::sunAltitude($info['nautical_twilight_end'], 50.85, 4.35))->toEqualWithDelta(-12.0, 0.1)
        ->and(TwilightSky::sunAltitude($info['astronomical_twilight_end'], 50.85, 4.35))->toEqualWithDelta(-18.0, 0.1);
});

it('brightens the sky in the twilight', function () {
    $sky = fn (float $sun) => MoonlightSky::toSqm(MoonlightSky::toNanoLambert(21.5) + TwilightSky::light($sun));

    // About two magnitudes at the end of nautical twilight, a few tenths at -15°, nothing from -16°
    expect($sky(-12.0))->toBeGreaterThan(19.3)->toBeLessThan(20.0)
        ->and($sky(-15.0))->toBeGreaterThan(21.0)->toBeLessThan(21.4)
        ->and($sky(-16.5))->toBe(21.5)
        ->and($sky(-18.0))->toBe(21.5)
        ->and($sky(-9.0))->toBeLessThan($sky(-12.0))
        ->and($sky(0.0))->toBeLessThan($sky(-9.0));

    // In a midsummer night without the Moon the sky is brightest at the ends and darkest in the middle
    $window = NightWindow::for(50.85, 4.35, '2026-06-14', 'Europe/Brussels');
    $sky    = new MoonlightSky($window, 4.35);
    $middle = $sky->moments()[intdiv(count($sky->moments()), 2)];
    expect($sky->isUp())->toBeFalse()
        ->and($sky->hasTwilight())->toBeTrue()
        ->and($sky->brightens())->toBeTrue()
        ->and($sky->twilightAt($window->start))->toBeTrue()
        ->and($sky->sqmAt($window->start, 270.0, 30.0, 21.5))->toBeLessThan(20.0)
        ->and($sky->sqmAt($middle, 270.0, 30.0, 21.5))->toBeGreaterThan(21.2);

    // A dark winter night without the Moon is not brightened
    $winter = new MoonlightSky(NightWindow::for(50.85, 4.35, '2026-12-08', 'Europe/Brussels'), 4.35);
    expect($winter->hasTwilight())->toBeFalse();
});

it('takes the moment an object stands out best, not only the darkest one', function () {
    $window = NightWindow::for(50.85, 4.35, '2026-06-14', 'Europe/Brussels');
    $sky    = new MoonlightSky($window, 4.35);

    // The best moment has the darkest sky less the extinction; for some
    // points of the sky that is a later, higher moment than the darkest one.
    $differs = false;
    foreach (range(0, 345, 15) as $ra) {
        $moments = array_values(array_filter($sky->moments(), fn (int $t) => $window->altitude($ra, 30.0, $t) >= 20.0));
        if ($moments === []) {
            continue;
        }
        $score   = fn (int $t) => round(round($sky->sqmAt($t, $ra, 30.0, 21.5), 2) - AtmosphericExtinction::magnitudes($window->altitude($ra, 30.0, $t)), 2);
        $best    = $sky->bestMoment($ra, 30.0, 21.5, 20.0);
        $darkest = collect($moments)->sortByDesc(fn (int $t) => $sky->sqmAt($t, $ra, 30.0, 21.5))->first();
        expect($score($best['timestamp']))->toBe(max(array_map($score, $moments)));
        $differs = $differs || ($best['timestamp'] !== $darkest && $best['altitude'] > $window->altitude($ra, 30.0, $darkest));
    }
    expect($differs)->toBeTrue();
});
