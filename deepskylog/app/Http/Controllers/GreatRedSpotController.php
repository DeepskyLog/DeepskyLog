<?php

namespace App\Http\Controllers;

use App\Models\GreatRedSpotPosition;
use App\Services\Planets\PlanetMoonEvents;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * /admin/great-red-spot: the measurements of the System II longitude of
 * Jupiter's Great Red Spot (GreatRedSpotPosition), the newest of which gives
 * the transits on /jupiter-saturn. For administrators and database experts
 * (ability manage_news), whom planets:grs-reminder asks every month to check
 * it against JUPOS or Sky & Telescope.
 */
class GreatRedSpotController extends Controller
{
    public function index(): View
    {
        return view('admin.great-red-spot', [
            'positions' => GreatRedSpotPosition::with('user:id,name')->orderByDesc('measured_on')->orderByDesc('id')->get(),
            'current'   => GreatRedSpotPosition::current(),
            'today'     => PlanetMoonEvents::grsLongitude(now()->getTimestamp()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'longitude'   => ['required', 'numeric', 'min:0', 'lt:360'],
            'measured_on' => ['required', 'date', 'before_or_equal:today'],
            'drift'       => ['required', 'numeric', 'between:-60,60'],
            'source'      => ['nullable', 'string', 'max:255'],
        ]);

        GreatRedSpotPosition::create($data + ['user_id' => $request->user()->id]);

        return redirect()->route('admin.great-red-spot')->with('success', __('The longitude of the Great Red Spot was saved.'));
    }

    public function destroy(GreatRedSpotPosition $position): RedirectResponse
    {
        $position->delete();

        return redirect()->route('admin.great-red-spot')->with('success', __('The measurement was removed.'));
    }
}
