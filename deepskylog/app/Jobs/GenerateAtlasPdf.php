<?php

namespace App\Jobs;

use App\Http\Controllers\AtlasController;
use App\Models\User;
use App\Services\StarChartService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Renders a DeepskyLog atlas in the background: the status goes to the cache
 * under atlas_pdf:{token} and the PDF to storage/app/atlas/{token}.pdf.
 */
class GenerateAtlasPdf extends QueuedPdfJob
{
    /**
     * A whole detail atlas is ~2600 pages; half an hour was nowhere near
     * enough for anything but the smallest run.
     */
    public int $timeout = 21600;

    protected static function prefix(): string
    {
        return 'atlas';
    }

    protected static function directory(): string
    {
        return 'atlas';
    }

    public static function kindLabel(?string $locale = null): string
    {
        return __('Atlas', [], $locale);
    }

    protected static function downloadRoute(): ?string
    {
        return 'downloads.dsl-atlas.download';
    }

    /**
     * A whole atlas is a PDF of a few hundred MB: it goes from the response
     * to the file in chunks, rather than through a string or two the size
     * of the PDF.
     */
    protected function writeTo(string $path, User $user, callable $progress): void
    {
        $request = Request::create('/downloads/dsl-atlas/generate', 'POST', $this->parameters);
        $request->setUserResolver(fn () => $user);

        // renderAtlas() rather than generate(): we want the real exception,
        // not the web wrapper's redirect-with-a-flash-message.
        $response = app(AtlasController::class)->renderAtlas(
            $request,
            app(StarChartService::class),
            fn (string $phase, int $page, int $totalPages) => $progress([
                'phase' => $phase,
                'page' => $page,
                'total_pages' => $totalPages,
            ])
        );
        $contentType = strtolower((string) $response->headers->get('Content-Type'));

        if ($response->getStatusCode() >= 400 || ! str_contains($contentType, 'application/pdf')) {
            throw new \RuntimeException('Atlas PDF generation returned no PDF content.');
        }

        $handle = fopen($path, 'wb');
        if ($handle === false) {
            throw new \RuntimeException('Could not write the PDF.');
        }

        $written = 0;
        ob_start(function (string $buffer) use ($handle, &$written) {
            $written += (int) fwrite($handle, $buffer);

            return '';
        }, 1024 * 1024);

        try {
            $response->sendContent();
        } finally {
            ob_end_clean();
            fclose($handle);
        }

        if ($written === 0) {
            throw new \RuntimeException('Atlas PDF generation returned no PDF content.');
        }

        // The status keeps the name, so download() can offer the file under it.
        $progress(['filename' => $this->filenameFrom($response)]);
    }

    /**
     * The name the atlas is offered under when it is downloaded.
     *
     * renderAtlas() already names its response -- a single page carries the
     * object it is centred on, which the parameters alone cannot tell us --
     * so take that name and only fall back to the parameters if the response
     * carried none.
     */
    private function filenameFrom(Response $response): string
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
