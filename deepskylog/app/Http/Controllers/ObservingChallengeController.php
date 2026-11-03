<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\ObservingChallenge;
use App\Models\ObservingList;
use App\Models\User;
use App\Services\ChallengeStandings;
use App\Services\Clubs\ClubNotifier;
use App\Services\ObjectSources;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Validator;
use Illuminate\View\View;

/**
 * The observing challenges: /challenges and the page of each challenge with
 * its leaderboard and shared progress, where observers join and leave, and
 * /admin/challenges, where the administrators and database experts set them.
 * The administrators of a club set the club's challenges on
 * /clubs/{club}/challenges.
 */
class ObservingChallengeController extends Controller
{
    /** The participants shown on the leaderboard (the viewer is added below). */
    public const LEADERBOARD = 50;

    /** The objects are listed on the page for a source of at most this many. */
    public const MAX_OBJECTS_SHOWN = 500;

    public const PER_PAGE = 10;

    /** The longest challenge, in days. */
    public const MAX_DAYS = 366;

    public function index(): View
    {
        $today = Carbon::today()->toDateString();

        return view('challenges.index', [
            'running' => ObservingChallenge::visible()->siteWide()->where('starts_on', '<=', $today)->where('ends_on', '>=', $today)->withCount('goals')->orderBy('ends_on')->get(),
            'upcoming' => ObservingChallenge::visible()->siteWide()->where('starts_on', '>', $today)->withCount('goals')->orderBy('starts_on')->get(),
            'ended' => ObservingChallenge::visible()->siteWide()->where('ends_on', '<', $today)->withCount('goals')->orderByDesc('ends_on')->paginate(self::PER_PAGE),
        ]);
    }

    public function show(Request $request, ObservingChallenge $challenge): View
    {
        $viewer = $request->user();
        abort_if($challenge->isHidden() && ! $challenge->canBeManagedBy($viewer), 404);

        $standings = ChallengeStandings::for($challenge);
        $rows      = collect($standings['rows']);
        $viewerRow = $viewer !== null ? $rows->firstWhere('user_id', $viewer->id) : null;
        $goal      = $viewer !== null ? $challenge->goalFor($viewer) : null;

        return view('challenges.show', [
            'challenge' => $challenge,
            'standings' => $standings,
            'leaderboard' => $rows->take(self::LEADERBOARD)->all(),
            'viewerRow' => $viewerRow !== null && $viewerRow['rank'] > self::LEADERBOARD ? $viewerRow : null,
            'joined' => $goal?->exists ?? false,
            'goal' => $goal,
            'seenByViewer' => $goal?->firstObservationDates() ?? collect(),
            'showObjects' => $standings['total'] <= self::MAX_OBJECTS_SHOWN,
            'canJoin' => $challenge->canBeJoinedBy($viewer),
            'editUrl' => ! $challenge->canBeManagedBy($viewer) ? null
                : ($challenge->club ? route('clubs.challenges.edit', [$challenge->club, $challenge]) : route('admin.challenges.edit', $challenge)),
        ]);
    }

    public function join(Request $request, ObservingChallenge $challenge): RedirectResponse
    {
        abort_unless($challenge->canBeJoinedBy($request->user()), 404);
        $challenge->join($request->user());

        return redirect()->route('challenges.show', $challenge)
            ->with('success', __('You joined the challenge. Your progress is shown here and on your observer page.'));
    }

    public function leave(Request $request, ObservingChallenge $challenge): RedirectResponse
    {
        $challenge->leave($request->user());

        return redirect()->route('challenges.show', $challenge)->with('success', __('You left the challenge.'));
    }

    /**
     * An observing list of the objects of the challenge the observer still
     * has to see (draw).
     */
    public function storeList(Request $request, ObservingChallenge $challenge): RedirectResponse
    {
        abort_if($challenge->isHidden() || $challenge->source() === null, 404);
        $this->authorize('create', ObservingList::class);

        $goal  = $challenge->goalFor($request->user());
        $done  = $goal->firstObservationDates();
        $names = $goal->countableObjects()->reject(fn (string $name) => $done->has(mb_strtolower($name)))->values();
        if ($names->isEmpty()) {
            return back()->with('success', __('You already have all objects of the challenge.'));
        }

        $list = ObservingList::createWithObjects(
            $request->user(),
            $challenge->name,
            $names,
            $challenge->task() . "\n" . route('challenges.show', $challenge),
        );

        return redirect()->route('observing-list.show', $list)
            ->with('success', __('Observing list created with :count object(s).', ['count' => $list->items()->count()]));
    }

    public function admin(): View
    {
        $challenges = ObservingChallenge::siteWide()->withCount('goals')->orderByDesc('starts_on')->orderByDesc('id')->paginate(25);

        return view('admin.challenges.index', ['challenges' => $challenges]);
    }

    public function create(Request $request): View
    {
        return $this->form($request, $this->newChallenge());
    }

    public function store(Request $request): RedirectResponse
    {
        $challenge = new ObservingChallenge(['user_id' => $request->user()->id]);
        $this->save($request, $challenge);

        return redirect()->route('admin.challenges')->with('success', __('The challenge is saved and announced in the news.'));
    }

    public function edit(Request $request, ObservingChallenge $challenge): View
    {
        return $this->form($request, $challenge);
    }

    public function update(Request $request, ObservingChallenge $challenge): RedirectResponse
    {
        $this->save($request, $challenge);

        return redirect()->route('admin.challenges')->with('success', __('The challenge is saved and announced in the news.'));
    }

    public function destroy(ObservingChallenge $challenge): RedirectResponse
    {
        $challenge->delete();

        return redirect()->route('admin.challenges')->with('success', __('The challenge is deleted.'));
    }

    /** A new challenge of a club, set by its administrators. */
    public function clubCreate(Request $request, Club $club): View
    {
        abort_unless($club->canBeManagedBy($request->user()), 403);

        return $this->form($request, $this->newChallenge(), $club);
    }

    public function clubStore(Request $request, Club $club): RedirectResponse
    {
        abort_unless($club->canBeManagedBy($request->user()), 403);

        $challenge = new ObservingChallenge(['user_id' => $request->user()->id, 'club_id' => $club->id]);
        $this->save($request, $challenge, $club);
        if (! $challenge->isHidden()) {
            ClubNotifier::challenge($club, $challenge, $request->user());
        }

        return redirect()->route('clubs.show', $club)->with('success', __('The challenge is saved. The members of the club have been told.'));
    }

    public function clubEdit(Request $request, Club $club, ObservingChallenge $challenge): View
    {
        $this->authorizeClubChallenge($request, $club, $challenge);

        return $this->form($request, $challenge, $club);
    }

    public function clubUpdate(Request $request, Club $club, ObservingChallenge $challenge): RedirectResponse
    {
        $this->authorizeClubChallenge($request, $club, $challenge);
        $this->save($request, $challenge, $club);

        return redirect()->route('challenges.show', $challenge)->with('success', __('The challenge is saved.'));
    }

    public function clubDestroy(Request $request, Club $club, ObservingChallenge $challenge): RedirectResponse
    {
        $this->authorizeClubChallenge($request, $club, $challenge);
        $challenge->delete();

        return redirect()->route('clubs.show', $club)->with('success', __('The challenge is deleted.'));
    }

    private function authorizeClubChallenge(Request $request, Club $club, ObservingChallenge $challenge): void
    {
        abort_unless((int) $challenge->club_id === (int) $club->id, 404);
        abort_unless($club->canBeManagedBy($request->user()), 403);
    }

    private function newChallenge(): ObservingChallenge
    {
        $start = Carbon::today()->addMonthNoOverflow()->startOfMonth();

        return new ObservingChallenge([
            'catalog' => 'Hickson',
            'mode' => 'observed',
            'starts_on' => $start,
            'ends_on' => $start->copy()->endOfMonth(),
        ]);
    }

    private function form(Request $request, ObservingChallenge $challenge, ?Club $club = null): View
    {
        $sources = $this->sources($request->user(), $club);
        // The list of an edited challenge the editor does not follow
        if ($challenge->source() !== null && ! $sources->contains('key', $challenge->source()['key'])) {
            $sources->push($challenge->source() + ['label' => html_entity_decode($challenge->source_label, ENT_QUOTES | ENT_HTML5, 'UTF-8')]);
        }

        if ($club !== null) {
            $action = $challenge->exists ? route('clubs.challenges.update', [$club, $challenge]) : route('clubs.challenges.store', $club);
            $cancel = route('clubs.show', $club);
        } else {
            $action = $challenge->exists ? route('admin.challenges.update', $challenge) : route('admin.challenges.store');
            $cancel = route('admin.challenges');
        }

        return view('admin.challenges.form', [
            'challenge' => $challenge,
            'sources' => $sources,
            'club' => $club,
            'action' => $action,
            'cancelUrl' => $cancel,
        ]);
    }

    /**
     * The catalogs, and the public observing lists the editor owns or
     * subscribed to: a challenge is for everybody. A challenge of a club can
     * also use the lists shared with the club.
     */
    private function sources(User $user, ?Club $club = null): Collection
    {
        $public  = ObservingList::where('public', true)->pluck('id')->flip();
        $sources = ObjectSources::options($user)
            ->filter(fn (array $source) => $source['type'] === 'catalog' || $public->has($source['list_id']))
            ->values();

        if ($club !== null) {
            foreach ($club->sharedLists() as $list) {
                if (! $sources->contains('key', 'list:' . $list->id)) {
                    $name = html_entity_decode((string) $list->name, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $sources->push([
                        'key' => 'list:' . $list->id,
                        'type' => 'list',
                        'list_id' => (int) $list->id,
                        'list_slug' => (string) $list->slug,
                        'short_label' => $name,
                        'label' => $name . ' (' . html_entity_decode((string) $list->owner?->name, ENT_QUOTES | ENT_HTML5, 'UTF-8') . ')',
                    ]);
                }
            }
        }

        return $sources;
    }

    private function save(Request $request, ObservingChallenge $challenge, ?Club $club = null): void
    {
        $key    = (string) $request->input('source');
        $source = $this->sources($request->user(), $club)->firstWhere('key', $key);
        if ($source === null && $challenge->exists && ($challenge->source()['key'] ?? null) === $key) {
            $source = $challenge->source() + ['short_label' => $challenge->source_label];
        }

        $validator = validator($request->all(), [
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:20000'],
            'source' => ['required', 'string'],
            'target' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'mode' => ['required', 'in:observed,drawn'],
            'new_only' => ['nullable', 'boolean'],
            'starts_on' => ['required', 'date_format:Y-m-d'],
            'ends_on' => ['required', 'date_format:Y-m-d', 'after_or_equal:starts_on'],
            'hidden' => ['nullable', 'boolean'],
        ], [], [
            'starts_on' => __('Start'),
            'ends_on' => __('End'),
            'target' => __('Number of objects'),
        ]);
        $validator->after(function (Validator $validator) use ($source, $request) {
            if ($source === null) {
                $validator->errors()->add('source', __('Choose a catalog or public observing list.'));
            }
            $start = rescue(fn () => Carbon::make($request->input('starts_on')), null, false);
            $end   = rescue(fn () => Carbon::make($request->input('ends_on')), null, false);
            if ($start !== null && $end !== null && $start->diffInDays($end) >= self::MAX_DAYS) {
                $validator->errors()->add('ends_on', __('A challenge lasts at most a year.'));
            }
        });
        $validated = $validator->validate();

        $challenge->fill([
            'name' => trim($validated['name']),
            'description' => $validated['description'] ?? null,
            'catalog' => $source['type'] === 'catalog' ? $source['catalog'] : null,
            'observing_list_id' => $source['type'] === 'list' ? $source['list_id'] : null,
            'source_label' => (string) ($source['short_label'] ?? $source['label'] ?? ''),
            'target_count' => ($validated['target'] ?? null) === null ? null : (int) $validated['target'],
            'mode' => $validated['mode'],
            'new_only' => (bool) ($validated['new_only'] ?? false),
            'starts_on' => $validated['starts_on'],
            'ends_on' => $validated['ends_on'],
            'hidden' => (bool) ($validated['hidden'] ?? false),
        ]);
        $challenge->saveAndAnnounce();
    }
}
