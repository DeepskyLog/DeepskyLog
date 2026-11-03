{{-- The personal sky calendar (App\Services\SkyCalendar), subscribed to with its secret address --}}
@php
    $calendarUrl = route("sky-calendar.show", [
        "token" => $user->calendarFeedToken(),
    ]);
    $webcalUrl = preg_replace("#^https?://#", "webcal://", $calendarUrl);
@endphp
<x-action-section>
    <x-slot name="title">
        {{
            __(
                "Sky calendar",
            )
        }}
    </x-slot>

    <x-slot name="description">
        {{
            __(
                "Subscribe to your personal sky calendar in the calendar of your phone or computer: the eclipses, occultations, conjunctions and shadows on Jupiter seen from your standard location, the nights forecast clear and dark, your starparties, the nights of your planned observing lists and the observing challenges.",
            )
        }}
    </x-slot>

    <x-slot name="content">
        <div x-data="{ copied: false }" class="space-y-3 text-sm text-gray-300">
            <p>
                {{
                    __(
                        "This address is secret: anyone who has it sees your calendar. Give it a new address when it was shared by mistake.",
                    )
                }}
            </p>
            <div class="flex flex-col gap-2 sm:flex-row">
                <input
                    type="text"
                    readonly
                    value="{{ $calendarUrl }}"
                    class="w-full rounded border-gray-700 bg-gray-800 text-sm text-gray-200"
                    x-on:focus="$el.select()"
                />
                <button
                    type="button"
                    class="rounded bg-gray-700 px-3 py-2 text-sm text-white hover:bg-gray-600"
                    x-on:click="navigator.clipboard.writeText(@js($calendarUrl)); copied = true; setTimeout(() => copied = false, 2000)"
                >
                    <span x-show="!copied">{{ __("Copy") }}</span>
                    <span x-show="copied" x-cloak>{{ __("Copied") }}</span>
                </button>
            </div>
            <div class="flex flex-wrap items-center gap-4">
                <a
                    href="{{ $webcalUrl }}"
                    class="text-blue-400 hover:underline"
                >
                    {{
                        __(
                            "Subscribe in your calendar app",
                        )
                    }}
                </a>
                <a
                    href="https://calendar.google.com/calendar/r?cid={{ urlencode($webcalUrl) }}"
                    target="_blank"
                    rel="noopener"
                    class="text-blue-400 hover:underline"
                >
                    {{
                        __(
                            "Add to Google Calendar",
                        )
                    }}
                </a>
                <form
                    method="POST"
                    action="{{ route("sky-calendar.regenerate") }}"
                >
                    @csrf
                    <button
                        type="submit"
                        class="text-gray-400 underline hover:text-white"
                    >
                        {{
                            __(
                                "Give the calendar a new address",
                            )
                        }}
                    </button>
                </form>
            </div>
            @if ($user->stdlocation === null)
                <p class="text-yellow-400">
                    {{
                        __(
                            "Choose a standard location to get the events and clear nights as seen from there.",
                        )
                    }}
                </p>
            @endif
        </div>
    </x-slot>
</x-action-section>
