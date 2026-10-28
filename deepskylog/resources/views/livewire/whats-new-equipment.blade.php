@php
    $input =
        "rounded-md border-gray-600 bg-gray-900 text-sm text-gray-200 focus:border-blue-500 focus:ring-blue-500";
@endphp

<div class="space-y-6" @if (!$result && !$unavailable && !$failed) wire:poll.3s @endif>
    <p class="text-sm text-gray-400">
        @if ($gain->kind === \App\Services\EquipmentGain::EYEPIECE)
            {{
                __("The deep-sky objects the :eyepiece lets you see with your :instrument that you could not see with it before, calculated from their contrast reserve. Objects you have already seen are left out.", [
                    "eyepiece" => $gain->equipmentName(),
                    "instrument" => $gain->instrumentName(),
                ])
            }}
        @else
            {{
                __("The deep-sky objects the :instrument lets you see that you could not see with any of your other instruments, calculated from their contrast reserve. Objects you have already seen are left out.", [
                    "instrument" => $gain->equipmentName(),
                ])
            }}
        @endif
    </p>

    @if ($locations)
        <label class="block max-w-sm text-sm">
            <span class="mb-1 block text-gray-400">{{ __("Location") }}</span>
            <select wire:model.live="locationId" class="{{ $input }} w-full">
                @foreach ($locations as $id => $label)
                    <option value="{{ $id }}">{{ $label }}</option>
                @endforeach
            </select>
        </label>
    @endif

    @if ($comparable)
        <fieldset class="text-sm">
            <legend class="mb-1 text-gray-400">
                {{ __("Compare with") }}
                <button type="button" wire:click="compareAll" class="ml-2 text-xs text-blue-400 hover:underline">{{ __("all") }}</button>
                ·
                <button type="button" wire:click="compareNone" class="text-xs text-blue-400 hover:underline">{{ __("none") }}</button>
            </legend>
            <div class="grid grid-cols-1 gap-x-4 gap-y-1 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($comparable as $id => $label)
                    <label class="flex cursor-pointer items-center gap-2 text-gray-300" wire:key="compare-{{ $id }}">
                        <input type="checkbox" value="{{ $id }}" wire:model.live="compare" class="rounded border-gray-600 bg-gray-900" />
                        {{ $label }}
                    </label>
                @endforeach
            </div>
        </fieldset>
    @endif

    @if ($unavailable)
        <x-card>
            <p class="px-2 text-yellow-400">{{ __($unavailable) }}</p>
            <p class="mt-2 px-2 text-sm">
                <a href="{{ route("location.index") }}" class="text-blue-400 hover:underline">{{ __("Locations") }}</a>
                ·
                <a href="{{ route("instrument.index") }}" class="text-blue-400 hover:underline">{{ __("Instruments") }}</a>
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
                                "No new objects: everything this equipment shows at :location you could already see, or have seen.",
                                ["location" => $gain->locationName()],
                            )
                        }}
                    </p>
                @else
                    <p class="text-base text-gray-100">
                        {{
                            trans_choice(
                                "{1} One object you have not seen becomes visible at :location.|[2,*] :count objects you have not seen become visible at :location.",
                                $shown["total"],
                                ["count" => number_format($shown["total"]), "location" => $gain->locationName()],
                            )
                        }}
                        @if ($shown["total"] > count($shown["objects"]))
                            {{
                                __("The :count most observed are shown.", [
                                    "count" => count($shown["objects"]),
                                ])
                            }}
                        @endif
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
                                __("An object counts as visible with a contrast reserve above :reserve (difficult or better), under a sky of :sqm mag/arcsec² and dimmed by the atmosphere at the highest altitude it reaches.", [
                                    "reserve" => \App\Services\EquipmentGain::VISIBLE_ABOVE,
                                    "sqm" => number_format($result["sqm"], 2),
                                ])
                            }}
                        </p>
                        <p>
                            @if ($result["compared"])
                                {{ __("Compared with: :equipment.", ["equipment" => implode(", ", $result["compared"])]) }}
                            @else
                                {{ __("Compared with no other instrument: all objects it shows are listed.") }}
                            @endif
                        </p>
                        <p>
                            {{ __("Double stars, stars, quasars and supernovae are not included.") }}
                        </p>
                    </div>
                </details>
            </div>
        </x-card>

        @if ($shown["objects"])
            @can ("create", \App\Models\ObservingList::class)
                <x-card>
                    <form wire:submit="create" class="flex flex-wrap items-end gap-3 px-2">
                        <label class="block grow text-sm">
                            <span class="mb-1 block text-gray-400">{{ __("Name of the list") }}</span>
                            <input
                                type="text"
                                wire:model="name"
                                maxlength="250"
                                placeholder="{{ __("New with :equipment", ["equipment" => $gain->equipmentName()]) }}"
                                class="{{ $input }} w-full"
                            />
                        </label>
                        <label class="flex items-center gap-2 text-sm text-gray-300">
                            <input type="checkbox" wire:model="public" class="rounded border-gray-600 bg-gray-900" />
                            {{ __("Public") }}
                        </label>
                        <x-button type="submit" primary :label="trans_choice('{1} Make an observing list of this object|[2,*] Make an observing list of these :count objects', count($shown['objects']), ['count' => count($shown['objects'])])" />
                    </form>
                    @error ("name")
                        <p class="mt-1 px-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </x-card>
            @endcan

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs uppercase text-gray-400">
                        <tr>
                            <th class="px-2 py-1">{{ __("Object") }}</th>
                            <th class="px-2 py-1">{{ __("Type") }}</th>
                            <th class="hidden px-2 py-1 md:table-cell">{{ __("Constellation") }}</th>
                            <th class="hidden px-2 py-1 text-right sm:table-cell">{{ __("Mag") }}</th>
                            <th class="px-2 py-1">{{ __("Visibility") }}</th>
                            <th class="hidden px-2 py-1 text-right sm:table-cell" title="{{ __("Optimum detection magnification") }}">{{ __("Magnification") }}</th>
                            <th class="hidden px-2 py-1 text-right md:table-cell" title="{{ __("The best contrast reserve with the equipment you had") }}">{{ __("Previously") }}</th>
                            <th class="hidden px-2 py-1 text-right md:table-cell" title="{{ __("Number of observations in DeepskyLog") }}">{{ __("Observations") }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @foreach ($shown["objects"] as $object)
                            <tr wire:key="gain-{{ $object["slug"] }}">
                                <td class="px-2 py-1">
                                    <a href="{{ route("object.show", $object["slug"]) }}" class="text-blue-400 hover:underline">{{ $object["name"] }}</a>
                                </td>
                                <td class="px-2 py-1">{{ __($object["type"]) }}</td>
                                <td class="hidden px-2 py-1 md:table-cell">{{ $object["constellation"] ? __($object["constellation"]) : "" }}</td>
                                <td class="hidden px-2 py-1 text-right sm:table-cell">{{ $object["magnitude"] !== null ? number_format($object["magnitude"], 1) : "" }}</td>
                                <td class="px-2 py-1">
                                    <span
                                        @class([
                                            "text-green-400" => $object["category"] === "very_easy",
                                            "text-green-600" => $object["category"] === "easy",
                                            "text-orange-400" => $object["category"] === "quite_difficult",
                                            "text-orange-600" => $object["category"] === "difficult",
                                        ])
                                        title="{{ __("Contrast reserve") }}: {{ number_format($object["contrast_reserve"], 2) }}"
                                    >{{ __("contrast.reserve.category." . $object["category"]) }}</span>
                                </td>
                                <td class="hidden px-2 py-1 text-right sm:table-cell">{{ $object["magnification"] ? $object["magnification"] . "×" : "" }}</td>
                                <td class="hidden px-2 py-1 text-right text-gray-400 md:table-cell">{{ $object["before"] !== null ? number_format($object["before"], 2) : "–" }}</td>
                                <td class="hidden px-2 py-1 text-right md:table-cell">{{ number_format($object["observations"]) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @endif
</div>
