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
            'observation_type' => ['required', 'in:deepsky,comet'],
            'observation_id' => ['required', 'integer'],
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $user = Auth::user();

        $owner = $this->resolveObservationOwner($validated['observation_type'], (int) $validated['observation_id']);
        if (!$owner) {
            abort(404);
        }

        ObservationComment::create([
            'observation_type' => $validated['observation_type'],
            'observation_id' => (int) $validated['observation_id'],
            'user_id' => $user->id,
            'body' => $validated['body'],
        ]);

        return redirect()->back()->with('success', __('Comment added.'));
    }

    public function destroy(ObservationComment $comment): RedirectResponse
    {
        $user = Auth::user();
        $owner = $this->resolveObservationOwner($comment->observation_type, (int) $comment->observation_id);

        if (!$comment->canBeDeletedBy($user, $owner)) {
            abort(403);
        }

        $comment->delete();

        return redirect()->back()->with('success', __('Comment deleted.'));
    }

    private function resolveObservationOwner(string $type, int $observationId): ?User
    {
        $obs = Observation::find($observationId);
        if (!$obs) {
            return null;
        }

        return User::find($obs->observer_user_id);
    }
}
