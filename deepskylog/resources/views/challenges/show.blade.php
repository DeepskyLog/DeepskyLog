<x-app-layout>
    @php
        $format = fn($date) => \Carbon\Carbon::parse($date)->locale(app()->getLocale())->isoFormat("LL");
        $status = $challenge->status();
        $sharedPercentage = $standings["total"] > 0 ? round((100 * $standings["seen"]) / $standings["total"]) : 0;
        $buttonClass = "inline-flex items-center rounded-md px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase";
    @endphp
    <div class="mx-auto max-w-screen-lg bg-gray-900 px-6 py-6 sm:px-6 lg:px-8">
        <p class="mb-2 text-sm"><a href="{{ route("challenges.index") }}" class="text-blue-400 hover:underline">&larr; {{ __("All challenges") }}</a></p>

        <div class="rounded-lg bg-gray-800 p-4">
            <div class="flex flex-wrap items-baseline justify-between gap-x-3">
                <h1 class="text-xl font-semibold text-white">{{ $challenge->name }}</h1>
                <span class="text-sm text-gray-400">{{ $format($challenge->starts_on) }} &ndash; {{ $format($challenge->ends_on) }}</span>
            </div>
            <p class="mt-1 text-sm text-gray-300">
                @if ($challenge->observingList)
                    {{ $challenge->task() }}
                    (<a href="{{ route("observing-list.show", $challenge->observingList->slug) }}" class="text-blue-400 hover:underline">{{ __("the observing list") }}</a>)
                @else
                    {{ $challenge->task() }}
                @endif
                &middot;
                @switch($status)
                    @case("upcoming")
                        {{ __("Starts on :date", ["date" => $format($challenge->starts_on)]) }}
                        @break
                    @case("ended")
                        <span class="text-gray-400">{{ __("The challenge has ended") }}</span>
                        @break
                    @default
                        @php $daysLeft = (int) \Carbon\Carbon::today()->diffInDays($challenge->ends_on) + 1; @endphp
                        {{ trans_choice(":count day left|:count days left", $daysLeft, ["count" => $daysLeft]) }}
                @endswitch
                @if ($challenge->hidden)
                    &middot; <span class="text-yellow-400">{{ __("Hidden") }}</span>
                @endif
            </p>

            @if ($challenge->description)
                <div class="prose prose-invert mt-3 max-w-none text-sm text-gray-300">
                    {!! \App\Support\Html::purify($challenge->description, true) !!}
                </div>
            @endif

            <div class="mt-4 flex flex-wrap gap-3">
                @auth
                    @if ($joined)
                        <form method="POST" action="{{ route("challenges.leave", $challenge) }}">
                            @csrf
                            @method ("DELETE")
                            <button type="submit" class="{{ $buttonClass }} bg-gray-600 hover:bg-gray-500" onclick="return confirm({{ \Illuminate\Support\Js::from(__("Leave this challenge?")) }})">
                                {{ __("Leave the challenge") }}
                            </button>
                        </form>
                    @elseif ($challenge->canBeJoined())
                        <form method="POST" action="{{ route("challenges.join", $challenge) }}">
                            @csrf
                            <button type="submit" class="{{ $buttonClass }} bg-green-700 hover:bg-green-600">{{ __("Join the challenge") }}</button>
                        </form>
                    @endif
                    @can ("create", \App\Models\ObservingList::class)
                        @if ($status !== "ended" && $challenge->source() !== null)
                            <form method="POST" action="{{ route("challenges.observing-list", $challenge) }}">
                                @csrf
                                <button type="submit" class="{{ $buttonClass }} bg-blue-600 hover:bg-blue-500">{{ __("Make an observing list of the objects I still need") }}</button>
                            </form>
                        @endif
                    @endcan
                @else
                    @if ($challenge->canBeJoined())
                        <a href="{{ route("login") }}" class="{{ $buttonClass }} bg-green-700 hover:bg-green-600">{{ __("Log in to join the challenge") }}</a>
                    @endif
                @endauth
                @can ("manage_challenges", \App\Models\User::class)
                    <a href="{{ route("admin.challenges.edit", $challenge) }}" class="{{ $buttonClass }} bg-gray-700 hover:bg-gray-600">{{ __("Edit") }}</a>
                @endcan
            </div>
        </div>

        @if ($goal)
            <div class="mt-4 rounded-lg bg-gray-800 p-4">
                <h2 class="mb-2 text-lg font-semibold text-white">{{ __("Your progress") }}</h2>
                <x-observing-goal :goal="$goal" class="text-sm" />
                @unless ($joined)
                    <p class="mt-2 text-xs text-gray-400">{{ __("You have not joined this challenge: your observations count, but you are not on the leaderboard.") }}</p>
                @endunless
            </div>
        @endif

        <div class="mt-4 rounded-lg bg-gray-800 p-4">
            <h2 class="mb-2 text-lg font-semibold text-white">{{ __("Together") }}</h2>
            <div class="h-2 w-full overflow-hidden rounded bg-gray-700">
                <div class="h-2 bg-blue-500" style="width: {{ $sharedPercentage }}%"></div>
            </div>
            <p class="mt-1 text-sm text-gray-300">
                {{ trans_choice(":count participant|:count participants", count($standings["rows"]), ["count" => count($standings["rows"])]) }}
                &middot;
                {{ __("Together they saw :seen of the :total objects.", ["seen" => $standings["seen"], "total" => $standings["total"]]) }}
            </p>
        </div>

        <div class="mt-4 rounded-lg bg-gray-800 p-4">
            <h2 class="mb-2 text-lg font-semibold text-white">{{ __("Leaderboard") }}</h2>
            @if (count($leaderboard) === 0)
                <p class="text-sm text-gray-400">{{ __("Nobody has joined yet.") }}</p>
            @else
                <table class="w-full text-left text-sm text-gray-300">
                    <thead class="text-xs text-gray-400 uppercase">
                        <tr>
                            <th class="py-1 pr-3">#</th>
                            <th class="py-1 pr-3">{{ __("Observer") }}</th>
                            <th class="py-1 pr-3 text-right">{{ __("Objects") }}</th>
                            <th class="py-1">{{ __("Target reached") }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @foreach (array_merge($leaderboard, $viewerRow ? [$viewerRow] : []) as $row)
                            <tr @class(["bg-gray-700/50" => auth()->id() === $row["user_id"]])>
                                <td class="py-1 pr-3">{{ $row["rank"] }}</td>
                                <td class="py-1 pr-3">
                                    <a href="{{ route("observer.show", $row["slug"]) }}" class="text-blue-400 hover:underline">{{ $row["name"] }}</a>
                                </td>
                                <td class="py-1 pr-3 text-right">{{ $row["count"] }} / {{ $row["target"] }}</td>
                                <td class="py-1">
                                    @if ($row["reached_on"])
                                        <span class="text-green-400">{{ $format($row["reached_on"]) }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <p class="mt-2 text-xs text-gray-500">
                    {{ __("Updated every :minutes minutes.", ["minutes" => \App\Services\ChallengeStandings::CACHE_MINUTES]) }}
                </p>
            @endif
        </div>

        @if ($showObjects && count($standings["objects"]) > 0)
            <div class="mt-4 rounded-lg bg-gray-800 p-4">
                <h2 class="mb-2 text-lg font-semibold text-white">{{ __("The objects") }}</h2>
                <p class="mb-2 text-xs text-gray-400">
                    {{ __("The number is the number of participants who saw the object in the challenge.") }}
                    @auth
                        {{ __("The objects you have are green.") }}
                    @endauth
                </p>
                <div class="flex flex-wrap gap-2 text-sm">
                    @foreach ($standings["objects"] as $object)
                        <a
                            href="{{ route("object.show", $object["slug"]) }}"
                            @class([
                                "rounded border px-2 py-0.5 hover:underline",
                                "border-green-500 text-green-300" => $seenByViewer->has(mb_strtolower($object["object_name"])),
                                "border-gray-600 text-gray-300" => !$seenByViewer->has(mb_strtolower($object["object_name"])),
                            ])
                            >{{ $object["display_name"] }}@if ($object["seen_by"] > 0)<span class="ml-1 text-xs text-gray-400">{{ $object["seen_by"] }}</span>@endif</a
                        >
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
