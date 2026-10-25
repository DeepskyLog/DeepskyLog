<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateObservationsZip;
use App\Models\User;
use App\Services\ObservationSearchQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

/**
 * Zip files of observations with their drawings, made by a queue worker
 * (App\Jobs\GenerateObservationsZip): all observations of an observer
 * (`?observer=`), or those of an observation search (`?q=` and the advanced
 * search filters). Observers only get their own observations, so nobody
 * collects the drawings of others; administrators get anyone's.
 */
class ObservationZipController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $administrator = $user->hasAdministratorPrivileges();

        $observer = null;
        if ($request->filled('observer')) {
            $observer = User::where('slug', (string) $request->query('observer'))->firstOrFail();
            abort_unless($observer->is($user) || $administrator, 403);
        }

        $q = trim((string) $request->query('q', ''));
        $source = $observer !== null
            ? ['observer_id' => (int) $observer->id]
            : ['q' => $q, 'filters' => ObservationSearchQuery::filtersFrom($request, ['observer', 'drawings_only'])];

        if (! $administrator) {
            $source['observer_id'] = (int) $user->id;
        }

        $own = ($source['observer_id'] ?? null) === (int) $user->id;
        $filtered = ! empty($source['filters']);
        $title = match (true) {
            $own && $q === '' && ! $filtered => __('All my observations'),
            $observer !== null => __('All observations of :name', ['name' => html_entity_decode((string) $observer->name, ENT_QUOTES | ENT_HTML5, 'UTF-8')]),
            $own && ! $filtered => __('My observations matching ":q"', ['q' => $q]),
            $own => __('My observations of your search'),
            ! $filtered => __('Observations matching ":q"', ['q' => $q]),
            default => __('Observations of your search'),
        };

        return view('observation-zips.index', [
            'source' => $source,
            'title' => $title,
            'drawingsOnly' => $request->boolean('drawings_only'),
            'backUrl' => url()->previous() !== url()->current() ? url()->previous() : null,
        ]);
    }

    public function download(Request $request, string $token)
    {
        abort_unless((bool) preg_match('/^[0-9a-f-]{36}$/i', $token), 404);

        $status = Cache::get(GenerateObservationsZip::statusKey($token));
        abort_unless(is_array($status) && (int) ($status['user_id'] ?? 0) === (int) $request->user()->id, 404);

        $path = GenerateObservationsZip::path($token);
        abort_unless(Storage::disk('local')->exists($path), 404);

        $filename = is_string($status['filename'] ?? null) && $status['filename'] !== ''
            ? $status['filename']
            : 'observations.zip';

        return Response::download(Storage::disk('local')->path($path), $filename, [
            'Content-Type' => 'application/zip',
        ]);
    }
}
