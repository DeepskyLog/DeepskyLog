<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserFollow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Following observers (issue #966): the follow and unfollow buttons of the
 * observer page, and a new token for the personal RSS feed.
 */
class FollowController extends Controller
{
    public function follow(Request $request, string $observer): RedirectResponse
    {
        $followed = User::where('slug', $observer)->firstOrFail();
        $user = $request->user();

        if (!$user->canFollow($followed)) {
            abort(403);
        }

        UserFollow::firstOrCreate(['follower_id' => $user->id, 'followed_id' => $followed->id]);
        RssFeedController::forgetFollowingFeed($user);

        return redirect()->back()->with('success', __('You now follow :name', ['name' => $followed->name]));
    }

    public function unfollow(Request $request, string $observer): RedirectResponse
    {
        $followed = User::where('slug', $observer)->firstOrFail();
        $user = $request->user();

        // Through the model, so the response cache is cleared
        UserFollow::where('follower_id', $user->id)->where('followed_id', $followed->id)->get()->each->delete();
        RssFeedController::forgetFollowingFeed($user);

        return redirect()->back()->with('success', __('You no longer follow :name', ['name' => $followed->name]));
    }

    public function regenerateFeedToken(Request $request): RedirectResponse
    {
        $request->user()->regenerateFollowFeedToken();

        return redirect()->back()->with('success', __('Your feed has a new address. The old address no longer works.'));
    }
}
