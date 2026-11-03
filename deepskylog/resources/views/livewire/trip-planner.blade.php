@php
    $input =
        "rounded-md border-gray-600 bg-gray-900 text-sm text-gray-200 focus:border-blue-500 focus:ring-blue-500";
    $date = fn($night) => \Carbon\Carbon::parse($night)
        ->locale(app()->getLocale())
        ->isoFormat("ddd D MMM");
@endphp

<div
    class="space-y-6"
    @if ($planner && !$result && !$unavailable && !$failed) wire:poll.3s @endif
>
    <p class="text-sm text-gray-400">
        {{
            __(
                "Going to La Palma, Namibia or a starparty abroad? Pick the place on the map, the nights and the instrument you take along: you get the deep-sky objects you have not seen that never rise high enough at home, or that none of your instruments shows from your own sky, the most observed first.",
            )
        }}
    </p>

    <x-card>
        <form wire:submit="calculate" class="space-y-4 px-2">
            <div
                wire:ignore
                x-data
                x-init="window.dslTripMap($refs.map, $wire, {{ is_numeric($lat) && is_numeric($lon) ? (float) $lat . ", " . (float) $lon : "null, null" }})"
            >
                <div
                    x-ref="map"
                    class="w-full rounded"
                    style="height: 20rem; z-index: 1"
                ></div>
            </div>
            <p class="text-xs text-gray-500">
                {{
                    __(
                        "Click on the map, drag the marker or search for a place. The time zone and the sky brightness of the place are proposed; change them when you know better.",
                    )
                }}
            </p>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <label class="block text-sm">
                    <span
                        class="mb-1 block text-gray-400"
                        >{{ __("Name") }}</span
                    >
                    <input
                        type="text"
                        wire:model="place"
                        maxlength="100"
                        placeholder="{{ __("La Palma") }}"
                        class="{{ $input }} w-full"
                    />
                </label>
                <label class="block text-sm">
                    <span
                        class="mb-1 block text-gray-400"
                        >{{ __("Latitude") }}</span
                    >
                    <input
                        type="number"
                        step="any"
                        wire:model="lat"
                        class="{{ $input }} w-full"
                    />
                </label>
                <label class="block text-sm">
                    <span class="mb-1 block text-gray-400">{{
                        __(
                            "Longitude",
                        )
                    }}</span>
                    <input
                        type="number"
                        step="any"
                        wire:model="lon"
                        class="{{ $input }} w-full"
                    />
                </label>
                <label class="block text-sm">
                    <span
                        class="mb-1 block text-gray-400"
                        title="{{ __("The sky brightness you expect there, in mag/arcsec²") }}"
                        >{{ __("SQM") }}</span
                    >
                    <input
                        type="number"
                        step="0.01"
                        min="16"
                        max="22.5"
                        wire:model="sqm"
                        class="{{ $input }} w-full"
                    />
                </label>
                <label class="block text-sm">
                    <span
                        class="mb-1 block text-gray-400"
                        >{{ __("Timezone") }}</span
                    >
                    <select wire:model="timezone" class="{{ $input }} w-full">
                        <option value=""></option>
                        @foreach ($timezones as $zone)
                            <option value="{{ $zone }}">{{ $zone }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block text-sm">
                    <span class="mb-1 block text-gray-400">{{
                        __(
                            "First night",
                        )
                    }}</span>
                    <input
                        type="date"
                        wire:model="from"
                        class="{{ $input }} w-full"
                    />
                </label>
                <label class="block text-sm">
                    <span class="mb-1 block text-gray-400">{{
                        __(
                            "Last night",
                        )
                    }}</span>
                    <input
                        type="date"
                        wire:model="until"
                        class="{{ $input }} w-full"
                    />
                </label>
                <div class="flex items-end">
                    <x-button
                        type="submit"
                        primary
                        :label="__('What could I see there?')"
                    />
                </div>
            </div>
            @foreach (["lat", "lon", "place", "sqm", "timezone", "from", "until"] as $field)
                @error ($field)
                    <p class="text-sm text-red-400">{{ $message }}</p>
                @enderror
            @endforeach

            <div class="flex flex-wrap items-end gap-3">
                @if ($instruments)
                    <label class="block max-w-sm text-sm">
                        <span class="mb-1 block text-gray-400">{{
                            __(
                                "Instrument you take along",
                            )
                        }}</span>
                        <select
                            wire:model.live="instrument"
                            class="{{ $input }} w-full"
                        >
                            @foreach ($instruments as $id => $label)
                                <option value="{{ $id }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                @endif
                @if ($locations)
                    <label class="block max-w-sm text-sm">
                        <span class="mb-1 block text-gray-400">{{
                            __(
                                "Compared with home",
                            )
                        }}</span>
                        <select
                            wire:model.live="home"
                            class="{{ $input }} w-full"
                        >
                            @foreach ($locations as $id => $label)
                                <option value="{{ $id }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                @endif
            </div>
        </form>
    </x-card>

    @if (!$planner)
        {{-- waiting for the form --}}
    @elseif ($unavailable)
        <x-card>
            <p class="px-2 text-yellow-400">{{
                __(
                    $unavailable,
                )
            }}</p>
            <p class="mt-2 px-2 text-sm">
                <a
                    href="{{ route("location.index") }}"
                    class="text-blue-400 hover:underline"
                    >{{
                        __(
                            "Locations",
                        )
                    }}</a
                >
                ·
                <a
                    href="{{ route("instrument.index") }}"
                    class="text-blue-400 hover:underline"
                    >{{
                        __(
                            "Instruments",
                        )
                    }}</a
                >
            </p>
        </x-card>
    @else
        @if ($nights)
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <caption class="pb-2 text-left text-gray-300">
                        {{
                            __("The nights at :place", [
                                "place" => $planner->placeName(),
                            ])
                        }}
                        <span class="text-xs text-gray-500"
                            >({{ $planner->trip["timezone"] }})</span
                        >
                    </caption>
                    <thead class="text-xs text-gray-400 uppercase">
                        <tr>
                            <th class="px-2 py-1">{{ __("Night") }}</th>
                            <th class="px-2 py-1">{{ __("Dark") }}</th>
                            <th class="px-2 py-1">{{ __("Moon") }}</th>
                            <th class="px-2 py-1 text-right">
                                {{
                                    __(
                                        "Dark without Moon",
                                    )
                                }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @foreach ($nights as $night)
                            <tr wire:key="trip-night-{{ $night["night"] }}">
                                <td class="px-2 py-1">
                                    {{
                                        $date(
                                            $night["night"],
                                        )
                                    }}
                                </td>
                                <td class="px-2 py-1">
                                    @if ($night["dark"])
                                        {{
                                            $night["dark"][
                                                "from"
                                            ]
                                        }}–{{
                                            $night["dark"][
                                                "until"
                                            ]
                                        }}
                                        @if ($night["dark"]["twilight"] === "nautical")
                                            <span class="text-xs text-gray-500"
                                                >({{ __("nautical twilight") }})</span
                                            >
                                        @endif
                                    @else
                                        <span
                                            class="text-gray-500"
                                            >{{ __("Not dark") }}</span
                                        >
                                    @endif
                                </td>
                                <td class="px-2 py-1">
                                    @if ($night["moon"])
                                        {{
                                            $night["moon"][
                                                "fraction"
                                            ]
                                        }}%,
                                        {{
                                            $night["moon"][
                                                "from"
                                            ]
                                        }}–{{
                                            $night["moon"][
                                                "until"
                                            ]
                                        }}
                                    @elseif ($night["dark"])
                                        <span
                                            class="text-gray-500"
                                            >{{ __("Not up") }}</span
                                        >
                                    @endif
                                </td>
                                <td class="px-2 py-1 text-right">
                                    @if ($night["dark"])
                                        {{
                                            intdiv(
                                                $night["moonless_minutes"],
                                                60,
                                            )
                                        }}:{{
                                            sprintf(
                                                "%02d",
                                                $night["moonless_minutes"] % 60,
                                            )
                                        }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if ($failed)
            <x-card>
                <p class="px-2 text-yellow-400">
                    {{
                        __(
                            "The objects could not be calculated. Please try again later; if it keeps failing, let us know.",
                        )
                    }}
                </p>
                <div class="mt-3 px-2">
                    <x-button secondary wire:click="retry">{{
                        __(
                            "Try again",
                        )
                    }}</x-button>
                </div>
            </x-card>
        @elseif (!$result)
            <x-card>
                <p class="px-2 text-gray-300">
                    {{
                        __(
                            "The objects are being calculated; this takes a few seconds. The page updates by itself.",
                        )
                    }}
                </p>
            </x-card>
        @else
            <x-card>
                <div class="space-y-2 px-2 text-sm text-gray-300">
                    @if ($shown["total"] === 0)
                        <p>
                            {{
                                __(
                                    "Nothing new: everything your :instrument shows at :place in these nights, you have seen or can see at :home.",
                                    [
                                        "instrument" => $planner->instrumentName(),
                                        "place" => $planner->placeName(),
                                        "home" => $planner->homeName(),
                                    ],
                                )
                            }}
                        </p>
                    @else
                        <p class="text-base text-gray-100">
                            {{
                                trans_choice(
                                    "{1} One object you have not seen is new for you at :place with your :instrument.|[2,*] :count objects you have not seen are new for you at :place with your :instrument.",
                                    $shown["total"],
                                    [
                                        "count" => number_format($shown["total"]),
                                        "place" => $planner->placeName(),
                                        "instrument" => $planner->instrumentName(),
                                    ],
                                )
                            }}
                            @if ($shown["total"] > count($shown["objects"]))
                                {{
                                    __(
                                        "The :count most observed are shown.",
                                        [
                                            "count" => count($shown["objects"]),
                                        ],
                                    )
                                }}
                            @endif
                        </p>
                    @endif
                    <details>
                        <summary
                            class="cursor-pointer text-xs text-gray-500 select-none hover:text-gray-300"
                        >
                            {{
                                __(
                                    "How is this list made?",
                                )
                            }}
                        </summary>
                        <div class="mt-1 space-y-1 text-xs">
                            <p>
                                {{
                                    __(
                                        "An object is listed when it stands at least :hours hour(s) above :altitude° in the dark hours of a night of the trip, and your :instrument shows it there (a contrast reserve above :reserve) under a sky of :sqm mag/arcsec², dimmed by the atmosphere at the highest altitude it reaches in those hours.",
                                        [
                                            "hours" => \App\Services\TripPlanner::MIN_HOURS,
                                            "altitude" => \App\Services\TripPlanner::MIN_ALTITUDE,
                                            "instrument" => $planner->instrumentName(),
                                            "reserve" => \App\Services\EquipmentGain::VISIBLE_ABOVE,
                                            "sqm" => number_format($result["sqm"], 2),
                                        ],
                                    )
                                }}
                            </p>
                            <p>
                                {{
                                    __(
                                        "It is new when at :home it never stands :altitude° high (also behind your local horizon), or none of your instruments shows it there under a sky of :sqm mag/arcsec²: :instruments.",
                                        [
                                            "home" => $planner->homeName(),
                                            "altitude" => \App\Services\TripPlanner::MIN_ALTITUDE,
                                            "sqm" => number_format($result["home_sqm"], 2),
                                            "instruments" => implode(", ", $result["compared"]),
                                        ],
                                    )
                                }}
                            </p>
                            <p>
                                {{
                                    __(
                                        "The Moon is not taken into account: plan the faint objects in the nights without it. Double stars, stars, quasars and supernovae are not included.",
                                    )
                                }}
                            </p>
                        </div>
                    </details>
                </div>
            </x-card>

            <label class="block max-w-sm text-sm">
                <span class="mb-1 block text-gray-400">{{ __("Show") }}</span>
                <select wire:model.live="reason" class="{{ $input }} w-full">
                    <option value="">
                        {{
                            __(
                                "All new objects",
                            )
                        }}
                    </option>
                    <option value="{{ \App\Services\TripPlanner::LOW }}">
                        {{
                            __(
                                "Too low at home",
                            )
                        }}
                    </option>
                    <option value="{{ \App\Services\TripPlanner::FAINTER }}">
                        {{
                            __(
                                "Too faint at home",
                            )
                        }}
                    </option>
                </select>
            </label>

            @if ($shown["objects"])
                @can ("create", \App\Models\ObservingList::class)
                    <x-card>
                        <form
                            wire:submit="create"
                            class="flex flex-wrap items-end gap-3 px-2"
                        >
                            <label class="block grow text-sm">
                                <span class="mb-1 block text-gray-400">{{
                                    __(
                                        "Name of the list",
                                    )
                                }}</span>
                                <input
                                    type="text"
                                    wire:model="name"
                                    maxlength="200"
                                    placeholder="{{ $planner->placeName() }}"
                                    class="{{ $input }} w-full"
                                />
                            </label>
                            <label class="block text-sm">
                                <span
                                    class="mb-1 block text-gray-400"
                                    >{{ __("Night") }}</span
                                >
                                <select
                                    wire:model="listNight"
                                    class="{{ $input }}"
                                >
                                    <option value="">
                                        {{
                                            __(
                                                "Every night, a list each",
                                            )
                                        }}
                                    </option>
                                    @foreach ($nights as $night)
                                        @if ($night["dark"])
                                            <option
                                                value="{{ $night["night"] }}"
                                            >
                                                {{
                                                    $date(
                                                        $night["night"],
                                                    )
                                                }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </label>
                            <label class="block w-28 text-sm">
                                <span class="mb-1 block text-gray-400">{{
                                    __(
                                        "Minutes per object",
                                    )
                                }}</span>
                                <input
                                    type="number"
                                    min="1"
                                    max="120"
                                    wire:model="minutes"
                                    class="{{ $input }} w-full"
                                />
                            </label>
                            <label
                                class="flex items-center gap-2 text-sm text-gray-300"
                            >
                                <input
                                    type="checkbox"
                                    wire:model="public"
                                    class="rounded border-gray-600 bg-gray-900"
                                />
                                {{ __("Public") }}
                            </label>
                            <x-button
                                type="submit"
                                primary
                                :label="__('Make observing lists with times')"
                            />
                        </form>
                        <p class="mt-2 px-2 text-xs text-gray-500">
                            {{
                                __(
                                    "The objects shown are spread over the nights they are well placed in, each with a time; what does not fit is left out.",
                                )
                            }}
                        </p>
                        @foreach (["name", "minutes", "listNight"] as $field)
                            @error ($field)
                                <p class="mt-1 px-2 text-sm text-red-400">
                                    {{ $message }}
                                </p>
                            @enderror
                        @endforeach
                    </x-card>
                @endcan

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-xs text-gray-400 uppercase">
                            <tr>
                                <th class="px-2 py-1">{{ __("Object") }}</th>
                                <th class="px-2 py-1">{{ __("Type") }}</th>
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
                                <th
                                    class="px-2 py-1"
                                    title="{{ __("The contrast reserve with your instrument there") }}"
                                >
                                    {{
                                        __(
                                            "Visibility",
                                        )
                                    }}
                                </th>
                                <th
                                    class="hidden px-2 py-1 text-right sm:table-cell"
                                    title="{{ __("Optimum detection magnification") }}"
                                >
                                    {{
                                        __(
                                            "Magnification",
                                        )
                                    }}
                                </th>
                                <th
                                    class="hidden px-2 py-1 text-right md:table-cell"
                                    title="{{ __("The nights it is well placed in") }}"
                                >
                                    {{ __("Nights") }}
                                </th>
                                <th class="px-2 py-1">
                                    {{
                                        __(
                                            "New because",
                                        )
                                    }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700">
                            @foreach ($shown["objects"] as $object)
                                <tr wire:key="trip-{{ $object["slug"] }}">
                                    <td class="px-2 py-1">
                                        <a
                                            href="{{ route("object.show", $object["slug"]) }}"
                                            class="text-blue-400 hover:underline"
                                            >{{
                                                $object[
                                                    "name"
                                                ]
                                            }}</a
                                        >
                                    </td>
                                    <td class="px-2 py-1">
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
                                            $object["magnitude"] !== null
                                                ? number_format($object["magnitude"], 1)
                                                : ""
                                        }}
                                    </td>
                                    <td class="px-2 py-1">
                                        <span
                                            @class ([
                                                "text-green-400" => $object["category"] === "very_easy",
                                                "text-green-600" => $object["category"] === "easy",
                                                "text-orange-400" => $object["category"] === "quite_difficult",
                                                "text-orange-600" => $object["category"] === "difficult"
                                            ])
                                            title="{{ __("Contrast reserve") }}: {{ number_format($object["contrast_reserve"], 2) }}"
                                            >{{
                                                __(
                                                    "contrast.reserve.category." . $object["category"],
                                                )
                                            }}</span
                                        >
                                    </td>
                                    <td
                                        class="hidden px-2 py-1 text-right sm:table-cell"
                                    >
                                        {{
                                            $object["magnification"]
                                                ? $object["magnification"] . "×"
                                                : ""
                                        }}
                                    </td>
                                    <td
                                        class="hidden px-2 py-1 text-right md:table-cell"
                                        title="{{ implode(", ", array_map($date, $object["nights"])) }}"
                                    >
                                        {{ count($object["nights"]) }}/{{ count($nights) }}
                                    </td>
                                    <td class="px-2 py-1 text-gray-400">
                                        @if ($object["reason"] === \App\Services\TripPlanner::LOW &&
                                            ($object["home_altitude"] ?? 0) >=
                                                \App\Services\TripPlanner::MIN_ALTITUDE)
                                            {{
                                                __(
                                                    "behind your local horizon at home",
                                                )
                                            }}
                                        @elseif ($object["reason"] === \App\Services\TripPlanner::LOW)
                                            {{
                                                __("at most :altitude° high at home", [
                                                    "altitude" => max(0, $object["home_altitude"] ?? 0),
                                                ])
                                            }}
                                        @else
                                            {{
                                                __(
                                                    "too faint at home",
                                                )
                                            }}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @endif
    @endif
</div>
