<?php

use App\Jobs\GenerateObservationsZip;
use App\Livewire\ObservationZips;
use App\Models\Instrument;
use App\Models\Location;
use App\Models\Observation;
use App\Models\User;
use App\Services\Observations\ObservationZipBuilder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\Support\ObservationImportFixtures as Fixtures;

/*
 * Zip files of observations with their drawings (#319): observations.xml
 * (OAL), the drawings and drawings.csv, made by GenerateObservationsZip.
 */

beforeEach(function () {
    Fixtures::seedTargets();
    $this->observer = Fixtures::createObserver(['username' => 'zip-owner', 'name' => 'Zip Owner', 'slug' => 'zip-owner']);
});

function zipObservation(User $user, string $object, string $date, array $attributes = []): Observation
{
    return Observation::query()->create($attributes + [
        'observer_username' => $user->username,
        'observer_user_id' => $user->id,
        'observed_on' => $date,
        'observed_time' => '21:30:00',
        'location_id' => Location::query()->where('user_id', $user->id)->value('id'),
        'instrument_id' => Instrument::query()->where('user_id', $user->id)->where('instrument_type_id', '<>', 1)->value('id'),
        'language' => 'en',
        'description' => "{$object} seen",
        'target_type' => 'deepsky',
        'target_table' => 'objects',
        'target_key' => $object,
        'target_name' => $object,
        'has_drawing' => false,
    ]);
}

/**
 * @return array<string, string>  name => content of the zip's entries
 */
function zipEntries(string $path): array
{
    $zip = new ZipArchive();
    expect($zip->open($path))->toBeTrue();

    $entries = [];
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $name = $zip->getNameIndex($i);
        $entries[$name] = $zip->getFromIndex($i);
    }
    $zip->close();

    return $entries;
}

it('puts the observations in OAL with their drawings next to them', function () {
    Storage::fake('public');
    Storage::disk('public')->put('observations/m31.jpg', 'JPEG of M 31');

    $drawn = zipObservation($this->observer, 'M 31', '2024-01-12', ['has_drawing' => true, 'drawing_filename' => 'observations/m31.jpg']);
    $lost = zipObservation($this->observer, 'M 42', '2024-01-13', ['has_drawing' => true, 'drawing_filename' => 'observations/gone.jpg']);
    zipObservation($this->observer, 'M 13', '2024-01-14');
    zipObservation(Fixtures::createObserver(['username' => 'someone-else']), 'M 81', '2024-01-12');

    $path = tempnam(sys_get_temp_dir(), 'zip');
    $result = app(ObservationZipBuilder::class)->write($path, ['observer_id' => $this->observer->id], fn () => null);
    $entries = zipEntries($path);
    @unlink($path);

    $entry = "drawings/2024-01-12_m-31_{$drawn->id}.jpg";
    expect($result)->toBe(['observations' => 3, 'drawings' => 1, 'missing' => 1])
        ->and(array_keys($entries))->toEqualCanonicalizing(['observations.xml', 'drawings.csv', $entry])
        ->and($entries[$entry])->toBe('JPEG of M 31')
        ->and(substr_count($entries['observations.xml'], '<observation id='))->toBe(3)
        ->and($entries['observations.xml'])->toContain("<image>{$entry}</image>")
        ->and(substr_count($entries['observations.xml'], '<image>'))->toBe(1)
        ->and($entries['observations.xml'])->not->toContain('M 81')
        ->and($entries['drawings.csv'])->toContain($entry . ',2024-01-12,"M 31","Zip Owner"')
        ->and($entries['drawings.csv'])->toContain('"Drawing missing",2024-01-13,"M 42"');

    expect($lost->fresh()->has_drawing)->toBeTrue();
});

it('finds the drawings of the old site by their legacy id', function () {
    $observation = zipObservation($this->observer, 'M 31', '2010-05-01', ['has_drawing' => true, 'legacy_id' => 987654321, 'legacy_source' => 'deepsky']);
    $file = public_path('images/drawings/987654321.jpg');
    @mkdir(dirname($file), 0755, true);
    file_put_contents($file, 'old');

    try {
        expect(ObservationZipBuilder::drawingPath($observation))->toBe($file);
    } finally {
        @unlink($file);
    }
});

it('never reads a drawing outside the drawing directories', function () {
    $observation = zipObservation($this->observer, 'M 31', '2010-05-01', ['has_drawing' => true, 'drawing_filename' => '../../.env']);

    expect(ObservationZipBuilder::drawingPath($observation))->toBeNull();
});

it('only lets observers zip their own observations, or administrators anyone\'s', function () {
    $other = Fixtures::createObserver(['username' => 'other-observer', 'slug' => 'other-observer']);

    $this->get(route('observations.zip', ['observer' => 'zip-owner']))->assertRedirect(route('login'));
    $this->actingAs($other)->get(route('observations.zip', ['observer' => 'zip-owner']))->assertForbidden();
    $this->actingAs($this->observer)->get(route('observations.zip', ['observer' => 'zip-owner']))
        ->assertOk()
        ->assertSee(__('All my observations'));
});

it('only puts the observer\'s own observations in the zip of a search', function () {
    $other = Fixtures::createObserver(['username' => 'other-observer', 'slug' => 'other-observer']);
    zipObservation($this->observer, 'M 31', '2024-01-12', ['has_drawing' => true]);
    zipObservation($other, 'M 31', '2024-01-13', ['has_drawing' => true]);

    $this->actingAs($this->observer)->get(route('observations.zip', ['q' => 'M 31']))
        ->assertOk()
        ->assertViewHas('source', ['q' => 'M 31', 'filters' => [], 'observer_id' => $this->observer->id])
        ->assertSee(__('My observations matching ":q"', ['q' => 'M 31']))
        ->assertSee('1 observation, 1 drawing');

    // Asking for another observer's search does not help either.
    $this->actingAs($this->observer)->get(route('observations.zip', ['observers' => ['other-observer'], 'drawing_state' => 'with']))
        ->assertOk()
        ->assertSee(__('There are no observations to put in a zip file.'));
});

it('puts everyone\'s observations in an administrator\'s zip of a search', function () {
    $other = Fixtures::createObserver(['username' => 'other-observer', 'slug' => 'other-observer']);
    zipObservation($this->observer, 'M 31', '2024-01-12', ['has_drawing' => true]);
    zipObservation($other, 'M 31', '2024-01-13', ['has_drawing' => true]);

    $administrator = User::factory()->create();
    $team = $this->getTeamFromString('Administrators');
    $administrator->teams()->attach($team);
    $administrator->switchTeam($team);

    $this->actingAs($administrator)->get(route('observations.zip', ['q' => 'M 31']))
        ->assertOk()
        ->assertViewHas('source', ['q' => 'M 31', 'filters' => []])
        ->assertSee('2 observations, 2 drawings');
});

it('queues the zip of the observer\'s drawings', function () {
    Queue::fake();
    zipObservation($this->observer, 'M 31', '2024-01-12', ['has_drawing' => true]);
    zipObservation($this->observer, 'M 13', '2024-01-14');

    $this->actingAs($this->observer);
    Livewire::test(ObservationZips::class, ['source' => ['observer_id' => $this->observer->id], 'title' => 'All my observations'])
        ->assertSee('2 observations, 1 drawing')
        ->set('drawingsOnly', true)
        ->assertSee('1 observation, 1 drawing')
        ->call('generate')
        ->assertHasNoErrors()
        ->assertSee(__('Waiting for a worker...'));

    Queue::assertPushed(GenerateObservationsZip::class, function (GenerateObservationsZip $job) {
        $status = Cache::get(GenerateObservationsZip::statusKey($job->token));

        return $job->parameters === ['observer_id' => $this->observer->id, 'drawings_only' => true]
            && $status['state'] === 'queued'
            && $status['filename'] === 'observations-zip-owner-drawings.zip';
    });
});

it('refuses a zip without observations or with too many', function () {
    Queue::fake();
    $this->actingAs($this->observer);

    Livewire::test(ObservationZips::class, ['source' => ['observer_id' => $this->observer->id], 'title' => 'All my observations'])
        ->assertSee(__('There are no observations to put in a zip file.'))
        ->call('generate')
        ->assertHasErrors('generate');

    expect(ObservationZipBuilder::refusal(['observations' => ObservationZipBuilder::MAX_OBSERVATIONS + 1, 'drawings' => 0]))->toContain('narrow down')
        ->and(ObservationZipBuilder::refusal(['observations' => 10, 'drawings' => ObservationZipBuilder::MAX_DRAWINGS + 1]))->toContain('drawings');
    Queue::assertNothingPushed();
});

it('makes the zip in the worker and lets only its owner download it', function () {
    Storage::fake('local');
    Storage::fake('public');
    Storage::disk('public')->put('observations/m31.jpg', 'JPEG of M 31');
    zipObservation($this->observer, 'M 31', '2024-01-12', ['has_drawing' => true, 'drawing_filename' => 'observations/m31.jpg']);

    $token = (string) Str::uuid();
    Cache::put(GenerateObservationsZip::statusKey($token), ['state' => 'queued', 'user_id' => $this->observer->id, 'filename' => 'observations-zip-owner.zip'], now()->addHour());
    (new GenerateObservationsZip($token, $this->observer->id, ['observer_id' => $this->observer->id]))->handle();

    expect(Cache::get(GenerateObservationsZip::statusKey($token))['state'])->toBe('complete')
        ->and(GenerateObservationsZip::path($token))->toEndWith('.zip')
        ->and(Storage::disk('local')->files('observation-zips'))->toBe([GenerateObservationsZip::path($token)]);

    $other = Fixtures::createObserver(['username' => 'other-observer']);
    $this->actingAs($other)->get(route('observations.zip.download', $token))->assertNotFound();
    $this->actingAs($this->observer)->get(route('observations.zip.download', $token))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/zip')
        ->assertDownload('observations-zip-owner.zip');
});

it('counts zips towards the observer\'s open pdfs', function () {
    $token = (string) Str::uuid();
    Cache::put(GenerateObservationsZip::statusKey($token), ['state' => 'processing', 'user_id' => $this->observer->id], now()->addHour());
    Cache::put(GenerateObservationsZip::recentKey($this->observer->id), [$token], now()->addHour());

    expect(GenerateObservationsZip::openCount($this->observer->id))->toBe(1);
});

it('offers the zip in the export menu of the observation search', function () {
    $this->actingAs($this->observer)
        ->get(route('observations.search', ['q' => 'M 31']))
        ->assertOk()
        ->assertSee(route('observations.zip', ['q' => 'M 31']), false)
        ->assertSee(__('Zip file with my drawings'));
});

it('offers the zip of all their observations on the observer\'s own observations page', function () {
    zipObservation($this->observer, 'M 31', '2024-01-12');
    $zipUrl = route('observations.zip', ['observer' => 'zip-owner']);

    $this->actingAs($this->observer)->get(route('observations.show', 'zip-owner'))
        ->assertOk()
        ->assertSee($zipUrl, false);

    $this->actingAs($this->observer)->get(route('observations.user.object', ['zip-owner', 'm-31']))
        ->assertOk()
        ->assertDontSee($zipUrl, false);

    $this->actingAs(Fixtures::createObserver(['username' => 'other-observer']))->get(route('observations.show', 'zip-owner'))
        ->assertOk()
        ->assertDontSee($zipUrl, false);
});
