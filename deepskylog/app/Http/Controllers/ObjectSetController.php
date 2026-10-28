<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateObjectSetsPdf;
use App\Services\ObjectSetSourceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

/**
 * Object sets: a PDF per list of objects with, for every object, a data
 * page, DSS photos and atlas charts at several fields of view.
 */
class ObjectSetController extends Controller
{
    public function index(Request $request, ObjectSetSourceService $sources)
    {
        $resolved = $sources->resolve($request, $request->user());

        return view('object-sets.index', [
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

        $status = Cache::get(GenerateObjectSetsPdf::statusKey($token));
        abort_unless(is_array($status) && (int) ($status['user_id'] ?? 0) === (int) $request->user()->id, 404);

        $path = GenerateObjectSetsPdf::path($token);
        abort_unless(Storage::disk('local')->exists($path), 404);

        $filename = is_string($status['filename'] ?? null) && $status['filename'] !== ''
            ? $status['filename']
            : 'object-sets.pdf';

        return Response::download(Storage::disk('local')->path($path), $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
