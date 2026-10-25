<?php

use App\Jobs\TranslateObservationDescriptions;
use App\Models\Observation;
use App\Models\ObservationTranslation;
use App\Models\User;
use App\Services\Observations\DescriptionTranslations;
use App\Services\Observations\DescriptionTranslator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Stichoza\GoogleTranslate\Exceptions\RateLimitException;

// Observations are shown translated (#310): guests in the language of the
// site, observers who turned on translations in their own language. The
// translations are stored in observation_translations. A request never
// calls Google Translate for a card: guests only see stored translations,
// observers who asked for them queue the missing ones (one job per page).

function dutchObservation(string $object, string $description, array $attributes = []): Observation
{
    $observer = User::factory()->create();

    return Observation::create(array_merge([
        'observer_username' => $observer->username,
        'observer_user_id' => $observer->id,
        'observed_on' => '2026-03-01',
        'target_type' => 'deepsky',
        'target_table' => 'objects',
        'target_key' => $object,
        'target_name' => $object,
        'description' => $description,
        'language' => 'nl',
    ], $attributes));
}

function translation(Observation $observation, string $language, ?string $description): void
{
    ObservationTranslation::create(['observation_id' => $observation->id, 'language' => $language, 'description' => $description]);
}

/** A translator that prefixes the language, and counts its calls. */
function fakeTranslator(?Closure $behaviour = null): object
{
    $fake = new class($behaviour) extends DescriptionTranslator {
        public int $calls = 0;

        public ?string $detected = null;

        public function __construct(private ?Closure $behaviour)
        {
        }

        public function translate(string $text, ?string $source, string $target): ?string
        {
            $this->calls++;
            if ($this->behaviour) {
                return ($this->behaviour)($text, $source, $target);
            }

            return "[{$target}] {$text}";
        }

        public function detectedSource(): ?string
        {
            return $this->detected;
        }
    };
    app()->instance(DescriptionTranslator::class, $fake);

    return $fake;
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
        && collect($job->observationIds)->sort()->values()->all() === [$first->id, $second->id]);

    // Reloading while the job runs does not queue them again
    $this->actingAs($reader)->get('/observations')->assertOk();
    Queue::assertPushed(TranslateObservationDescriptions::class, 1);
});

it('shows the stored translation to an observer who asked for it', function () {
    Queue::fake();
    $reader = User::factory()->create(['translate' => true, 'language' => 'en']);
    $observation = dutchObservation('M 65', 'Mooie stofbaan');
    translation($observation, 'en', 'Nice dust lane');

    $this->actingAs($reader)->get('/observations')
        ->assertOk()
        ->assertSee('Nice dust lane')
        ->assertSee('Translated by Google · original language: Dutch');

    Queue::assertNothingPushed();
});

it('does not translate for observers who did not ask for it', function () {
    Queue::fake();
    $observation = dutchObservation('M 65', 'Mooie stofbaan');
    translation($observation, 'en', 'Nice dust lane');

    $this->actingAs(User::factory()->create(['translate' => false, 'language' => 'en']))->get('/observations')
        ->assertOk()
        ->assertSee('Mooie stofbaan')
        ->assertDontSee('Nice dust lane');

    Queue::assertNothingPushed();
});

it('shows guests the translation into the language of the site', function () {
    Queue::fake();
    $observation = dutchObservation('M 65', 'Mooie stofbaan');
    translation($observation, 'en', 'Nice dust lane');
    translation($observation, 'fr', 'Belle bande de poussière');

    $this->withSession(['locale' => 'fr'])->get('/observations')
        ->assertOk()
        ->assertSee('Belle bande de poussière')
        ->assertDontSee('Nice dust lane');

    Queue::assertNothingPushed();
});

it('never queues a translation for a guest', function () {
    Queue::fake();
    dutchObservation('M 65', 'Mooie stofbaan');

    $this->withSession(['locale' => 'en'])->get('/observations')
        ->assertOk()
        ->assertSee('Mooie stofbaan')
        ->assertDontSee('Translated by Google');

    Queue::assertNothingPushed();
});

it('shows guests an observation in the language of the site as written', function () {
    Queue::fake();
    $observation = dutchObservation('M 65', 'Mooie stofbaan');
    translation($observation, 'en', 'Nice dust lane');

    $this->withSession(['locale' => 'nl'])->get('/observations')
        ->assertOk()
        ->assertSee('Mooie stofbaan')
        ->assertDontSee('Nice dust lane');
});

it('translates on the detail page in the request for an observer who asked for it', function () {
    config(['app.translate_observations' => true]);
    $translator = fakeTranslator();
    $reader = User::factory()->create(['translate' => true, 'language' => 'en']);
    $observation = dutchObservation('M 65', 'Mooie stofbaan');

    $this->actingAs($reader)->get($observation->detailUrl())
        ->assertOk()
        ->assertSee('[en] Mooie stofbaan');

    expect($translator->calls)->toBe(1)
        ->and(ObservationTranslation::where('observation_id', $observation->id)->where('language', 'en')->value('description'))
        ->toBe('[en] Mooie stofbaan');
});

it('stores the translations of a job', function () {
    config(['app.translate_observations' => true]);
    $translator = fakeTranslator();
    $observation = dutchObservation('M 65', 'Mooie stofbaan');
    $english = dutchObservation('M 66', 'Bright core', ['language' => 'en']);

    TranslateObservationDescriptions::dispatchSync([$observation->id, $english->id], 'en');
    // A second run does not translate again
    TranslateObservationDescriptions::dispatchSync([$observation->id, $english->id], 'en');

    expect($translator->calls)->toBe(1)
        ->and(ObservationTranslation::where('observation_id', $observation->id)->value('description'))->toBe('[en] Mooie stofbaan')
        ->and(ObservationTranslation::where('observation_id', $english->id)->exists())->toBeFalse();
});

it('stores no translation when Google finds the description in the target language', function () {
    config(['app.translate_observations' => true]);
    $translator = fakeTranslator();
    $translator->detected = 'en';
    $observation = dutchObservation('M 65', 'Bright core', ['language' => '']);

    TranslateObservationDescriptions::dispatchSync([$observation->id], 'en');

    $row = ObservationTranslation::where('observation_id', $observation->id)->first();
    expect($row)->not->toBeNull()
        ->and($row->description)->toBeNull();
});

it('reuses the translation of an observation of the same field', function () {
    config(['app.translate_observations' => true]);
    $translator = fakeTranslator();
    $first = dutchObservation('M 65', 'Drie sterrenstelsels in één veld');
    $copy = dutchObservation('M 66', 'Drie sterrenstelsels in één veld', ['seen_with_observation_id' => $first->id]);

    TranslateObservationDescriptions::dispatchSync([$first->id, $copy->id], 'en');

    expect($translator->calls)->toBe(1)
        ->and(ObservationTranslation::where('observation_id', $copy->id)->value('description'))
        ->toBe('[en] Drie sterrenstelsels in één veld');
});

it('pauses all translations when Google refuses', function () {
    config(['app.translate_observations' => true]);
    $translator = fakeTranslator(fn () => throw new RateLimitException('Too many requests'));
    $first = dutchObservation('M 65', 'Mooie stofbaan');
    $second = dutchObservation('M 66', 'Heldere kern');

    TranslateObservationDescriptions::dispatchSync([$first->id, $second->id], 'en');

    expect($translator->calls)->toBe(1)
        ->and(DescriptionTranslations::paused())->toBeTrue()
        ->and(ObservationTranslation::count())->toBe(0);

    TranslateObservationDescriptions::dispatchSync([$second->id], 'fr');
    expect($translator->calls)->toBe(1);
});

it('retranslates an edited description', function () {
    $observation = dutchObservation('M 65', 'Mooie stofbaan');
    translation($observation, 'en', 'Nice dust lane');
    config(['app.translate_observations' => true]);
    Queue::fake();

    $observation->update(['description' => 'Mooie stofbaan en een heldere kern']);

    expect(ObservationTranslation::where('observation_id', $observation->id)->exists())->toBeFalse();
    Queue::assertPushed(TranslateObservationDescriptions::class, 5);
    Queue::assertNotPushed(TranslateObservationDescriptions::class, fn ($job) => $job->language === 'nl');
});

it('queues the newest untranslated observations per language', function () {
    config(['app.translate_observations' => true]);
    Queue::fake();
    $old = dutchObservation('M 65', 'Mooie stofbaan');
    $new = dutchObservation('M 66', 'Heldere kern');
    translation($new, 'fr', 'Noyau brillant');
    dutchObservation('M 67', '');

    $this->artisan('observations:translate-descriptions')->assertSuccessful();
    Queue::assertNothingPushed();

    $this->artisan('observations:translate-descriptions', ['--apply' => true, '--limit' => 1, '--language' => ['en', 'fr', 'nl']])
        ->assertSuccessful();

    Queue::assertPushed(TranslateObservationDescriptions::class, 2);
    Queue::assertPushed(TranslateObservationDescriptions::class, fn ($job) => $job->language === 'en' && $job->observationIds === [$new->id]);
    Queue::assertPushed(TranslateObservationDescriptions::class, fn ($job) => $job->language === 'fr' && $job->observationIds === [$old->id]);

    // What is queued is not queued again on the next run
    $this->artisan('observations:translate-descriptions', ['--apply' => true, '--limit' => 1, '--language' => ['en']])->assertSuccessful();
    Queue::assertPushed(TranslateObservationDescriptions::class, fn ($job) => $job->language === 'en' && $job->observationIds === [$old->id]);
});

it('queues nothing while translations are paused', function () {
    config(['app.translate_observations' => true]);
    Queue::fake();
    dutchObservation('M 65', 'Mooie stofbaan');
    DescriptionTranslations::pause();

    $this->artisan('observations:translate-descriptions', ['--apply' => true])->assertSuccessful();

    Queue::assertNothingPushed();
});
