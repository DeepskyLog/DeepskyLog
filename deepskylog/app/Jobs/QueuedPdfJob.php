<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * A PDF an observer asked for, rendered in the background: the status goes
 * to the cache under {prefix}_pdf:{token} and the PDF to
 * storage/app/{directory}/{token}.pdf, where it is kept for KEEP_HOURS.
 *
 * Each observer's recent PDFs of a kind are listed in the cache as well, so
 * the page that queued them can show them again when the observer returns.
 *
 * These jobs run for up to hours, so they go to the long-running connection
 * of config/queue.php, whose retry_after outlasts them: on the default
 * connection a second worker would pick a PDF up again after 90 seconds.
 */
abstract class QueuedPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    /** Generated PDFs are kept this long for downloading. */
    public const KEEP_HOURS = 48;

    /**
     * How many PDFs, of all kinds together, an observer may have waiting or
     * being made: one worker makes them one after the other, for hours.
     */
    public const MAX_OPEN_PER_USER = 2;

    /** How many PDFs, of all kinds together, an observer may ask for in an hour. */
    public const MAX_PER_HOUR = 20;

    /** How many of a user's recent PDFs of a kind are kept track of. */
    public const RECENT = 20;

    /** @var array<int, class-string<QueuedPdfJob>> */
    public const KINDS = [GenerateAtlasPdf::class, GenerateObjectSetsPdf::class, GenerateObjectReportPdf::class];

    /**
     * The language of the request that queued the PDF: the worker has no
     * session to take it from.
     */
    public string $locale;

    /*
     * Not readonly: the queue restores these properties when it unserializes
     * the job, from the scope of the subclass, and PHP only lets the class
     * that declares a readonly property initialize it.
     */
    public function __construct(
        public string $token,
        public int $userId,
        public array $parameters,
        ?string $locale = null,
    ) {
        $this->locale = $locale ?? App::getLocale();

        if (is_string($connection = config('queue.long_running'))) {
            $this->onConnection($connection);
        }
    }

    /** Cache key prefix, e.g. "object_sets". */
    abstract protected static function prefix(): string;

    /** Directory under storage/app, e.g. "object-sets". */
    abstract protected static function directory(): string;

    /**
     * @param  callable(array<string, mixed>): void  $progress  merges its argument into the status
     */
    abstract protected function renderPdf(User $user, callable $progress): string;

    public static function statusKey(string $token): string
    {
        return static::prefix() . '_pdf:' . $token;
    }

    /**
     * Cache key of the list of a user's recent PDFs, newest first.
     */
    public static function recentKey(int $userId): string
    {
        return static::prefix() . '_recent:' . $userId;
    }

    public static function path(string $token): string
    {
        return static::directory() . '/' . $token . '.pdf';
    }

    /**
     * Why the observer may not queue another PDF now, or null when they may.
     */
    public static function refusal(int $userId): ?string
    {
        if (self::openCount($userId) >= self::MAX_OPEN_PER_USER) {
            return __('You already have :count PDFs waiting or being made. Wait until one of them is ready.', ['count' => self::MAX_OPEN_PER_USER]);
        }

        $key = self::rateKey($userId);
        if (RateLimiter::tooManyAttempts($key, self::MAX_PER_HOUR)) {
            return __('You have asked for too many PDFs. Try again in :minutes minutes.', [
                'minutes' => max(1, (int) ceil(RateLimiter::availableIn($key) / 60)),
            ]);
        }

        return null;
    }

    /**
     * Count a queued PDF towards the observer's hourly limit.
     */
    public static function recordQueued(int $userId): void
    {
        RateLimiter::hit(self::rateKey($userId), 3600);
    }

    /**
     * The observer's PDFs, of all kinds, that are waiting or being made.
     */
    public static function openCount(int $userId): int
    {
        $open = 0;

        foreach (self::KINDS as $kind) {
            foreach ((array) Cache::get($kind::recentKey($userId), []) as $token) {
                $status = is_string($token) ? Cache::get($kind::statusKey($token)) : null;

                if (is_array($status)
                    && (int) ($status['user_id'] ?? 0) === $userId
                    && in_array($status['state'] ?? '', ['queued', 'processing'], true)) {
                    $open++;
                }
            }
        }

        return $open;
    }

    private static function rateKey(int $userId): string
    {
        return 'pdf_jobs:' . $userId;
    }

    /**
     * Forget the observer's earlier PDFs of the same list that are not
     * finished -- waiting, being made or failed -- now that a new one of it
     * has been asked for. A finished one stays until the new one is ready,
     * which then replaces it (see removeReplacedPdfs).
     *
     * @param  array<int, string>  $tokens  the observer's recent PDFs
     * @return array<int, string>  the tokens that are left
     */
    public static function forgetUnfinished(int $userId, string $filename, array $tokens): array
    {
        return array_values(array_filter($tokens, function ($token) use ($userId, $filename) {
            $status = Cache::get(static::statusKey($token));

            if (! is_array($status)
                || (int) ($status['user_id'] ?? 0) !== $userId
                || ($status['filename'] ?? null) !== $filename
                || ($status['state'] ?? '') === 'complete') {
                return true;
            }

            Cache::forget(static::statusKey($token));
            Storage::disk('local')->delete(static::path($token));

            return false;
        }));
    }

    public function handle(): void
    {
        $this->pruneOldPdfs();

        // Forgotten while it waited, because the observer asked for the same
        // PDF again (forgetUnfinished): the newer job makes it. Without this
        // asking again and again would fill the worker with the same PDF.
        if (! Cache::has(static::statusKey($this->token)) && ! $this->stillWanted()) {
            return;
        }

        $this->updateStatus('processing');

        App::setLocale($this->locale);
        Carbon::setLocale($this->locale);

        $previousUser = Auth::hasUser() ? Auth::user() : null;

        try {
            $user = User::findOrFail($this->userId);

            // The charts mark what the observer saw or drew themselves, and
            // ask auth() who that is; the worker has no one logged in.
            Auth::setUser($user);

            $content = $this->renderPdf($user, fn (array $extra) => $this->updateStatus('processing', $extra));

            if ($content === '') {
                throw new \RuntimeException('PDF generation returned no content.');
            }

            if (! $this->stillWanted()) {
                // A newer PDF of the same list was asked for meanwhile.
                Cache::forget(static::statusKey($this->token));

                return;
            }

            $path = static::path($this->token);
            Storage::disk('local')->makeDirectory(static::directory());
            Storage::disk('local')->put($path, $content);
            // The worker usually runs as another user than the web server,
            // which must still be able to read the file to offer the download.
            @chmod(storage_path('app/' . static::directory()), 0755);
            @chmod(Storage::disk('local')->path($path), 0644);

            $this->updateStatus('complete', ['path' => $path]);
            $this->removeReplacedPdfs();
        } catch (Throwable $exception) {
            $this->updateStatus('failed', [
                'error' => 'PDF generation failed.',
                'detail' => Str::limit(get_class($exception) . ': ' . $exception->getMessage(), 500),
            ]);

            try {
                report($exception);
            } catch (Throwable $_) {
                // Never let a broken log channel take the worker down.
            }
        } finally {
            // The worker goes on to other jobs, which must not run as this
            // user; run synchronously, the request keeps its own.
            $previousUser === null ? Auth::forgetUser() : Auth::setUser($previousUser);
        }
    }

    /**
     * Called by the queue when the job died outside handle()'s own catch:
     * it ran out of time, or the worker was killed (e.g. out of memory) and
     * the job was given up on after retry_after. Without this the status
     * would say "processing" until it expired.
     */
    public function failed(?Throwable $exception): void
    {
        // Without a status the PDF expired or was forgotten for a newer one.
        $status = Cache::get(static::statusKey($this->token));
        if (! is_array($status) || ($status['state'] ?? '') === 'complete') {
            return;
        }

        $this->updateStatus('failed', [
            'error' => 'PDF generation failed.',
            'detail' => $exception === null
                ? 'The worker stopped before the PDF was finished.'
                : Str::limit(get_class($exception) . ': ' . $exception->getMessage(), 500),
        ]);
    }

    /**
     * Whether the observer still has this PDF in their list of recent PDFs,
     * rather than having asked for a new one of the same list since.
     */
    private function stillWanted(): bool
    {
        $recent = Cache::get(static::recentKey($this->userId));

        // Without a list (it expired, or the job was queued by hand) there is
        // nothing to say the PDF is not wanted.
        return ! is_array($recent) || in_array($this->token, $recent, true);
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    private function updateStatus(string $state, array $extra = []): void
    {
        $previous = Cache::get(static::statusKey($this->token));

        Cache::put(static::statusKey($this->token), array_merge([
            'user_id' => $this->userId,
            'label' => is_array($previous) ? ($previous['label'] ?? '') : '',
            'filename' => is_array($previous) ? ($previous['filename'] ?? static::directory() . '.pdf') : static::directory() . '.pdf',
            'created_at' => is_array($previous) ? ($previous['created_at'] ?? now()->toIso8601String()) : now()->toIso8601String(),
        ], $extra, ['state' => $state]), now()->addHours(static::KEEP_HOURS));
    }

    /**
     * A new PDF of the same list or object replaces the older ones: they
     * are the same PDF made again, so only the newest is worth keeping.
     *
     * This runs once the new PDF is there, so a failed run never leaves the
     * observer without the PDF they had. A PDF still being made is left
     * alone; it replaces this one in turn when it finishes, if it is newer.
     */
    private function removeReplacedPdfs(): void
    {
        try {
            $current = Cache::get(static::statusKey($this->token));
            $filename = is_array($current) ? ($current['filename'] ?? null) : null;
            $createdAt = is_array($current) ? (string) ($current['created_at'] ?? '') : '';
            if (! is_string($filename) || $filename === '') {
                return;
            }

            $recentKey = static::recentKey($this->userId);
            $tokens = collect(Cache::get($recentKey, []))->filter(fn ($token) => is_string($token));
            $removed = [];

            foreach ($tokens as $token) {
                if ($token === $this->token) {
                    continue;
                }

                $status = Cache::get(static::statusKey($token));
                if (! is_array($status)
                    || (int) ($status['user_id'] ?? 0) !== $this->userId
                    || ($status['filename'] ?? null) !== $filename
                    || in_array($status['state'] ?? '', ['queued', 'processing'], true)
                    || (string) ($status['created_at'] ?? '') > $createdAt) {
                    continue;
                }

                Storage::disk('local')->delete(static::path($token));
                Cache::forget(static::statusKey($token));
                $removed[] = $token;
            }

            if ($removed !== []) {
                Cache::put(
                    $recentKey,
                    $tokens->reject(fn (string $token) => in_array($token, $removed, true))->values()->all(),
                    now()->addHours(static::KEEP_HOURS)
                );
            }
        } catch (Throwable $_) {
            // Housekeeping only; the new PDF is already there.
        }
    }

    /**
     * PDFs are only kept for a couple of days; without this the directory
     * would grow with every PDF anyone ever made.
     */
    private function pruneOldPdfs(): void
    {
        try {
            $disk = Storage::disk('local');
            $cutoff = now()->subHours(static::KEEP_HOURS)->getTimestamp();

            foreach ($disk->files(static::directory()) as $file) {
                if ($disk->lastModified($file) < $cutoff) {
                    $disk->delete($file);
                }
            }
        } catch (Throwable $_) {
            // Housekeeping only.
        }
    }
}
