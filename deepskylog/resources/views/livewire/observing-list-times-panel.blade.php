@php
    $input =
        "rounded-md border-gray-600 bg-gray-900 text-sm text-gray-200 focus:border-blue-500 focus:ring-blue-500";
    $time = fn(int $timestamp) => \Carbon\Carbon::createFromTimestamp(
        $timestamp,
        $proposal["timezone"] ?? $timezone,
    )->format("H:i");
    $date = fn($night) => \Carbon\Carbon::parse($night)
        ->locale(app()->getLocale())
        ->isoFormat("dddd LL");
    $twelveHours = (bool) (auth()->user()?->showInches ?? false);
@endphp

<div
    class="mb-4 rounded-lg border border-gray-700 bg-gray-900/60 p-4"
    x-data="{ open: false }"
>
    <div class="flex flex-wrap items-center justify-between gap-2">
        <div>
            <h3 class="font-semibold text-white">
                {{
                    __(
                        "Times for a night",
                    )
                }}
            </h3>
            @if ($plannedNight && $isOwner)
                <p class="text-xs text-gray-400">
                    {{
                        __(
                            "The times of this list are for the night of :date (times in :timezone).",
                            ["date" => $date($plannedNight), "timezone" => $list->planned_timezone],
                        )
                    }}
                </p>
            @else
                <p class="text-xs text-gray-400">{{
                    __(
                        "DeepskyLog proposes when to observe each object, for the night and hours you choose.",
                    )
                }}</p>
            @endif
        </div>
        <button
            type="button"
            @click="open = !open"
            class="inline-flex items-center rounded-md bg-gray-700 px-3 py-1.5 text-xs font-semibold text-gray-200 hover:bg-gray-600"
        >
            <span x-show="!open">{{
                $plannedNight && $isOwner
                    ? __("Plan again")
                    : __("Plan the times")
            }}</span>
            <span x-show="open" x-cloak>{{ __("Close") }}</span>
        </button>
    </div>

    <div x-show="open" x-cloak class="mt-4 space-y-4">
        @if (!$locationOptions)
            <p class="text-sm text-yellow-400">
                {{
                    __(
                        "Add a location to plan the times.",
                    )
                }}
                <a
                    href="{{ route("location.index") }}"
                    class="ml-1 text-blue-400 hover:underline"
                    >{{
                        __(
                            "Add a location",
                        )
                    }}</a
                >
            </p>
        @else
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div class="text-sm">
                    <span
                        class="mb-1 block text-gray-400"
                        >{{ __("Night") }}</span
                    >
                    <x-datetime-picker
                        without-time="true"
                        without-timezone="true"
                        name="night"
                        wire:model.live="night"
                        value="{{ $night }}"
                        :clearable="false"
                    />
                    @error ("night")
                        <span
                            class="mt-1 block text-red-400"
                            >{{ $message }}</span
                        >
                    @enderror
                </div>
                <label class="block text-sm">
                    <span
                        class="mb-1 block text-gray-400"
                        >{{ __("Location") }}</span
                    >
                    <select
                        wire:model.live="locationId"
                        class="{{ $input }} w-full"
                    >
                        @foreach ($locationOptions as $id => $label)
                            <option value="{{ $id }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <div class="text-sm">
                    <span
                        class="mb-1 block text-gray-400"
                        >{{ __("Hours") }}</span
                    >
                    <div class="flex items-center gap-2 text-gray-300">
                        <div class="w-32">
                            <x-time-picker
                                name="from"
                                wire:model.live="from"
                                :military-time="!$twelveHours"
                                without-seconds="true"
                            />
                        </div>
                        {{ __("until") }}
                        <div class="w-32">
                            <x-time-picker
                                name="until"
                                wire:model.live="until"
                                :military-time="!$twelveHours"
                                without-seconds="true"
                            />
                        </div>
                    </div>
                    @error ("from")
                        <span
                            class="mt-1 block text-red-400"
                            >{{ $message }}</span
                        >
                    @enderror
                    @error ("until")
                        <span
                            class="mt-1 block text-red-400"
                            >{{ $message }}</span
                        >
                    @enderror
                    <span class="mt-1 block text-xs text-gray-500">
                        @if ($dark === false)
                            {{
                                __(
                                    "It does not get dark during this night at this location: choose the hours.",
                                )
                            }}
                        @elseif ($dark)
                            {{
                                __(
                                    "Empty for the dark hours, from :from to :until.",
                                    $dark,
                                )
                            }}
                        @endif
                    </span>
                </div>
                <label class="block text-sm">
                    <span class="mb-1 block text-gray-400">{{
                        __(
                            "Lowest altitude (°)",
                        )
                    }}</span>
                    <input
                        type="number"
                        wire:model.live.debounce.500ms="minAltitude"
                        min="0"
                        max="60"
                        class="{{ $input }} w-28"
                    />
                    @error ("minAltitude")
                        <span
                            class="mt-1 block text-red-400"
                            >{{ $message }}</span
                        >
                    @enderror
                </label>
                <label class="block text-sm">
                    <span class="mb-1 block text-gray-400">{{
                        __(
                            "Minutes per object",
                        )
                    }}</span>
                    <input
                        type="number"
                        wire:model.live.debounce.500ms="minutes"
                        min="1"
                        max="60"
                        class="{{ $input }} w-28"
                    />
                    @error ("minutes")
                        <span
                            class="mt-1 block text-red-400"
                            >{{ $message }}</span
                        >
                    @enderror
                </label>
                <label class="block text-sm">
                    <span class="mb-1 block text-gray-400">{{
                        __(
                            "Minutes between objects",
                        )
                    }}</span>
                    <input
                        type="number"
                        wire:model.live.debounce.500ms="gap"
                        min="0"
                        max="30"
                        class="{{ $input }} w-28"
                    />
                    <span class="mt-1 block text-xs text-gray-500">{{
                        __(
                            "To move the telescope to the next object and find it.",
                        )
                    }}</span>
                    @error ("gap")
                        <span
                            class="mt-1 block text-red-400"
                            >{{ $message }}</span
                        >
                    @enderror
                </label>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button
                    type="button"
                    wire:click="propose"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase hover:bg-blue-500"
                >
                    {{
                        __(
                            "Propose times",
                        )
                    }}
                </button>
                @if ($isOwner && $plannedNight)
                    <button
                        type="button"
                        wire:click="clear"
                        wire:confirm="{{ __("Remove the times of all objects of this list?") }}"
                        class="text-xs text-red-400 hover:underline"
                    >
                        {{
                            __(
                                "Remove the times",
                            )
                        }}
                    </button>
                @endif
            </div>

            @if ($proposal)
                <div class="space-y-3 border-t border-gray-700 pt-4">
                    <p class="text-sm text-gray-300">
                        {{
                            __(
                                ":count of the :total objects get a time between :from and :until (times in :timezone).",
                                [
                                    "count" => count($proposal["objects"]),
                                    "total" => count($proposal["objects"]) + count($proposal["missed"]),
                                    "from" => $time($proposal["start"]),
                                    "until" => $time($proposal["end"]),
                                    "timezone" => $proposal["timezone"],
                                ],
                            )
                        }}
                    </p>

                    @if ($proposal["objects"])
                        <div class="max-h-96 overflow-auto">
                            <table class="w-full text-left text-sm">
                                <thead class="text-xs text-gray-400 uppercase">
                                    <tr>
                                        <th class="px-2 py-1">
                                            {{ __("Time") }}
                                        </th>
                                        <th class="px-2 py-1">
                                            {{ __("Object") }}
                                        </th>
                                        <th class="px-2 py-1 text-right">
                                            {{ __("Altitude") }}
                                        </th>
                                        <th
                                            class="px-2 py-1"
                                            title="{{ __("When the object stands high enough") }}"
                                        >
                                            {{ __("Window") }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-700">
                                    @foreach ($proposal["objects"] as $object)
                                        <tr
                                            wire:key="proposal-{{ $loop->index }}"
                                        >
                                            <td
                                                class="px-2 py-1 whitespace-nowrap text-gray-200"
                                            >
                                                {{
                                                    $time(
                                                        $object["at"],
                                                    )
                                                }}
                                            </td>
                                            <td class="px-2 py-1 text-gray-200">
                                                {{
                                                    $object[
                                                        "name"
                                                    ]
                                                }}
                                            </td>
                                            <td class="px-2 py-1 text-right">
                                                {{
                                                    (int) round(
                                                        $object["altitude"],
                                                    )
                                                }}°
                                            </td>
                                            <td
                                                class="px-2 py-1 whitespace-nowrap text-gray-400"
                                            >
                                                {{ $time($object["from"]) }}–{{ $time($object["until"]) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    @if ($proposal["missed"])
                        <div class="text-sm text-gray-400">
                            <h4 class="font-semibold text-white">
                                {{
                                    __(
                                        "Without a time",
                                    )
                                }}
                            </h4>
                            @foreach ([
                                    "never" => __("Never :altitude° high in these hours:", [
                                        "altitude" => $minAltitude
                                    ]),
                                    "time" => __("No time left:"),
                                    "unknown" => __("Position unknown:")
                                ]
                                as $reason => $label)
                                @php
                                    $names = array_column(
                                        array_filter(
                                            $proposal["missed"],
                                            fn($missed) => $missed["reason"] === $reason,
                                        ),
                                        "name",
                                    );
                                @endphp
                                @if ($names)
                                    <p>{{ $label }} {{
                                        implode(
                                            ", ",
                                            $names,
                                        )
                                    }}</p>
                                @endif
                            @endforeach
                        </div>
                    @endif

                    <div class="flex flex-wrap items-center gap-3">
                        @if ($isOwner)
                            <button
                                type="button"
                                wire:click="save"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase hover:bg-blue-500"
                            >
                                {{
                                    __(
                                        "Save the times in this list",
                                    )
                                }}
                            </button>
                        @endif
                        @if ($canCreate)
                            <input
                                type="text"
                                wire:model="newName"
                                placeholder="{{ $defaultName }}"
                                maxlength="250"
                                class="{{ $input }} w-full max-w-sm"
                            />
                            <button
                                type="button"
                                wire:click="saveAsNew"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center rounded-md bg-gray-700 px-4 py-2 text-xs font-semibold tracking-widest text-gray-200 uppercase hover:bg-gray-600"
                            >
                                {{
                                    __(
                                        "Save as a new list",
                                    )
                                }}
                            </button>
                        @endif
                    </div>
                    @error ("newName")
                        <p class="text-sm text-red-400">{{ $message }}</p>
                    @enderror
                    @if ($isOwner)
                        <p class="text-xs text-gray-500">{{
                            __(
                                "Saving puts the objects with a time first, in their order; the objects without a time lose theirs.",
                            )
                        }}</p>
                    @endif
                </div>
            @endif
        @endif
    </div>
</div>
