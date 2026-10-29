<?php

namespace App\Http\Controllers;

use App\Models\ObjectFollow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Following objects: the follow and unfollow buttons of the object page.
 * The object is posted as the observations store it (target_table and
 * target_key).
 */
class ObjectFollowController extends Controller
{
    public function follow(Request $request): RedirectResponse
    {
        $user = $request->user();
        $target = $this->target($request);

        if (!ObjectFollow::isFollowedBy($user, $target['table'], $target['key'])) {
            if ($user->followedObjects()->count() >= ObjectFollow::MAX_PER_USER) {
                return redirect()->back()->with('error', __('You can follow at most :count objects.', ['count' => ObjectFollow::MAX_PER_USER]));
            }
            ObjectFollow::firstOrCreate(
                ['user_id' => $user->id, 'target_table' => $target['table'], 'target_key' => $target['key']],
                ['target_type' => $target['type']],
            );
        }
        RssFeedController::forgetFollowingFeed($user);

        return redirect()->back()->with('success', __('You now follow :name', ['name' => $target['name']]));
    }

    public function unfollow(Request $request): RedirectResponse
    {
        $user = $request->user();
        $target = $this->target($request);

        ObjectFollow::where('user_id', $user->id)
            ->where('target_table', $target['table'])
            ->where('target_key', $target['key'])
            ->delete();
        RssFeedController::forgetFollowingFeed($user);

        return redirect()->back()->with('success', __('You no longer follow :name', ['name' => $target['name']]));
    }

    /**
     * @return array{type: string, table: string, key: string, name: string, slug: string}
     */
    private function target(Request $request): array
    {
        $validated = $request->validate([
            'table' => ['required', 'string', Rule::in(array_keys(ObjectFollow::TABLES))],
            'key' => ['required', 'string', 'max:255'],
        ]);

        return ObjectFollow::resolve($validated['table'], $validated['key']) ?? abort(404);
    }
}
