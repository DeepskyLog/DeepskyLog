<?php

namespace Tests\Support;

use App\Jobs\QueuedPdfJob;
use App\Models\User;

/**
 * A PDF job whose rendering throws with internal details in the message.
 */
class FailingPdfJob extends QueuedPdfJob
{
    protected static function prefix(): string
    {
        return 'failing';
    }

    protected static function directory(): string
    {
        return 'failing';
    }

    protected function renderPdf(User $user, callable $progress): string
    {
        throw new \RuntimeException('Cannot open /secret/path/db.sqlite');
    }
}
