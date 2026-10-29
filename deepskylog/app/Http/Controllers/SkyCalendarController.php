<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\SkyCalendar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * The personal sky calendar (App\Services\SkyCalendar): an iCalendar feed
 * opened with the observer's secret token instead of a login, so a calendar
 * app can subscribe to it.
 */
class SkyCalendarController extends Controller
{
    public function show(string $token): Response
    {
        $user = User::where('calendar_feed_token', $token)->firstOrFail();

        return response(SkyCalendar::ics($user), 200, [
            'Content-Type'        => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'inline; filename="deepskylog.ics"',
        ]);
    }

    /** Give the calendar a new address, so the old one stops working. */
    public function regenerate(Request $request): RedirectResponse
    {
        $request->user()->regenerateCalendarFeedToken();
        SkyCalendar::forget($request->user());

        return redirect()->back()->with('success', __('Your sky calendar has a new address. The old address no longer works.'));
    }
}
