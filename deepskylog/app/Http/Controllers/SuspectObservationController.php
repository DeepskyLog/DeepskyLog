<?php

namespace App\Http\Controllers;

use App\Console\Commands\CheckSuspectObservations;
use App\Models\SuspectObservation;
use App\Models\User;
use App\Services\Observations\ObservationCardData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

/**
 * The observations that look wrong (App\Services\Observations\SuspectObservations):
 * an observer's own on /observations/suspect, everybody's for administrators
 * and database experts on /admin/suspect-observations. The observer corrects
 * the observation, deletes it, or marks the finding as right (dismiss()).
 */
class SuspectObservationController extends Controller
{
    public const PER_PAGE = 50;

    public const STATES = ['open', 'dismissed', 'all'];

    public function index(Request $request): View
    {
        return $this->page($request, $request->user(), false);
    }

    public function admin(Request $request): View
    {
        $this->ensureAdministrator($request->user());

        $observer = trim((string) $request->query('observer', ''));
        $user = $observer !== '' ? User::query()->where('username', $observer)->first() : null;

        return $this->page($request, $observer !== '' ? ($user ?? (new User())->forceFill(['username' => $observer])) : null, true);
    }

    public function dismiss(Request $request, SuspectObservation $suspect): RedirectResponse
    {
        abort_unless($suspect->canBeManagedBy($request->user()), 403);
        $suspect->dismiss($request->user());

        return back()->with('success', __('The observation is marked as right.'));
    }

    public function restore(Request $request, SuspectObservation $suspect): RedirectResponse
    {
        abort_unless($suspect->canBeManagedBy($request->user()), 403);
        $suspect->restore();

        return back();
    }

    /**
     * The open findings per reason, with those dismissed, for /admin/check-objects.
     *
     * @return array{reasons: array<string, array{open: int, dismissed: int}>, checked_until: ?string}
     */
    public static function summary(): array
    {
        $reasons = array_fill_keys(SuspectObservation::REASONS, ['open' => 0, 'dismissed' => 0]);
        $rows = SuspectObservation::query()
            ->selectRaw('reason, dismissed_at IS NULL AS open, count(*) AS n')
            ->groupBy('reason', 'open')
            ->get();
        foreach ($rows as $row) {
            if (isset($reasons[$row->reason])) {
                $reasons[$row->reason][$row->open ? 'open' : 'dismissed'] = (int) $row->n;
            }
        }

        return ['reasons' => $reasons, 'checked_until' => Cache::get(CheckSuspectObservations::CHECKED_UNTIL_KEY)];
    }

    private function page(Request $request, ?User $observer, bool $admin): View
    {
        $reason = in_array($request->query('reason'), SuspectObservation::REASONS, true) ? $request->query('reason') : null;
        $state = in_array($request->query('state'), self::STATES, true) ? $request->query('state') : 'open';

        $base = SuspectObservation::query()
            ->when($observer !== null, fn ($q) => $q->ofObserver($observer))
            ->when($state === 'open', fn ($q) => $q->whereNull('suspect_observations.dismissed_at'))
            ->when($state === 'dismissed', fn ($q) => $q->whereNotNull('suspect_observations.dismissed_at'));

        $counts = (clone $base)->selectRaw('reason, count(*) AS n')->groupBy('reason')->pluck('n', 'reason');

        $suspects = $base
            ->when($reason !== null, fn ($q) => $q->where('suspect_observations.reason', $reason))
            ->join('observations', 'observations.id', '=', 'suspect_observations.observation_id')
            ->select('suspect_observations.*')
            ->with(['observation', 'relatedObservation'])
            ->orderByDesc('observations.observed_on')
            ->orderByDesc('observations.id')
            ->orderBy('suspect_observations.reason')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        ObservationCardData::instance()->preloadDetailUrls($suspects->getCollection()
            ->flatMap(fn (SuspectObservation $s) => array_filter([$s->observation, $s->relatedObservation])));

        return view('observations.suspect', [
            'suspects' => $suspects,
            'counts' => $counts,
            'reason' => $reason,
            'state' => $state,
            'admin' => $admin,
            'observer' => $admin ? $observer?->username : null,
            'checkedUntil' => Cache::get(CheckSuspectObservations::CHECKED_UNTIL_KEY),
        ]);
    }

    private function ensureAdministrator(?User $user): void
    {
        abort_unless($user !== null && ($user->isAdministrator() || $user->isDatabaseExpert()), 403);
    }
}
