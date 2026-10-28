<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateObjectReportPdf;
use App\Livewire\ObjectReport;
use App\Services\ObjectSetSourceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

/**
 * Object reports: a PDF listing a list of objects, each field placed where
 * the observer's layout puts it -- the "Report" of the old DeepskyLog.
 */
class ObjectReportController extends Controller
{
    public function index(Request $request, ObjectSetSourceService $sources)
    {
        $resolved = $sources->resolve($request, $request->user(), ObjectSetSourceService::KIND_REPORT, ObjectReport::MAX_OBJECTS);

        return view('object-report.index', [
            'objects' => $resolved['objects'],
            'title' => $resolved['title'],
            'total' => $resolved['total'],
            'listId' => $resolved['list_id'],
            'filename' => $resolved['filename'],
            'backUrl' => url()->previous() !== url()->current() ? url()->previous() : null,
        ]);
    }

    public function download(Request $request, string $token)
    {
        abort_unless((bool) preg_match('/^[0-9a-f-]{36}$/i', $token), 404);

        $status = Cache::get(GenerateObjectReportPdf::statusKey($token));
        abort_unless(is_array($status) && (int) ($status['user_id'] ?? 0) === (int) $request->user()->id, 404);

        $path = GenerateObjectReportPdf::path($token);
        abort_unless(Storage::disk('local')->exists($path), 404);

        $filename = is_string($status['filename'] ?? null) && $status['filename'] !== ''
            ? $status['filename']
            : 'report.pdf';

        return Response::download(Storage::disk('local')->path($path), $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
