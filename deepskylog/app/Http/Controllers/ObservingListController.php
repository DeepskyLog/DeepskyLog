<?php

namespace App\Http\Controllers;

use App\Models\ObservingList;
use App\Models\ObservingListComment;
use App\Models\ObservingListItem;
use App\Models\ObservationsOld;
use App\Services\ActiveObservingListService;
use App\Services\ObservingListFileImportService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ObservingListController extends Controller
{
    protected ActiveObservingListService $activeListService;

    public function __construct(ActiveObservingListService $activeListService)
    {
        $this->activeListService = $activeListService;
    }

    /**
     * Show the user's observing lists (owned + subscribed).
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $search = trim((string) $request->query('q', ''));

        // Get owned lists
        $ownedListsQuery = $user->observingLists()
            ->withCount('items')
            ->orderBy('created_at', 'desc');

        if ($search !== '') {
            $ownedListsQuery->where('name', 'like', '%' . $search . '%');
        }

        $ownedLists = $ownedListsQuery
            ->paginate(15, ['*'], 'owned_page')
            ->withQueryString();

        // Get subscribed lists
        $subscribedListsQuery = $user->subscribedObservingLists()
            ->with('owner')
            ->withCount('items')
            ->orderBy('observing_list_subscriptions.created_at', 'desc');

        if ($search !== '') {
            $subscribedListsQuery->where('observing_lists.name', 'like', '%' . $search . '%');
        }

        $subscribedLists = $subscribedListsQuery
            ->paginate(15, ['*'], 'subscribed_page')
            ->withQueryString();

        // Get active list (with items count for the banner)
        $activeList = $this->activeListService->getActiveList($user);

        return view('observing-lists.index', [
            'ownedLists' => $ownedLists,
            'subscribedLists' => $subscribedLists,
            'activeList' => $activeList,
            'search' => $search,
        ]);
    }

    /**
     * Show the public observing lists discovery page.
     */
    public function discover(Request $request): View
    {
        $sortBy = $request->query('sort', 'newest'); // 'newest' or 'popular'
        $user = auth()->user();

        $query = ObservingList::with('owner')
            ->withCount('items')
            ->where('public', 1);

        // Exclude user's own lists if logged in
        if ($user) {
            $query->where('owner_user_id', '<>', $user->id);
        }

        if ($sortBy === 'popular') {
            $query->orderBy('likes_count', 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $publicLists = $query->paginate(15);

        // Mark which lists user is already subscribed to (only if logged in)
        $subscribedIds = [];
        if ($user) {
            $subscribedIds = $user->subscribedObservingLists()
                ->pluck('observing_lists.id')
                ->map(fn($id) => (int) $id)
                ->toArray();
        }

        return view('observing-lists.discover', [
            'publicLists' => $publicLists,
            'subscribedIds' => $subscribedIds,
            'sortBy' => $sortBy,
        ]);
    }

    /**
     * Show a specific observing list.
     */
    public function show(ObservingList $list): View
    {
        $user = auth()->user();

        // Guests may only view public lists.
        if (!$user && !$list->public) {
            throw new AuthorizationException();
        }

        // Logged-in users follow policy rules (owner/subscriber/public).
        if ($user && !$user->can('view', $list)) {
            throw new AuthorizationException();
        }

        $list->load('owner');

        $items = $list->items()
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        $comments = $list->comments()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        $isOwner = $user ? ($user->id === $list->owner_user_id) : false;
        $isSubscribed = $user ? $list->isSubscribedBy($user) : false;
        $isLiked = $user ? $list->isLikedBy($user) : false;
        $isActive = $user ? $this->activeListService->isActive($user, $list) : false;

        return view('observing-lists.show', [
            'list' => $list,
            'items' => $items,
            'comments' => $comments,
            'isOwner' => $isOwner,
            'isSubscribed' => $isSubscribed,
            'isLiked' => $isLiked,
            'isActive' => $isActive,
        ]);
    }

    /**
     * Show create form for a new observing list.
     */
    public function create(): View
    {
        $this->authorize('create', ObservingList::class);
        return view('observing-lists.create');
    }

    /**
     * Store a newly created observing list.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', ObservingList::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'public' => ['boolean'],
        ]);

        $list = ObservingList::create([
            'owner_user_id' => auth()->id(),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'public' => $request->boolean('public'),
        ]);

        return redirect()->route('observing-list.show', $list)
            ->with('success', __('Observing list created.'));
    }

    /**
     * Show edit form for an existing observing list.
     */
    public function edit(ObservingList $list): View
    {
        $this->authorize('update', $list);
        return view('observing-lists.edit', compact('list'));
    }

    /**
     * Update an existing observing list.
     */
    public function update(Request $request, ObservingList $list): RedirectResponse
    {
        $this->authorize('update', $list);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'public' => ['boolean'],
        ]);

        $list->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'public' => $request->boolean('public'),
        ]);

        return redirect()->route('observing-list.show', $list)
            ->with('success', __('Observing list updated.'));
    }

    /**
     * Delete an observing list.
     */
    /**
     * Empty all items from an observing list (but keep the list itself).
     */
    public function emptyList(ObservingList $list): RedirectResponse
    {
        $this->authorize('delete', $list);

        $itemCount = $list->items()->count();
        $list->items()->delete();

        return redirect()->route('observing-list.show', $list)
            ->with('success', __(':count items removed from the list.', ['count' => $itemCount]));
    }

    public function destroy(ObservingList $list): RedirectResponse
    {
        $this->authorize('delete', $list);

        $list->items()->delete();
        $list->comments()->delete();
        $list->subscriptions()->delete();
        $list->likes()->delete();
        $list->delete();

        return redirect()->route('observing-lists.index')
            ->with('success', __('Observing list deleted.'));
    }

    /**
     * Set a list as the user's active observing list.
     */
    public function setActive(ObservingList $list): RedirectResponse
    {
        $user = auth()->user();

        if (!$user->can('setAsActive', $list)) {
            throw new AuthorizationException();
        }

        // If already active, clear it (toggle behaviour)
        if ($this->activeListService->isActive($user, $list)) {
            $this->activeListService->clearActiveList($user);
            return redirect()->back()->with('success', __('Active observing list cleared.'));
        }

        $this->activeListService->setActiveList($user, $list);

        return redirect()->back()->with('success', __('Active observing list updated.'));
    }

    /**
     * Subscribe to a public observing list.
     */
    public function subscribe(Request $request, ObservingList $list): RedirectResponse
    {
        $user = auth()->user();

        if (!$user->can('subscribe', $list)) {
            throw new AuthorizationException();
        }

        $list->subscribers()->syncWithoutDetaching([$user->id]);

        return redirect()->back()->with('success', __('Subscribed to observing list.'));
    }

    /**
     * Unsubscribe from an observing list.
     */
    public function unsubscribe(Request $request, ObservingList $list): RedirectResponse
    {
        $user = auth()->user();

        if (!$user->can('unsubscribe', $list)) {
            throw new AuthorizationException();
        }

        $list->subscribers()->detach($user->id);

        return redirect()->back()->with('success', __('Unsubscribed from observing list.'));
    }

    /**
     * Toggle like on an observing list (AJAX/API).
     */
    public function toggleLike(Request $request, ObservingList $list)
    {
        $user = auth()->user();

        if (!$user->can('like', $list)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $liked = $list->toggleLike($user);

        return response()->json([
            'liked' => $liked,
            'likes_count' => $list->fresh()->likes_count,
        ]);
    }

    /**
     * Show form to add a single object to a list.
     */
    public function createItem(ObservingList $list): View
    {
        $this->authorize('addItem', $list);
        return view('observing-lists.add-item', compact('list'));
    }

    /**
     * Show form to edit the description of an existing item.
     */
    public function editItem(ObservingList $list, ObservingListItem $item): View
    {
        $this->authorize('addItem', $list);

        if ($item->observing_list_id !== $list->id) {
            abort(404);
        }

        $longestObservationNote = ObservationsOld::query()
            ->where('objectname', $item->object_name)
            ->whereNotNull('description')
            ->whereRaw('TRIM(description) <> ""')
            ->orderByRaw('CHAR_LENGTH(description) DESC')
            ->value('description');

        if ($longestObservationNote !== null) {
            $longestObservationNote = preg_replace('/<br\s*\/?>/i', "\n", $longestObservationNote) ?? $longestObservationNote;
            $longestObservationNote = strip_tags($longestObservationNote);
            $longestObservationNote = html_entity_decode($longestObservationNote, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $longestObservationNote = trim($longestObservationNote);
        }

        return view('observing-lists.edit-item', [
            'list' => $list,
            'item' => $item,
            'longestObservationNote' => $longestObservationNote,
        ]);
    }

    /**
     * Update the description of an existing item.
     */
    public function updateItem(Request $request, ObservingList $list, ObservingListItem $item): RedirectResponse
    {
        $user = auth()->user();

        if (!$user->can('addItem', $list)) {
            throw new AuthorizationException();
        }

        if ($item->observing_list_id !== $list->id) {
            abort(404);
        }

        $validated = $request->validate([
            'item_description' => ['nullable', 'string', 'max:2000'],
        ]);

        $item->update([
            'item_description' => $validated['item_description'] ?? null,
        ]);

        return redirect()->route('observing-list.show', $list)
            ->with('success', __('Note updated.'));
    }

    /**
     * Autofill notes for all items in the list that have no note yet,
     * using the longest legacy observation description for each object.
     */
    public function batchAutofillNotes(ObservingList $list): RedirectResponse
    {
        $user = auth()->user();

        if (!$user->can('addItem', $list)) {
            throw new AuthorizationException();
        }

        $items = $list->items()
            ->where(function ($q) {
                $q->whereNull('item_description')
                    ->orWhere('item_description', '');
            })
            ->get();

        $filled = 0;
        foreach ($items as $item) {
            $note = ObservationsOld::query()
                ->where('objectname', $item->object_name)
                ->whereNotNull('description')
                ->whereRaw('TRIM(description) <> ""')
                ->orderByRaw('CHAR_LENGTH(description) DESC')
                ->value('description');

            if ($note === null) {
                continue;
            }

            $note = preg_replace('/<br\s*\/?>/i', "\n", $note) ?? $note;
            $note = strip_tags($note);
            $note = html_entity_decode($note, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $note = trim($note);

            if ($note === '') {
                continue;
            }

            $item->update(['item_description' => $note]);
            $filled++;
        }

        return redirect()->route('observing-list.show', $list)
            ->with('success', __(':count note(s) filled in automatically.', ['count' => $filled]));
    }

    /**
     * Batch-add a list of object names to the user's active observing list.
     * Accepts either:
     *   - object_names[] array (direct POST)
     *   - search_query string (re-runs the search to resolve names)
     */
    public function batchAddToActiveList(Request $request): RedirectResponse
    {
        $user = auth()->user();

        /** @var \App\Services\ActiveObservingListService $svc */
        $svc = app(\App\Services\ActiveObservingListService::class);
        $activeList = $svc->getActiveList($user);

        if (!$activeList) {
            return redirect()->back()->with('error', __('No active observing list set. Please set one first.'));
        }

        if (!$user->can('addItem', $activeList)) {
            throw new AuthorizationException();
        }

        $objectNames = [];

        if ($request->filled('search_query')) {
            // Re-run the search to get matching display names
            $q = trim($request->input('search_query'));
            if (strlen($q) > 0) {
                $like = '%' . $q . '%';
                $objectNames = DB::table('search_index')
                    ->whereRaw('LOWER(name) LIKE ?', [strtolower($like)])
                    ->whereNotNull('display_name')
                    ->pluck('display_name')
                    ->unique()
                    ->values()
                    ->toArray();
            }
        } elseif ($request->has('object_names')) {
            $request->validate([
                'object_names' => ['required', 'array', 'max:2000'],
                'object_names.*' => ['required', 'string', 'max:255'],
            ]);
            $objectNames = $request->input('object_names');
        }

        $added = 0;
        $addedNames = [];
        foreach ($objectNames as $name) {
            $name = trim((string) $name);
            if ($name === '') {
                continue;
            }
            $new = $activeList->items()->firstOrCreate(
                ['object_name' => $name],
                [
                    'source_mode' => 'manual',
                    'added_by_user_id' => $user->id,
                ]
            );
            if ($new->wasRecentlyCreated) {
                $added++;
                $addedNames[] = $name;
            }
        }

        if (count($addedNames) === 1) {
            return redirect()->back()->with('success', __(':object added to :list.', [
                'object' => $addedNames[0],
                'list' => $activeList->name,
            ]));
        }

        return redirect()->back()->with('success', __(':count object(s) added to :list.', [
            'count' => $added,
            'list' => $activeList->name,
        ]));
    }

    /**
     * Batch-add all objects within a given radius around RA/Dec to the user's active observing list.
     */
    public function batchAddNearbyToActiveList(Request $request): RedirectResponse
    {
        $user = auth()->user();

        /** @var \App\Services\ActiveObservingListService $svc */
        $svc = app(\App\Services\ActiveObservingListService::class);
        $activeList = $svc->getActiveList($user);

        if (!$activeList) {
            return redirect()->back()->with('error', __('No active observing list set. Please set one first.'));
        }

        if (!$user->can('addItem', $activeList)) {
            throw new AuthorizationException();
        }

        $validated = $request->validate([
            'ra' => ['required', 'numeric'],
            'decl' => ['required', 'numeric'],
            'radius_arc_min' => ['required', 'integer', 'min:1', 'max:360'],
            'exclude_name' => ['nullable', 'string', 'max:255'],
        ]);

        $ra = (float) $validated['ra'];
        $decl = (float) $validated['decl'];
        $radiusDeg = $validated['radius_arc_min'] / 60.0;
        $excludeName = $validated['exclude_name'] ?? null;

        // Convert RA hours to degrees if needed (≤24 → hours, >24 → already degrees)
        $centerRaDeg = $ra <= 24.0 ? $ra * 15.0 : $ra;

        $expr = "DEGREES(ACOS(LEAST(1, GREATEST(-1, "
            . "SIN(RADIANS(?))*SIN(RADIANS(`decl`)) "
            . "+ COS(RADIANS(?))*COS(RADIANS(`decl`))*COS(RADIANS(? - (CASE WHEN `ra` <= 24 THEN `ra`*15 ELSE `ra` END)))"
            . "))))";

        $query = DB::table('objects')
            ->selectRaw('name')
            ->whereRaw("{$expr} <= ?", [$decl, $decl, $centerRaDeg, $radiusDeg])
            ->whereNotNull('name');

        if ($excludeName) {
            $query->where('name', '<>', $excludeName);
        }

        $names = $query->pluck('name')->filter()->unique()->values()->toArray();

        $added = 0;
        $addedNames = [];
        foreach ($names as $name) {
            $name = trim((string) $name);
            if ($name === '') {
                continue;
            }
            $item = $activeList->items()->firstOrCreate(
                ['object_name' => $name],
                [
                    'source_mode' => 'manual',
                    'added_by_user_id' => $user->id,
                ]
            );
            if ($item->wasRecentlyCreated) {
                $added++;
                $addedNames[] = $name;
            }
        }

        if (count($addedNames) === 1) {
            return redirect()->back()->with('success', __(':object added to :list.', [
                'object' => $addedNames[0],
                'list' => $activeList->name,
            ]));
        }

        return redirect()->back()->with('success', __(':count object(s) added to :list.', [
            'count' => $added,
            'list' => $activeList->name,
        ]));
    }

    /**
     * Import objects from a file into an observing list.
     */
    public function importFromFile(Request $request, ObservingList $list): RedirectResponse
    {
        $user = auth()->user();

        if (!$user->can('addItem', $list)) {
            throw new AuthorizationException();
        }

        $validator = Validator::make($request->all(), [
            'file' => [
                'required',
                'file',
                // AstroPlanner APD files are SQLite DBs and often fail MIME sniffing; validate by extension.
                'extensions:txt,argo,skylist,apd,csv',
                'max:5120', // 5MB max
            ],
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->with('error', __('Import failed. Please select a supported file (.argo, .skylist, .txt, .apd, .csv).'));
        }

        $file = $request->file('file');

        // Use the import service
        $importService = new ObservingListFileImportService();
        $result = $importService->importFromFile($list, $file, $user);

        if (!$result['success']) {
            $errorMessage = __('Import failed');
            if (!empty($result['errors'])) {
                $errorMessage .= ': ' . implode('; ', $result['errors']);
            }
            return redirect()->back()->with('error', $errorMessage);
        }

        $message = __(':count object(s) imported, :skipped skipped.', [
            'count' => $result['imported'],
            'skipped' => $result['skipped'],
        ]);

        if (!empty($result['errors'])) {
            return redirect()->back()->with('warning', $message . ' ' . implode('; ', $result['errors']));
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Toggle one object in the current user's active observing list.
     */
    public function toggleActiveListItem(Request $request)
    {
        $user = auth()->user();

        /** @var \App\Services\ActiveObservingListService $svc */
        $svc = app(\App\Services\ActiveObservingListService::class);
        $activeList = $svc->getActiveList($user);

        if (!$activeList) {
            $message = __('No active observing list set. Please set one first.');
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 400);
            }
            return redirect()->back()->with('error', $message);
        }

        $validated = $request->validate([
            'object_name' => ['required', 'string', 'max:255'],
        ]);

        $objectName = trim((string) $validated['object_name']);
        if ($objectName === '') {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => __('Invalid object name')], 400);
            }
            return redirect()->back();
        }

        $exists = $activeList->items()->where('object_name', $objectName)->exists();

        if ($exists) {
            if (!$user->can('removeItem', $activeList)) {
                throw new AuthorizationException();
            }

            $activeList->items()->where('object_name', $objectName)->delete();
            $message = __(':object removed from :list.', [
                'object' => $objectName,
                'list' => $activeList->name,
            ]);

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => $message, 'action' => 'removed']);
            }
            return redirect()->back()->with('success', $message);
        }

        if (!$user->can('addItem', $activeList)) {
            throw new AuthorizationException();
        }

        $activeList->items()->firstOrCreate(
            ['object_name' => $objectName],
            [
                'source_mode' => 'manual',
                'added_by_user_id' => $user->id,
            ]
        );

        $message = __(':object added to :list.', [
            'object' => $objectName,
            'list' => $activeList->name,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message, 'action' => 'added']);
        }
        return redirect()->back()->with('success', $message);
    }

    /**
     * Add an object to an observing list.
     */
    public function addItem(Request $request, ObservingList $list): RedirectResponse
    {
        $user = auth()->user();

        if (!$user->can('addItem', $list)) {
            throw new AuthorizationException();
        }

        $validated = $request->validate([
            'object_name' => ['required', 'string', 'max:255'],
            'item_description' => ['nullable', 'string', 'max:2000'],
        ]);

        // Idempotent insert – ignore if already in list
        $list->items()->firstOrCreate(
            ['object_name' => $validated['object_name']],
            [
                'item_description' => $validated['item_description'] ?? null,
                'source_mode' => 'manual',
                'added_by_user_id' => $user->id,
            ]
        );

        // Prefer returning JSON for AJAX callers, redirect otherwise
        if ($request->expectsJson()) {
            $inList = $list->items()->where('object_name', $validated['object_name'])->exists();
            return response()->json(['in_list' => $inList, 'list_name' => $list->name]);
        }

        return redirect()->back()->with('success', __(':object added to :list.', [
            'object' => $validated['object_name'],
            'list' => $list->name,
        ]));
    }

    /**
     * Remove an item from an observing list.
     */
    public function removeItem(Request $request, ObservingList $list, ObservingListItem $item): RedirectResponse
    {
        $user = auth()->user();

        if (!$user->can('removeItem', $list)) {
            throw new AuthorizationException();
        }

        if ($item->observing_list_id !== $list->id) {
            abort(404);
        }

        $objectName = $item->object_name;
        $item->delete();

        if ($request->expectsJson()) {
            return response()->json(['in_list' => false, 'object_name' => $objectName]);
        }

        return redirect()->back()->with('success', __(':object removed from :list.', [
            'object' => $objectName,
            'list' => $list->name,
        ]));
    }

    /**
     * Store a comment on an observing list.
     */
    public function storeComment(Request $request, ObservingList $list): RedirectResponse
    {
        $user = auth()->user();

        if (!$user->can('comment', $list)) {
            throw new AuthorizationException();
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $list->addComment($user, $validated['body']);

        return redirect()->back()->with('success', __('Comment added.'));
    }

    /**
     * Delete a comment from an observing list.
     */
    public function destroyComment(Request $request, ObservingList $list, ObservingListComment $comment): RedirectResponse
    {
        $user = auth()->user();

        if (!$comment->canBeDeletedBy($user)) {
            throw new AuthorizationException();
        }

        $list->removeComment($comment);

        return redirect()->back()->with('success', __('Comment deleted.'));
    }
}
