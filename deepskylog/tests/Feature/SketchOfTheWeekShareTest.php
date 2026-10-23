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
