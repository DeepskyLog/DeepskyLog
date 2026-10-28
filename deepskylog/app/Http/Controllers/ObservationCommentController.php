<?php

namespace App\Http\Controllers;

use App\Models\Observation;
use App\Models\ObservationComment;
use App\Models\User;
use App\Services\CommentNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ObservationCommentController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'observation_id' => ['required', 'integer'],
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $user = Auth::user();

        $observation = Observation::find((int) $validated['observation_id']);
        $owner = $observation ? $this->ownerOf($observation) : null;
        if (!$owner) {
            abort(404);
        }

        ObservationComment::create([
            'observation_type' => ObservationComment::TYPE_OBSERVATION,
            'observation_id' => $observation->id,
            'user_id' => $user->id,
            'body' => $validated['body'],
        ]);

        $url = $observation->detailUrl($owner->slug);
        if ($url !== null) {
            CommentNotifier::notify(
                $owner,
                $user,
                $validated['body'],
                'New comment on your observation of :object',
                'User :user commented on your observation of :object.',
                ['object' => html_entity_decode((string) $observation->target_name, ENT_QUOTES | ENT_HTML5, 'UTF-8')],
                $url,
                'Open observation',
            );
        }

        return redirect()->back()->with('success', __('Comment added.'));
    }

    public function destroy(ObservationComment $comment): RedirectResponse
    {
        $user = Auth::user();
        $owner = $this->resolveObservationOwner((int) $comment->observation_id);

        if (!$comment->canBeDeletedBy($user, $owner)) {
            abort(403);
        }

        $comment->delete();

        return redirect()->back()->with('success', __('Comment deleted.'));
    }

    /**
     * @param  int  $observationId  the id in the `observations` table
     */
    private function resolveObservationOwner(int $observationId): ?User
    {
        $obs = Observation::find($observationId);

        return $obs ? $this->ownerOf($obs) : null;
    }

    private function ownerOf(Observation $observation): ?User
    {
        return User::find($observation->observer_user_id)
            ?? User::where('username', html_entity_decode((string) $observation->observer_username))->first();
    }
}
