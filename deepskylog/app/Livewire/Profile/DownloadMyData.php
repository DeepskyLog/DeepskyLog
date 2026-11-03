<?php

namespace App\Livewire\Profile;

use App\Jobs\GenerateAccountExport;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * "Download all my data" on the profile page: queues the zip of
 * AccountDataExport (GenerateAccountExport) and shows how far it is, until
 * it can be downloaded. The zip is also listed on /downloads/mine.
 */
class DownloadMyData extends Component
{
    /** The file name the zip is downloaded as, also how a newer one replaces it. */
    public const FILENAME = 'deepskylog-my-data.zip';

    public function generate(): void
    {
        $userId = (int) auth()->id();
        if (($refusal = GenerateAccountExport::refusal($userId)) !== null) {
            $this->addError('generate', $refusal);

            return;
        }

        $token = (string) Str::uuid();
        Cache::put(GenerateAccountExport::statusKey($token), [
            'state' => 'queued',
            'user_id' => $userId,
            'label' => (string) auth()->user()->username,
            'filename' => self::FILENAME,
            'created_at' => now()->toIso8601String(),
        ], now()->addHours(GenerateAccountExport::KEEP_HOURS));

        GenerateAccountExport::dispatch($token, $userId, []);
        GenerateAccountExport::recordQueued($userId);

        $tokens = GenerateAccountExport::forgetUnfinished($userId, self::FILENAME, $this->tokens($userId));
        array_unshift($tokens, $token);
        Cache::put(
            GenerateAccountExport::recentKey($userId),
            array_slice($tokens, 0, GenerateAccountExport::RECENT),
            now()->addHours(GenerateAccountExport::KEEP_HOURS)
        );

        $this->resetErrorBag('generate');
    }

    public function render()
    {
        $userId = (int) auth()->id();

        // The newest only: a finished one replaces the older ones.
        $job = collect($this->tokens($userId))
            ->map(fn (string $token) => ($status = Cache::get(GenerateAccountExport::statusKey($token))) && (int) ($status['user_id'] ?? 0) === $userId
                ? ['token' => $token] + $status
                : null)
            ->filter()
            ->first();

        if ($job !== null) {
            // The observer sees the result here and needs no message about it.
            GenerateAccountExport::markSeen([$job]);
        }

        return view('livewire.profile.download-my-data', [
            'job' => $job,
            'pending' => in_array($job['state'] ?? '', ['queued', 'processing'], true),
            'expiresAt' => $job !== null ? GenerateAccountExport::expiresAt($job) : null,
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function tokens(int $userId): array
    {
        return array_values(array_filter((array) Cache::get(GenerateAccountExport::recentKey($userId), []), 'is_string'));
    }
}
