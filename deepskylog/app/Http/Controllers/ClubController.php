<?php

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\ClubMember;
use App\Models\ObservingChallenge;
use App\Models\ObservingList;
use App\Models\StarParty;
use App\Models\User;
use App\Services\ClubRanking;
use App\Services\Clubs\ClubNotifier;
use App\Services\ObjectSources;
use App\Services\ObserverCountries;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Monarobase\CountryList\CountryListFacade as Countries;

/**
 * Clubs and groups of observers: /clubs lists them, the page of a club
 * shows its members, the observing lists they share, its challenges and
 * starparties and the ranking of its members. Observers join (or ask to
 * join) as many clubs as they like; the club administrators edit the club,
 * let observers in, invite them and set the club's challenges
 * (ObservingChallengeController).
 */
class ClubController extends Controller
{
    public const PER_PAGE = 25;

    /** The ended challenges and past starparties shown on the page of a club. */
    public const PAST_SHOWN = 5;

    public function index(Request $request): View
    {
        $user    = $request->user();
        $search  = trim((string) $request->query('q', ''));
        $country = strtoupper(trim((string) $request->query('country', '')));

        $countries = Club::whereNotNull('country')->where('country', '<>', '')->distinct()->pluck('country')
            ->mapWithKeys(fn (string $code) => [$code => ObserverCountries::label($code) ?? $code])
            ->sort(fn (string $a, string $b) => strcoll($a, $b))
            ->all();
        $country = isset($countries[$country]) ? $country : null;

        $clubs = Club::query()
            ->withCount('members')
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.addcslashes($search, '%_\\').'%';
                $query->where(fn ($q) => $q->where('name', 'like', $like)->orWhere('place', 'like', $like));
            })
            ->when($country !== null, fn ($query) => $query->where('country', $country))
            ->orderBy('name')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        $mine        = collect();
        $invitations = collect();
        $requests    = collect();
        $legacyClub  = null;
        if ($user) {
            $mine        = $user->clubs()->withCount('members')->orderBy('name')->get();
            $pending     = ClubMember::where('user_id', $user->id)->where('status', '<>', ClubMember::MEMBER)->with('club')->get();
            $invitations = $pending->where('status', ClubMember::INVITED)->pluck('club')->filter()->values();
            $requests    = $pending->where('status', ClubMember::REQUESTED)->pluck('club')->filter()->values();
            $legacyClub  = $this->legacyClub($user, $mine->concat($invitations)->concat($requests));
        }

        return view('clubs.index', compact('clubs', 'mine', 'invitations', 'requests', 'search', 'country', 'countries', 'legacyClub'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        if ($refusal = $this->refusal($request->user())) {
            return redirect()->route('clubs.index')->with('error', $refusal);
        }

        return view('clubs.form', [
            'club' => new Club([
                'country' => $request->user()->country,
                'name'    => (string) $request->query('name', ''),
            ]),
            'countries' => $this->countries(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        if ($refusal = $this->refusal($user)) {
            return redirect()->route('clubs.index')->with('error', $refusal);
        }

        $club = new Club(['user_id' => $user->id]);
        DB::transaction(function () use ($request, $club, $user) {
            $this->save($request, $club);
            ClubMember::create(['club_id' => $club->id, 'user_id' => $user->id, 'role' => ClubMember::ROLE_ADMIN, 'status' => ClubMember::MEMBER]);
        });

        return redirect()->route('clubs.show', $club)->with('success', __('The club has been made. Invite its members below.'));
    }

    public function show(Request $request, Club $club): View
    {
        $viewer     = $request->user();
        $membership = $club->membershipOf($viewer);
        $isMember   = $club->isMember($viewer);
        $canManage  = $club->canBeManagedBy($viewer);

        $people = $club->memberships()->with('user:id,name,slug,username,profile_photo_path')->get()
            ->filter(fn (ClubMember $row) => $row->user !== null);
        $members = $people->where('status', ClubMember::MEMBER)
            ->sortBy(fn (ClubMember $row) => [$row->role === ClubMember::ROLE_ADMIN ? 0 : 1, mb_strtolower((string) $row->user->name)])
            ->values();

        // The challenges: the hidden ones only for the club administrators
        $challenges = $club->challenges()->withCount('goals')
            ->when(! $canManage, fn ($query) => $query->where('hidden', false))
            ->get();
        $running  = $challenges->filter(fn (ObservingChallenge $c) => $c->status() === 'active')->sortBy('ends_on')->values();
        $upcoming = $challenges->filter(fn (ObservingChallenge $c) => $c->status() === 'upcoming')->sortBy('starts_on')->values();
        $ended    = $challenges->filter(fn (ObservingChallenge $c) => $c->status() === 'ended')->sortByDesc('ends_on')->take(self::PAST_SHOWN)->values();

        $starParties = StarParty::visibleTo($viewer)->where('club_id', $club->id)->upcoming()
            ->with(['organiser:id,name,slug', 'participants'])->orderBy('start_date')->get();
        $pastStarParties = StarParty::visibleTo($viewer)->where('club_id', $club->id)->past()
            ->with(['organiser:id,name,slug', 'participants'])->orderByDesc('start_date')->limit(self::PAST_SHOWN)->get();

        // The ranking of the members
        $sourceOptions = $this->sourceOptions($club, $viewer);
        $source        = ObjectSources::resolve((string) $request->query('source', ''), $viewer, $sourceOptions);
        $period        = in_array($request->query('period'), ClubRanking::PERIODS, true) ? $request->query('period') : ClubRanking::PERIODS[0];
        $sort          = in_array($request->query('sort'), ClubRanking::SORTS, true) ? $request->query('sort') : ClubRanking::SORTS[0];
        $ranking       = ClubRanking::for($club, $period, $source, $sort);

        $sharedLists = $club->sharedLists();
        $ownLists    = $isMember
            ? ObservingList::where('owner_user_id', $viewer->id)->where('public', true)
                ->whereNotIn('id', $sharedLists->pluck('id'))->orderBy('name')->get(['id', 'name'])
            : collect();

        return view('clubs.show', [
            'club'            => $club,
            'membership'      => $membership,
            'isMember'        => $isMember,
            'canManage'       => $canManage,
            'members'         => $members,
            'requested'       => $canManage ? $people->where('status', ClubMember::REQUESTED)->values() : collect(),
            'invited'         => $canManage ? $people->where('status', ClubMember::INVITED)->values() : collect(),
            'adminCount'      => $members->where('role', ClubMember::ROLE_ADMIN)->count(),
            'running'         => $running,
            'upcoming'        => $upcoming,
            'ended'           => $ended,
            'starParties'     => $starParties,
            'pastStarParties' => $pastStarParties,
            'sharedLists'     => $sharedLists,
            'ownLists'        => $ownLists,
            'ranking'         => $ranking,
            'sourceOptions'   => $sourceOptions,
            'source'          => $source,
            'period'          => $period,
            'sort'            => $sort,
        ]);
    }

    public function edit(Request $request, Club $club): View
    {
        abort_unless($club->canBeManagedBy($request->user()), 403);

        return view('clubs.form', ['club' => $club, 'countries' => $this->countries()]);
    }

    public function update(Request $request, Club $club): RedirectResponse
    {
        abort_unless($club->canBeManagedBy($request->user()), 403);
        $this->save($request, $club);

        return redirect()->route('clubs.show', $club)->with('success', __('The club has been saved.'));
    }

    public function destroy(Request $request, Club $club): RedirectResponse
    {
        abort_unless($club->canBeManagedBy($request->user()), 403);
        $club->delete();

        return redirect()->route('clubs.index')->with('success', __('The club has been deleted.'));
    }

    /**
     * Join the club: at once when it is open or the observer was invited,
     * else the club administrators get the request.
     */
    public function join(Request $request, Club $club): RedirectResponse
    {
        $user = $request->user();
        if ($club->isMember($user)) {
            return back();
        }

        $membership = $club->join($user);
        if (! $membership->isMember()) {
            if ($membership->wasRecentlyCreated || $membership->wasChanged('status')) {
                ClubNotifier::requested($club, $user);
            }

            return back()->with('success', __('Your request has been sent to the administrators of the club.'));
        }

        return back()->with('success', __('Welcome! You are now a member of the club :club.', ['club' => $club->name]));
    }

    /** Leave the club, withdraw a request to join or decline an invitation. */
    public function leave(Request $request, Club $club): RedirectResponse
    {
        $wasMember = $club->isMember($request->user());
        if ($club->remove($request->user())) {
            return redirect()->route('clubs.index')->with('success', __('You were the last member: the club has been deleted.'));
        }

        return back()->with('success', $wasMember ? __('You left the club.') : __('Done.'));
    }

    /**
     * The club administrators invite observers (the multiselect posts
     * 'receivers', a JSON list of usernames). An observer who asked to join
     * is let in.
     */
    public function invite(Request $request, Club $club): RedirectResponse
    {
        $admin = $request->user();
        abort_unless($club->canBeManagedBy($admin), 403);

        $usernames = $this->usernames($request);
        if ($usernames === []) {
            return back()->with('error', __('Choose the observers to invite.'));
        }
        if (count($usernames) > MessagesController::MAX_RECEIVERS) {
            return back()->with('error', __('You can invite at most :max observers at once.', ['max' => MessagesController::MAX_RECEIVERS]));
        }

        $invited = 0;
        foreach (User::whereIn('username', $usernames)->get() as $receiver) {
            $membership = $club->membershipOf($receiver);
            if ($membership?->status === ClubMember::REQUESTED) {
                $club->approve($membership);
                ClubNotifier::answered($club, $receiver, $admin, true);
                $invited++;

                continue;
            }
            if ($membership !== null || (int) $receiver->id === (int) $admin->id) {
                continue;
            }
            if (ClubNotifier::invite($club, $receiver, $admin)) {
                ClubMember::create([
                    'club_id'            => $club->id,
                    'user_id'            => $receiver->id,
                    'status'             => ClubMember::INVITED,
                    'invited_by_user_id' => $admin->id,
                ]);
                $invited++;
            }
        }

        return back()->with('success', trans_choice('{0} Nobody new was invited.|{1} One invitation has been sent.|[2,*] :count invitations have been sent.', $invited, ['count' => $invited]));
    }

    /** A club administrator lets in an observer who asked to join. */
    public function approve(Request $request, Club $club, ClubMember $member): RedirectResponse
    {
        $this->authorizeMember($request, $club, $member);
        abort_unless($member->status === ClubMember::REQUESTED, 404);

        $club->approve($member);
        ClubNotifier::answered($club, $member->user, $request->user(), true);

        return back()->with('success', __(':name is now a member of the club.', ['name' => $member->user->name]));
    }

    /**
     * A club administrator removes a member, declines a request or
     * withdraws an invitation.
     */
    public function removeMember(Request $request, Club $club, ClubMember $member): RedirectResponse
    {
        $this->authorizeMember($request, $club, $member);

        $requested = $member->status === ClubMember::REQUESTED;
        if ($club->remove($member->user)) {
            return redirect()->route('clubs.index')->with('success', __('The club had no members left and has been deleted.'));
        }
        if ($requested) {
            ClubNotifier::answered($club, $member->user, $request->user(), false);
        }

        return back()->with('success', __('The observer is no longer in the club.'));
    }

    /** A club administrator makes a member administrator, or a plain member again. */
    public function role(Request $request, Club $club, ClubMember $member): RedirectResponse
    {
        $this->authorizeMember($request, $club, $member);
        $role = (string) $request->input('role');
        abort_unless(in_array($role, [ClubMember::ROLE_ADMIN, ClubMember::ROLE_MEMBER], true), 422);

        if (! $club->setRole($member, $role)) {
            return back()->with('error', __('A club needs at least one administrator.'));
        }

        return back()->with('success', $role === ClubMember::ROLE_ADMIN
            ? __(':name is now an administrator of the club.', ['name' => $member->user->name])
            : __(':name is no longer an administrator of the club.', ['name' => $member->user->name]));
    }

    /** A member shares one of their public observing lists with the club. */
    public function shareList(Request $request, Club $club): RedirectResponse
    {
        $user = $request->user();
        abort_unless($club->isMember($user), 403);

        $list = ObservingList::where('owner_user_id', $user->id)->where('public', true)->find((int) $request->input('list_id'));
        if ($list === null) {
            return back()->with('error', __('Choose one of your public observing lists.'));
        }
        $club->observingLists()->syncWithoutDetaching([$list->id => ['user_id' => $user->id]]);

        return back()->with('success', __('The observing list is shared with the club.'));
    }

    /** The owner of the list or a club administrator stops sharing it. */
    public function unshareList(Request $request, Club $club): RedirectResponse
    {
        $user = $request->user();
        $list = $club->observingLists()->where('observing_lists.id', (int) $request->input('list_id'))->first();
        abort_if($list === null, 404);
        abort_unless((int) $list->owner_user_id === (int) $user->id || (int) $list->pivot->user_id === (int) $user->id || $club->canBeManagedBy($user), 403);

        $club->observingLists()->detach($list->id);

        return back()->with('success', __('The observing list is no longer shared with the club.'));
    }

    private function authorizeMember(Request $request, Club $club, ClubMember $member): void
    {
        abort_unless((int) $member->club_id === (int) $club->id && $member->user !== null, 404);
        abort_unless($club->canBeManagedBy($request->user()), 403);
    }

    /** Why the observer cannot make another club, or null when they can. */
    private function refusal(User $user): ?string
    {
        if ($user->hasAdministratorPrivileges()) {
            return null;
        }

        return Club::where('user_id', $user->id)->count() >= Club::MAX_PER_CREATOR
            ? __('You can make at most :max clubs.', ['max' => Club::MAX_PER_CREATOR])
            : null;
    }

    private function save(Request $request, Club $club): void
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:120', Rule::unique('clubs', 'name')->ignore($club->id)],
            'description' => ['nullable', 'string', 'max:20000'],
            'website'     => ['nullable', 'url:http,https', 'max:255'],
            'place'       => ['nullable', 'string', 'max:120'],
            'country'     => ['nullable', 'string', 'size:2', Rule::in(array_keys(Countries::getList('en')))],
            'join_policy' => ['required', Rule::in([Club::OPEN, Club::APPROVAL])],
        ]);
        $validated['name'] = trim($validated['name']);

        $club->fill($validated)->save();
    }

    /** @return array<string, string> code => name in the language of the site */
    private function countries(): array
    {
        $countries = Countries::getList(app()->getLocale());
        asort($countries, SORT_NATURAL | SORT_FLAG_CASE);

        return $countries;
    }

    /**
     * The sources the ranking counts: the catalogs, the viewer's lists and
     * the lists shared with the club.
     */
    private function sourceOptions(Club $club, ?User $viewer): Collection
    {
        $options = ObjectSources::options($viewer);
        foreach ($club->sharedLists() as $list) {
            if (! $options->contains('key', 'list:'.$list->id)) {
                $options->push([
                    'key'         => 'list:'.$list->id,
                    'type'        => 'list',
                    'list_id'     => (int) $list->id,
                    'list_slug'   => (string) $list->slug,
                    'short_label' => html_entity_decode((string) $list->name, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                    'label'       => html_entity_decode((string) $list->name, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                ]);
            }
        }

        return $options;
    }

    /**
     * The club the observer's profile on the old DeepskyLog named, when they
     * are not in a club of that name yet.
     */
    private function legacyClub(User $user, Collection $clubs): ?string
    {
        $name = rescue(fn () => DB::table('user_preferences')->where('user_id', $user->id)->where('key', 'legacy_club')->value('value'), null, false);
        $name = trim(html_entity_decode((string) $name, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if ($name === '' || $clubs->contains(fn (Club $club) => mb_strtolower(html_entity_decode($club->name, ENT_QUOTES | ENT_HTML5, 'UTF-8')) === mb_strtolower($name))) {
            return null;
        }

        return $name;
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
