<?php

use App\Jobs\GenerateAccountExport;
use App\Livewire\Profile\DownloadMyData;
use App\Models\Instrument;
use App\Models\Location;
use App\Models\Observation;
use App\Models\User;
use App\Services\AccountDataExport;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\Support\ObservationImportFixtures as Fixtures;

/*
 * "Download all my data": one zip with everything the account holds
 * (AccountDataExport, GenerateAccountExport).
 */

beforeEach(function () {
    Fixtures::seedTargets();
    $this->observer = Fixtures::createObserver(['username' => 'export-owner', 'name' => 'Export Owner', 'slug' => 'export-owner']);
});

function accountExportObservation(User $user, string $object, array $attributes = []): Observation
{
    return Observation::query()->create($attributes + [
        'observer_username' => $user->username,
        'observer_user_id' => $user->id,
        'observed_on' => '2024-01-12',
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
 * @return array<string, string>
 */
function accountExportEntries(User $user): array
{
    $path = tempnam(sys_get_temp_dir(), 'export');
    app(AccountDataExport::class)->write($path, $user, fn () => null);

    $zip = new ZipArchive;
    expect($zip->open($path))->toBeTrue();
    $entries = [];
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $entries[$zip->getNameIndex($i)] = $zip->getFromIndex($i);
    }
    $zip->close();
    @unlink($path);

    return $entries;
}

it('puts the account, its rows, observations, drawings and pictures in the zip', function () {
    Storage::fake('public');
    Storage::disk('public')->put('observations/m31.jpg', 'JPEG of M 31');
    Storage::disk('public')->put('photos/sessions/1.png', 'PNG of a session');

    $drawn = accountExportObservation($this->observer, 'M 31', ['has_drawing' => true, 'drawing_filename' => 'observations/m31.jpg']);
    $other = Fixtures::createObserver(['username' => 'export-other']);
    accountExportObservation($other, 'M 81');

    $sessionId = DB::table('observation_sessions')->insertGetId([
        'name' => 'A night', 'slug' => 'a-night', 'observerid' => $this->observer->username,
        'begindate' => '2024-01-12 20:00:00', 'enddate' => '2024-01-13 02:00:00', 'locationid' => 0,
        'weather' => '', 'equipment' => '', 'comments' => '', 'language' => 'en', 'active' => 1,
        'picture' => 'photos/sessions/1.png',
    ]);
    DB::table('messages')->insert([
        ['sender' => 'export-other', 'receiver' => 'export-owner', 'subject' => 'To me', 'message' => 'Hi', 'date' => now()],
        ['sender' => 'export-other', 'receiver' => 'someone', 'subject' => 'Not mine', 'message' => 'Hi', 'date' => now()],
    ]);

    $entries = accountExportEntries($this->observer);

    $account = json_decode($entries['account.json'], true);
    $observations = json_decode($entries['data/observations.json'], true);
    $messages = json_decode($entries['data/messages.json'], true);

    expect($account['username'])->toBe('export-owner')
        ->and($account)->not->toHaveKeys(['password', 'two_factor_secret', 'remember_token', 'calendar_feed_token', 'follow_feed_token'])
        ->and(array_column($observations, 'target_name'))->toBe(['M 31'])
        ->and(array_column($messages, 'subject'))->toBe(['To me'])
        ->and(array_column(json_decode($entries['data/observation_sessions.json'], true), 'id'))->toBe([$sessionId])
        ->and(json_decode($entries['data/instruments.json'], true))->not->toBeEmpty()
        ->and($entries)->toHaveKeys([
            'README.txt',
            'observations/observations.xml',
            'observations/drawings.csv',
            "observations/drawings/2024-01-12_m-31_{$drawn->id}.jpg",
            "files/sessions/{$sessionId}.png",
        ])
        ->and($entries['observations/observations.xml'])->toContain("drawings/2024-01-12_m-31_{$drawn->id}.jpg")
        ->and($entries['observations/observations.xml'])->not->toContain('M 81');
});

it('leaves out the secrets of tokens and push subscriptions', function () {
    $this->observer->createToken('pydeepskylog', ['read']);
    DB::table('push_subscriptions')->insert([
        'user_id' => $this->observer->id, 'endpoint' => 'https://push.example/1', 'endpoint_hash' => hash('sha256', 'https://push.example/1'),
        'public_key' => 'secret-key', 'auth_token' => 'secret-auth', 'created_at' => now(), 'updated_at' => now(),
    ]);

    $entries = accountExportEntries($this->observer);

    $tokens = json_decode($entries['data/personal_access_tokens.json'], true);
    $push = json_decode($entries['data/push_subscriptions.json'], true);

    expect(array_column($tokens, 'name'))->toBe(['pydeepskylog'])
        ->and($tokens[0])->not->toHaveKey('token')
        ->and($push[0]['endpoint'])->toBe('https://push.example/1')
        ->and($push[0])->not->toHaveKeys(['public_key', 'auth_token']);
});

it('queues the zip from the profile page and lets only its owner download it', function () {
    Queue::fake();

    Livewire::actingAs($this->observer)
        ->test(DownloadMyData::class)
        ->call('generate')
        ->assertHasNoErrors()
        ->assertSee(__('Waiting for a worker...'));

    Queue::assertPushed(GenerateAccountExport::class, fn ($job) => $job->userId === $this->observer->id);

    $token = Cache::get(GenerateAccountExport::recentKey($this->observer->id))[0];
    Storage::disk('local')->put(GenerateAccountExport::path($token), 'zip');
    Cache::put(GenerateAccountExport::statusKey($token), ['state' => 'complete', 'user_id' => $this->observer->id, 'filename' => DownloadMyData::FILENAME, 'completed_at' => now()->toIso8601String()]);

    $this->actingAs($this->observer)->get(route('account-export.download', $token))
        ->assertOk()
        ->assertDownload(DownloadMyData::FILENAME);
    $this->actingAs(Fixtures::createObserver(['username' => 'export-thief']))->get(route('account-export.download', $token))
        ->assertNotFound();
    $this->actingAs($this->observer)->get(route('account-export.download', (string) Str::uuid()))
        ->assertNotFound();

    Storage::disk('local')->delete(GenerateAccountExport::path($token));
});

it('is listed with the other downloads', function () {
    $token = (string) Str::uuid();
    Cache::put(GenerateAccountExport::statusKey($token), ['state' => 'queued', 'user_id' => $this->observer->id, 'label' => 'export-owner', 'created_at' => now()->toIso8601String()]);
    Cache::put(GenerateAccountExport::recentKey($this->observer->id), [$token]);

    expect(GenerateAccountExport::jobsFor($this->observer->id)->pluck('kind')->all())->toBe([GenerateAccountExport::class]);
});

it('offers the download on the profile page and in the privacy policy', function () {
    Livewire::actingAs($this->observer)
        ->test(DownloadMyData::class)
        ->assertSee(__('Make the zip file'))
        ->assertDontSee(__('Download zip file'));
    expect(file_get_contents(resource_path('views/profile/show.blade.php')))->toContain('@livewire("profile.download-my-data")');

    $this->get('/privacy')
        ->assertOk()
        ->assertSee(__('Download all my data'));
});
