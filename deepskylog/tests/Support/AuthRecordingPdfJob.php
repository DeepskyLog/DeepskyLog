<?php

namespace Tests\Support;

use App\Jobs\QueuedPdfJob;
use App\Models\User;

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
