@php
    $input = "rounded-md border-gray-600 bg-gray-900 text-sm text-gray-200 focus:border-blue-500 focus:ring-blue-500";
@endphp

<div class="space-y-6">
    <x-card>
        <div class="space-y-2 px-2 text-sm text-gray-300">
            <p class="text-base text-gray-100">{{ __("Count the stars you see inside a figure of bright stars, and DeepskyLog tells you the faintest stars you can see: your naked-eye limiting magnitude, measured instead of guessed.") }}</p>
            <p>{{ __("Let your eyes get used to the dark for at least 20 minutes. Count every star you see inside the dashed outline, including the bright ones, but not the corner stars. Take your time, and use averted vision for the faintest ones. Count twice and take the number you are surest of.") }}</p>
        </div>
    </x-card>

    @if (!$locations)
        <x-card>
            <div class="space-y-2 px-2 text-sm text-gray-300">
                <p class="text-yellow-400">{{ __("Add the place where you observe first: the figures depend on what stands high enough there.") }}</p>
                <p><a href="{{ route("location.create") }}" class="text-blue-400 hover:underline">{{ __("Add a location") }}</a></p>
            </div>
        </x-card>
    @else
        <x-card>
            <div class="flex flex-wrap items-end gap-3 px-2">
                <label class="block text-sm">
                    <span class="mb-1 block text-gray-400">{{ __("Location") }}</span>
                    <select wire:model.live="locationId" class="{{ $input }}">
                        @foreach ($locations as $id => $label)
                            <option value="{{ $id }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="block text-sm">
                    <span class="mb-1 block text-gray-400">{{ __("Moment (local time at the location)") }}</span>
                    <input type="datetime-local" wire:model.live.debounce.500ms="moment" class="{{ $input }}"
                        value="{{ $moment !== '' ? $moment : $localMoment?->format('Y-m-d\TH:i') }}" />
                </label>
                @if ($moment !== '')
                    <button type="button" wire:click="now" class="rounded bg-gray-700 px-3 py-2 text-xs font-medium text-white hover:bg-gray-600">{{ __("Now") }}</button>
                @endif
            </div>
            @if ($location)
                <p class="mt-2 px-2 text-xs text-gray-400">
                    {{ $localMoment->locale(app()->getLocale())->isoFormat("LLL") }} ({{ $localMoment->getTimezone()->getName() }})
                    @if ($locationNelm)
                        · {{ __("Limiting magnitude of the location now: :nelm", ["nelm" => number_format($locationNelm, 1)]) }}
                    @endif
                </p>
            @endif
        </x-card>

        @if ($location)
            @if (!$visible)
                <x-card>
                    <p class="px-2 text-sm text-yellow-400">{{ __("No figure stands high enough at that moment. Choose another moment.") }}</p>
                </x-card>
            @else
                <div class="grid gap-6 lg:grid-cols-2">
                    <x-card>
                        <div class="space-y-3 px-2">
                            <label class="block text-sm">
                                <span class="mb-1 block text-gray-400">{{ __("Figure") }}</span>
                                <select wire:model.live="area" class="{{ $input }} w-full">
                                    @foreach ($visible as $option)
                                        <option value="{{ $option["key"] }}" @selected($option["key"] === $areaKey)>
                                            {{ __($option["label"]) }} — {{ __(":altitude° high", ["altitude" => number_format($option["altitude"], 0)]) }}
                                        </option>
                                    @endforeach
                                </select>
                            </label>
                            @if ($chart)
                                <div class="mx-auto max-w-sm overflow-hidden rounded border border-gray-700">{!! $chart !!}</div>
                                <p class="text-xs text-gray-400">{{ __("As you see it at that moment, facing the figure with the zenith up. Only the corner stars and the bright stars around it are drawn, so the chart does not tell you what to look for.") }}</p>
                            @endif
                        </div>
                    </x-card>

                    <x-card>
                        <form wire:submit="save" class="space-y-3 px-2 text-sm text-gray-300">
                            <label class="block">
                                <span class="mb-1 block text-gray-400">{{ __("Stars I see inside the figure") }}</span>
                                <input type="number" min="1" max="{{ \App\Livewire\SkyStarCount::MAX_COUNT }}" wire:model.live.debounce.300ms="count" class="{{ $input }} w-32" />
                                @error("count")
                                    <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                                @enderror
                            </label>

                            @if ($result)
                                <div class="rounded border border-gray-700 bg-gray-900/60 p-3">
                                    <p class="text-2xl font-semibold text-white">{{ __("NELM :nelm", ["nelm" => number_format($result["nelm"], 1)]) }}</p>
                                    <p class="mt-1 text-xs text-gray-400">
                                        {{ __("The faintest stars you counted are of magnitude :magnitude in the figure, :altitude° high; the air there dims them by :extinction, so at the zenith you would see stars of magnitude :nelm.", [
                                            "magnitude" => number_format($result["area_magnitude"], 1),
                                            "altitude" => number_format($result["altitude"], 0),
                                            "extinction" => number_format($result["extinction"], 2),
                                            "nelm" => number_format($result["nelm"], 1),
                                        ]) }}
                                    </p>
                                    @if (!$result["reliable"])
                                        <p class="mt-1 text-xs text-yellow-400">{{ __("The figure is rather low: the dimming by the air there is uncertain. A higher figure gives a better result.") }}</p>
                                    @endif
                                    @if ($twilight)
                                        <p class="mt-1 text-xs text-yellow-400">{{ __("The sky is not completely dark yet at that moment: the result is that of the twilight sky.") }}</p>
                                    @endif
                                </div>

                                <label class="flex items-center gap-2">
                                    <input type="checkbox" wire:model="applyToLocation" class="rounded border-gray-600 bg-gray-900 text-blue-600" />
                                    <span>{{ __("Also use it as the limiting magnitude of :location", ["location" => html_entity_decode($location->name, ENT_QUOTES | ENT_HTML5, "UTF-8")]) }}</span>
                                </label>
                                <button type="submit" class="rounded bg-blue-600 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-500">{{ __("Save the measurement") }}</button>
                            @elseif ($count !== null)
                                <p class="text-xs text-yellow-400">{{ __("Count at least one star.") }}</p>
                            @endif

                            @if ($savedId)
                                <p class="text-sm text-green-400">{{ __("Saved. It counts in the sky history of the location.") }}</p>
                                <p><a href="{{ route("observation.create") }}" class="text-blue-400 hover:underline">{{ __("Add an observation") }}</a></p>
                            @endif
                        </form>
                    </x-card>
                </div>
            @endif

            @if ($measurements->isNotEmpty())
                <x-card>
                    <div class="px-2">
                        <h3 class="mb-2 text-base font-semibold text-white">{{ __("My counts at this location") }}</h3>
                        <ul class="divide-y divide-gray-700 text-sm text-gray-300">
                            @foreach ($measurements as $measurement)
                                <li class="flex items-center justify-between gap-3 py-1.5" wire:key="measurement-{{ $measurement->id }}">
                                    <span>
                                        {{ $measurement->measured_at->copy()->setTimezone($localMoment->getTimezone())->locale(app()->getLocale())->isoFormat("LLL") }}
                                        · {{ __(\App\Services\StarCountAreas::AREAS[$measurement->area]["label"] ?? $measurement->area) }}
                                        · {{ trans_choice(":count star|:count stars", $measurement->star_count, ["count" => $measurement->star_count]) }}
                                    </span>
                                    <span class="flex items-center gap-3">
                                        <span class="font-semibold text-white">{{ number_format($measurement->nelm, 1) }}</span>
                                        <button type="button" wire:click="deleteMeasurement({{ $measurement->id }})" wire:confirm="{{ __("Delete this measurement?") }}" class="text-gray-400 hover:text-red-400" title="{{ __("Delete") }}">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            <span class="sr-only">{{ __("Delete") }}</span>
                                        </button>
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                        <p class="mt-2 text-xs"><a href="{{ route("location.show", [$location->user?->slug, $location->slug]) }}" class="text-blue-400 hover:underline">{{ __("Sky history of the location") }}</a></p>
                    </div>
                </x-card>
            @endif
        @endif
    @endif
</div>
