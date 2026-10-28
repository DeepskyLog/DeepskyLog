<?php

use App\Models\Location;
use App\Models\Observation;
use App\Models\ObservationSession;
use App\Models\User;
use App\Services\Import\OalSchemaValidator;
use Illuminate\Support\Facades\DB;

it('shows a session oal export button and exports the session as valid oal', function () {
    $owner = User::factory()->create([
        'name' => 'Wim De Meester',
        'username' => 'wim',
        'slug' => 'wim-de-meester',
    ]);

    $coObserver = User::factory()->create([
        'name' => 'Jane Companion',
        'username' => 'jane',
        'slug' => 'jane-companion',
    ]);

    $location = Location::query()->create([
        'user_id' => $owner->id,
        'name' => 'Session Export Site',
        'longitude' => 4.5,
        'latitude' => 50.5,
        'timezone' => 'UTC',
        'limitingMagnitude' => -999,
        'skyBackground' => -999,
        'elevation' => 120,
        'country' => 'Belgium',
        'active' => true,
        'observer' => '',
        'observations' => 0,
        'slug' => 'session-export-site',
    ]);

    $session = ObservationSession::query()->create([
        'id' => 24001,
        'name' => 'Cold observing nights in January 2024',
        'slug' => 'cold-observing-nights-in-january-2024',
        'observerid' => $owner->username,
        'begindate' => '2024-01-12 19:30:00',
        'enddate' => '2024-01-13 00:45:00',
        'locationid' => $location->id,
        'weather' => '<p>Clear and frosty</p>',
        'equipment' => '<p>12 inch Dobson</p>',
        'comments' => '<p>Steady transparency all night.</p>',
        'language' => 'en',
        'active' => 1,
    ]);

    DB::table('sessionObservers')->insert([
        'sessionid' => $session->id,
        'observer' => $coObserver->username,
    ]);

    $sessionObservation = Observation::query()->create([
        'observer_username' => $owner->username,
        'observer_user_id' => $owner->id,
        'observed_on' => '2024-01-12',
        'observed_time' => '21:15:00',
        'location_id' => $location->id,
        'language' => 'en',
        'description' => 'Bright core with extended wings',
        'target_type' => 'deepsky',
        'target_table' => 'objects',
        'target_key' => 'M 42',
        'target_name' => 'M 42',
        'has_drawing' => false,
        'legacy_source' => null,
    ]);

    $otherObservation = Observation::query()->create([
        'observer_username' => $owner->username,
        'observer_user_id' => $owner->id,
        'observed_on' => '2024-01-20',
        'observed_time' => '00:05:00',
        'location_id' => $location->id,
        'language' => 'en',
        'description' => 'Should stay out of this export',
        'target_type' => 'deepsky',
        'target_table' => 'objects',
        'target_key' => 'NGC 2392',
        'target_name' => 'NGC 2392',
        'has_drawing' => false,
        'legacy_source' => null,
    ]);

    // Observations are linked to the session automatically; the second one
    // falls outside its dates.
    expect(DB::table('sessionObservations')->where('sessionid', $session->id)->pluck('observationid')->map(fn ($v) => (int) $v)->all())
        ->toBe([$sessionObservation->id]);

    $showResponse = $this->get(route('session.show', [$owner->slug, $session->slug]));

    $showResponse->assertOk();
    $showResponse->assertSee('Export OAL');
    $showResponse->assertSee(route('session.export.oal', [$owner->slug, $session->slug]), false);

    $response = $this->get(route('session.export.oal', [$owner->slug, $session->slug]));

    $response->assertOk();
    expect(strtolower((string) $response->headers->get('content-type')))->toContain('application/xml');
    expect($response->headers->get('content-disposition'))->toContain('.oal.xml');

    $content = $response->getContent();

    $document = new DOMDocument();
    expect($document->loadXML($content))->toBeTrue();
    expect($content)->toContain('<session id="session-' . $session->id . '"');
    expect($content)->toContain('<session>session-' . $session->id . '</session>');
    expect($content)->toContain('<site>site-' . $location->id . '</site>');
    expect($content)->toContain('<weather>Clear and frosty</weather>');
    expect($content)->toContain('<equipment>12 inch Dobson</equipment>');
    expect($content)->toContain('<comments>Steady transparency all night.</comments>');
    expect($content)->toContain('<coObserver>observer-' . $coObserver->id . '</coObserver>');
    expect($content)->toContain('M 42');
    expect($content)->not->toContain('NGC 2392');
    expect(substr_count($content, '<observation id="observation-'))->toBe(1);
    expect(app(OalSchemaValidator::class)->validate($content))->toBe([]);
});