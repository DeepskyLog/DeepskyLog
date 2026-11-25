<?php

namespace App\Http\Controllers;

use App\Models\ObjectFindingNote;
use App\Models\ObjectFindingNoteLike;
use App\Models\Observation;
use App\Models\ObservationLike;
use App\Models\ObservationSession;
use App\Models\ObservingList;
use App\Models\ObservingListLike;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Who liked an observation, session, observing list or finding note: the
 * names behind a like count, for logged-in observers only (the route has
 * `auth`), loaded when the likers button of `x-likers-button` is clicked.
 */
class LikersController extends Controller
{
    /** At most this many names are returned; the rest is a count. */
    public const LIMIT = 50;

    public const KINDS = ['observation', 'session', 'observing-list', 'finding-note'];

    public function show(Request $request, string $kind, int $id): JsonResponse
    {
        $viewer = $request->user();

        $likes = match ($kind) {
            'observation' => $this->observationLikes($id),
            'session' => $this->sessionLikes($id, $viewer),
            'observing-list' => $this->listLikes($id, $viewer),
            'finding-note' => $this->findingNoteLikes($id),
            default => abort(404),
        };

        $total = (clone $likes)->count();
        $userIds = $likes->orderByDesc('created_at')->orderByDesc('id')
            ->limit(self::LIMIT)->pluck('user_id');
        $users = User::whereIn('id', $userIds)->get(['id', 'name', 'slug'])->keyBy('id');

        $likers = $userIds
            ->map(fn ($userId) => $users->get($userId))
            ->filter()
            ->map(fn (User $user) => [
                'name' => html_entity_decode((string) $user->name, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                'url' => route('observer.show', $user->slug),
            ])
            ->values();

        return response()->json([
            'total' => $total,
            'likers' => $likers,
            'more' => max(0, $total - $likers->count()),
        ]);
    }

    private function observationLikes(int $id): Builder
    {
        abort_unless(Observation::whereKey($id)->exists(), 404);

        return ObservationLike::where('observation_type', ObservationLike::TYPE_OBSERVATION)
            ->where('observation_id', $id);
    }

    /** An inactive session is only the observer's (and an administrator's), as on its page. */
    private function sessionLikes(int $id, User $viewer): Builder
    {
        $session = ObservationSession::findOrFail($id);
        if ((int) ($session->active ?? 1) === 0) {
            $isOwner = html_entity_decode((string) $session->observerid) === $viewer->username;
            $adminMay = config('sessions.allow_admin_override', false) && $viewer->isAdministrator();
            abort_unless($isOwner || $adminMay, 404);
        }

        return ObservationLike::where('observation_type', ObservationLike::TYPE_SESSION)
            ->where('observation_id', $id);
    }

    private function listLikes(int $id, User $viewer): Builder
    {
        $list = ObservingList::findOrFail($id);
        abort_unless($viewer->can('view', $list), 404);

        return ObservingListLike::where('observing_list_id', $id);
    }

    private function findingNoteLikes(int $id): Builder
    {
        abort_unless(ObjectFindingNote::whereKey($id)->exists(), 404);

        return ObjectFindingNoteLike::where('object_finding_note_id', $id);
    }
}
