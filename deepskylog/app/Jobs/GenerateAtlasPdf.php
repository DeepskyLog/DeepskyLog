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

    protected function renderPdf(User $user, callable $progress): string
    {
        $parameters = $this->parameters;
        $parameters['_user_id'] = $user->id;
        $request = Request::create('/downloads/dsl-atlas/generate', 'POST', $parameters);
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
        ob_start();
        $response->sendContent();
        $content = ob_get_clean();
        $contentType = strtolower((string) $response->headers->get('Content-Type'));

        if (! is_string($content) || $content === '' || $response->getStatusCode() >= 400 || ! str_contains($contentType, 'application/pdf')) {
            throw new \RuntimeException('Atlas PDF generation returned no PDF content.');
        }

        // The status keeps the name, so download() can offer the file under it.
        $progress(['filename' => $this->filenameFrom($response)]);

        return $content;
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
