<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\ObjectSetPdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Renders an object set PDF in the background, as GenerateAtlasPdf does for
 * a whole atlas: the status goes to the cache under object_sets_pdf:{token}
 * and the PDF to storage/app/object-sets/{token}.pdf.
 */
class GenerateObjectSetsPdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Each object costs its charts and two photo downloads -- ten to thirty
     * seconds -- and a set can hold a couple of hundred of them.
     */
    public int $timeout = 21600;

    public int $tries = 1;

    /** Generated sets are kept this long for downloading. */
    public const KEEP_HOURS = 48;

    public function __construct(
        public readonly string $token,
        public readonly int $userId,
        public readonly array $parameters,
    ) {
    }

    public static function statusKey(string $token): string
    {
        return 'object_sets_pdf:' . $token;
    }

    /**
     * Cache key of the list of a user's recent sets, newest first.
     */
    public static function recentKey(int $userId): string
    {
        return 'object_sets_recent:' . $userId;
    }

    public static function path(string $token): string
    {
        return 'object-sets/' . $token . '.pdf';
    }

    public function handle(ObjectSetPdfService $service): void
    {
        $this->updateStatus('processing');
        $this->pruneOldSets();

        try {
            $user = User::findOrFail($this->userId);

            $content = $service->render(
                $this->parameters,
                $user,
                fn (int $done, int $total, string $name) => $this->updateStatus('processing', [
                    'phase' => $done >= $total ? 'rendering' : 'building',
                    'done' => $done,
                    'total' => $total,
                    'object' => $name,
                ])
            );

            if ($content === '') {
                throw new \RuntimeException('Object set generation returned no PDF content.');
            }

            $path = self::path($this->token);
            Storage::disk('local')->makeDirectory('object-sets');
            Storage::disk('local')->put($path, $content);
            // The worker usually runs as another user than the web server,
            // which must still be able to read the file to offer the download.
            @chmod(storage_path('app/object-sets'), 0755);
            @chmod(Storage::disk('local')->path($path), 0644);

            $this->updateStatus('complete', ['path' => $path]);
            $this->removeReplacedSets();
        } catch (Throwable $exception) {
            $this->updateStatus('failed', [
                'error' => 'Object set generation failed.',
                'detail' => Str::limit(get_class($exception) . ': ' . $exception->getMessage(), 500),
            ]);

            try {
                report($exception);
            } catch (Throwable $_) {
                // Never let a broken log channel take the worker down.
            }
        }
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    private function updateStatus(string $state, array $extra = []): void
    {
        $previous = Cache::get(self::statusKey($this->token));

        Cache::put(self::statusKey($this->token), array_merge([
            'user_id' => $this->userId,
            'label' => is_array($previous) ? ($previous['label'] ?? '') : '',
            'filename' => is_array($previous) ? ($previous['filename'] ?? 'object-sets.pdf') : 'object-sets.pdf',
            'created_at' => is_array($previous) ? ($previous['created_at'] ?? now()->toIso8601String()) : now()->toIso8601String(),
        ], $extra, ['state' => $state]), now()->addHours(self::KEEP_HOURS));
    }

    /**
     * A new set of the same list or object replaces the older ones: they
     * are the same PDF made again, so only the newest is worth keeping.
     *
     * This runs once the new PDF is there, so a failed run never leaves the
     * observer without the set they had. A set still being made is left
     * alone; it replaces this one in turn when it finishes, if it is newer.
     */
    private function removeReplacedSets(): void
    {
        try {
            $current = Cache::get(self::statusKey($this->token));
            $filename = is_array($current) ? ($current['filename'] ?? null) : null;
            $createdAt = is_array($current) ? (string) ($current['created_at'] ?? '') : '';
            if (! is_string($filename) || $filename === '') {
                return;
            }

            $recentKey = self::recentKey($this->userId);
            $tokens = collect(Cache::get($recentKey, []))->filter(fn ($token) => is_string($token));
            $removed = [];

            foreach ($tokens as $token) {
                if ($token === $this->token) {
                    continue;
                }

                $status = Cache::get(self::statusKey($token));
                if (! is_array($status)
                    || (int) ($status['user_id'] ?? 0) !== $this->userId
                    || ($status['filename'] ?? null) !== $filename
                    || in_array($status['state'] ?? '', ['queued', 'processing'], true)
                    || (string) ($status['created_at'] ?? '') > $createdAt) {
                    continue;
                }

                Storage::disk('local')->delete(self::path($token));
                Cache::forget(self::statusKey($token));
                $removed[] = $token;
            }

            if ($removed !== []) {
                Cache::put(
                    $recentKey,
                    $tokens->reject(fn (string $token) => in_array($token, $removed, true))->values()->all(),
                    now()->addHours(self::KEEP_HOURS)
                );
            }
        } catch (Throwable $_) {
            // Housekeeping only; the new set is already there.
        }
    }

    /**
     * Sets are only kept for a couple of days; without this the directory
     * would grow with every set anyone ever made.
     */
    private function pruneOldSets(): void
    {
        try {
            $disk = Storage::disk('local');
            $cutoff = now()->subHours(self::KEEP_HOURS)->getTimestamp();

            foreach ($disk->files('object-sets') as $file) {
                if ($disk->lastModified($file) < $cutoff) {
                    $disk->delete($file);
                }
            }
        } catch (Throwable $_) {
            // Housekeeping only.
        }
    }
}
