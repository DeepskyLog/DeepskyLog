<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\Observations\ObservationZipBuilder;

/**
 * Makes a zip of observations with their drawings in the background
 * (ObservationZipBuilder): the status goes to the cache under
 * observation_zips_pdf:{token} and the zip to
 * storage/app/observation-zips/{token}.zip.
 */
class GenerateObservationsZip extends QueuedPdfJob
{
    /** 2,650 drawings (2.1 GB) take 15 s, the OAL of 25,000 observations four minutes. */
    public int $timeout = 3600;

    protected static function prefix(): string
    {
        return 'observation_zips';
    }

    protected static function directory(): string
    {
        return 'observation-zips';
    }

    public static function kindLabel(?string $locale = null): string
    {
        return __('Zip file with drawings', [], $locale);
    }

    protected static function downloadRoute(): ?string
    {
        return 'observations.zip.download';
    }

    protected static function extension(): string
    {
        return 'zip';
    }

    protected function writeTo(string $path, User $user, callable $progress): void
    {
        app(ObservationZipBuilder::class)->write($path, $this->parameters, $progress);
    }
}
