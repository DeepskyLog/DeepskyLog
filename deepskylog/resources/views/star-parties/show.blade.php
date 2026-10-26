<x-app-layout>
    @php
        $user = Auth::user();
        $byStatus = $party->participants->groupBy("status");
        $statusLabels = [
            \App\Models\StarPartyParticipant::GOING => __("Coming"),
            \App\Models\StarPartyParticipant::MAYBE => __("Maybe"),
            \App\Models\StarPartyParticipant::INVITED => __("Invited"),
            \App\Models\StarPartyParticipant::DECLINED => __("Not coming"),
        ];
        $coming = $party->participants
            ->whereIn("status", [\App\Models\StarPartyParticipant::GOING, \App\Models\StarPartyParticipant::MAYBE])
            ->pluck("user")
            ->filter()
            ->reject(fn ($u) => $user && (int) $u->id === (int) $user->id);
    @endphp

    <div class="mx-auto max-w-screen-xl bg-gray-900 px-4 py-6 sm:px-6 lg:px-8">
        <p class="mb-2 text-sm">
            <a href="{{ route("star-parties.index") }}" class="text-blue-400 hover:underline">&larr; {{ __("All starparties") }}</a>
        </p>

        {{-- Name, dates, site and the actions of the organiser --}}
        <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
            <div class="min-w-0">
                <h1 class="text-2xl font-semibold text-white">
                    {{ html_entity_decode($party->name, ENT_QUOTES | ENT_HTML5, "UTF-8") }}
                    @if ($party->isCancelled())
                        <span class="ml-2 rounded bg-red-700 px-2 py-0.5 align-middle text-sm font-medium text-white">{{ __("Cancelled") }}</span>
                    @elseif ($party->isOngoing())
                        <span class="ml-2 rounded bg-green-700 px-2 py-0.5 align-middle text-sm font-medium text-white">{{ __("Now") }}</span>
                    @endif
                    @if (! $party->isPublic())
                        <span class="ml-2 rounded bg-gray-700 px-2 py-0.5 align-middle text-sm font-medium text-gray-200">{{ __("Private") }}</span>
                    @endif
                </h1>
                <p class="mt-1 text-gray-300">
                    {{ trans_choice("{1} :dates (one night)|[2,*] :dates (:count nights)", count($party->nights()), ["dates" => $party->dateRange(), "count" => count($party->nights())]) }}
                    &middot; {{ $party->location_name }}@if ($party->country) ({{ $party->country }})@endif
                </p>
                @if ($party->organiser)
                    <p class="text-sm text-gray-400">
                        {{ __("Organised by") }}
                        <a href="{{ route("observer.show", $party->organiser->slug) }}" class="text-blue-400 hover:underline">{{ html_entity_decode($party->organiser->name, ENT_QUOTES | ENT_HTML5, "UTF-8") }}</a>
                    </p>
                @endif
            </div>
            <div class="flex flex-wrap items-center gap-2 text-sm">
                <a href="{{ route("star-parties.calendar", $party) }}" class="rounded bg-gray-700 px-3 py-2 text-white hover:bg-gray-600">{{ __("Add to my calendar") }}</a>
                @if ($canManage)
                    <a href="{{ route("star-parties.edit", $party) }}" class="rounded bg-blue-700 px-3 py-2 text-white hover:bg-blue-600">{{ __("Edit") }}</a>
                    @if (! $party->isPast())
                        <form method="POST" action="{{ route("star-parties.cancel", $party) }}" data-confirm="{{ $party->isCancelled() ? __("Let the starparty take place after all? The participants get a message.") : __("Cancel the starparty? The participants get a message.") }}">
                            @csrf
                            <button type="submit" class="rounded bg-yellow-700 px-3 py-2 text-white hover:bg-yellow-600">
                                {{ $party->isCancelled() ? __("Take place after all") : __("Cancel the starparty") }}
                            </button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route("star-parties.destroy", $party) }}" data-confirm="{{ __("Delete the starparty? This cannot be undone.") }}">
                        @csrf
                        @method ("DELETE")
                        <button type="submit" class="rounded bg-red-700 px-3 py-2 text-white hover:bg-red-600">{{ __("Delete") }}</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="min-w-0 space-y-6 lg:col-span-2">
                {{-- The description and the site --}}
                <section class="rounded-lg bg-gray-800 p-4">
                    @if ($party->description)
                        <div class="prose prose-invert max-w-none text-gray-200">
                            {!! \App\Support\Html::purify($party->displayDescription(), true) !!}
                        </div>
                        @if ($party->isTranslated())
                            <p class="mt-2 text-xs text-gray-500">{{ __("Translated by Google") }}</p>
                        @endif
                    @endif

                    <dl class="mt-4 grid gap-x-4 gap-y-1 text-sm text-gray-300 sm:grid-cols-[auto_1fr]">
                        <dt class="font-medium text-gray-400">{{ __("Location") }}</dt>
                        <dd>
                            {{ $party->location_name }}@if ($party->country) ({{ $party->country }})@endif
                            &middot; {{ \App\Models\Location::dms($party->latitude, true) }}, {{ \App\Models\Location::dms($party->longitude, false) }}
                        </dd>
                        @if ($party->venue)
                            <dt class="font-medium text-gray-400">{{ __("Address or directions") }}</dt>
                            <dd>{{ $party->venue }}</dd>
                        @endif
                        @if ($party->website)
                            <dt class="font-medium text-gray-400">{{ __("Website") }}</dt>
                            <dd><a href="{{ $party->website }}" target="_blank" rel="noopener noreferrer nofollow" class="break-all text-blue-400 hover:underline">{{ $party->website }}</a></dd>
                        @endif
                        <dt class="font-medium text-gray-400">{{ __("Time zone") }}</dt>
                        <dd>{{ $party->timezone }}</dd>
                    </dl>

                    <div
                        id="star-party-map"
                        class="mt-4 h-64 w-full rounded"
                        style="height: 16rem"
                        data-lat="{{ $party->latitude }}"
                        data-lng="{{ $party->longitude }}"
                        data-name="{{ $party->location_name }}"
                    ></div>
                    <p class="mt-2 text-sm">
                        <a href="https://www.openstreetmap.org/directions?to={{ $party->latitude }}%2C{{ $party->longitude }}" target="_blank" rel="noopener noreferrer" class="text-blue-400 hover:underline">{{ __("Route to the site") }}</a>
                    </p>
                </section>

                {{-- The nights at the site --}}
                @if ($nights !== [])
                    <section class="rounded-lg bg-gray-800 p-4">
                        <h2 class="mb-2 text-lg font-semibold text-white">{{ __("The nights") }}</h2>
                        <p class="mb-2 text-xs text-gray-400">{{ __("Local time at the site.") }}</p>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm text-gray-300">
                                <thead class="text-xs uppercase text-gray-400">
                                    <tr>
                                        <th class="px-2 py-1">{{ __("Night of") }}</th>
                                        <th class="px-2 py-1">{{ __("Dark") }}</th>
                                        <th class="px-2 py-1">{{ __("Moon") }}</th>
                                        <th class="px-2 py-1 text-right">{{ __("Dark without Moon") }}</th>
                                        @if ($forecast !== [])
                                            <th class="px-2 py-1">{{ __("Weather") }}</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-700">
                                    @foreach ($nights as $row)
                                        <tr>
                                            <td class="px-2 py-1 whitespace-nowrap">{{ \Carbon\Carbon::parse($row["night"])->locale(app()->getLocale())->isoFormat("ddd LL") }}</td>
                                            <td class="px-2 py-1 whitespace-nowrap">
                                                @if ($row["dark"])
                                                    {{ $row["dark"]["from"] }}–{{ $row["dark"]["until"] }}
                                                    @if ($row["dark"]["twilight"] !== "astronomical")
                                                        <span class="text-xs text-gray-500">({{ __("nautical twilight") }})</span>
                                                    @endif
                                                @else
                                                    {{ __("It does not get dark.") }}
                                                @endif
                                            </td>
                                            <td class="px-2 py-1 whitespace-nowrap">
                                                @if ($row["moon"])
                                                    {{ $row["moon"]["fraction"] }}% &middot; {{ $row["moon"]["from"] }}–{{ $row["moon"]["until"] }}
                                                @elseif ($row["dark"])
                                                    {{ __("No Moon") }}
                                                @endif
                                            </td>
                                            <td class="px-2 py-1 text-right whitespace-nowrap">
                                                @if ($row["dark"])
                                                    {{ intdiv($row["moonless_minutes"], 60) }}h {{ str_pad((string) ($row["moonless_minutes"] % 60), 2, "0", STR_PAD_LEFT) }}m
                                                @endif
                                            </td>
                                            @if ($forecast !== [])
                                                <td class="px-2 py-1">
                                                    @isset ($forecast[$row["night"]])
                                                        <x-forecast-verdict :night="$forecast[$row['night']]" />
                                                        @if ($forecast[$row["night"]]["cloud"] !== null)
                                                            <span class="text-xs whitespace-nowrap text-gray-400">{{ __("Clouds :percent%", ["percent" => $forecast[$row["night"]]["cloud"]]) }}</span>
                                                        @endif
                                                    @endisset
                                                </td>
                                            @endif
                                        </tr>
                                        @if (! empty($forecast[$row["night"]]["hours"]))
                                            <tr class="border-t-0">
                                                <td colspan="5" class="px-2 pb-2">
                                                    <x-forecast-hours :hours="$forecast[$row['night']]['hours']" />
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if ($forecast !== [])
                            <x-forecast-credit class="mt-2" />
                        @endif
                    </section>
                @endif

                {{-- The objects worth observing at the site --}}
                @if ($best !== null && $night !== null)
                    <section class="rounded-lg bg-gray-800 p-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <h2 class="text-lg font-semibold text-white">{{ __("What to observe") }}</h2>
                            @if (count($party->nights()) > 1)
                                <form method="GET" action="{{ route("star-parties.show", $party) }}" class="text-sm">
                                    <label for="night" class="sr-only">{{ __("Night of") }}</label>
                                    <select id="night" name="night" onchange="this.form.submit()" class="rounded border border-gray-600 bg-gray-900 px-2 py-1 text-gray-100">
                                        @foreach ($party->nights() as $option)
                                            <option value="{{ $option }}" @selected ($option === $night)>{{ \Carbon\Carbon::parse($option)->locale(app()->getLocale())->isoFormat("ddd LL") }}</option>
                                        @endforeach
                                    </select>
                                    <noscript><button type="submit" class="ml-1 text-blue-400">{{ __("Show") }}</button></noscript>
                                </form>
                            @endif
                        </div>
                        <p class="mt-1 text-sm text-gray-400">
                            {{ __("The objects worth observing at the site in the dark hours of the night of :date, spread over the night. Each night has its own objects: those of the earlier nights are left out.", ["date" => \Carbon\Carbon::parse($night)->locale(app()->getLocale())->isoFormat("LL")]) }}
                        </p>
                        @if (! $best["dark"])
                            <p class="mt-4 text-gray-300">{{ __("It does not get dark.") }}</p>
                        @elseif (empty($best["objects"]))
                            <p class="mt-4 text-gray-300">{{ __("No objects found for tonight.") }}</p>
                        @else
                            <x-tonights-best-table :best="$best" />
                            @auth
                                @can ("create", \App\Models\ObservingList::class)
                                    <form method="POST" action="{{ route("star-parties.observing-list", $party) }}" class="mt-4">
                                        @csrf
                                        <input type="hidden" name="night" value="{{ $night }}" />
                                        <button type="submit" class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase hover:bg-blue-500">
                                            {{ __("Make an observing list") }}
                                        </button>
                                    </form>
                                @endcan
                            @endauth
                        @endif
                    </section>
                @endif

                {{-- What was observed at the site, once the starparty started --}}
                @if ($party->startsAt()->isPast())
                    <section class="rounded-lg bg-gray-800 p-4">
                        <h2 class="text-lg font-semibold text-white">{{ __("Observed at the starparty") }}</h2>
                        <p class="mt-1 text-sm text-gray-400">
                            {{
                                $party->activity_participants_only
                                    ? __("The sessions and observations of the observers who come, at a location within :km km of the site in the nights of the starparty.", ["km" => (float) $party->radiusKm()])
                                    : __("The sessions and observations of all observers at a location within :km km of the site in the nights of the starparty.", ["km" => (float) $party->radiusKm()])
                            }}
                        </p>

                        @if ($sessions->isEmpty() && $observations->isEmpty())
                            <p class="mt-4 text-gray-300">{{ __("Nothing has been recorded yet.") }}</p>
                        @endif

                        @if ($observers->isNotEmpty())
                            <p class="mt-3 text-sm text-gray-300">
                                @foreach ($observers as $row)
                                    @php($observer = $observerUsers[mb_strtolower($row->observer_username)] ?? null)
                                    <span class="mr-3 inline-block">
                                        @if ($observer)
                                            <a href="{{ route("observer.show", $observer->slug) }}" class="text-blue-400 hover:underline">{{ html_entity_decode($observer->name, ENT_QUOTES | ENT_HTML5, "UTF-8") }}</a>
                                        @else
                                            {{ $row->observer_username }}
                                        @endif
                                        ({{ $row->total }})
                                    </span>
                                @endforeach
                            </p>
                        @endif

                        @if ($sessions->isNotEmpty())
                            <h3 class="mt-4 mb-2 font-semibold text-white">{{ __("Sessions") }}</h3>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                @foreach ($sessions as $session)
                                    <x-session-card :session="$session" />
                                @endforeach
                            </div>
                        @endif

                        @if ($observations->isNotEmpty())
                            <h3 class="mt-4 mb-2 font-semibold text-white">
                                {{ __("Observations") }}
                                @if ($observations->count() >= \App\Services\StarParties\StarPartyActivity::MAX_OBSERVATIONS)
                                    <span class="text-sm font-normal text-gray-400">({{ __("the newest :count", ["count" => $observations->count()]) }})</span>
                                @endif
                            </h3>
                            <div class="divide-y divide-gray-700">
                                @foreach ($observations as $observation)
                                    <x-observation-row :observation="$observation" />
                                @endforeach
                            </div>
                        @endif
                    </section>
                @endif
            </div>

            <div class="min-w-0 space-y-6">
                {{-- Whether the observer comes --}}
                <section class="rounded-lg bg-gray-800 p-4 text-sm">
                    <h2 class="mb-2 text-lg font-semibold text-white">{{ __("Are you coming?") }}</h2>
                    <p class="text-gray-300">
                        {{ trans_choice("{0} Nobody has said they come yet.|{1} One observer comes.|[2,*] :count observers come.", $party->goingCount(), ["count" => $party->goingCount()]) }}
                        @if ($party->max_participants)
                            {{ __("There is room for :max observers.", ["max" => $party->max_participants]) }}
                        @endif
                    </p>
                    @if ($party->registration_deadline && ! $party->isPast())
                        <p class="text-gray-400">{{ __("Answer before :date.", ["date" => $party->registration_deadline->locale(app()->getLocale())->isoFormat("LL")]) }}</p>
                    @endif

                    @if ($status)
                        <p class="mt-2 text-gray-200">
                            {{ __("Your answer") }}: <strong>{{ $statusLabels[$status] ?? $status }}</strong>
                        </p>
                    @endif

                    @auth
                        @php($answers = collect(\App\Models\StarPartyParticipant::ANSWERS)->filter(fn ($answer) => $answer !== $status && $party->canAnswer($user, $answer)))
                        @if ($answers->isNotEmpty() && (int) $party->user_id !== (int) $user->id)
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach ($answers as $answer)
                                    <form method="POST" action="{{ route("star-parties.answer", $party) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="{{ $answer }}" />
                                        <button type="submit" @class ([
                                            "rounded px-3 py-2 font-medium text-white",
                                            "bg-green-700 hover:bg-green-600" => $answer === \App\Models\StarPartyParticipant::GOING,
                                            "bg-blue-700 hover:bg-blue-600" => $answer === \App\Models\StarPartyParticipant::MAYBE,
                                            "bg-gray-700 hover:bg-gray-600" => $answer === \App\Models\StarPartyParticipant::DECLINED,
                                        ])>
                                            {{ match ($answer) { \App\Models\StarPartyParticipant::GOING => __("I come"), \App\Models\StarPartyParticipant::MAYBE => __("Maybe"), default => __("I do not come") } }}
                                        </button>
                                    </form>
                                @endforeach
                            </div>
                        @elseif ($party->isFull() && $status !== \App\Models\StarPartyParticipant::GOING && ! $party->isPast())
                            <p class="mt-2 text-yellow-300">{{ __("The starparty is full.") }}</p>
                        @endif
                    @else
                        @if (! $party->isPast() && ! $party->isCancelled())
                            <p class="mt-2 text-gray-300">
                                <a href="{{ route("login") }}" class="text-blue-400 hover:underline">{{ __("Log in") }}</a>
                                {{ __("to tell whether you come.") }}
                            </p>
                        @endif
                    @endauth
                </section>

                {{-- The participants --}}
                <section class="rounded-lg bg-gray-800 p-4 text-sm">
                    <h2 class="mb-2 text-lg font-semibold text-white">{{ __("Observers") }}</h2>
                    @foreach ([\App\Models\StarPartyParticipant::GOING, \App\Models\StarPartyParticipant::MAYBE, \App\Models\StarPartyParticipant::INVITED, \App\Models\StarPartyParticipant::DECLINED] as $group)
                        @continue ($group === \App\Models\StarPartyParticipant::DECLINED && ! $canManage)
                        @php($members = ($byStatus[$group] ?? collect())->filter(fn ($p) => $p->user)->sortBy(fn ($p) => mb_strtolower($p->user->name)))
                        @continue ($members->isEmpty())
                        <h3 class="mt-3 font-medium text-gray-400">{{ $statusLabels[$group] }} ({{ $members->count() }})</h3>
                        <ul class="mt-1 space-y-1">
                            @foreach ($members as $participant)
                                <li class="flex items-center justify-between gap-2">
                                    <a href="{{ route("observer.show", $participant->user->slug) }}" class="flex min-w-0 items-center gap-2 text-gray-200 hover:underline">
                                        <img src="{{ $participant->user->profile_photo_url }}" alt="" class="h-6 w-6 rounded-full object-cover" loading="lazy" />
                                        <span class="truncate">{{ html_entity_decode($participant->user->name, ENT_QUOTES | ENT_HTML5, "UTF-8") }}</span>
                                        @if ((int) $participant->user_id === (int) $party->user_id)
                                            <span class="text-xs text-gray-500">({{ __("organiser") }})</span>
                                        @endif
                                    </a>
                                    @if ($canManage && (int) $participant->user_id !== (int) $party->user_id)
                                        <form method="POST" action="{{ route("star-parties.participants.destroy", [$party, $participant]) }}" data-confirm="{{ __("Take :name off the starparty?", ["name" => html_entity_decode($participant->user->name, ENT_QUOTES | ENT_HTML5, "UTF-8")]) }}">
                                            @csrf
                                            @method ("DELETE")
                                            <button type="submit" class="text-xs text-red-400 hover:underline" title="{{ __("Take off the starparty") }}">&times;</button>
                                        </form>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endforeach

                    @if ($canManage && $coming->isNotEmpty())
                        <p class="mt-3">
                            <a href="{{ route("messages.create", ["to" => $coming->take(\App\Http\Controllers\MessagesController::MAX_RECEIVERS)->pluck("username")->implode(",")]) }}" class="text-blue-400 hover:underline">{{ __("Send a message to the observers who come") }}</a>
                        </p>
                    @endif
                </section>

                {{-- Inviting observers and e-mail addresses --}}
                @if ($canInvite)
                    @php($usersAsyncRoute = route("users.select.api"))
                    <section class="rounded-lg bg-gray-800 p-4 text-sm">
                        <h2 class="mb-2 text-lg font-semibold text-white">{{ __("Invite observers") }}</h2>
                        <form method="POST" action="{{ route("star-parties.invite", $party) }}" class="space-y-3">
                            @csrf
                            <div x-data="{ receivers: [] }">
                                <x-select
                                    label="{{ __('Observers of DeepskyLog') }}"
                                    name="receivers"
                                    async-data="{{ $usersAsyncRoute }}"
                                    x-model="receivers"
                                    option-label="name"
                                    option-value="id"
                                    multiselect
                                    class="w-full"
                                />
                            </div>
                            @if ($canManage)
                                <div>
                                    <label for="emails" class="mb-1 block font-medium text-gray-400">{{ __("E-mail addresses (optional)") }}</label>
                                    <textarea id="emails" name="emails" rows="3" placeholder="name@example.org" class="block w-full rounded border border-gray-600 bg-gray-900 px-3 py-2 text-gray-100">{{ old("emails") }}</textarea>
                                    <p class="mt-1 text-xs text-gray-400">{{ __("Separate the addresses with commas or new lines. They get a mail with a link to the starparty.") }}</p>
                                    @error ("emails")
                                        <p class="mt-1 text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endif
                            <button type="submit" class="rounded bg-green-700 px-4 py-2 font-medium text-white hover:bg-green-600">{{ __("Invite") }}</button>
                        </form>

                        @if ($canManage && $invitations->isNotEmpty())
                            <h3 class="mt-4 font-medium text-gray-400">{{ __("Invited by e-mail") }}</h3>
                            <ul class="mt-1 space-y-1">
                                @foreach ($invitations as $invitation)
                                    <li class="flex items-center justify-between gap-2 text-gray-300">
                                        <span class="truncate">
                                            {{ $invitation->email }}
                                            @if ($invitation->accepted_at)
                                                <span class="text-xs text-green-400">({{ __("opened") }})</span>
                                            @endif
                                        </span>
                                        <form method="POST" action="{{ route("star-parties.invitations.destroy", [$party, $invitation]) }}">
                                            @csrf
                                            @method ("DELETE")
                                            <button type="submit" class="text-xs text-red-400 hover:underline" title="{{ __("Withdraw the invitation") }}">&times;</button>
                                        </form>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </section>
                @endif

                {{-- Sharing --}}
                @if ($party->isPublic())
                    <section class="rounded-lg bg-gray-800 p-4 text-sm">
                        <h2 class="mb-2 text-lg font-semibold text-white">{{ __("Share") }}</h2>
                        <div x-data="{ copied: false }" class="flex items-center gap-2">
                            <input type="text" readonly value="{{ route("star-parties.show", $party) }}" class="min-w-0 flex-1 rounded border border-gray-600 bg-gray-900 px-2 py-1 text-gray-200" onclick="this.select()" />
                            <button type="button" class="rounded bg-gray-700 px-3 py-1 text-white hover:bg-gray-600" x-on:click="navigator.clipboard.writeText('{{ route("star-parties.show", $party) }}'); copied = true; setTimeout(() => copied = false, 2000)">
                                <span x-show="! copied">{{ __("Copy") }}</span>
                                <span x-show="copied" x-cloak>{{ __("Copied") }}</span>
                            </button>
                        </div>
                        <p class="mt-2">
                            <a href="{{ route("feed.star-parties") }}" class="text-orange-400 hover:underline">{{ __("RSS feed of the starparties") }}</a>
                        </p>
                    </section>
                @endif
            </div>
        </div>
    </div>

    @push ("scripts")
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                document.querySelectorAll("form[data-confirm]").forEach(function (form) {
                    form.addEventListener("submit", function (e) {
                        if (!confirm(form.getAttribute("data-confirm") || "")) {
                            e.preventDefault();
                        }
                    });
                });

                var mapEl = document.getElementById("star-party-map");
                if (!mapEl || typeof L === "undefined") return;
                var coords = [parseFloat(mapEl.getAttribute("data-lat")), parseFloat(mapEl.getAttribute("data-lng"))];
                var map = L.map("star-party-map", { fullscreenControl: true }).setView(coords, 11);
                L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                    maxZoom: 19,
                    attribution: "© OpenStreetMap contributors",
                }).addTo(map);
                var name = document.createElement("strong");
                name.textContent = mapEl.getAttribute("data-name") || "";
                L.marker(coords).addTo(map).bindPopup(name).openPopup();
            });
        </script>
    @endpush
</x-app-layout>
