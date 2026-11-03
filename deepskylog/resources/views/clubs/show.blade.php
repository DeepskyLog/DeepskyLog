<x-app-layout>
    @php
        $buttonClass =
            "inline-flex items-center rounded-md px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase";
        $smallButton = "rounded px-2 py-1 text-xs font-medium text-white";
        $status = $membership?->status;
        $name = html_entity_decode($club->name, ENT_QUOTES | ENT_HTML5, "UTF-8");
        $sortLink = fn(string $column) => request()->fullUrlWithQuery([
            "sort" => $column,
        ]) . "#ranking";
        $periodLabels = [
            "year" => __("This year"),
            "12m" => __("The last 12 months"),
            "all" => __("All observations"),
        ];
    @endphp
    <div class="mx-auto max-w-screen-lg bg-gray-900 px-6 py-6 sm:px-6 lg:px-8">
        <p class="mb-2 text-sm"><a href="{{ route("clubs.index") }}" class="text-blue-400 hover:underline">&larr; {{ __("All clubs") }}</a></p>

        {{-- The club and what the viewer can do --}}
        <div class="rounded-lg bg-gray-800 p-4">
            <div class="flex flex-wrap items-baseline justify-between gap-x-3">
                <h1 class="text-xl font-semibold text-white">{{ $name }}</h1>
                <span class="text-sm text-gray-400">{{
                    trans_choice(
                        ":count member|:count members",
                        $members->count(),
                        ["count" => $members->count()],
                    )
                }}</span>
            </div>
            <p class="mt-1 text-sm text-gray-400">
                @if ($club->placeLabel() !== "")
                    {{ $club->placeLabel() }} &middot;
                @endif
                {{
                    $club->isOpen()
                        ? __("Every observer can join")
                        : __("Observers ask to join")
                }}
                @if ($club->website)
                    &middot;
                    <a
                        href="{{ $club->website }}"
                        class="text-blue-400 hover:underline"
                        rel="noopener nofollow"
                        target="_blank"
                        >{{ __("Website") }}</a
                    >
                @endif
            </p>

            @if ($club->description)
                <div
                    class="prose prose-invert mt-3 max-w-none text-sm text-gray-300"
                >
                    {!!
                        \App\Support\Html::purify(
                            $club->description,
                            true,
                        )
                    !!}
                </div>
            @endif

            <div class="mt-4 flex flex-wrap gap-3">
                @auth
                    @if ($isMember)
                        <form
                            method="POST"
                            action="{{ route("clubs.leave", $club) }}"
                        >
                            @csrf
                            @method ("DELETE")
                            <button
                                type="submit"
                                class="{{ $buttonClass }} bg-gray-600 hover:bg-gray-500"
                                onclick="return confirm({{ \Illuminate\Support\Js::from($members->count() === 1 ? __("You are the last member: leaving deletes the club. Leave it?") : __("Leave this club? You also leave its challenges that have not ended.")) }})"
                            >
                                {{
                                    __(
                                        "Leave the club",
                                    )
                                }}
                            </button>
                        </form>
                    @elseif ($status === \App\Models\ClubMember::INVITED)
                        <form
                            method="POST"
                            action="{{ route("clubs.join", $club) }}"
                        >
                            @csrf
                            <button
                                type="submit"
                                class="{{ $buttonClass }} bg-green-700 hover:bg-green-600"
                            >
                                {{
                                    __(
                                        "Accept the invitation",
                                    )
                                }}
                            </button>
                        </form>
                        <form
                            method="POST"
                            action="{{ route("clubs.leave", $club) }}"
                        >
                            @csrf
                            @method ("DELETE")
                            <button
                                type="submit"
                                class="{{ $buttonClass }} bg-gray-600 hover:bg-gray-500"
                            >
                                {{ __("Decline") }}
                            </button>
                        </form>
                    @elseif ($status === \App\Models\ClubMember::REQUESTED)
                        <span class="self-center text-sm text-yellow-400">{{
                            __(
                                "You asked to join. An administrator of the club will let you in.",
                            )
                        }}</span>
                        <form
                            method="POST"
                            action="{{ route("clubs.leave", $club) }}"
                        >
                            @csrf
                            @method ("DELETE")
                            <button
                                type="submit"
                                class="{{ $buttonClass }} bg-gray-600 hover:bg-gray-500"
                            >
                                {{
                                    __(
                                        "Withdraw the request",
                                    )
                                }}
                            </button>
                        </form>
                    @else
                        <form
                            method="POST"
                            action="{{ route("clubs.join", $club) }}"
                        >
                            @csrf
                            <button
                                type="submit"
                                class="{{ $buttonClass }} bg-green-700 hover:bg-green-600"
                            >
                                {{
                                    $club->isOpen()
                                        ? __("Join the club")
                                        : __("Ask to join")
                                }}
                            </button>
                        </form>
                    @endif
                    @if ($canManage)
                        <a
                            href="{{ route("clubs.edit", $club) }}"
                            class="{{ $buttonClass }} bg-gray-700 hover:bg-gray-600"
                            >{{ __("Edit") }}</a
                        >
                    @endif
                @else
                    <a
                        href="{{ route("login") }}"
                        class="{{ $buttonClass }} bg-green-700 hover:bg-green-600"
                        >{{
                            __(
                                "Log in to join the club",
                            )
                        }}</a
                    >
                @endauth
            </div>
        </div>

        {{-- The club administrators: requests and invitations --}}
        @if ($canManage)
            <div class="mt-4 rounded-lg bg-gray-800 p-4 text-sm">
                @if ($requested->isNotEmpty())
                    <h2 class="mb-2 text-lg font-semibold text-white">
                        {{
                            __(
                                "Asked to join",
                            )
                        }}
                    </h2>
                    <ul class="mb-4 space-y-2">
                        @foreach ($requested as $row)
                            <li
                                class="flex flex-wrap items-center justify-between gap-2"
                            >
                                <a
                                    href="{{ route("observer.show", $row->user->slug) }}"
                                    class="text-blue-400 hover:underline"
                                    >{{
                                        html_entity_decode(
                                            $row->user->name,
                                            ENT_QUOTES | ENT_HTML5,
                                            "UTF-8",
                                        )
                                    }}</a
                                >
                                <span class="flex gap-2">
                                    <form
                                        method="POST"
                                        action="{{ route("clubs.members.approve", [$club, $row]) }}"
                                    >
                                        @csrf
                                        <button
                                            type="submit"
                                            class="{{ $smallButton }} bg-green-700 hover:bg-green-600"
                                        >
                                            {{ __("Let in") }}
                                        </button>
                                    </form>
                                    <form
                                        method="POST"
                                        action="{{ route("clubs.members.destroy", [$club, $row]) }}"
                                    >
                                        @csrf
                                        @method ("DELETE")
                                        <button
                                            type="submit"
                                            class="{{ $smallButton }} bg-gray-600 hover:bg-gray-500"
                                        >
                                            {{ __("Decline") }}
                                        </button>
                                    </form>
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif

                <h2 class="mb-2 text-lg font-semibold text-white">
                    {{
                        __(
                            "Invite observers",
                        )
                    }}
                </h2>
                <form
                    method="POST"
                    action="{{ route("clubs.invite", $club) }}"
                    class="space-y-3"
                >
                    @csrf
                    <div x-data="{ receivers: [] }">
                        <x-select
                            label="{{ __('Observers of DeepskyLog') }}"
                            name="receivers"
                            async-data="{{ route('users.select.api') }}"
                            x-model="receivers"
                            option-label="name"
                            option-value="id"
                            multiselect
                            class="w-full"
                        />
                    </div>
                    <button
                        type="submit"
                        class="rounded bg-green-700 px-4 py-2 font-medium text-white hover:bg-green-600"
                    >
                        {{ __("Invite") }}
                    </button>
                </form>

                @if ($invited->isNotEmpty())
                    <h3 class="mt-4 font-medium text-gray-400">
                        {{ __("Invited") }}
                    </h3>
                    <ul class="mt-1 space-y-1">
                        @foreach ($invited as $row)
                            <li
                                class="flex items-center justify-between gap-2 text-gray-300"
                            >
                                <a
                                    href="{{ route("observer.show", $row->user->slug) }}"
                                    class="hover:underline"
                                    >{{
                                        html_entity_decode(
                                            $row->user->name,
                                            ENT_QUOTES | ENT_HTML5,
                                            "UTF-8",
                                        )
                                    }}</a
                                >
                                <form
                                    method="POST"
                                    action="{{ route("clubs.members.destroy", [$club, $row]) }}"
                                >
                                    @csrf
                                    @method ("DELETE")
                                    <button
                                        type="submit"
                                        class="text-xs text-red-400 hover:underline"
                                    >
                                        {{ __("Withdraw") }}
                                    </button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif

        {{-- The members --}}
        <div class="mt-4 rounded-lg bg-gray-800 p-4">
            <h2 class="mb-2 text-lg font-semibold text-white">
                {{ __("Members") }}
            </h2>
            <ul class="grid grid-cols-1 gap-2 text-sm sm:grid-cols-2">
                @foreach ($members as $row)
                    <li
                        class="flex flex-wrap items-center justify-between gap-2"
                    >
                        <span class="flex items-center gap-2">
                            <img
                                src="{{ $row->user->profile_photo_url }}"
                                alt=""
                                class="h-6 w-6 rounded-full object-cover"
                            />
                            <a
                                href="{{ route("observer.show", $row->user->slug) }}"
                                class="text-blue-400 hover:underline"
                                >{{
                                    html_entity_decode(
                                        $row->user->name,
                                        ENT_QUOTES | ENT_HTML5,
                                        "UTF-8",
                                    )
                                }}</a
                            >
                            @if ($row->role === \App\Models\ClubMember::ROLE_ADMIN)
                                <span
                                    class="rounded bg-blue-800 px-2 py-0.5 text-xs text-white"
                                    >{{
                                        __(
                                            "Administrator",
                                        )
                                    }}</span
                                >
                            @endif
                        </span>
                        @if ($canManage)
                            <span class="flex gap-2">
                                @if ($row->role === \App\Models\ClubMember::ROLE_ADMIN)
                                    @if ($adminCount > 1)
                                        <form
                                            method="POST"
                                            action="{{ route("clubs.members.role", [$club, $row]) }}"
                                        >
                                            @csrf
                                            @method ("PUT")
                                            <input
                                                type="hidden"
                                                name="role"
                                                value="member"
                                            />
                                            <button
                                                type="submit"
                                                class="text-xs text-gray-400 hover:underline"
                                            >
                                                {{
                                                    __(
                                                        "No longer administrator",
                                                    )
                                                }}
                                            </button>
                                        </form>
                                    @endif
                                @else
                                    <form
                                        method="POST"
                                        action="{{ route("clubs.members.role", [$club, $row]) }}"
                                    >
                                        @csrf
                                        @method ("PUT")
                                        <input
                                            type="hidden"
                                            name="role"
                                            value="admin"
                                        />
                                        <button
                                            type="submit"
                                            class="text-xs text-gray-400 hover:underline"
                                        >
                                            {{
                                                __(
                                                    "Make administrator",
                                                )
                                            }}
                                        </button>
                                    </form>
                                @endif
                                @if ((int) $row->user_id !== (int) auth()->id())
                                    <form
                                        method="POST"
                                        action="{{ route("clubs.members.destroy", [$club, $row]) }}"
                                    >
                                        @csrf
                                        @method ("DELETE")
                                        <button
                                            type="submit"
                                            class="text-xs text-red-400 hover:underline"
                                            onclick="return confirm({{ \Illuminate\Support\Js::from(__("Remove this member from the club?")) }})"
                                        >
                                            {{ __("Remove") }}
                                        </button>
                                    </form>
                                @endif
                            </span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- The ranking of the members --}}
        <div id="ranking" class="mt-4 rounded-lg bg-gray-800 p-4">
            <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-lg font-semibold text-white">
                    {{
                        __(
                            "Club ranking",
                        )
                    }}
                </h2>
                <form
                    method="GET"
                    action="{{ route("clubs.show", $club) }}#ranking"
                    class="flex flex-wrap items-center gap-2 text-sm"
                >
                    <input type="hidden" name="sort" value="{{ $sort }}" />
                    <select
                        name="period"
                        onchange="this.form.submit()"
                        class="rounded border border-gray-600 bg-gray-900 px-2 py-1 text-gray-100"
                    >
                        @foreach ($periodLabels as $key => $label)
                            <option
                                value="{{ $key }}"
                                @selected ($period === $key)
                            >
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <select
                        name="source"
                        onchange="this.form.submit()"
                        class="max-w-xs rounded border border-gray-600 bg-gray-900 px-2 py-1 text-gray-100"
                    >
                        <optgroup label="{{ __("Catalog") }}">
                            @foreach ($sourceOptions->where("type", "catalog") as $option)
                                <option
                                    value="{{ $option["key"] }}"
                                    @selected ($source["key"] === $option["key"])
                                >
                                    {{
                                        $option[
                                            "label"
                                        ]
                                    }}
                                </option>
                            @endforeach
                        </optgroup>
                        @if ($sourceOptions->where("type", "list")->isNotEmpty())
                            <optgroup label="{{ __("Observing list") }}">
                                @foreach ($sourceOptions->where("type", "list") as $option)
                                    <option
                                        value="{{ $option["key"] }}"
                                        @selected ($source["key"] === $option["key"])
                                    >
                                        {{
                                            $option[
                                                "label"
                                            ]
                                        }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                    </select>
                    <noscript
                        ><button
                            type="submit"
                            class="rounded bg-gray-700 px-3 py-1 text-white"
                        >
                            {{ __("Show") }}
                        </button></noscript
                    >
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-300">
                    <thead class="text-xs text-gray-400 uppercase">
                        <tr>
                            <th class="py-1 pr-3">#</th>
                            <th class="py-1 pr-3">{{ __("Observer") }}</th>
                            @foreach ([
                                    "objects" => __("Different objects"),
                                    "observations" => __("Observations"),
                                    "drawings" => __("Drawings"),
                                    "source" => __("Observed :source", [
                                        "source" => $source["short_label"] ?? $source["label"]
                                    ])
                                ]
                                as $column => $heading)
                                <th class="py-1 pr-3 text-right">
                                    <a
                                        href="{{ $sortLink($column) }}"
                                        @class ([ "hover:underline", "text-white" => $sort === $column ])
                                        >{{ $heading }}</a
                                    >
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @foreach ($ranking["rows"] as $row)
                            <tr
                                @class ([ "bg-gray-700/50" => auth()->id() === $row["user_id"] ])
                            >
                                <td class="py-1 pr-3">{{ $row["rank"] }}</td>
                                <td class="py-1 pr-3">
                                    <a
                                        href="{{ route("observer.show", $row["slug"]) }}"
                                        class="text-blue-400 hover:underline"
                                        >{{ $row["name"] }}</a
                                    >
                                </td>
                                <td class="py-1 pr-3 text-right">
                                    {{
                                        $row[
                                            "objects"
                                        ]
                                    }}
                                </td>
                                <td class="py-1 pr-3 text-right">
                                    {{
                                        $row[
                                            "observations"
                                        ]
                                    }}
                                </td>
                                <td class="py-1 pr-3 text-right">
                                    {{
                                        $row[
                                            "drawings"
                                        ]
                                    }}
                                </td>
                                <td class="py-1 pr-3 text-right">
                                    {{ $row["source"] }}
                                    @if ($ranking["source_total"] > 0)
                                        /{{
                                            $ranking[
                                                "source_total"
                                            ]
                                        }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="mt-2 text-xs text-gray-500">
                {{
                    __("Updated every :minutes minutes.", [
                        "minutes" => \App\Services\ClubRanking::CACHE_MINUTES,
                    ])
                }}
            </p>
        </div>

        {{-- The challenges of the club --}}
        <div class="mt-4 rounded-lg bg-gray-800 p-4">
            <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-lg font-semibold text-white">
                    {{
                        __(
                            "Challenges of the club",
                        )
                    }}
                </h2>
                @if ($canManage)
                    <a
                        href="{{ route("clubs.challenges.create", $club) }}"
                        class="rounded bg-green-700 px-3 py-1 text-sm font-medium text-white hover:bg-green-600"
                        >{{
                            __(
                                "New challenge",
                            )
                        }}</a
                    >
                @endif
            </div>
            @if ($running->isEmpty() && $upcoming->isEmpty() && $ended->isEmpty())
                <p class="text-sm text-gray-400">{{
                    __(
                        "The club has no challenges yet.",
                    )
                }}</p>
            @endif
            @foreach ([
                    [__("Running challenges"), $running],
                    [__("Coming challenges"), $upcoming],
                    [__("Past challenges"), $ended]
                ]
                as [$heading, $challenges])
                @if ($challenges->isNotEmpty())
                    <h3 class="mt-3 mb-2 font-medium text-gray-300">
                        {{ $heading }}
                    </h3>
                    <div class="space-y-3">
                        @foreach ($challenges as $challenge)
                            @include ("challenges.partials.card", ["challenge" => $challenge])
                        @endforeach
                    </div>
                @endif
            @endforeach
        </div>

        {{-- The starparties of the club --}}
        <div class="mt-4 rounded-lg bg-gray-800 p-4">
            <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-lg font-semibold text-white">
                    {{
                        __(
                            "Starparties of the club",
                        )
                    }}
                </h2>
                @if ($isMember)
                    <a
                        href="{{ route("star-parties.create", ["club" => $club->slug]) }}"
                        class="rounded bg-green-700 px-3 py-1 text-sm font-medium text-white hover:bg-green-600"
                        >{{
                            __(
                                "Organise a starparty",
                            )
                        }}</a
                    >
                @endif
            </div>
            @if ($starParties->isEmpty() && $pastStarParties->isEmpty())
                <p class="text-sm text-gray-400">{{
                    __(
                        "The club has no starparties yet.",
                    )
                }}</p>
            @endif
            @foreach ([
                    [__("Coming starparties"), $starParties],
                    [__("Past starparties"), $pastStarParties]
                ]
                as [$heading, $parties])
                @if ($parties->isNotEmpty())
                    <h3 class="mt-3 font-medium text-gray-300">
                        {{ $heading }}
                    </h3>
                    <ul class="divide-y divide-gray-700">
                        @foreach ($parties as $party)
                            @include ("star-parties.partials.row", ["party" => $party])
                        @endforeach
                    </ul>
                @endif
            @endforeach
        </div>

        {{-- The observing lists the members share --}}
        <div class="mt-4 rounded-lg bg-gray-800 p-4 text-sm">
            <h2 class="mb-2 text-lg font-semibold text-white">
                {{
                    __(
                        "Shared observing lists",
                    )
                }}
            </h2>
            @if ($sharedLists->isEmpty())
                <p class="text-gray-400">{{
                    __(
                        "No observing lists have been shared with the club yet.",
                    )
                }}</p>
            @else
                <ul class="divide-y divide-gray-700">
                    @foreach ($sharedLists as $list)
                        <li
                            class="flex flex-wrap items-center justify-between gap-2 py-2"
                        >
                            <span>
                                <a
                                    href="{{ route("observing-list.show", $list->slug) }}"
                                    class="font-medium text-blue-400 hover:underline"
                                    >{{
                                        html_entity_decode(
                                            $list->name,
                                            ENT_QUOTES | ENT_HTML5,
                                            "UTF-8",
                                        )
                                    }}</a
                                >
                                <span class="text-gray-400">
                                    &middot; {{
                                        trans_choice(
                                            ":count object|:count objects",
                                            $list->items_count,
                                            ["count" => $list->items_count],
                                        )
                                    }}
                                    @if ($list->owner)
                                        &middot; {{
                                            html_entity_decode(
                                                $list->owner->name,
                                                ENT_QUOTES | ENT_HTML5,
                                                "UTF-8",
                                            )
                                        }}
                                    @endif
                                    @if ($list->pivot->members_edit)
                                        &middot;
                                        <span class="text-green-400">{{
                                            __(
                                                "Edited together",
                                            )
                                        }}</span>
                                    @endif
                                </span>
                                @if ($isMember || $canManage)
                                    &middot;
                                    <a
                                        href="{{ route("clubs.lists.progress", [$club, $list->slug]) }}"
                                        class="text-blue-400 hover:underline"
                                        >{{
                                            __(
                                                "Who has seen what",
                                            )
                                        }}</a
                                    >
                                @endif
                            </span>
                            @auth
                                @if ((int) $list->owner_user_id === (int) auth()->id())
                                    {{-- The owner lets the members edit the list, or stops that --}}
                                    <form
                                        method="POST"
                                        action="{{ route("clubs.lists.editing", [$club, $list->slug]) }}"
                                    >
                                        @csrf
                                        @method ("PUT")
                                        <input
                                            type="hidden"
                                            name="members_edit"
                                            value="{{ $list->pivot->members_edit ? 0 : 1 }}"
                                        />
                                        <button
                                            type="submit"
                                            class="text-xs text-blue-400 hover:underline"
                                        >
                                            {{
                                                $list->pivot->members_edit
                                                    ? __("Only I edit it")
                                                    : __("Let the members edit it")
                                            }}
                                        </button>
                                    </form>
                                @endif
                                @if ((int) $list->owner_user_id === (int) auth()->id() || $canManage)
                                    <form
                                        method="POST"
                                        action="{{ route("clubs.lists.destroy", $club) }}"
                                    >
                                        @csrf
                                        @method ("DELETE")
                                        <input
                                            type="hidden"
                                            name="list_id"
                                            value="{{ $list->id }}"
                                        />
                                        <button
                                            type="submit"
                                            class="text-xs text-red-400 hover:underline"
                                        >
                                            {{
                                                __(
                                                    "Stop sharing",
                                                )
                                            }}
                                        </button>
                                    </form>
                                @endif
                            @endauth
                        </li>
                    @endforeach
                </ul>
            @endif
            @if ($isMember)
                @if ($ownLists->isNotEmpty())
                    <form
                        method="POST"
                        action="{{ route("clubs.lists.store", $club) }}"
                        class="mt-3 flex flex-wrap items-center gap-2"
                    >
                        @csrf
                        <select
                            name="list_id"
                            class="rounded border border-gray-600 bg-gray-900 px-2 py-1 text-gray-100"
                        >
                            @foreach ($ownLists as $list)
                                <option value="{{ $list->id }}">
                                    {{
                                        html_entity_decode(
                                            $list->name,
                                            ENT_QUOTES | ENT_HTML5,
                                            "UTF-8",
                                        )
                                    }}
                                </option>
                            @endforeach
                        </select>
                        <label
                            class="inline-flex items-center gap-1 text-gray-300"
                        >
                            <input
                                type="checkbox"
                                name="members_edit"
                                value="1"
                                class="rounded border-gray-600 bg-gray-900"
                            />
                            {{
                                __(
                                    "The members may add and remove objects",
                                )
                            }}
                        </label>
                        <button
                            type="submit"
                            class="rounded bg-blue-600 px-3 py-1 font-medium text-white hover:bg-blue-500"
                        >
                            {{
                                __(
                                    "Share with the club",
                                )
                            }}
                        </button>
                    </form>
                @else
                    <p class="mt-3 text-xs text-gray-400">{{
                        __(
                            "Only public observing lists can be shared with a club.",
                        )
                    }}</p>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
