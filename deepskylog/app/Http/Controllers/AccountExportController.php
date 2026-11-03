<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateAccountExport;
use App\Livewire\Profile\DownloadMyData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

/**
 * Downloads the zip of "Download all my data" (GenerateAccountExport),
 * only for the observer whose data it holds.
 */
class AccountExportController extends Controller
{
    public function download(Request $request, string $token)
    {
        abort_unless((bool) preg_match('/^[0-9a-f-]{36}$/i', $token), 404);

        $status = Cache::get(GenerateAccountExport::statusKey($token));
        abort_unless(is_array($status) && (int) ($status['user_id'] ?? 0) === (int) $request->user()->id, 404);

        $path = GenerateAccountExport::path($token);
        abort_unless(Storage::disk('local')->exists($path), 404);

        return Response::download(Storage::disk('local')->path($path), DownloadMyData::FILENAME, [
            'Content-Type' => 'application/zip',
        ]);
    }
}
