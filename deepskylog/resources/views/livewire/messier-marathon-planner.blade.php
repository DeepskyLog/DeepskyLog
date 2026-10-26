@php
    $input =
        "rounded-md border-gray-600 bg-gray-900 text-sm text-gray-200 focus:border-blue-500 focus:ring-blue-500";
    $choice = "flex items-center gap-2 text-sm text-gray-300 cursor-pointer";
    $time = fn(?int $timestamp) => $timestamp !== null
        ? \Carbon\Carbon::createFromTimestamp($timestamp, $timezone)->format("H:i")
        : "";
    $date = fn(string $night, string $format = "ddd LL") => \Carbon\Carbon::parse(
        $night,
    )
        ->locale(app()->getLocale())
        ->isoFormat($format);
@endphp

<div class="space-y-6">
    <p class="text-sm text-gray-400">
        {{
            __(
                "In March and April, around the new Moon, all Messier objects can be seen in one night from most places in the northern hemisphere: the marathon starts with M 77 and M 74 low in the west after sunset and ends with M 30 low in the east before sunrise. In October and November nearly all of them can: the Sun, in Libra, then hides M 83 and some objects low in Scorpius and Ophiuchus. The planner gives the best nights at your location and the order to see the objects in, with the time each one stands high enough.",
            )
        }}
    </p>

    <x-card>
        <div class="grid grid-cols-1 gap-4 px-2 sm:grid-cols-2 lg:grid-cols-3">
            <label class="block text-sm">
                <span
                    class="mb-1 block text-gray-400"
                    >{{ __("Location") }}</span
                >
                @if ($locationOptions)
                    <select
                        wire:model.live="locationId"
                        class="{{ $input }} w-full"
                    >
                        @if ($locationId === null)
                            <option value="">
                                {{
                                    __("Latitude :latitude°", [
                                        "latitude" => $latitude,
                                    ])
                                }}
                            </option>
                        @endif
                        @foreach ($locationOptions as $id => $label)
                            <option value="{{ $id }}">{{ $label }}</option>
                        @endforeach
                    </select>
                @else
                    <span class="text-yellow-400">{{
                        $latitude !== null
                            ? __("No location of your own: a latitude of :latitude° is used.", [
                                "latitude" => $latitude,
                            ])
                            : __("No location")
                    }}</span>
                    <a
                        href="{{ route("location.index") }}"
                        class="ml-1 text-blue-400 hover:underline"
                        >{{
                            __(
                                "Add a location",
                            )
                        }}</a
                    >
                @endif
            </label>
            <label class="block text-sm">
                <span class="mb-1 block text-gray-400">{{ __("Season") }}</span>
                <select wire:model.live="season" class="{{ $input }} w-full">
                    @foreach ($seasonOptions as $option)
                        <option value="{{ $option["key"] }}">
                            {{
                                $option["season"] === "spring"
                                    ? __("March and April :year", ["year" => $option["year"]])
                                    : __("October and November :year", [
                                        "year" => $option["year"],
                                    ])
                            }}
                        </option>
                    @endforeach
                </select>
            </label>
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
                    max="30"
                    class="{{ $input }} w-28"
                />
                @error ("minAltitude")
                    <span class="mt-1 block text-red-400">{{ $message }}</span>
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
                    max="15"
                    class="{{ $input }} w-28"
                />
                @error ("minutes")
                    <span class="mt-1 block text-red-400">{{ $message }}</span>
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
                    max="15"
                    class="{{ $input }} w-28"
                />
                @error ("gap")
                    <span class="mt-1 block text-red-400">{{ $message }}</span>
                @enderror
            </label>
        </div>
        <p class="mt-3 px-2 text-xs text-gray-500">{{ __("An object can be seen while it stands at least the lowest altitude high, between the ends of nautical twilight (the Sun 12° below the horizon); an object only that high in the brighter twilight is marked. Times are in :timezone.", ["timezone" => $timezone]) }}</p>
    </x-card>

    @if ($total === 0)
        <x-card>
            <p class="px-2 text-gray-300">{{
                __(
                    "No location with valid coordinates.",
                )
            }}</p>
        </x-card>
    @else
        {{-- The nights of the season --}}
        <x-card>
            <div class="flex flex-wrap items-center justify-between gap-2 px-2">
                <h3 class="font-semibold text-white">
                    {{
                        $allNights
                            ? __("All nights of the season")
                            : __("The nights near the new Moon")
                    }}
                </h3>
                <label class="{{ $choice }}">
                    <input
                        type="checkbox"
                        wire:model.live="allNights"
                        class="rounded text-blue-600"
                    />
                    {{
                        __(
                            "Show all nights",
                        )
                    }}
                </label>
            </div>
            <div class="mt-3 overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs text-gray-400 uppercase">
                        <tr>
                            <th class="px-2 py-1">{{ __("Night") }}</th>
                            <th class="px-2 py-1">{{ __("Dark") }}</th>
                            <th class="px-2 py-1">{{ __("Moon") }}</th>
                            <th class="px-2 py-1 text-right">
                                {{ __("Objects") }}
                            </th>
                            <th class="px-2 py-1"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @foreach ($nights as $row)
                            <tr
                                wire:key="night-{{ $row["night"] }}"
                                @class ([ "bg-gray-700" => $plan && $plan["night"] === $row["night"] ])
                            >
                                <td
                                    class="px-2 py-1 whitespace-nowrap text-gray-200"
                                >
                                    {{
                                        $date(
                                            $row["night"],
                                        )
                                    }}
                                    @if ($row["best"])
                                        <span
                                            class="ml-1 rounded bg-green-800 px-1.5 py-0.5 text-xs text-green-100"
                                            >{{ __("Best") }}</span
                                        >
                                    @endif
                                </td>
                                @if ($row["start"] === null)
                                    <td
                                        colspan="3"
                                        class="px-2 py-1 text-gray-400"
                                    >
                                        {{
                                            __(
                                                "It does not get dark enough.",
                                            )
                                        }}
                                    </td>
                                    <td></td>
                                @else
                                    <td
                                        class="px-2 py-1 whitespace-nowrap text-gray-300"
                                    >
                                        {{ $time($row["start"]) }}–{{ $time($row["end"]) }}
                                    </td>
                                    <td
                                        class="px-2 py-1 whitespace-nowrap text-gray-300"
                                    >
                                        @if ($row["moon"] && $row["moon"]["from"] !== null)
                                            @php
                                                $moon = [
                                                    "fraction" => (int) round($row["moon"]["fraction"] * 100),
                                                    "from" => $time($row["moon"]["from"]),
                                                    "until" => $time($row["moon"]["until"]),
                                                ];
                                            @endphp
                                            @if ($row["moon"]["from"] <= $row["start"] &&
                                                $row["moon"]["until"] >= $row["end"])
                                                {{
                                                    __(
                                                        ":fraction%, up all night",
                                                        $moon,
                                                    )
                                                }}
                                            @elseif ($row["moon"]["from"] <= $row["start"])
                                                {{
                                                    __(
                                                        ":fraction%, up until :until",
                                                        $moon,
                                                    )
                                                }}
                                            @elseif ($row["moon"]["until"] >= $row["end"])
                                                {{
                                                    __(
                                                        ":fraction%, up from :from",
                                                        $moon,
                                                    )
                                                }}
                                            @else
                                                {{
                                                    __(
                                                        ":fraction%, up :from–:until",
                                                        $moon,
                                                    )
                                                }}
                                            @endif
                                        @else
                                            {{
                                                __(
                                                    "Below the horizon",
                                                )
                                            }}
                                        @endif
                                    </td>
                                    <td
                                        class="px-2 py-1 text-right whitespace-nowrap text-gray-200"
                                    >
                                        {{
                                            $row[
                                                "scheduled"
                                            ]
                                        }}
                                        @if ($row["twilight"])
                                            <span
                                                class="text-yellow-400"
                                                title="{{ __("In the twilight") }}"
                                                >+ {{ $row["twilight"] }}</span
                                            >
                                        @endif
                                        / {{ $total }}
                                    </td>
                                    <td class="px-2 py-1 text-right">
                                        <button
                                            type="button"
                                            wire:click="plan('{{ $row["night"] }}')"
                                            class="text-xs text-blue-400 hover:underline"
                                        >
                                            {{ __("Plan") }}
                                        </button>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-card>

        {{-- The order of the chosen night --}}
        @if ($plan)
            <x-card>
                <div class="space-y-1 px-2 text-sm text-gray-400">
                    <h3 class="text-base font-semibold text-white">
                        {{
                            __("The night of :date", [
                                "date" => $date($plan["night"], "dddd LL"),
                            ])
                        }}
                    </h3>
                    <p>
                        @php
                            $twilight = count(
                                array_filter($plan["objects"], fn($object) => $object["twilight"]),
                            );
                        @endphp
                        {{
                            __(
                                ":count of the :total objects in the dark from :from to :until.",
                                [
                                    "count" => count($plan["objects"]) - $twilight,
                                    "total" => $total,
                                    "from" => $time($plan["start"]),
                                    "until" => $time($plan["end"]),
                                ],
                            )
                        }}
                        @if ($twilight)
                            {{
                                trans_choice(
                                    "{1} One more in the twilight.|[2,*] :count more in the twilight.",
                                    $twilight,
                                    ["count" => $twilight],
                                )
                            }}
                        @endif
                    </p>
                    <p class="text-xs text-gray-500">{{ __("Each object is planned when it stands as high as the ones after it allow, within the time it stands high enough.") }}</p>
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-xs text-gray-400 uppercase">
                            <tr>
                                <th class="px-2 py-1 text-right">#</th>
                                <th class="px-2 py-1">{{ __("Time") }}</th>
                                <th class="px-2 py-1">{{ __("Object") }}</th>
                                <th class="hidden px-2 py-1 sm:table-cell">
                                    {{ __("Type") }}
                                </th>
                                <th class="hidden px-2 py-1 md:table-cell">
                                    {{
                                        __(
                                            "Constellation",
                                        )
                                    }}
                                </th>
                                <th
                                    class="hidden px-2 py-1 text-right sm:table-cell"
                                >
                                    {{ __("Mag") }}
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
                            @foreach ($plan["objects"] as $object)
                                <tr wire:key="object-{{ $object["number"] }}">
                                    <td
                                        class="px-2 py-1 text-right text-gray-500"
                                    >
                                        {{ $loop->iteration }}
                                    </td>
                                    <td
                                        class="px-2 py-1 whitespace-nowrap text-gray-200"
                                    >
                                        {{
                                            $time(
                                                $object["at"],
                                            )
                                        }}
                                    </td>
                                    <td class="px-2 py-1 whitespace-nowrap">
                                        <a
                                            href="{{ route("object.show", $object["slug"]) }}"
                                            class="text-blue-400 hover:underline"
                                            >{{
                                                $object[
                                                    "name"
                                                ]
                                            }}</a
                                        >
                                        @if ($object["twilight"])
                                            <span
                                                class="ml-1 rounded bg-yellow-800 px-1.5 py-0.5 text-xs text-yellow-100"
                                                >{{ __("Twilight") }}</span
                                            >
                                        @endif
                                        @if (isset($seen[mb_strtolower($object["list_name"])]))
                                            <span
                                                class="ml-1 rounded bg-gray-700 px-1.5 py-0.5 text-xs text-gray-400"
                                                >{{ __("Seen") }}</span
                                            >
                                        @endif
                                    </td>
                                    <td class="hidden px-2 py-1 sm:table-cell">
                                        {{
                                            __(
                                                $object["type"],
                                            )
                                        }}
                                    </td>
                                    <td class="hidden px-2 py-1 md:table-cell">
                                        {{
                                            $object["constellation"]
                                                ? __($object["constellation"])
                                                : ""
                                        }}
                                    </td>
                                    <td
                                        class="hidden px-2 py-1 text-right sm:table-cell"
                                    >
                                        {{
                                            $object["mag"] !== null
                                                ? number_format($object["mag"], 1)
                                                : ""
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

                @if ($plan["missed"])
                    <div class="mt-4 px-2 text-sm">
                        <h4 class="font-semibold text-white">
                            {{
                                __(
                                    "Not in this night",
                                )
                            }}
                        </h4>
                        <ul class="mt-1 space-y-0.5 text-gray-400">
                            @foreach ($plan["missed"] as $object)
                                <li>
                                    <a
                                        href="{{ route("object.show", $object["slug"]) }}"
                                        class="text-blue-400 hover:underline"
                                        >{{
                                            $object[
                                                "name"
                                            ]
                                        }}</a
                                    >:
                                    @if ($object["reason"] === "never")
                                        {{
                                            __(
                                                "never :altitude° high in the night.",
                                                ["altitude" => $minAltitude],
                                            )
                                        }}
                                    @else
                                        {{
                                            __(
                                                "not reached in time (high enough from :from to :until).",
                                                [
                                                    "from" => $time($object["intervals"][0][0]),
                                                    "until" => $time(end($object["intervals"])[1]),
                                                ],
                                            )
                                        }}
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="mt-6 space-y-3 px-2">
                    <h3 class="font-semibold text-white">
                        {{
                            __(
                                "Make the observing list",
                            )
                        }}
                    </h3>
                    <p class="text-xs text-gray-500">{{
                        __(
                            "The objects in this order, each with its time and window; the objects not in this night at the end, with the reason.",
                        )
                    }}</p>
                    <div class="flex flex-wrap items-center gap-4">
                        <input
                            type="text"
                            wire:model="name"
                            placeholder="{{ $defaultName }}"
                            maxlength="250"
                            class="{{ $input }} w-full max-w-md"
                        />
                        <label class="{{ $choice }}">
                            <input
                                type="checkbox"
                                wire:model="public"
                                class="rounded text-blue-600"
                            />
                            {{ __("Public") }}
                        </label>
                    </div>
                    @error ("name")
                        <p class="text-sm text-red-400">{{ $message }}</p>
                    @enderror
                    @error ("night")
                        <p class="text-sm text-red-400">{{ $message }}</p>
                    @enderror
                    @can ("create", \App\Models\ObservingList::class)
                        <button
                            type="button"
                            wire:click="create"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase hover:bg-blue-500"
                        >
                            {{
                                __(
                                    "Make the observing list (:count objects)",
                                    ["count" => count($plan["objects"]) + count($plan["missed"])],
                                )
                            }}
                        </button>
                    @else
                        <p class="text-sm text-gray-400">{{
                            __(
                                "Switch to the Observer role to create a list",
                            )
                        }}</p>
                    @endcan
                </div>
            </x-card>
        @endif
    @endif
</div>
