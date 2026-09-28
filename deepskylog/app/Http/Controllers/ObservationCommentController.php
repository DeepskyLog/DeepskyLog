<?php

namespace App\Http\Controllers;

use App\Models\Observation;
use App\Models\ObservationComment;
use App\Models\User;
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

        $owner = $this->resolveObservationOwner((int) $validated['observation_id']);
        if (!$owner) {
            abort(404);
        }

        ObservationComment::create([
            'observation_type' => ObservationComment::TYPE_OBSERVATION,
            'observation_id' => (int) $validated['observation_id'],
            'user_id' => $user->id,
            'body' => $validated['body'],
        ]);

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
        if (!$obs) {
            return null;
        }

        return User::find($obs->observer_user_id)
            ?? User::where('username', html_entity_decode((string) $obs->observer_username))->first();
    }
}
