@props (["goal"])

{{-- An observing goal (App\Models\ObservingGoal) with its period and progress --}}
@php
    $progress = $goal->progress();
    $percentage = $progress["target"] > 0 ? min(100, round((100 * $progress["count"]) / $progress["target"])) : 0;
    $format = fn($date) => $date->copy()->locale(app()->getLocale())->isoFormat("LL");
    $barClass = match ($progress["status"]) {
        "reached" => "bg-green-500",
        "ended" => "bg-gray-500",
        default => "bg-blue-500",
    };
    $observer = $goal->user;
    $catalogUrl =
        $goal->catalog !== null && $observer
            ? route("observer.catalog", [
                "observer" => $observer->slug,
                "source" => $goal->source()["key"],
                "mode" => $goal->drawings() ? "drawn" : null,
            ])
            : ($goal->observingList
                ? route("observing-list.show", $goal->observingList->slug)
                : null);
@endphp

<div {{ $attributes }}>
    <div class="flex flex-wrap items-baseline justify-between gap-x-3">
        <span class="font-medium text-white">
            @if ($catalogUrl)
                <a href="{{ $catalogUrl }}" class="hover:underline">{{ $goal->title() }}</a>
            @else
                {{ $goal->title() }}
            @endif
        </span>
        <span class="text-sm text-gray-400">
            {{ $format($goal->starts_on) }} &ndash; {{ $format($goal->ends_on) }}
        </span>
    </div>
    <div class="mt-1 h-2 w-full overflow-hidden rounded bg-gray-700">
        <div class="{{ $barClass }} h-2" style="width: {{ $percentage }}%"></div>
    </div>
    <p class="mt-1 text-sm text-gray-300">
        {{
            __(":count of :target objects", [
                "count" => min($progress["count"], $progress["target"]),
                "target" => $progress["target"],
            ])
        }}
        &middot;
        @switch($progress["status"])
            @case("reached")
                <span class="text-green-400">{{ __("Reached on :date", ["date" => $format($progress["reached_on"])]) }}</span>
                @break
            @case("upcoming")
                {{ __("Starts on :date", ["date" => $format($goal->starts_on)]) }}
                @break
            @case("ended")
                <span class="text-gray-400">{{ __("Not reached") }}</span>
                @break
            @default
                {{ trans_choice(":count day left|:count days left", $progress["days_left"], ["count" => $progress["days_left"]]) }}
        @endswitch
        @if ($goal->observing_list_id === null && $goal->catalog === null)
            &middot; <span class="text-gray-400">{{ __("The observing list was deleted") }}</span>
        @endif
    </p>
</div>
