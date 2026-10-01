<?php

use App\Jobs\GenerateAtlasPdf;
use App\Jobs\GenerateObjectReportPdf;
use App\Jobs\GenerateObjectSetsPdf;
use App\Jobs\QueuedPdfJob;
use App\Models\User;
use Illuminate\Queue\MaxAttemptsExceededException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * A PDF job whose rendering throws with internal details in the message.
 */
class FailingPdfJob extends QueuedPdfJob
{
    protected static function prefix(): string
    {
        return 'failing';
    }

    protected static function directory(): string
    {
        return 'failing';
    }

    protected function renderPdf(User $user, callable $progress): string
    {
        throw new RuntimeException('Cannot open /secret/path/db.sqlite');
    }
}

/**
 * A PDF job that records who auth() says is logged in while it renders.
 */
class AuthRecordingPdfJob extends QueuedPdfJob
{
    public static ?int $renderedAs = null;

    protected static function prefix(): string
    {
        return 'auth_recording';
    }

    protected static function directory(): string
    {
        return 'auth-recording';
    }

    protected function renderPdf(User $user, callable $progress): string
    {
        self::$renderedAs = auth()->id();

        return '%PDF-1.4';
    }
}

it('marks the pdf failed when the queue gives up on the job', function () {
    $user = User::factory()->create();
    $token = (string) Str::uuid();
    Cache::put(GenerateObjectSetsPdf::statusKey($token), ['state' => 'processing', 'user_id' => $user->id, 'filename' => 'm-31.pdf'], now()->addHour());

    (new GenerateObjectSetsPdf($token, $user->id, []))->failed(new MaxAttemptsExceededException('attempted too many times'));

    $status = Cache::get(GenerateObjectSetsPdf::statusKey($token));
    expect($status['state'])->toBe('failed')
        ->and($status['filename'])->toBe('m-31.pdf')
        ->and($status['detail'])->not->toContain('attempted too many times');
});

it('does not show the exception text when rendering fails', function () {
    $user = User::factory()->create();
    $token = (string) Str::uuid();
    Cache::put(FailingPdfJob::statusKey($token), ['state' => 'queued', 'user_id' => $user->id], now()->addHour());

    (new FailingPdfJob($token, $user->id, []))->handle();

    $status = Cache::get(FailingPdfJob::statusKey($token));
    expect($status['state'])->toBe('failed')
        ->and(json_encode($status))->not->toContain('/secret/path');
});

it('only lets the observer who asked for an atlas download it', function () {
    Storage::fake('local');
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $token = (string) Str::uuid();
    Cache::put(GenerateAtlasPdf::statusKey($token), ['state' => 'complete', 'user_id' => $owner->id, 'filename' => 'a.pdf'], now()->addHour());
    Storage::disk('local')->put(GenerateAtlasPdf::path($token), '%PDF');

    $this->get(route('downloads.dsl-atlas.download', $token))->assertRedirect(route('login'));
    $this->actingAs($other)->get(route('downloads.dsl-atlas.download', $token))->assertNotFound();
    $this->actingAs($owner)->get(route('downloads.dsl-atlas.download', $token))->assertOk();
});

it('leaves a finished or forgotten pdf alone when the job is failed afterwards', function () {
    $user = User::factory()->create();
    $done = (string) Str::uuid();
    $gone = (string) Str::uuid();
    Cache::put(GenerateObjectSetsPdf::statusKey($done), ['state' => 'complete', 'user_id' => $user->id], now()->addHour());

    (new GenerateObjectSetsPdf($done, $user->id, []))->failed(null);
    (new GenerateObjectSetsPdf($gone, $user->id, []))->failed(null);

    expect(Cache::get(GenerateObjectSetsPdf::statusKey($done))['state'])->toBe('complete')
        ->and(Cache::has(GenerateObjectSetsPdf::statusKey($gone)))->toBeFalse();
});

it('queues the pdf jobs on a connection that does not hand them out again while they run', function () {
    config(['queue.long_running' => 'database-long']);

    foreach ([GenerateAtlasPdf::class, GenerateObjectSetsPdf::class, GenerateObjectReportPdf::class] as $class) {
        $job = new $class((string) Str::uuid(), 1, []);

        expect($job->connection)->toBe('database-long')
            ->and(config('queue.connections.database-long.retry_after'))->toBeGreaterThan($job->timeout);
    }
});

it('renders as the observer who asked for the pdf, and logs them out again', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    $token = (string) Str::uuid();

    (new AuthRecordingPdfJob($token, $user->id, []))->handle();

    expect(AuthRecordingPdfJob::$renderedAs)->toBe($user->id)
        ->and(auth()->hasUser())->toBeFalse()
        ->and(Cache::get(AuthRecordingPdfJob::statusKey($token))['state'])->toBe('complete');
});

it('keeps the user of the request when the job runs synchronously', function () {
    Storage::fake('local');
    $requester = User::factory()->create();
    $owner = User::factory()->create();
    $this->actingAs($requester);

    (new AuthRecordingPdfJob((string) Str::uuid(), $owner->id, []))->handle();

    expect(AuthRecordingPdfJob::$renderedAs)->toBe($owner->id)
        ->and(auth()->id())->toBe($requester->id);
});

it('renders the atlas in the language it was asked for and keeps its file name', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    $token = (string) Str::uuid();
    Cache::put(GenerateAtlasPdf::statusKey($token), ['state' => 'queued', 'user_id' => $user->id], now()->addHour());

    $job = new GenerateAtlasPdf($token, $user->id, [
        'type' => 'detail',
        'paper' => 'A4',
        'orientation' => 'landscape',
        'scope' => 'single',
        'ra' => 10.684,
        'dec' => 41.269,
        'title' => 'Andromeda',
    ], 'nl');
    app()->setLocale('en');
    $job->handle();

    $status = Cache::get(GenerateAtlasPdf::statusKey($token));
    expect($status['state'])->toBe('complete', $status['detail'] ?? '')
        ->and(app()->getLocale())->toBe('nl')
        ->and($status['filename'])->toStartWith('dsl-atlas-landscape-a4-detail')
        ->and(Storage::disk('local')->get(GenerateAtlasPdf::path($token)))->toStartWith('%PDF');

    $this->actingAs($user)
        ->get(route('downloads.dsl-atlas.download', $token))
        ->assertOk()
        ->assertDownload($status['filename']);
});

it('prunes atlas pdfs older than two days', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    $old = GenerateAtlasPdf::path('old');
    Storage::disk('local')->put($old, '%PDF');
    touch(Storage::disk('local')->path($old), now()->subHours(QueuedPdfJob::KEEP_HOURS + 1)->getTimestamp());

    (new GenerateAtlasPdf((string) Str::uuid(), $user->id, ['scope' => 'single', 'ra' => 10.684, 'dec' => 41.269]))->handle();

    expect(Storage::disk('local')->exists($old))->toBeFalse();
});
