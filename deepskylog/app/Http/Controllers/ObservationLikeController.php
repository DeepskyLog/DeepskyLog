<?php

namespace App\Http\Controllers;

use App\Models\CometObservationsOld;
use App\Models\ObservationLike;
use App\Models\ObservationsOld;
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

        if (! Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $user = Auth::user();

        $type = $request->input('observation_type');
        $id = $request->input('observation_id');

        $like = ObservationLike::where('user_id', $user->id)
            ->where('observation_type', $type)
            ->where('observation_id', $id)
            ->first();

        // Determine owner of observation
        $owner = null;
        if ($type === 'deepsky') {
            $obs = ObservationsOld::find($id);
            if ($obs) {
                $owner = User::where('username', html_entity_decode($obs->observerid))->first();
            }
        } elseif ($type === 'comet') {
            $obs = CometObservationsOld::find($id);
            if ($obs) {
                $owner = User::where('username', html_entity_decode($obs->observerid))->first();
            }
        }

        if (! $owner) {
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
