<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Observation;
use App\Models\ObservationSession;
use App\Models\ObservingList;
use App\Models\StarParty;
use App\Models\StarPartyParticipant;
use App\Models\User;
use App\Support\Html;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Read-only API on the data of the authenticated observer, for programs such
 * as pydeepskylog. The routes (/api/v1/me/...) need 'auth:sanctum' and the
 * 'read' ability: a personal access token made on /user/api-tokens, sent as
 * "Authorization: Bearer <token>", or the session cookie of the site itself.
 *
 * Lists that can grow without bound (observations, sessions) are paginated
 * with ?page= and ?per_page= (at most MAX_PER_PAGE), in Laravel's usual
 * {data, current_page, last_page, per_page, total, ...} shape.
 */
class MeController extends Controller
{
    public const DEFAULT_PER_PAGE = 100;

    public const MAX_PER_PAGE = 500;

    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'username' => $user->username,
            'name' => html_entity_decode((string) $user->name, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'slug' => $user->slug,
            'country' => $user->country,
            'language' => $user->language,
            'observation_language' => $user->observationlanguage,
            'fst_offset' => $user->fstOffset !== null ? (float) $user->fstOffset : null,
            'standard_location_id' => $user->stdlocation ?: null,
            'standard_instrument_id' => $user->stdtelescope ?: null,
            'standard_eyepiece_id' => $user->stdeyepiece ?: null,
            // The lens of the standard telescope, which replaced the standard lens of the observer.
            'standard_lens_id' => $user->standardLensId(),
            'standard_instrument_set_id' => $user->stdinstrumentset ?: null,
            'active_observing_list_id' => $user->active_observing_list_id ?: null,
            'show_inches' => (bool) $user->showInches,
        ]);
    }

    public function locations(Request $request): JsonResponse
    {
        $user = $request->user();

        $locations = $user->locations()->orderBy('name')->get()
            ->map(fn (Location $location) => [
                'id' => $location->id,
                'name' => html_entity_decode((string) $location->name, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                'slug' => $location->slug,
                'latitude' => $location->latitude,
                'longitude' => $location->longitude,
                'elevation' => $location->elevation,
                'country' => $location->country,
                'timezone' => $location->timezone,
                // -999 (or 0) in the database means "not known".
                'limiting_magnitude' => $location->limitingMagnitude > 0 ? $location->limitingMagnitude : null,
                'sky_background' => $location->skyBackground > 0 ? $location->skyBackground : null,
                'sqm' => $location->getSqm(),
                'nelm' => $location->getNelm((float) ($user->fstOffset ?? 0)),
                'bortle' => $location->getBortle(),
                'active' => (bool) $location->active,
                'hidden' => (bool) $location->hidden,
                'standard' => (int) $user->stdlocation === (int) $location->id,
                'description' => Html::purify($location->description, true),
            ]);

        return response()->json($locations->values());
    }

    public function instruments(Request $request): JsonResponse
    {
        return response()->json($request->user()->instruments()->orderBy('name')->get());
    }

    public function eyepieces(Request $request): JsonResponse
    {
        return response()->json($request->user()->eyepieces()->orderBy('name')->get());
    }

    public function lenses(Request $request): JsonResponse
    {
        return response()->json($request->user()->lenses()->orderBy('name')->get());
    }

    public function filters(Request $request): JsonResponse
    {
        return response()->json($request->user()->filters()->orderBy('name')->get());
    }

    public function instrumentSets(Request $request): JsonResponse
    {
        $sets = $request->user()->instrumentSets()
            ->with(['instruments:id', 'eyepieces:id', 'lenses:id', 'filters:id', 'locations:id'])
            ->orderBy('name')
            ->get()
            ->map(fn ($set) => [
                'id' => $set->id,
                'name' => html_entity_decode((string) $set->name, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                'slug' => $set->slug,
                'active' => (bool) $set->active,
                'description' => Html::purify($set->description, true),
                'instrument_ids' => $set->instruments->pluck('id')->values(),
                'eyepiece_ids' => $set->eyepieces->pluck('id')->values(),
                'lens_ids' => $set->lenses->pluck('id')->values(),
                'filter_ids' => $set->filters->pluck('id')->values(),
                'location_ids' => $set->locations->pluck('id')->values(),
            ]);

        return response()->json($sets->values());
    }

    /**
     * The observations of the user, newest first. Filters: from / to
     * (observation date, Y-m-d), type (deepsky, comet, planet, lunar, ...),
     * object (the name of the object), instrument_id, location_id,
     * session_id and updated_since (to fetch only what changed).
     */
    public function observations(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d'],
            'type' => ['nullable', 'string', 'max:32'],
            'object' => ['nullable', 'string', 'max:255'],
            'instrument_id' => ['nullable', 'integer'],
            'location_id' => ['nullable', 'integer'],
            'session_id' => ['nullable', 'integer'],
            'updated_since' => ['nullable', 'date'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:'.self::MAX_PER_PAGE],
        ]);
        $user = $request->user();

        $query = Observation::query()->where('observer_username', $user->username);
        if (! empty($filters['from'])) {
            $query->where('observed_on', '>=', $filters['from']);
        }
        if (! empty($filters['to'])) {
            $query->where('observed_on', '<=', $filters['to']);
        }
        if (! empty($filters['type'])) {
            $query->where('target_type', $filters['type']);
        }
        if (! empty($filters['object'])) {
            $query->where('target_name', $filters['object']);
        }
        if (! empty($filters['instrument_id'])) {
            $query->where('instrument_id', $filters['instrument_id']);
        }
        if (! empty($filters['location_id'])) {
            $query->where('location_id', $filters['location_id']);
        }
        if (! empty($filters['session_id'])) {
            $query->whereIn('id', DB::table('sessionObservations')
                ->where('sessionid', $filters['session_id'])
                ->select('observationid'));
        }
        if (! empty($filters['updated_since'])) {
            $query->where('updated_at', '>=', $filters['updated_since']);
        }

        $page = $query->orderByDesc('observed_on')->orderByDesc('id')
            ->paginate((int) ($filters['per_page'] ?? self::DEFAULT_PER_PAGE))
            ->withQueryString();

        return response()->json($this->mapPage($page, $this->observationRows($page->getCollection()->all())));
    }

    public function observation(Request $request, int $id): JsonResponse
    {
        $observation = Observation::query()
            ->where('observer_username', $request->user()->username)
            ->findOrFail($id);

        return response()->json($this->observationRows([$observation])[0]);
    }

    /**
     * The observing sessions of the user (their own copy of a shared session
     * included), newest first.
     */
    public function sessions(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:'.self::MAX_PER_PAGE],
        ]);

        $page = ObservationSession::query()
            ->where('observerid', $request->user()->username)
            ->orderByDesc('begindate')->orderByDesc('id')
            ->paginate((int) ($filters['per_page'] ?? self::DEFAULT_PER_PAGE))
            ->withQueryString();

        $sessions = $page->getCollection();
        ObservationSession::preloadOtherObservers($sessions);
        $counts = DB::table('sessionObservations')
            ->whereIn('sessionid', $sessions->pluck('id')->all())
            ->groupBy('sessionid')
            ->pluck(DB::raw('count(*)'), 'sessionid');

        $rows = $sessions->map(fn (ObservationSession $session) => $this->sessionRow($session)
            + ['observation_count' => (int) ($counts[$session->id] ?? 0)])->all();

        return response()->json($this->mapPage($page, $rows));
    }

    public function session(Request $request, int $id): JsonResponse
    {
        $session = ObservationSession::query()
            ->where('observerid', $request->user()->username)
            ->findOrFail($id);

        $observationIds = DB::table('sessionObservations')
            ->where('sessionid', $session->id)
            ->orderBy('observationid')
            ->pluck('observationid')
            ->map(fn ($id) => (int) $id);

        return response()->json($this->sessionRow($session) + [
            'observation_count' => $observationIds->count(),
            'observation_ids' => $observationIds->values(),
        ]);
    }

    /**
     * The starparties (#315) the user organises or was invited to, with
     * their answer (going, maybe, declined, invited), the first night first.
     */
    public function starParties(Request $request): JsonResponse
    {
        $user = $request->user();

        $parties = StarParty::query()
            ->where(fn ($q) => $q->where('user_id', $user->id)
                ->orWhereIn('id', StarPartyParticipant::select('star_party_id')->where('user_id', $user->id)))
            ->with(['organiser:id,username', 'participants'])
            ->orderBy('start_date')->orderBy('id')
            ->get()
            ->map(fn (StarParty $party) => [
                'id' => $party->id,
                'name' => html_entity_decode($party->name, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                'url' => route('star-parties.show', $party),
                'first_night' => $party->start_date->toDateString(),
                'last_night' => $party->end_date->toDateString(),
                'visibility' => $party->visibility,
                'cancelled' => $party->isCancelled(),
                'organiser' => $party->organiser?->username,
                'status' => $party->statusOf($user),
                'going_count' => $party->goingCount(),
                'max_participants' => $party->max_participants,
                'site' => [
                    'name' => $party->location_name,
                    'latitude' => $party->latitude,
                    'longitude' => $party->longitude,
                    'elevation' => $party->elevation,
                    'timezone' => $party->timezone,
                    'country' => $party->country,
                ],
            ]);

        return response()->json($parties->values());
    }

    /**
     * The observing lists of the user; with ?include=subscribed also the
     * lists they subscribed to.
     */
    public function observingLists(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = ObservingList::query()->withCount('items')->with('owner:id,username');
        if ($request->query('include') === 'subscribed') {
            $query->where(fn ($q) => $q->where('owner_user_id', $user->id)
                ->orWhereIn('id', $user->subscribedObservingLists()->select('observing_lists.id')));
        } else {
            $query->where('owner_user_id', $user->id);
        }

        $lists = $query->orderBy('name')->get()
            ->map(fn (ObservingList $list) => $this->observingListRow($list, $user));

        return response()->json($lists->values());
    }

    /**
     * One observing list with its objects: a list of the user, or a public
     * list of somebody else.
     */
    public function observingList(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        $list = ObservingList::query()->withCount('items')->with('owner:id,username')->findOrFail($id);
        abort_unless($user->can('view', $list), 404);

        $items = $list->items()->orderBy('sort_order')->orderBy('id')->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'object_name' => $item->object_name,
                'description' => $item->item_description,
                'sort_order' => $item->sort_order,
                'planned_at' => $item->planned_at?->toIso8601String(),
                'planned_from' => $item->planned_from?->toIso8601String(),
                'planned_until' => $item->planned_until?->toIso8601String(),
                'source_observation_id' => $item->source_observation_id,
                'added_at' => $item->created_at?->toIso8601String(),
            ]);

        return response()->json($this->observingListRow($list, $user) + ['planned_timezone' => $list->planned_timezone, 'items' => $items->values()]);
    }

    private function observingListRow(ObservingList $list, User $user): array
    {
        return [
            'id' => $list->id,
            'name' => $list->name_decoded,
            'slug' => $list->slug,
            'description' => $list->description,
            'public' => (bool) $list->public,
            'owner' => $list->owner?->username,
            'own' => (int) $list->owner_user_id === (int) $user->id,
            'active' => (int) $user->active_observing_list_id === (int) $list->id,
            'item_count' => (int) $list->items_count,
            'created_at' => $list->created_at?->toIso8601String(),
            'updated_at' => $list->updated_at?->toIso8601String(),
        ];
    }

    private function sessionRow(ObservationSession $session): array
    {
        return [
            'id' => $session->id,
            'name' => html_entity_decode((string) $session->name, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'slug' => $session->slug,
            'begin' => $session->begindate?->format('Y-m-d H:i:s'),
            'end' => $session->enddate?->format('Y-m-d H:i:s'),
            'location_id' => $session->locationid ?: null,
            'active' => (bool) $session->active,
            'language' => $session->language,
            'weather' => Html::purify($session->weather, true),
            'equipment' => Html::purify($session->equipment, true),
            'comments' => Html::purify($session->comments, true),
            'other_observers' => array_values(array_diff(
                array_unique($session->otherObservers()),
                [$session->observerid]
            )),
        ];
    }

    /**
     * The observations as arrays, with the fields of their detail table
     * (deep-sky or comet) read in one query per table.
     *
     * @param  array<int, Observation>  $observations
     * @return array<int, array<string, mixed>>
     */
    private function observationRows(array $observations): array
    {
        $details = [];
        foreach ([Observation::TYPE_DEEPSKY => 'deepsky_observation_details', Observation::TYPE_COMET => 'comet_observation_details'] as $type => $table) {
            $ids = collect($observations)->where('target_type', $type)->pluck('id')->all();
            if ($ids !== []) {
                $details[$type] = DB::table($table)->whereIn('observation_id', $ids)->get()->keyBy('observation_id');
            }
        }

        return array_map(function (Observation $observation) use ($details) {
            $detail = $details[$observation->target_type][$observation->id] ?? null;
            $detail = $detail !== null ? collect((array) $detail)->except('observation_id')->all() : null;

            return [
                'id' => $observation->id,
                'object' => $observation->target_name,
                'type' => $observation->target_type,
                'target_table' => $observation->target_table,
                'target_key' => $observation->target_key,
                'date' => $observation->observed_on?->format('Y-m-d'),
                'time' => $observation->observed_time,
                'instrument_id' => $observation->instrument_id ?: null,
                'location_id' => $observation->location_id ?: null,
                'eyepiece_id' => $observation->eyepiece_id ?: null,
                'filter_id' => $observation->filter_id ?: null,
                'lens_id' => $observation->lens_id ?: null,
                'seeing' => $observation->seeing,
                'transparency' => $observation->transparency,
                'limiting_magnitude' => $observation->limiting_magnitude !== null ? (float) $observation->limiting_magnitude : null,
                'sqm' => $observation->getRawOriginal('sqm') !== null ? (float) $observation->getRawOriginal('sqm') : null,
                'class' => $observation->observer_class,
                'language' => $observation->language,
                'description' => Html::purify($observation->description, true),
                'has_drawing' => (bool) $observation->has_drawing,
                'details' => $detail,
                'created_at' => $observation->created_at?->toIso8601String(),
                'updated_at' => $observation->updated_at?->toIso8601String(),
            ];
        }, array_values($observations));
    }

    private function mapPage(LengthAwarePaginator $page, array $rows): array
    {
        return array_merge($page->toArray(), ['data' => $rows]);
    }
}
