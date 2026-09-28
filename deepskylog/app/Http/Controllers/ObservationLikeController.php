<?php

namespace App\Http\Controllers;

use App\Models\Observation;
use App\Models\ObservationLike;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ObservationLikeController extends Controller
{
    public function toggle(Request $request)
    {
        $request->validate([
            'observation_type' => 'required|string',
            'observation_id' => 'required|integer',
        ]);

        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user = Auth::user();

        // Anything but a session is an observation, liked by its id in the
        // `observations` table. Pages rendered before likes were moved to that
        // id still send the target type (deepsky, comet, planet, ...).
        $type = $request->input('observation_type') === ObservationLike::TYPE_SESSION
            ? ObservationLike::TYPE_SESSION
            : ObservationLike::TYPE_OBSERVATION;
        $id = (int) $request->input('observation_id');

        $like = ObservationLike::where('user_id', $user->id)
            ->where('observation_type', $type)
            ->where('observation_id', $id)
            ->first();

        // Determine owner of observation/session
        $owner = null;
        if ($type === ObservationLike::TYPE_SESSION) {
            // For sessions, owner is the observer (observerid) on the session record
            $session = \App\Models\ObservationSession::find($id);
            if ($session) {
                $owner = User::where('username', html_entity_decode($session->observerid))->first();
            }
        } else {
            $obs = Observation::find($id);
            if ($obs) {
                $owner = User::find($obs->observer_user_id)
                    ?? User::where('username', html_entity_decode((string) $obs->observer_username))->first();
            }
        }

        if (!$owner) {
            return response()->json(['message' => 'Observation not found'], 404);
        }

        if ($like) {
            // unlike
            $like->delete();
            if (method_exists($owner, 'deductPoints')) {
                try {
                    $owner->deductPoints(1);
                } catch (\Throwable $e) {
                    // ignore level-up errors
                }
            }
            $status = 'unliked';
        } else {
            // create like
            ObservationLike::create([
                'user_id' => $user->id,
                'observation_type' => $type,
                'observation_id' => $id,
            ]);
            if (method_exists($owner, 'addPoints')) {
                try {
                    $owner->addPoints(1);
                } catch (\Throwable $e) {
                    // ignore level-up errors
                }
            }
            $status = 'liked';
        }

        $count = ObservationLike::where('observation_type', $type)->where('observation_id', $id)->count();

        return response()->json(['status' => $status, 'count' => $count]);
    }
}
