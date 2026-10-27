<?php

namespace App\Http\Controllers;

use App\Models\ObservingChallenge;
use App\Models\ObservingList;
use App\Models\User;
use App\Services\ChallengeStandings;
use App\Services\ObjectSources;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Validator;
use Illuminate\View\View;

/**
 * The observing challenges: /challenges and the page of each challenge with
 * its leaderboard and shared progress, where observers join and leave, and
 * /admin/challenges, where the administrators and database experts set them.
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
            'running' => ObservingChallenge::visible()->where('starts_on', '<=', $today)->where('ends_on', '>=', $today)->withCount('goals')->orderBy('ends_on')->get(),
            'upcoming' => ObservingChallenge::visible()->where('starts_on', '>', $today)->withCount('goals')->orderBy('starts_on')->get(),
            'ended' => ObservingChallenge::visible()->where('ends_on', '<', $today)->withCount('goals')->orderByDesc('ends_on')->paginate(self::PER_PAGE),
        ]);
    }

    public function show(Request $request, ObservingChallenge $challenge): View
    {
        abort_if($challenge->hidden && ! Gate::allows('manage_challenges', User::class), 404);

        $viewer    = $request->user();
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
        ]);
    }

    public function join(Request $request, ObservingChallenge $challenge): RedirectResponse
    {
        abort_unless($challenge->canBeJoined(), 404);
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
        abort_if($challenge->hidden || $challenge->source() === null, 404);
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
        $challenges = ObservingChallenge::withCount('goals')->orderByDesc('starts_on')->orderByDesc('id')->paginate(25);

        return view('admin.challenges.index', ['challenges' => $challenges]);
    }

    public function create(Request $request): View
    {
        $start = Carbon::today()->addMonthNoOverflow()->startOfMonth();

        return $this->form($request, new ObservingChallenge([
            'catalog' => 'Hickson',
            'mode' => 'observed',
            'starts_on' => $start,
            'ends_on' => $start->copy()->endOfMonth(),
        ]));
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

    private function form(Request $request, ObservingChallenge $challenge): View
    {
        $sources = $this->sources($request->user());
        // The list of an edited challenge the editor does not follow
        if ($challenge->source() !== null && ! $sources->contains('key', $challenge->source()['key'])) {
            $sources->push($challenge->source() + ['label' => html_entity_decode($challenge->source_label, ENT_QUOTES | ENT_HTML5, 'UTF-8')]);
        }

        return view('admin.challenges.form', ['challenge' => $challenge, 'sources' => $sources]);
    }

    /**
     * The catalogs, and the public observing lists the editor owns or
     * subscribed to: a challenge is for everybody.
     */
    private function sources(User $user): Collection
    {
        $public = ObservingList::where('public', true)->pluck('id')->flip();

        return ObjectSources::options($user)
            ->filter(fn (array $source) => $source['type'] === 'catalog' || $public->has($source['list_id']))
            ->values();
    }

    private function save(Request $request, ObservingChallenge $challenge): void
    {
        $key    = (string) $request->input('source');
        $source = $this->sources($request->user())->firstWhere('key', $key);
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
