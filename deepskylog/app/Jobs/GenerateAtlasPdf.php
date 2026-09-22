<?php

namespace App\Jobs;

use App\Http\Controllers\AtlasController;
use App\Services\StarChartService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class GenerateAtlasPdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * A whole detail atlas is ~2600 pages; half an hour was nowhere near
     * enough for anything but the smallest run.
     */
    public int $timeout = 21600;

    public int $tries = 1;

    public function __construct(
        public readonly string $token,
        public readonly int $userId,
        public readonly array $parameters,
    ) {
    }

    public function handle(AtlasController $atlasController): void
    {
        $this->updateStatus('processing');

        try {
            $parameters = $this->parameters;
            $parameters['_user_id'] = $this->userId;
            $request = Request::create('/downloads/dsl-atlas/generate', 'POST', $parameters);

            // renderAtlas() rather than generate(): we want the real exception,
            // not the web wrapper's redirect-with-a-flash-message.
            $response = $atlasController->renderAtlas(
                $request,
                app(StarChartService::class),
                fn(string $phase, int $page, int $totalPages) => $this->updateStatus('processing', [
                    'phase' => $phase,
                    'page' => $page,
                    'total_pages' => $totalPages,
                ])
            );
            ob_start();
            $response->sendContent();
            $content = ob_get_clean();
            $contentType = strtolower((string) $response->headers->get('Content-Type'));

            if (! is_string($content) || $content === '' || $response->getStatusCode() >= 400 || ! str_contains($contentType, 'application/pdf')) {
                throw new \RuntimeException('Atlas PDF generation returned no PDF content.');
            }

            $path = 'atlas/' . $this->token . '.pdf';
            Storage::disk('local')->makeDirectory('atlas');
            Storage::disk('local')->put($path, $content);
            @chmod(storage_path('app/atlas'), 0755);
            @chmod(Storage::disk('local')->path($path), 0644);
            Cache::put($this->statusKey(), [
                'state' => 'complete',
                'user_id' => $this->userId,
                'path' => $path,
                'filename' => $this->filenameFrom($response),
            ], now()->addDay());
        } catch (Throwable $exception) {
            Cache::put($this->statusKey(), [
                'state' => 'failed',
                'user_id' => $this->userId,
                'error' => 'Atlas PDF generation failed.',
                'detail' => Str::limit(get_class($exception) . ': ' . $exception->getMessage(), 500),
            ], now()->addDay());

            try {
                report($exception);
            } catch (Throwable $_) {
                // Reporting failed too (e.g. an unwritable log file). The cached
                // status above already carries the cause; never let this kill
                // the job -- an uncaught throw here takes the worker down with it.
            }
        }
    }

    /**
     * @param  array<string, mixed>  $progress
     */
    private function updateStatus(string $state, array $progress = []): void
    {
        Cache::put($this->statusKey(), array_merge([
            'state' => $state,
            'user_id' => $this->userId,
        ], $progress), now()->addDay());
    }

    private function statusKey(): string
    {
        return 'atlas_pdf:' . $this->token;
    }

    /**
     * The name the atlas is offered under when it is downloaded.
     *
     * renderAtlas() already names its response -- a single page carries the
     * object it is centred on, which the parameters alone cannot tell us --
     * so take that name and only fall back to the parameters if the response
     * carried none.
     */
    private function filenameFrom(\Symfony\Component\HttpFoundation\Response $response): string
    {
        $disposition = (string) $response->headers->get('Content-Disposition');

        if (preg_match('/filename="?([^";]+)"?/i', $disposition, $matches) === 1) {
            $name = trim($matches[1]);

            if ($name !== '') {
                return $name;
            }
        }

        return AtlasController::filenameFor(
            (string) ($this->parameters['type'] ?? 'detail'),
            (string) ($this->parameters['paper'] ?? 'A4'),
            (string) ($this->parameters['orientation'] ?? 'landscape'),
        );
    }
}
