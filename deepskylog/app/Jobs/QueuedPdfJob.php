<?php

namespace App\Jobs;

use App\Mail\MessageReceived;
use App\Models\Message;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * A PDF an observer asked for, rendered in the background: the status goes
 * to the cache under {prefix}_pdf:{token} and the PDF to
 * storage/app/{directory}/{token}.pdf, where it is kept for KEEP_HOURS.
 * A job that makes another kind of file (the zip of GenerateObservationsZip)
 * gives its extension() and writes it itself in writeTo().
 *
 * Each observer's recent PDFs of a kind are listed in the cache as well, so
 * the page that queued them can show them again when the observer returns;
 * /downloads/mine lists those of all kinds (jobsFor()). When a PDF is ready
 * or failed, the observer gets a DeepskyLog message about it, unless a page
 * showing its status was open (watch()).
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

    /**
     * A page that shows the status of a PDF marks it as watched for this
     * long, and polls more often: the observer sees it finish there, so no
     * message is sent. A tab in the background polls less often.
     */
    public const WATCH_SECONDS = 20;

    /** @var array<int, class-string<QueuedPdfJob>> */
    public const KINDS = [GenerateAtlasPdf::class, GenerateObjectSetsPdf::class, GenerateObjectReportPdf::class, GenerateObservationsZip::class];

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

    /** Extension of the files the job makes. */
    protected static function extension(): string
    {
        return 'pdf';
    }

    /** What kind of file the job makes, for the downloads page and the message. */
    public static function kindLabel(?string $locale = null): string
    {
        return __('PDF', [], $locale);
    }

    /** Name of the route that downloads the finished file by its token. */
    protected static function downloadRoute(): ?string
    {
        return null;
    }

    public static function downloadUrl(string $token): ?string
    {
        return ($route = static::downloadRoute()) !== null ? route($route, $token) : null;
    }

    /**
     * @param  callable(array<string, mixed>): void  $progress  merges its argument into the status
     */
    protected function renderPdf(User $user, callable $progress): string
    {
        throw new \LogicException(static::class . ' must override renderPdf() or writeTo().');
    }

    /**
     * Write the file to $path (absolute). A PDF is rendered in memory by
     * renderPdf(); a job whose file does not fit in memory overrides this.
     *
     * @param  callable(array<string, mixed>): void  $progress  merges its argument into the status
     */
    protected function writeTo(string $path, User $user, callable $progress): void
    {
        $content = $this->renderPdf($user, $progress);

        if ($content === '') {
            throw new \RuntimeException('PDF generation returned no content.');
        }

        if (file_put_contents($path, $content) === false) {
            throw new \RuntimeException('Could not write the PDF.');
        }
    }

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
        return static::directory() . '/' . $token . '.' . static::extension();
    }

    /**
     * Why the observer may not queue another PDF now, or null when they may.
     */
    public static function refusal(int $userId): ?string
    {
        if (self::openCount($userId) >= self::MAX_OPEN_PER_USER) {
            return __('You already have :count PDFs or zip files waiting or being made. Wait until one of them is ready.', ['count' => self::MAX_OPEN_PER_USER]);
        }

        $key = self::rateKey($userId);
        if (RateLimiter::tooManyAttempts($key, self::MAX_PER_HOUR)) {
            return __('You have asked for too many PDFs or zip files. Try again in :minutes minutes.', [
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
     * The observer's PDFs of all kinds, newest first, each its status with
     * the token and the job class under `kind`.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public static function jobsFor(int $userId): Collection
    {
        $jobs = collect();

        foreach (self::KINDS as $kind) {
            foreach ((array) Cache::get($kind::recentKey($userId), []) as $token) {
                $status = is_string($token) ? Cache::get($kind::statusKey($token)) : null;

                if (is_array($status) && (int) ($status['user_id'] ?? 0) === $userId) {
                    $jobs->push(['token' => $token, 'kind' => $kind] + $status);
                }
            }
        }

        return $jobs->sortByDesc(fn (array $job) => (string) ($job['created_at'] ?? ''))->values();
    }

    /**
     * Mark the PDFs that are waiting or being made as watched: a page shows
     * their status, so the observer needs no message when they finish.
     *
     * @param  iterable<array<string, mixed>>  $jobs  statuses with their token
     */
    public static function watch(iterable $jobs): void
    {
        foreach ($jobs as $job) {
            if (in_array($job['state'] ?? '', ['queued', 'processing'], true) && is_string($job['token'] ?? null)) {
                Cache::put(self::watchKey($job['token']), true, self::WATCH_SECONDS);
            }
        }
    }

    private static function watchKey(string $token): string
    {
        return 'pdf_watched:' . $token;
    }

    /**
     * Until when a finished PDF can be downloaded: its status, without
     * which download() refuses it, expires KEEP_HOURS after it was finished.
     *
     * @param  array<string, mixed>  $status
     */
    public static function expiresAt(array $status): ?Carbon
    {
        if (($status['state'] ?? '') !== 'complete' || ! is_string($status['completed_at'] ?? null)) {
            return null;
        }

        return Carbon::parse($status['completed_at'])->addHours(static::KEEP_HOURS);
    }

    /**
     * How far a PDF being made is, in percent, or null when the job does
     * not say.
     *
     * @param  array<string, mixed>  $status
     */
    public static function progressPercent(array $status): ?int
    {
        [$done, $total] = match (true) {
            isset($status['percent']) => [(int) $status['percent'], 100],
            isset($status['page'], $status['total_pages']) => [(int) $status['page'], (int) $status['total_pages']],
            isset($status['done'], $status['total']) => [(int) $status['done'], (int) $status['total']],
            default => [0, 0],
        };

        return $total > 0 ? max(0, min(100, (int) floor(100 * $done / $total))) : null;
    }

    /**
     * Remove one of the observer's PDFs: a finished or failed one with its
     * file, or one still waiting, which handle() then skips. One being made
     * is left alone: the worker is busy with it anyway, and removing it
     * would free a place for another (MAX_OPEN_PER_USER).
     */
    public static function remove(int $userId, string $token): bool
    {
        foreach (self::KINDS as $kind) {
            $recent = (array) Cache::get($kind::recentKey($userId), []);
            if (! in_array($token, $recent, true)) {
                continue;
            }

            $status = Cache::get($kind::statusKey($token));
            if (is_array($status) && ((int) ($status['user_id'] ?? 0) !== $userId || ($status['state'] ?? '') === 'processing')) {
                return false;
            }

            Cache::forget($kind::statusKey($token));
            Storage::disk('local')->delete($kind::path($token));
            Cache::put(
                $kind::recentKey($userId),
                array_values(array_filter($recent, fn ($listed) => $listed !== $token)),
                now()->addHours($kind::KEEP_HOURS)
            );

            return true;
        }

        return false;
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

            $disk = Storage::disk('local');
            $path = static::path($this->token);
            $partial = $path . '.part';
            $disk->makeDirectory(static::directory());

            try {
                $this->writeTo($disk->path($partial), $user, fn (array $extra) => $this->updateStatus('processing', $extra));

                if (! $this->stillWanted()) {
                    // A newer PDF of the same list was asked for meanwhile.
                    Cache::forget(static::statusKey($this->token));

                    return;
                }

                $disk->move($partial, $path);
            } finally {
                $disk->delete($partial);
            }

            // The worker usually runs as another user than the web server,
            // which must still be able to read the file to offer the download.
            @chmod(storage_path('app/' . static::directory()), 0755);
            @chmod(Storage::disk('local')->path($path), 0644);

            $this->updateStatus('complete', ['path' => $path, 'completed_at' => now()->toIso8601String()]);
            $this->keepListed();
            $this->removeReplacedPdfs();
            $this->notifyObserver(true);
        } catch (Throwable $exception) {
            // The exception text can name files, queries or settings, so it
            // goes to the log and the observer only gets a generic message.
            $this->updateStatus('failed', ['error' => 'PDF generation failed.']);
            $this->notifyObserver(false);

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
        if (! is_array($status) || in_array($status['state'] ?? '', ['complete', 'failed'], true)) {
            return;
        }

        // The queue logs the exception in failed_jobs; the observer is only
        // told that the PDF was not finished.
        $this->updateStatus('failed', [
            'error' => 'PDF generation failed.',
            'detail' => 'The worker stopped before the PDF was finished.',
        ]);
        $this->notifyObserver(false);
    }

    /**
     * Keep the PDF in the observer's list as long as the PDF itself: the
     * list was stored when it was queued, maybe hours ago.
     */
    private function keepListed(): void
    {
        $recent = Cache::get(static::recentKey($this->userId));

        if (is_array($recent) && in_array($this->token, $recent, true)) {
            Cache::put(static::recentKey($this->userId), $recent, now()->addHours(static::KEEP_HOURS));
        }
    }

    /**
     * Tell the observer in a DeepskyLog message (and a mail, if they want
     * those) that the PDF is ready or failed, in the language they asked
     * for it in. Not when a page showing its status is open: they see it
     * there.
     */
    private function notifyObserver(bool $ready): void
    {
        try {
            $status = Cache::get(static::statusKey($this->token));
            $user = User::find($this->userId);
            if (! is_array($status) || $user === null || Cache::has(self::watchKey($this->token))) {
                return;
            }

            $locale = $this->locale;
            $label = trim((string) ($status['label'] ?? '')) ?: (string) ($status['filename'] ?? '');
            $name = static::kindLabel($locale) . ($label !== '' ? ' - ' . $label : '');
            $overview = '<a href="' . e(route('downloads.mine')) . '">' . e(__('Your PDFs and zip files', [], $locale)) . '</a>';

            if ($ready) {
                $subject = __('Your file is ready: :name', ['name' => $name], $locale);
                $body = '<p>' . e(__('The file ":name" you asked for is ready. You can download it until :date.', [
                    'name' => $name,
                    'date' => static::expiresAt($status)?->locale($locale)->isoFormat('LLL') ?? '',
                ], $locale)) . '</p><p>';
                if (($url = static::downloadUrl($this->token)) !== null) {
                    $body .= '<a href="' . e($url) . '">' . e(__('Download', [], $locale)) . '</a> - ';
                }
                $body .= $overview . '</p>';
            } else {
                $subject = __('Your file could not be made: :name', ['name' => $name], $locale);
                $body = '<p>' . e(__('The file ":name" you asked for could not be made. You can ask for it again; if that fails too, please contact the administrators.', ['name' => $name], $locale))
                    . '</p><p>' . $overview . '</p>';
            }

            $body = Message::sanitizeHtml($body);
            $message = Message::create([
                'sender' => 'deepskylog',
                'receiver' => $user->username,
                'subject' => Message::sanitizeHtml($subject),
                'message' => $body,
                'date' => now(),
            ]);

            if ($user->sendMail) {
                Mail::to($user->email)
                    ->locale($locale)
                    ->send(new MessageReceived('DeepskyLog', $subject, $body, $user->name, route('messages.show', $message->id)));
            }
        } catch (Throwable $exception) {
            // The PDF is there; only the message about it failed.
            try {
                report($exception);
            } catch (Throwable $_) {
            }
        }
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
            'filename' => is_array($previous) ? ($previous['filename'] ?? static::directory() . '.' . static::extension()) : static::directory() . '.' . static::extension(),
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
