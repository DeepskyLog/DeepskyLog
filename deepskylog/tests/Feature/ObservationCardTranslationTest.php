<?php

use App\Jobs\TranslateObservationDescriptions;
use App\Models\Observation;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;

// The observation cards never call Google Translate in the request: a page
// of 20 untranslated observations took half a minute. They show the cached
// translation, or the description while one job translates the page.

function dutchObservation(string $object, string $description): Observation
{
    $observer = User::factory()->create();

    return Observation::create([
        'observer_username' => $observer->username,
        'observer_user_id' => $observer->id,
        'observed_on' => '2026-03-01',
        'target_type' => 'deepsky',
        'target_table' => 'objects',
        'target_key' => $object,
        'target_name' => $object,
        'description' => $description,
        'language' => 'nl',
    ]);
}

it('shows the description and queues one translation job for the page', function () {
    Queue::fake();
    $reader = User::factory()->create(['translate' => true, 'language' => 'en']);
    $first = dutchObservation('M 65', 'Mooie stofbaan');
    $second = dutchObservation('M 66', 'Heldere kern');

    $this->actingAs($reader)->get('/observations')
        ->assertOk()
        ->assertSee('Mooie stofbaan')
        ->assertSee('Heldere kern');

    Queue::assertPushed(TranslateObservationDescriptions::class, 1);
    Queue::assertPushed(TranslateObservationDescriptions::class, fn ($job) => $job->language === 'en'
        && $job->kind === TranslateObservationDescriptions::DEEPSKY
        && collect($job->observationIds)->sort()->values()->all() === [$first->id, $second->id]);

    // Reloading while the job runs does not queue them again
    $this->actingAs($reader)->get('/observations')->assertOk();
    Queue::assertPushed(TranslateObservationDescriptions::class, 1);
});

it('shows the cached translation', function () {
    Queue::fake();
    $reader = User::factory()->create(['translate' => true, 'language' => 'en']);
    $observation = dutchObservation('M 65', 'Mooie stofbaan');
    Cache::put(TranslateObservationDescriptions::cacheKey(TranslateObservationDescriptions::DEEPSKY, $observation->id, 'en'), 'Nice dust lane', now()->addDay());

    $this->actingAs($reader)->get('/observations')
        ->assertOk()
        ->assertSee('Nice dust lane')
        ->assertDontSee('Mooie stofbaan');

    Queue::assertNothingPushed();
});

it('does not translate for observers who did not ask for it', function () {
    Queue::fake();
    dutchObservation('M 65', 'Mooie stofbaan');

    $this->actingAs(User::factory()->create(['translate' => false, 'language' => 'en']))->get('/observations')
        ->assertOk()
        ->assertSee('Mooie stofbaan');

    Queue::assertNothingPushed();
});
