@php
    $input =
        "rounded-md border-gray-600 bg-gray-900 text-sm text-gray-200 focus:border-blue-500 focus:ring-blue-500";
    $size = fn($mm) => $inches
        ? rtrim(rtrim(number_format($mm / 25.4, 1, ".", ""), "0"), ".") . '"'
        : round($mm) . " " . __("mm");
@endphp

<div
    class="space-y-6"
    @if (!$result && !$unavailable && !$failed) wire:poll.3s @endif
>
    <p class="text-sm text-gray-400">
        {{
            __(
                "The deep-sky objects you have seen only with a much smaller instrument, or never drew, that your telescope shows much better, calculated from their contrast reserve. The largest gain first: these are worth a second look.",
            )
        }}
    </p>

    <div class="flex flex-wrap items-end gap-3">
        @if ($instruments)
            <label class="block max-w-sm text-sm">
                <span class="mb-1 block text-gray-400">{{
                    __(
                        "Instrument",
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
                <span
                    class="mb-1 block text-gray-400"
                    >{{ __("Location") }}</span
                >
                <select wire:model.live="location" class="{{ $input }} w-full">
                    @foreach ($locations as $id => $label)
                        <option value="{{ $id }}">{{ $label }}</option>
                    @endforeach
                </select>
            </label>
        @endif

        <label class="block max-w-sm text-sm">
            <span class="mb-1 block text-gray-400">{{ __("Show") }}</span>
            <select wire:model.live="reason" class="{{ $input }} w-full">
                <option value="">
                    {{
                        __(
                            "All objects worth a second look",
                        )
                    }}
                </option>
                <option value="{{ \App\Services\RevisitObjects::SMALLER }}">
                    {{
                        __(
                            "Only seen with a much smaller instrument",
                        )
                    }}
                </option>
                <option value="{{ \App\Services\RevisitObjects::UNDRAWN }}">
                    {{
                        __(
                            "Never drawn",
                        )
                    }}
                </option>
            </select>
        </label>
    </div>

    @if ($unavailable)
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
    @elseif ($failed)
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
                                "No objects to revisit: none of the objects you have already seen is worth a second look with your :instrument at :location.",
                                [
                                    "instrument" => $revisit->instrumentName(),
                                    "location" => $revisit->locationName(),
                                ],
                            )
                        }}
                    </p>
                @else
                    <p class="text-base text-gray-100">
                        {{
                            trans_choice(
                                "{1} One object you have already seen is worth a second look with your :instrument at :location.|[2,*] :count objects you have already seen are worth a second look with your :instrument at :location.",
                                $shown["total"],
                                [
                                    "count" => number_format($shown["total"]),
                                    "instrument" => $revisit->instrumentName(),
                                    "location" => $revisit->locationName(),
                                ],
                            )
                        }}
                        @if ($shown["total"] > count($shown["objects"]))
                            {{
                                __(
                                    "The :count with the largest gain are shown.",
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
                                    "For every object you have seen, the contrast reserve with your telescope is compared with the best one of the instruments you saw it with, both with your eyepieces, under a sky of :sqm mag/arcsec² and dimmed by the atmosphere at the highest altitude it reaches.",
                                    [
                                        "sqm" => number_format($result["sqm"], 2),
                                    ],
                                )
                            }}
                        </p>
                        <p>
                            {{
                                __(
                                    "An object is listed when your telescope shows it with a contrast reserve at least :gain higher and above :reserve (difficult or better), and you saw it only with instruments of at most :percent% of its aperture, or never drew it.",
                                    [
                                        "gain" => \App\Services\RevisitObjects::MIN_GAIN,
                                        "reserve" => \App\Services\EquipmentGain::VISIBLE_ABOVE,
                                        "percent" => round(100 / \App\Services\RevisitObjects::APERTURE_RATIO),
                                    ],
                                )
                            }}
                        </p>
                        <p>
                            {{
                                __(
                                    "Double stars, stars, quasars and supernovae are not included.",
                                )
                            }}
                        </p>
                    </div>
                </details>
            </div>
        </x-card>

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
                                maxlength="250"
                                placeholder="{{ $revisit->defaultListName() }}"
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
                            :label="
                                trans_choice(
        '{1} Make an observing list of this object|[2,*] Make an observing list of these :count objects',
        count($shown['objects']),
        ['count' => count($shown['objects'])],
    )
                            "
                        />
                    </form>
                    @error ("name")
                        <p class="mt-1 px-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
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
                                title="{{ __("The contrast reserve with your telescope") }}"
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
                                title="{{ __("The best contrast reserve with the instruments you saw it with") }}"
                            >
                                {{
                                    __(
                                        "Previously",
                                    )
                                }}
                            </th>
                            <th class="px-2 py-1 text-right">
                                {{ __("Gain") }}
                            </th>
                            <th class="px-2 py-1">
                                {{
                                    __(
                                        "Seen with",
                                    )
                                }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @foreach ($shown["objects"] as $object)
                            <tr wire:key="revisit-{{ $object["slug"] }}">
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
                                    class="hidden px-2 py-1 text-right text-gray-400 md:table-cell"
                                >
                                    {{
                                        number_format(
                                            $object["before"],
                                            2,
                                        )
                                    }}
                                </td>
                                <td class="px-2 py-1 text-right text-green-400">
                                    +{{
                                        number_format(
                                            $object["gain"],
                                            2,
                                        )
                                    }}
                                </td>
                                <td class="px-2 py-1 text-gray-400">
                                    {{
                                        __("at most :aperture", [
                                            "aperture" => $size($object["aperture"]),
                                        ])
                                    }}
                                    @if (!$object["drawn"])
                                        ·
                                        {{
                                            __(
                                                "never drawn",
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
</div>
