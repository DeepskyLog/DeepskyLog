<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\ObjectSetPdfService;

/**
 * Renders an object set PDF in the background, as GenerateAtlasPdf does for
 * a whole atlas: the status goes to the cache under object_sets_pdf:{token}
 * and the PDF to storage/app/object-sets/{token}.pdf.
 */
class GenerateObjectSetsPdf extends QueuedPdfJob
{
    /**
     * Each object costs its charts and two photo downloads -- ten to thirty
     * seconds -- and a set can hold a couple of hundred of them.
     */
    public int $timeout = 21600;

    protected static function prefix(): string
    {
        return 'object_sets';
    }

    protected static function directory(): string
    {
        return 'object-sets';
    }

    public static function kindLabel(?string $locale = null): string
    {
        return __('Object sets', [], $locale);
    }

    protected static function downloadRoute(): ?string
    {
        return 'object-sets.download';
    }

    protected function renderPdf(User $user, callable $progress): string
    {
        return app(ObjectSetPdfService::class)->render(
            $this->parameters,
            $user,
            fn (int $done, int $total, string $name) => $progress([
                'phase' => $done >= $total ? 'rendering' : 'building',
                'done' => $done,
                'total' => $total,
                'object' => $name,
            ])
        );
    }
}
