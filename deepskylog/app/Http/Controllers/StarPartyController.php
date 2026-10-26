<?php

namespace App\Http\Controllers;

use App\Jobs\TranslateStarParty;
use App\Mail\StarPartyInvitationMail;
use App\Models\Country;
use App\Models\Location;
use App\Models\StarParty;
use App\Models\StarPartyInvitation;
use App\Models\StarPartyParticipant;
use App\Models\User;
use App\Services\Observations\ObservationCardData;
use App\Services\ObserverCountries;
use App\Services\StarParties\StarPartyActivity;
use App\Services\StarParties\StarPartyBest;
use App\Services\StarParties\StarPartyCalendar;
use App\Services\StarParties\StarPartyNights;
use App\Services\StarParties\StarPartyNotifier;
use App\Services\TonightsBest;
use App\Services\Weather\NightForecast;
use App\Support\EphemerisDate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Starparties (#315): observers organise a starparty at one of their
 * locations and invite other observers (or e-mail addresses); observers say
 * whether they come. The page of a starparty shows the site on a map, the
 * nights with their darkness and Moon, the objects worth observing there
 * and, once it started, the sessions and observations made at the site.
 */
class StarPartyController extends Controller
{
    /** The session key holding the tokens of the e-mail invitations opened, per starparty id. */
    public const SESSION_TOKENS = 'star_party_invitations';

    /** The starparties per page of each list of /star-parties. */
    public const PER_PAGE = 20;

    public function index(Request $request): View
    {
        $user = $request->user();

        // The countries of the coming starparties the observer may see, in the language of the site
        $countries = StarParty::visibleTo($user)->upcoming()
            ->whereNotNull('country')->where('country', '<>', '')
            ->distinct()->pluck('country')
            ->mapWithKeys(fn (string $name) => [$name => ObserverCountries::label(Country::codeFor($name)) ?? $name])
            ->sort(fn (string $a, string $b) => strcoll($a, $b))
            ->all();
        $country   = is_string($request->query('country')) && isset($countries[$request->query('country')]) ? $request->query('country') : null;
        $inCountry = fn ($query) => $query->when($country !== null, fn ($q) => $q->where('country', $country));

        // All coming starparties on the map, a page of them in the list
        $markers = StarParty::visibleTo($user)->upcoming()->tap($inCountry)
            ->orderBy('start_date')->orderBy('id')
            ->get(['id', 'name', 'slug', 'start_date', 'end_date', 'latitude', 'longitude']);
        $upcoming = StarParty::visibleTo($user)->upcoming()->tap($inCountry)
            ->with(['organiser:id,name,slug', 'participants'])
            ->orderBy('start_date')->orderBy('id')
            ->paginate(self::PER_PAGE, ['*'], 'upcoming')
            ->withQueryString();

        // The observer's own: those that have not ended, or all of them with ?mine=all
        $mine    = null;
        $hasMine = false;
        $allMine = $request->query('mine') === 'all';
        if ($user) {
            $own = fn () => StarParty::where(fn ($q) => $q->where('user_id', $user->id)
                ->orWhereIn('id', StarPartyParticipant::select('star_party_id')->where('user_id', $user->id)->where('status', '<>', StarPartyParticipant::DECLINED)));
            $hasMine = $own()->exists();
            $mine    = $own()
                ->when(! $allMine, fn ($q) => $q->upcoming())
                ->with(['organiser:id,name,slug', 'participants'])
                ->when($allMine, fn ($q) => $q->orderByDesc('start_date'), fn ($q) => $q->orderBy('start_date'))
                ->orderBy('id')
                ->paginate(self::PER_PAGE, ['*'], 'mine_page')
                ->withQueryString();
        }

        $past = StarParty::visibleTo($user)->past()->tap($inCountry)
            ->with(['organiser:id,name,slug', 'participants'])
            ->orderByDesc('start_date')->orderByDesc('id')
            ->paginate(self::PER_PAGE, ['*'], 'past')
            ->withQueryString();

        return view('star-parties.index', compact('markers', 'upcoming', 'mine', 'hasMine', 'allMine', 'past', 'countries', 'country'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        if ($refusal = $this->refusal($request->user())) {
            return redirect()->route('star-parties.index')->with('error', $refusal);
        }

        $party = new StarParty([
            'visibility'  => StarParty::PUBLIC,
            'language'    => $request->user()->observationlanguage ?: $request->user()->language ?: config('app.locale'),
            'location_id' => $request->user()->stdlocation,
        ]);

        return view('star-parties.form', ['party' => $party, 'locations' => $this->locations($request->user())]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        if ($refusal = $this->refusal($user)) {
            return redirect()->route('star-parties.index')->with('error', $refusal);
        }

        $party = new StarParty(['user_id' => $user->id]);
        DB::transaction(function () use ($request, $party, $user) {
            $this->save($request, $party);
            StarPartyParticipant::create(['star_party_id' => $party->id, 'user_id' => $user->id, 'status' => StarPartyParticipant::GOING]);
        });

        return redirect()->route('star-parties.show', $party)
            ->with('success', __('The starparty has been saved. Invite observers below.'));
    }

    public function show(Request $request, StarParty $starParty): View
    {
        $user  = $request->user();
        $token = $this->invitationToken($request, $starParty);
        abort_unless($starParty->canBeViewedBy($user, $token), 404);

        if ($user && $token !== null) {
            $this->acceptInvitation($starParty, $token, $user);
        }

        $starParty->load(['organiser', 'participants.user:id,name,slug,username,profile_photo_path']);

        $nights = [];
        $forecast = [];
        try {
            $nights = StarPartyNights::for($starParty);
            // The cloud forecast of the nights it reaches, once Open-Meteo answered
            $forecast = NightForecast::forStarParty($starParty, fetch: false) ?? [];
        } catch (\Throwable $e) {
            report($e);
        }

        // The objects worth observing at the site in the night chosen, the
        // coming one by default, while the starparty has not ended.
        $night = null;
        $best  = null;
        if (! $starParty->isPast() && ! $starParty->isCancelled()) {
            $night = $this->chosenNight($starParty, $request->query('night'));
            try {
                $best = StarPartyBest::for($starParty, $user, $night);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        // What was observed at the site, once it started.
        $sessions     = collect();
        $observations = collect();
        $observers    = collect();
        if ($starParty->startsAt()->isPast()) {
            $activity     = new StarPartyActivity($starParty);
            $sessions     = app(SessionController::class)->prepareSessionCards($activity->sessions());
            $observations = $activity->observations();
            $observers    = $activity->observers();
            ObservationCardData::instance()->preload($observations);
        }

        return view('star-parties.show', [
            'party'         => $starParty,
            'nights'        => $nights,
            'forecast'      => $forecast,
            'night'         => $night,
            'best'          => $best,
            'sessions'      => $sessions,
            'observations'  => $observations,
            'observers'     => $observers,
            'observerUsers' => User::whereIn('username', $observers->pluck('observer_username'))->get(['id', 'name', 'slug', 'username'])->keyBy(fn (User $u) => mb_strtolower($u->username)),
            'status'        => $starParty->statusOf($user),
            'canManage'     => $starParty->canBeManagedBy($user),
            'canInvite'     => $starParty->canInvite($user),
            'invitations'   => $starParty->canBeManagedBy($user) ? $starParty->invitations()->orderBy('email')->get() : collect(),
        ]);
    }

    public function edit(Request $request, StarParty $starParty): View
    {
        abort_unless($starParty->canBeManagedBy($request->user()), 403);

        return view('star-parties.form', ['party' => $starParty, 'locations' => $this->locations($starParty->organiser)]);
    }

    public function update(Request $request, StarParty $starParty): RedirectResponse
    {
        abort_unless($starParty->canBeManagedBy($request->user()), 403);

        $before = [$starParty->start_date->toDateString(), $starParty->end_date->toDateString(), $starParty->latitude, $starParty->longitude];
        $this->save($request, $starParty);

        $changes = [];
        if ($before[0] !== $starParty->start_date->toDateString() || $before[1] !== $starParty->end_date->toDateString()) {
            $changes[] = 'dates';
            // A new first night gets its own reminder.
            $starParty->forceFill(['reminder_sent_at' => null])->save();
        }
        if (abs($before[2] - $starParty->latitude) > 1e-6 || abs($before[3] - $starParty->longitude) > 1e-6) {
            $changes[] = 'site';
        }
        if ($changes !== [] && ! $starParty->isPast()) {
            StarPartyNotifier::changed($starParty, $request->user(), $changes);
        }

        return redirect()->route('star-parties.show', $starParty)->with('success', __('The starparty has been saved.'));
    }

    public function destroy(Request $request, StarParty $starParty): RedirectResponse
    {
        abort_unless($starParty->canBeManagedBy($request->user()), 403);

        // Deleting a starparty that is still to come cancels it for the participants.
        if (! $starParty->isPast() && ! $starParty->isCancelled()) {
            $starParty->cancelled_at = now();
            StarPartyNotifier::cancelled($starParty, $request->user());
        }
        $starParty->delete();

        return redirect()->route('star-parties.index')->with('success', __('The starparty has been deleted.'));
    }

    /** Cancel the starparty, or let it take place after all. */
    public function cancel(Request $request, StarParty $starParty): RedirectResponse
    {
        abort_unless($starParty->canBeManagedBy($request->user()), 403);
        abort_if($starParty->isPast(), 403);

        $starParty->forceFill(['cancelled_at' => $starParty->isCancelled() ? null : now()])->save();
        StarPartyNotifier::cancelled($starParty, $request->user());

        return back()->with('success', $starParty->isCancelled()
            ? __('The starparty is cancelled. The participants have been told.')
            : __('The starparty takes place after all. The participants have been told.'));
    }

    /** The observer says whether they come: going, maybe or declined. */
    public function answer(Request $request, StarParty $starParty): RedirectResponse
    {
        $user  = $request->user();
        $token = $this->invitationToken($request, $starParty);
        abort_unless($starParty->canBeViewedBy($user, $token), 404);
        if ($token !== null) {
            $this->acceptInvitation($starParty, $token, $user);
        }

        $status = (string) $request->input('status');
        $starParty->load('participants');
        if (! $starParty->canAnswer($user, $status)) {
            return back()->with('error', $starParty->isFull() && $status === StarPartyParticipant::GOING
                ? __('The starparty is full.')
                : __('You can no longer answer for this starparty.'));
        }

        $participant         = StarPartyParticipant::firstOrNew(['star_party_id' => $starParty->id, 'user_id' => $user->id]);
        $previous            = $participant->exists ? $participant->status : null;
        $participant->status = $status;
        $participant->save();

        $wasComing = in_array($previous, [StarPartyParticipant::GOING, StarPartyParticipant::MAYBE], true);
        if ($previous !== $status && ($status !== StarPartyParticipant::DECLINED || $wasComing)) {
            StarPartyNotifier::answered($starParty, $user, $status);
        }

        return back()->with('success', match ($status) {
            StarPartyParticipant::GOING => __('You come to this starparty.'),
            StarPartyParticipant::MAYBE => __('You may come to this starparty.'),
            default                     => __('You do not come to this starparty.'),
        });
    }

    /**
     * Invite observers (the multiselect posts 'receivers', a JSON list of
     * usernames) and e-mail addresses ('emails', separated by commas,
     * semicolons or new lines).
     */
    public function invite(Request $request, StarParty $starParty): RedirectResponse
    {
        $user = $request->user();
        abort_unless($starParty->canInvite($user), 403);

        $request->validate([
            'receivers' => ['nullable'],
            'emails'    => ['nullable', 'string', 'max:10000'],
        ]);

        $usernames = $this->usernames($request);
        $emails    = array_values(array_unique(array_filter(
            array_map(fn (string $e) => mb_strtolower(trim($e)), preg_split('/[\s,;]+/', (string) $request->input('emails', '')) ?: []),
            fn (string $e) => $e !== ''
        )));

        if ($usernames === [] && $emails === []) {
            return back()->with('error', __('Choose the observers or give the e-mail addresses to invite.'));
        }
        if (count($usernames) > MessagesController::MAX_RECEIVERS) {
            return back()->with('error', __('You can invite at most :max observers at once.', ['max' => MessagesController::MAX_RECEIVERS]));
        }
        $invalid = array_filter($emails, fn (string $e) => filter_var($e, FILTER_VALIDATE_EMAIL) === false);
        if ($invalid !== []) {
            throw ValidationException::withMessages(['emails' => __('These are no valid e-mail addresses: :emails', ['emails' => implode(', ', $invalid)])]);
        }
        $emails = array_values(array_diff($emails, $starParty->invitations()->pluck('email')->map(fn ($e) => mb_strtolower($e))->all()));
        if ($starParty->invitations()->count() + count($emails) > StarParty::MAX_EMAIL_INVITATIONS) {
            throw ValidationException::withMessages(['emails' => __('A starparty can have at most :max invitations by e-mail.', ['max' => StarParty::MAX_EMAIL_INVITATIONS])]);
        }

        $invited = 0;
        $starParty->load('participants');
        $known = $starParty->participants->pluck('user_id')->map(fn ($id) => (int) $id)->all();
        foreach (User::whereIn('username', $usernames)->get() as $receiver) {
            if ((int) $receiver->id === (int) $user->id || in_array((int) $receiver->id, $known, true)) {
                continue;
            }
            if (StarPartyNotifier::invite($starParty, $receiver, $user)) {
                StarPartyParticipant::create([
                    'star_party_id'      => $starParty->id,
                    'user_id'            => $receiver->id,
                    'status'             => StarPartyParticipant::INVITED,
                    'invited_by_user_id' => $user->id,
                ]);
                $invited++;
            }
        }

        $locale = app()->getLocale();
        foreach ($emails as $email) {
            $invitation = StarPartyInvitation::create([
                'star_party_id'      => $starParty->id,
                'email'              => $email,
                'token'              => Str::random(48),
                'invited_by_user_id' => $user->id,
            ]);
            Mail::to($email)->locale($locale)->send(new StarPartyInvitationMail($invitation));
            $invited++;
        }

        return back()->with('success', trans_choice('{0} Nobody new was invited.|{1} One invitation has been sent.|[2,*] :count invitations have been sent.', $invited, ['count' => $invited]));
    }

    /** The organiser takes an observer off the starparty. */
    public function removeParticipant(Request $request, StarParty $starParty, StarPartyParticipant $participant): RedirectResponse
    {
        abort_unless($starParty->canBeManagedBy($request->user()), 403);
        abort_unless((int) $participant->star_party_id === (int) $starParty->id, 404);
        abort_if((int) $participant->user_id === (int) $starParty->user_id, 403);

        $participant->delete();

        return back()->with('success', __('The observer has been taken off the starparty.'));
    }

    public function revokeInvitation(Request $request, StarParty $starParty, StarPartyInvitation $invitation): RedirectResponse
    {
        abort_unless($starParty->canBeManagedBy($request->user()), 403);
        abort_unless((int) $invitation->star_party_id === (int) $starParty->id, 404);

        $invitation->delete();

        return back()->with('success', __('The invitation has been withdrawn.'));
    }

    /**
     * The link of a mailed invitation: it keeps the token in the session, so
     * a private starparty can be seen, and makes a logged-in observer an
     * invited participant.
     */
    public function invitation(Request $request, string $token): RedirectResponse
    {
        $invitation = StarPartyInvitation::where('token', $token)->with('starParty')->firstOrFail();
        $party      = $invitation->starParty;

        $tokens             = (array) $request->session()->get(self::SESSION_TOKENS, []);
        $tokens[$party->id] = $token;
        $request->session()->put(self::SESSION_TOKENS, $tokens);

        if ($request->user()) {
            $this->acceptInvitation($party, $token, $request->user());

            return redirect()->route('star-parties.show', $party);
        }

        return redirect()->route('star-parties.show', $party)
            ->with('success', __('Log in or register to tell the organiser whether you come.'));
    }

    public function calendar(Request $request, StarParty $starParty): Response
    {
        abort_unless($starParty->canBeViewedBy($request->user(), $this->invitationToken($request, $starParty)), 404);

        return response(StarPartyCalendar::ics($starParty), 200, [
            'Content-Type'        => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$starParty->slug.'.ics"',
        ]);
    }

    /** Make an observing list of the objects worth observing at the site in a night. */
    public function storeList(Request $request, StarParty $starParty): RedirectResponse
    {
        $user = $request->user();
        abort_unless($starParty->canBeViewedBy($user, $this->invitationToken($request, $starParty)), 404);
        $this->authorize('create', \App\Models\ObservingList::class);

        $night  = $this->chosenNight($starParty, $request->input('night'));
        $result = StarPartyBest::for($starParty, $user, $night);
        $name   = __(':party - night of :date', [
            'party' => html_entity_decode($starParty->name, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'date'  => \Carbon\Carbon::parse($night)->locale(app()->getLocale())->isoFormat('LL'),
        ]);
        $list = $result ? TonightsBest::createList($user, $result, $name) : null;
        if (! $list) {
            return back()->with('error', __('There are no deep-sky objects for this night to put on a list.'));
        }

        return redirect()->route('observing-list.show', $list)
            ->with('success', __('Observing list created with :count object(s).', ['count' => $list->items()->count()]));
    }

    /**
     * Why the observer cannot organise another starparty, or null when they
     * can.
     */
    private function refusal(User $user): ?string
    {
        if ($user->hasAdministratorPrivileges()) {
            return null;
        }
        $open = StarParty::where('user_id', $user->id)->upcoming()->count();

        return $open >= StarParty::MAX_UPCOMING_PER_ORGANISER
            ? __('You can organise at most :max starparties that have not ended yet.', ['max' => StarParty::MAX_UPCOMING_PER_ORGANISER])
            : null;
    }

    private function save(Request $request, StarParty $party): void
    {
        $organiserId = $party->user_id ?? $request->user()->id;
        $validated   = $request->validate([
            'name'                       => ['required', 'string', 'max:255'],
            'description'                => ['nullable', 'string', 'max:20000'],
            'language'                   => ['required', Rule::in(array_values(config('app.available_locales')))],
            'visibility'                 => ['required', Rule::in([StarParty::PUBLIC, StarParty::PRIVATE])],
            'start_date'                 => array_merge(['required', 'date'], $party->exists ? [] : ['after_or_equal:today']),
            'end_date'                   => ['required', 'date', 'after_or_equal:start_date'],
            'registration_deadline'      => ['nullable', 'date', 'before_or_equal:end_date'],
            'max_participants'           => ['nullable', 'integer', 'min:1', 'max:5000'],
            'location_id'                => [$party->exists ? 'nullable' : 'required', 'integer'],
            'venue'                      => ['nullable', 'string', 'max:255'],
            'website'                    => ['nullable', 'url:http,https', 'max:255'],
            'activity_radius_km'         => ['nullable', Rule::in(StarParty::RADIUS_OPTIONS)],
            'activity_participants_only' => ['nullable', 'boolean'],
        ]);
        // The form always sends both; without them the earlier choice stays.
        $validated['activity_radius_km'] = (float) ($validated['activity_radius_km'] ?? $party->activity_radius_km);
        if ($request->has('activity_participants_only')) {
            $validated['activity_participants_only'] = $request->boolean('activity_participants_only');
        } else {
            unset($validated['activity_participants_only']);
        }

        $nights = \Carbon\Carbon::parse($validated['start_date'])->diffInDays(\Carbon\Carbon::parse($validated['end_date'])) + 1;
        if ($nights > StarParty::MAX_NIGHTS) {
            throw ValidationException::withMessages(['end_date' => __('A starparty lasts at most :max nights.', ['max' => StarParty::MAX_NIGHTS])]);
        }

        if (! empty($validated['location_id'])) {
            $location = Location::where('user_id', $organiserId)->find($validated['location_id']);
            if (! $location || ! $location->hasValidCoordinates()) {
                throw ValidationException::withMessages(['location_id' => __('Choose one of your locations.')]);
            }
            $party->useLocation($location);
        }
        unset($validated['location_id']);

        $party->fill($validated);
        $translate = ! $party->exists || $party->isDirty(['description', 'language']);
        if ($translate) {
            $party->translations = null;
        }
        $party->save();

        if ($translate && trim(strip_tags((string) $party->description)) !== '' && config('app.translate_observations')) {
            TranslateStarParty::dispatch($party->id);
        }
    }

    /** @return \Illuminate\Support\Collection<int, Location> the active locations of the observer */
    private function locations(?User $user)
    {
        if (! $user) {
            return collect();
        }

        return Location::where('user_id', $user->id)
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'latitude', 'longitude', 'country'])
            ->filter(fn (Location $location) => $location->hasValidCoordinates())
            ->values();
    }

    /**
     * The night asked for when it is one of the starparty, else the coming
     * night of the starparty (the first one before it starts).
     */
    private function chosenNight(StarParty $party, mixed $asked): string
    {
        $nights = $party->nights();
        if (is_string($asked) && in_array($asked, $nights, true)) {
            return $asked;
        }
        $today = EphemerisDate::today($party->timezone ?: 'UTC');
        foreach ($nights as $night) {
            if ($night >= $today) {
                return $night;
            }
        }

        return $nights[0];
    }

    private function invitationToken(Request $request, StarParty $party): ?string
    {
        $token = $request->hasSession() ? ($request->session()->get(self::SESSION_TOKENS, [])[$party->id] ?? null) : null;

        return is_string($token) && $token !== '' ? $token : null;
    }

    /** The observer who opened a mailed invitation becomes an invited participant. */
    private function acceptInvitation(StarParty $party, string $token, User $user): void
    {
        $invitation = $party->invitations()->where('token', $token)->first();
        // The invitation is for one observer: a forwarded link only shows the starparty.
        if (! $invitation || ($invitation->accepted_by_user_id !== null && (int) $invitation->accepted_by_user_id !== (int) $user->id)) {
            return;
        }
        if ($invitation->accepted_by_user_id === null) {
            $invitation->forceFill(['accepted_by_user_id' => $user->id, 'accepted_at' => now()])->save();
        }
        StarPartyParticipant::firstOrCreate(
            ['star_party_id' => $party->id, 'user_id' => $user->id],
            ['status' => StarPartyParticipant::INVITED, 'invited_by_user_id' => $invitation->invited_by_user_id]
        );
        $party->unsetRelation('participants');
    }

    /**
     * The usernames of the multiselect: 'receivers', a JSON list or an array.
     *
     * @return list<string>
     */
    private function usernames(Request $request): array
    {
        $receivers = $request->input('receivers');
        if (is_string($receivers)) {
            $decoded   = json_decode($receivers, true);
            $receivers = is_array($decoded) ? $decoded : [$receivers];
        }

        return collect((array) $receivers)
            ->filter(fn ($u) => is_string($u) && trim($u) !== '')
            ->map(fn ($u) => trim($u))
            ->unique(fn ($u) => mb_strtolower($u))
            ->values()
            ->all();
    }
}
