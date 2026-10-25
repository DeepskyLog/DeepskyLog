<?php

use App\Jobs\GenerateAtlasPdf;
use App\Jobs\GenerateObjectSetsPdf;
use App\Jobs\GenerateObservationsZip;
use App\Jobs\QueuedPdfJob;
use App\Livewire\MyDownloads;
use App\Mail\MessageReceived;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\Support\AuthRecordingPdfJob;
use Tests\Support\FailingPdfJob;

/*
 * The PDFs and zip files made in the background: a DeepskyLog message when
 * one is ready or failed, and /downloads/mine with all of them.
 */

function listPdf(string $kind, User $user, string $state, array $extra = []): string
{
    $token = (string) Str::uuid();
    Cache::put($kind::statusKey($token), $extra + [
        'state' => $state,
        'user_id' => $user->id,
        'label' => 'Label ' . $token,
        'created_at' => now()->toIso8601String(),
    ], now()->addHour());
    Cache::put($kind::recentKey($user->id), array_merge([$token], (array) Cache::get($kind::recentKey($user->id), [])), now()->addHour());

    return $token;
}

it('sends a message when a pdf nobody is watching is ready', function () {
    Storage::fake('local');
    Mail::fake();
    $user = User::factory()->create(['sendMail' => true]);
    $token = (string) Str::uuid();
    Cache::put(AuthRecordingPdfJob::statusKey($token), ['state' => 'queued', 'user_id' => $user->id, 'label' => 'M 31'], now()->addHour());

    (new AuthRecordingPdfJob($token, $user->id, [], 'nl'))->handle();

    $message = Message::where('receiver', $user->username)->sole();
    $status = Cache::get(AuthRecordingPdfJob::statusKey($token));
    expect($message->sender)->toBe('deepskylog')
        ->and($message->subject)->toContain('M 31')
        ->and($message->subject)->toStartWith('Je bestand is klaar')
        ->and($message->message)->toContain(route('downloads.mine'))
        ->and(QueuedPdfJob::expiresAt($status)->diffInHours(now(), true))->toBeGreaterThan(QueuedPdfJob::KEEP_HOURS - 1);
    Mail::assertQueued(MessageReceived::class);
});

it('sends no message when a page shows the pdf finish', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    $token = (string) Str::uuid();
    Cache::put(AuthRecordingPdfJob::statusKey($token), ['state' => 'processing', 'user_id' => $user->id], now()->addHour());

    QueuedPdfJob::watch([['token' => $token, 'state' => 'processing']]);
    (new AuthRecordingPdfJob($token, $user->id, []))->handle();

    expect(Message::where('receiver', $user->username)->exists())->toBeFalse();
});

it('sends one message when a pdf fails', function () {
    $user = User::factory()->create();
    $token = (string) Str::uuid();
    Cache::put(FailingPdfJob::statusKey($token), ['state' => 'queued', 'user_id' => $user->id], now()->addHour());

    $job = new FailingPdfJob($token, $user->id, []);
    $job->handle();
    $job->failed(null);

    $message = Message::where('receiver', $user->username)->sole();
    expect($message->subject)->toStartWith('Your file could not be made')
        ->and($message->message)->not->toContain('/secret/path');
});

it('lists the pdfs and zip files of all kinds of the observer only', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $atlas = listPdf(GenerateAtlasPdf::class, $user, 'complete', ['completed_at' => now()->toIso8601String()]);
    $zip = listPdf(GenerateObservationsZip::class, $user, 'processing', ['phase' => 'writing', 'percent' => 40]);
    $sets = listPdf(GenerateObjectSetsPdf::class, $user, 'queued');
    $foreign = listPdf(GenerateObjectSetsPdf::class, $other, 'complete');

    $this->get(route('downloads.mine'))->assertRedirect(route('login'));

    $this->actingAs($user)->get(route('downloads.mine'))
        ->assertOk()
        ->assertSee('Label ' . $atlas)
        ->assertSee(route('downloads.dsl-atlas.download', $atlas))
        ->assertSee('Can be downloaded until')
        ->assertSee('Label ' . $zip)
        ->assertSee('Being generated: 40%')
        ->assertSee('Label ' . $sets)
        ->assertDontSee('Label ' . $foreign);
});

it('removes a finished or waiting pdf, but not one being made', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    $done = listPdf(GenerateObjectSetsPdf::class, $user, 'complete');
    $waiting = listPdf(GenerateObjectSetsPdf::class, $user, 'queued');
    $busy = listPdf(GenerateObjectSetsPdf::class, $user, 'processing');
    Storage::disk('local')->put(GenerateObjectSetsPdf::path($done), '%PDF');

    Livewire::actingAs($user)->test(MyDownloads::class)
        ->call('remove', $done)
        ->call('remove', $waiting)
        ->call('remove', $busy);

    expect(Storage::disk('local')->exists(GenerateObjectSetsPdf::path($done)))->toBeFalse()
        ->and(Cache::has(GenerateObjectSetsPdf::statusKey($done)))->toBeFalse()
        ->and(Cache::has(GenerateObjectSetsPdf::statusKey($waiting)))->toBeFalse()
        ->and(Cache::get(GenerateObjectSetsPdf::statusKey($busy))['state'])->toBe('processing')
        ->and(Cache::get(GenerateObjectSetsPdf::recentKey($user->id)))->toBe([$busy]);
});

it('marks the pdfs it shows as watched', function () {
    $user = User::factory()->create();
    $token = listPdf(GenerateObjectSetsPdf::class, $user, 'queued');

    Livewire::actingAs($user)->test(MyDownloads::class);

    expect(Cache::has('pdf_watched:' . $token))->toBeTrue();
});
