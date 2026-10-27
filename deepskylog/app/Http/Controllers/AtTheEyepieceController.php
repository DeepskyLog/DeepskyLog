<?php

namespace App\Http\Controllers;

use App\Models\ObservingList;
use App\Services\AtTheEyepiece;
use App\Services\FieldViews\FieldViewBuilder;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The page of an observing list with times at the telescope
 * (App\Services\AtTheEyepiece): the current and next object with the finder
 * chart, "Log it", and the observations logged on the page while offline.
 */
class AtTheEyepieceController extends Controller
{
    /**
     * The lists to take to the telescope, each linking to its page at the
     * eyepiece: the start page of the DeepskyLog GoTo app.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('observing-lists.eyepiece-lists', AtTheEyepiece::lists($user) + [
            'activeId' => (int) $user->active_observing_list_id,
        ]);
    }

    public function show(Request $request, ObservingList $list, FieldViewBuilder $builder): View|RedirectResponse
    {
        $this->authorize('view', $list);
        $user = $request->user();

        $itemId = $request->query('item');
        $page = AtTheEyepiece::page($list, $user, is_numeric($itemId) ? (int) $itemId : null);
        if ($page['current'] === null) {
            return redirect()->route('observing-list.show', $list)
                ->with('error', __('This list has no objects yet.'));
        }

        // The views of the current object at its time tonight, now in a list without times
        $current = $page['current'];
        $views = null;
        if ($current['object'] && is_numeric($current['object']->ra) && is_numeric($current['object']->decl)) {
            try {
                $views = $builder->build($user, $current['object'], array_filter([
                    'location' => $list->planned_location_id,
                    'datetime' => ($current['at'] ?? $page['now'])->format('Y-m-d\TH:i'),
                ]), 'dark', 600);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return view('observing-lists.eyepiece', $page + [
            'views' => $views,
            'details' => AtTheEyepiece::details($current['object']),
            'nextDetails' => AtTheEyepiece::details($page['next']['object'] ?? null),
            'goto' => AtTheEyepiece::gotoLink($current, $page['location'], $current['at'] ?? $page['now']),
        ]);
    }

    /**
     * "Log it": the observation form for the object, filled in for now.
     */
    public function log(Request $request, ObservingList $list, int $item): RedirectResponse
    {
        $this->authorize('view', $list);
        $listItem = AtTheEyepiece::itemOf($list, $item);
        abort_if($listItem === null, 404);

        return redirect()->route('observation.create', AtTheEyepiece::logParameters($request->user(), $list, $listItem));
    }

    /**
     * Save an observation logged on the page, also one the page kept while
     * offline: the moment is when it was written down.
     */
    public function store(Request $request, ObservingList $list): JsonResponse
    {
        $this->authorize('view', $list);
        $user = $request->user();

        $validated = $request->validate([
            'item' => ['required', 'integer'],
            'observed_at' => ['required', 'date'],
            'description' => ['required', 'string', 'max:'.AtTheEyepiece::MAX_DESCRIPTION],
            'visibility' => ['nullable', 'integer', 'min:1', 'max:7'],
            // The observer who wrote it down: the page keeps the observations
            // of whoever was logged in, which may not be who sends them
            'user' => ['nullable', 'integer'],
        ]);

        if (isset($validated['user']) && (int) $validated['user'] !== $user->id) {
            return response()->json(['message' => __('This observation was written down by another observer: log in as that observer to send it.')], 409);
        }

        $item = AtTheEyepiece::itemOf($list, (int) $validated['item']);
        if ($item === null) {
            return response()->json(['message' => __('The object is no longer in the list.')], 422);
        }

        $moment = Carbon::parse($validated['observed_at']);
        if ($moment->isAfter(now()->addMinutes(10))) {
            return response()->json(['message' => __('The time of the observation is in the future.')], 422);
        }

        $result = AtTheEyepiece::store($user, $list, $item, $moment, nl2br(e(trim($validated['description']))), $validated['visibility'] ?? null);
        if ($result->hasErrors() || $result->imported === 0) {
            $errors = collect($result->log)->where('type', 'error')->pluck('message');

            return response()->json(['message' => $errors->first() ?? __('The observation could not be saved.')], 422);
        }

        $saved = $result->importedObservations[0];

        return response()->json([
            'id' => $saved['id'],
            'updated' => $saved['updated'],
            'object' => $saved['object'],
            'warnings' => collect($result->log)->where('type', 'warning')->pluck('message')->values(),
        ], $saved['updated'] ? 200 : 201);
    }
}
