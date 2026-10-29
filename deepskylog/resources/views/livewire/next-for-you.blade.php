@php
    $input =
        "rounded-md border-gray-600 bg-gray-900 text-sm text-gray-200 focus:border-blue-500 focus:ring-blue-500";
@endphp

<div
    class="space-y-6"
    @if ((!$result && !$unavailable && !$failed) || ($shown["pending"] ?? false)) wire:poll.3s @endif
>
    <p class="text-sm text-gray-400">
        {{
            __(
                "Deep-sky objects you have not seen yet that the observers who saw the same objects as you saw too, and that your standard telescope shows at your standard location. They follow your taste, not only what is popular.",
            )
        }}
    </p>

    @if ($unavailable)
        <x-card>
            <p class="px-2 text-yellow-400">{{ __($unavailable) }}</p>
            <p class="mt-2 px-2 text-sm">
                <a
                    href="{{ route("location.index") }}"
                    class="text-blue-400 hover:underline"
                    >{{ __("Locations") }}</a
                >
                ·
                <a
                    href="{{ route("instrument.index") }}"
                    class="text-blue-400 hover:underline"
                    >{{ __("Instruments") }}</a
                >
                ·
                <a
                    href="{{ route("beginner-path") }}"
                    class="text-blue-400 hover:underline"
                    >{{ __("Beginner path") }}</a
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
                <x-button secondary wire:click="retry">{{ __("Try again") }}</x-button>
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
                                "No suggestions for your :instrument at :location yet.",
                                [
                                    "instrument" => $next->instrumentName(),
                                    "location" => $next->locationName(),
                                ],
                            )
                        }}
                    </p>
                @else
                    <p class="text-base text-gray-100">
                        {{
                            trans_choice(
                                "{1} One object for your :instrument at :location.|[2,*] :count objects for your :instrument at :location.",
                                $shown["total"],
                                [
                                    "count" => number_format($shown["total"]),
                                    "instrument" => $next->instrumentName(),
                                    "location" => $next->locationName(),
                                ],
                            )
                        }}
                        @if ($shown["total"] > count($shown["objects"]))
                            {{
                                __("The best :count are shown.", [
                                    "count" => count($shown["objects"]),
                                ])
                            }}
                        @endif
                    </p>
                @endif
                @if ($shown["pending"])
                    <p class="text-yellow-400">
                        {{
                            __(
                                "The visibility of some objects is still being calculated; more may appear. The page updates by itself.",
                            )
                        }}
                    </p>
                @endif
                <details>
                    <summary
                        class="cursor-pointer text-xs text-gray-500 select-none hover:text-gray-300"
                    >
                        {{ __("How is this list made?") }}
                    </summary>
                    <div class="mt-1 space-y-1 text-xs">
                        <p>
                            {{
                                __(
                                    "For every object, DeepskyLog knows which objects were seen most by the same observers, compared with how many observers saw each (an observer who saw thousands of objects counts less). The :count objects you have seen each bring their neighbours; the objects most of them bring come first, with the objects you saw that bring them most.",
                                    ["count" => number_format($result["seen"])],
                                )
                            }}
                        </p>
                        <p>
                            {{
                                __(
                                    "An object is listed when you have not seen it and your telescope shows it with a contrast reserve above :reserve (difficult or better), or splits it for a double star. The neighbours are calculated again every week.",
                                    [
                                        "reserve" => \App\Services\EquipmentGain::VISIBLE_ABOVE,
                                    ],
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
                            <span class="mb-1 block text-gray-400">{{ __("Name of the list") }}</span>
                            <input
                                type="text"
                                wire:model="name"
                                maxlength="250"
                                placeholder="{{ $next->defaultListName() }}"
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
                                {{ __("Constellation") }}
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
                                {{ __("Visibility") }}
                            </th>
                            <th
                                class="hidden px-2 py-1 text-right sm:table-cell"
                                title="{{ __("Optimum detection magnification") }}"
                            >
                                {{ __("Magnification") }}
                            </th>
                            <th class="px-2 py-1">
                                {{ __("Because you saw") }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @foreach ($shown["objects"] as $object)
                            <tr wire:key="next-{{ $object["slug"] }}">
                                <td class="px-2 py-1">
                                    <a
                                        href="{{ route("object.show", $object["slug"]) }}"
                                        class="text-blue-400 hover:underline"
                                        >{{ $object["name"] }}</a
                                    >
                                </td>
                                <td class="px-2 py-1">
                                    {{ __($object["type"]) }}
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
                                    @if (isset($object["verdict"]))
                                        <span class="text-green-600">{{ \App\Services\DoubleStarResolvability::label($object["verdict"]) }}</span>
                                    @else
                                        <span
                                            @class ([
                                                "text-green-400" => $object["category"] === "very_easy",
                                                "text-green-600" => $object["category"] === "easy",
                                                "text-orange-400" => $object["category"] === "quite_difficult",
                                                "text-orange-600" => $object["category"] === "difficult"
                                            ])
                                            title="{{ __("Contrast reserve") }}: {{ number_format($object["contrast_reserve"], 2) }}"
                                            >{{ __("contrast.reserve.category." . $object["category"]) }}</span
                                        >
                                    @endif
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
                                <td class="px-2 py-1 text-gray-400">
                                    @foreach ($object["because"] as $seen)
                                        <a
                                            href="{{ route("object.show", $seen["slug"]) }}"
                                            class="hover:text-blue-400 hover:underline"
                                            >{{ $seen["name"] }}</a
                                        >@if (!$loop->last), @endif
                                    @endforeach
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @endif
</div>
