<?php

namespace App\Support\Exports;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use PowerComponents\Turbine\Components\SetUp\Exportable;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * PowerGrid's exporters write to storage_path('{fileName}.{ext}'): one file
 * directly in storage/, which the web server may not be allowed to write
 * (500 Server Error), and which every export running at the same moment
 * shares. Each download gets a file of its own in storage/app/exports instead,
 * deleted once sent, under the name the table chose.
 */
trait WritesOwnFile
{
    /** The file extension of this exporter ('csv', 'xlsx'). */
    abstract protected function extension(): string;

    public function download(Exportable|array $exportOptions): BinaryFileResponse
    {
        $downloadName = $this->fileName.'.'.$this->extension();

        File::ensureDirectoryExists(storage_path('app/exports'));
        $this->fileName = 'app/exports/'.$this->fileName.'-'.Str::uuid();

        try {
            // The parent builds the file and answers with it; its download
            // name (with the uuid) is replaced by the table's.
            $response = parent::download($exportOptions);
        } catch (\Throwable $e) {
            @unlink(storage_path($this->fileName.'.'.$this->extension()));

            throw $e;
        }

        return $response
            ->setContentDisposition('attachment', $downloadName)
            ->deleteFileAfterSend(true);
    }
}
