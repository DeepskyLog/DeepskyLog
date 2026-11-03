<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\AccountDataExport;

/**
 * Makes the zip of "Download all my data" in the background
 * (AccountDataExport): the status goes to the cache under
 * account_export_pdf:{token} and the zip to
 * storage/app/account-exports/{token}.zip.
 */
class GenerateAccountExport extends QueuedPdfJob
{
    /** As GenerateObservationsZip, which makes most of it. */
    public int $timeout = 3600;

    protected static function prefix(): string
    {
        return 'account_export';
    }

    protected static function directory(): string
    {
        return 'account-exports';
    }

    public static function kindLabel(?string $locale = null): string
    {
        return __('All my data', [], $locale);
    }

    protected static function downloadRoute(): ?string
    {
        return 'account-export.download';
    }

    protected static function extension(): string
    {
        return 'zip';
    }

    protected function writeTo(string $path, User $user, callable $progress): void
    {
        app(AccountDataExport::class)->write($path, $user, $progress);
    }
}
