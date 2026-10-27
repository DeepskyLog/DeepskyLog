@props (["entries"])

{{-- The running and coming observing challenges on the home page: those the observer joined with their progress and place, the others with a link to join --}}
<x-home-section :title="__('Observing challenges')" {{ $attributes }}>
    <ul class="divide-y divide-gray-700 px-2 text-sm">
        @foreach ($entries as $entry)
            @php
                $challenge = $entry["challenge"];
                $daysLeft = (int) \Carbon\Carbon::today()->diffInDays($challenge->ends_on) + 1;
                $when =
                    $challenge->status() === "upcoming"
                        ? __("Starts on :date", ["date" => $challenge->starts_on->copy()->locale(app()->getLocale())->isoFormat("LL")])
                        : trans_choice(":count day left|:count days left", $daysLeft, ["count" => $daysLeft]);
            @endphp
            <li class="py-2">
                <a href="{{ route("challenges.show", $challenge) }}" class="font-semibold text-white hover:underline">{{ $challenge->name }}</a>
                @if ($entry["goal"])
                    @php
                        $progress = $entry["goal"]->progress();
                        $percentage = $progress["target"] > 0 ? min(100, round((100 * $progress["count"]) / $progress["target"])) : 0;
                    @endphp
                    <div class="mt-1 h-2 w-full overflow-hidden rounded bg-gray-700">
                        <div class="{{ $progress["status"] === "reached" ? "bg-green-500" : "bg-blue-500" }} h-2" style="width: {{ $percentage }}%"></div>
                    </div>
                    <p class="mt-1 text-gray-300">
                        {{ __(":count of :target objects", ["count" => min($progress["count"], $progress["target"]), "target" => $progress["target"]]) }}
                        @if ($entry["rank"])
                            &middot; {{ __("Place :rank of :count", ["rank" => $entry["rank"], "count" => $challenge->goals_count]) }}
                        @endif
                        &middot; {{ $when }}
                    </p>
                @else
                    <p class="text-gray-400">
                        {{ $challenge->task() }}
                        &middot; {{ trans_choice(":count participant|:count participants", $challenge->goals_count, ["count" => $challenge->goals_count]) }}
                        &middot; {{ $when }}
                    </p>
                    @auth
                        <form method="POST" action="{{ route("challenges.join", $challenge) }}" class="mt-1">
                            @csrf
                            <button type="submit" class="text-blue-400 hover:underline">{{ __("Join the challenge") }} &rarr;</button>
                        </form>
                    @endauth
                @endif
            </li>
        @endforeach
    </ul>
    <x-slot name="footer">
        <a href="{{ route("challenges.index") }}" class="text-blue-400 hover:underline">{{ __("All challenges") }} &rarr;</a>
    </x-slot>
</x-home-section>
