@props (["step", "highlight" => false])

{{-- An object of the beginner path (App\Services\BeginnerPath::steps()): details, months, where to start looking, finding notes and links --}}
@php
    $details = array_filter([
        $step["details"]["type"],
        $step["details"]["constellation"],
        $step["details"]["magnitude"] !== null
            ? __("mag") . " " . $step["details"]["magnitude"]
            : null,
        $step["details"]["size"],
    ]);
    $monthName = fn($month) => \Carbon\Carbon::create(2000, $month, 1)
        ->locale(app()->getLocale())
        ->isoFormat("MMMM");
    $done = $step["done_on"] !== null;
@endphp

<div
    {{
        $attributes->merge([
            "class" =>
                "rounded-lg border px-4 py-3 text-sm " .
                ($highlight
                    ? "border-blue-500 bg-gray-800"
                    : "border-gray-700 bg-gray-800/60"),
        ])
    }}
>
    <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
        <span class="min-w-0">
            <span class="mr-1 text-gray-500">{{ $step["number"] }}.</span>
            @if ($step["slug"])
                <a
                    href="{{ route("object.show", $step["slug"]) }}"
                    class="font-semibold text-white hover:underline"
                    >{{ $step["name"] }}</a
                >
            @else
                <span
                    class="font-semibold text-white"
                    >{{ $step["name"] }}</span
                >
            @endif
            @if ($details)
                <span class="text-gray-400"
                    >&middot; {{ implode(" · ", $details) }}</span
                >
            @endif
        </span>
        <span class="flex flex-wrap items-baseline gap-2">
            @if ($done)
                <span class="text-green-400"
                    >&check; {{
                        __("Seen on :date", [
                            "date" => \Carbon\Carbon::parse($step["done_on"])
                                ->locale(app()->getLocale())
                                ->isoFormat("LL"),
                        ])
                    }}</span
                >
            @elseif ($step["now"])
                <span
                    class="rounded bg-blue-900 px-2 py-0.5 text-xs text-blue-200"
                    >{{
                        __(
                            "Well placed this month",
                        )
                    }}</span
                >
            @endif
        </span>
    </div>

    @if (!$done || $highlight)
        <div class="mt-2 space-y-1 text-gray-300">
            @if ($step["best"])
                <p>
                    {{
                        __("Best in :month in the evening", [
                            "month" => $monthName($step["best"]),
                        ])
                    }}
                    @if ($step["months"])
                        <span class="text-gray-400"
                            >&middot; {{
                                __("well placed: :months", [
                                    "months" => \App\Services\BeginnerPath::monthRanges($step["months"]),
                                ])
                            }}</span
                        >
                    @endif
                </p>
            @endif
            @if ($step["hop"] && $step["hop"]["direction"] === null)
                <p>{{
                    __(
                        "Where to start: the bright star :star lies in the object.",
                        ["star" => $step["hop"]["star"]],
                    )
                }}</p>
            @elseif ($step["hop"])
                <p>
                    {{
                        __(
                            "Where to start: the object lies :distance° :direction the bright star :star.",
                            [
                                "star" => $step["hop"]["star"],
                                "distance" => number_format($step["hop"]["distance"], 1),
                                "direction" => __($step["hop"]["direction"]),
                            ],
                        )
                    }}
                </p>
            @endif
            @if ($step["notes"]->isNotEmpty())
                <ul class="space-y-1">
                    @foreach ($step["notes"] as $note)
                        <li class="text-gray-300">
                            &ldquo;{{ $note->text }}&rdquo;
                            <span class="text-xs text-gray-500"
                                >&mdash; {{
                                    html_entity_decode(
                                        (string) $note->user?->name,
                                        ENT_QUOTES | ENT_HTML5,
                                        "UTF-8",
                                    )
                                }}</span
                            >
                        </li>
                    @endforeach
                </ul>
            @elseif ($step["slug"])
                <p class="text-xs text-gray-500">{{
                    __(
                        "No finding tips yet. Once you found it, add yours on the object page.",
                    )
                }}</p>
            @endif
        </div>

        @if ($step["slug"])
            <p class="mt-2 flex flex-wrap gap-x-4 gap-y-1">
                <a
                    href="{{ route("object.field-views", $step["slug"]) }}"
                    class="text-blue-400 hover:underline"
                    >{{
                        __(
                            "Finder and eyepiece views",
                        )
                    }}</a
                >
                @unless ($done)
                    <a
                        href="{{ route("observation.create", ["slug" => $step["slug"], "return_to" => "/beginner-path"]) }}"
                        class="text-blue-400 hover:underline"
                        >{{
                            __(
                                "I saw it: log my observation",
                            )
                        }}</a
                    >
                @endunless
            </p>
        @endif
    @endif
</div>
