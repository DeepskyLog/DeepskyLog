<?php

use App\Models\Observation;
use App\Models\User;

it('shares the sketch of the week post as it is, without a second link or the post as mail subject', function () {
    $admin       = $this->createUserAndAssignToTeam('Administrators');
    $observer    = User::factory()->create(['name' => 'Ann Sketcher']);
    $observation = Observation::create([
        'observer_username' => $observer->username,
        'observer_user_id'  => $observer->id,
        'observed_on'       => '2026-03-01',
        'target_type'       => 'deepsky',
        'target_table'      => 'objects',
        'target_key'        => 'M 65',
        'target_name'       => 'M 65',
        'description'       => 'Seen',
        'has_drawing'       => true,
        'drawing_filename'  => 'sketches/m65.jpg',
    ]);
    $link = rawurlencode($observation->detailUrl());

    $response = $this->actingAs($admin)
        ->post(route('sketch-of-the-week.store'), ['observation_id' => $observation->id, 'date' => today()->next(\Carbon\Carbon::SUNDAY)->toDateString()])
        ->assertOk();

    $html = $response->getContent();
    preg_match('/href="(https:\/\/twitter\.com\/intent\/tweet[^"]*)"/', $html, $x);
    preg_match('/href="(https:\/\/wa\.me\/[^"]*)"/', $html, $whatsapp);
    preg_match('/href="(mailto:\?subject=[^"]*)"/', $html, $mail);

    expect(substr_count($x[1], $link))->toBe(1)
        ->and(substr_count($whatsapp[1], $link))->toBe(1)
        ->and(html_entity_decode($mail[1]))->toStartWith('mailto:?subject='.rawurlencode('DeepskyLog sketch of the week').'&body=')
        ->and($html)->toContain('data-share-image="/images/sketches/m65.jpg"')
        ->and($html)->toContain('native-share');
});

it('shares the sketch of the week post on the cards for administrators and the card text for observers', function () {
    $admin       = $this->createUserAndAssignToTeam('Administrators');
    $viewer      = User::factory()->create();
    $observer    = User::factory()->create(['name' => 'Ann Sketcher']);
    $observation = Observation::create([
        'observer_username' => $observer->username,
        'observer_user_id'  => $observer->id,
        'observed_on'       => '2026-03-01',
        'target_type'       => 'deepsky',
        'target_table'      => 'objects',
        'target_key'        => 'M 65',
        'target_name'       => 'M 65',
        'description'       => 'Seen',
        'has_drawing'       => true,
        'drawing_filename'  => 'sketches/m65.jpg',
    ]);
    \App\Models\SketchOfTheWeek::forceCreate(['observation_id' => $observation->id, 'date' => today()->subDays(3)->toDateString(), 'user_id' => $observer->id]);
    \App\Models\SketchOfTheMonth::forceCreate(['observation_id' => $observation->id, 'date' => '2026-04-08', 'user_id' => $observer->id]);
    $link = rawurlencode($observation->detailUrl());

    $tweet = function ($user, string $route) {
        preg_match('/href="(https:\/\/twitter\.com\/intent\/tweet[^"]*)"/', $this->actingAs($user)->get(route($route))->assertOk()->getContent(), $x);

        return rawurldecode(html_entity_decode($x[1]));
    };

    expect($tweet($admin, 'sketch-of-the-week'))->toContain('As new #deepskylog sketch of the week, we selected this sketch of M 65 by Ann Sketcher.')
        ->toContain('#sketchoftheweek')
        ->and(substr_count($tweet($admin, 'sketch-of-the-week'), rawurldecode($link)))->toBe(1)
        ->and($tweet($admin, 'sketch-of-the-month'))->toContain('The #deepskylog sketch of March 2026 is this sketch of M 65 by Ann Sketcher.')
        ->toContain('Congratulations, Ann!')
        ->and($tweet($viewer, 'sketch-of-the-week'))->toContain('Look at this sketch of M 65')
        ->not->toContain('sketchoftheweek');
});
