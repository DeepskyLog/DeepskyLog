@php
    $input = "w-full rounded-md border-gray-600 bg-gray-900 text-sm text-gray-200 focus:border-blue-500 focus:ring-blue-500";
    $choice = "flex items-center gap-2 text-sm text-gray-300 cursor-pointer";
    $typeLabels = [
        "galaxies" => __("Galaxies and galaxy clusters"),
        "open_clusters" => __("Open clusters"),
        "globular_clusters" => __("Globular clusters"),
        "planetary_nebulae" => __("Planetary nebulae"),
        "nebulae" => __("Bright nebulae"),
        "dark_nebulae" => __("Dark nebulae"),
        "supernova_remnants" => __("Supernova remnants"),
        "asterisms" => __("Asterisms"),
        "double_stars" => __("Double stars"),
        "planets" => __("Planets"),
        "comets" => __("Comets"),
    ];
@endphp

<div class="space-y-6">
    <p class="text-sm text-gray-400">
        {{ __("Answer a few questions and DeepskyLog chooses the objects that are well placed during that night, for your standard telescope and location when you have set them, taking the Moon into account. You see the objects before the list is made.") }}
    </p>

    {{-- The location and telescope the objects are chosen for --}}
    <x-card>
        <div class="space-y-1 px-2 text-sm">
            <div>
                <span class="inline-block w-24 text-gray-400">{{ __("Location") }}:</span>
                @if ($equipment["location"])
                    <span class="font-semibold text-white">{{ $equipment["location"] }}</span>
                @else
                    <span class="text-yellow-400">{{ $equipment["latitude"] !== null ? __("No standard location: a latitude of :latitude° is used.", ["latitude" => $equipment["latitude"]]) : __("No location") }}</span>
                @endif
                <a href="{{ route("location.index") }}" class="ml-1 text-blue-400 hover:underline">{{ __("Change") }}</a>
            </div>
            <div>
                <span class="inline-block w-24 text-gray-400">{{ __("Telescope") }}:</span>
                @if ($equipment["instrument"])
                    <span class="font-semibold text-white">{{ $equipment["instrument"] }}</span>
                @else
                    <span class="text-yellow-400">{{ __("No standard telescope at this location: the most observed objects are chosen, without their visibility.") }}</span>
                @endif
                <a href="{{ route("instrument.index") }}" class="ml-1 text-blue-400 hover:underline">{{ __("Change") }}</a>
            </div>
        </div>
    </x-card>

    <x-card>
        <div class="space-y-6 px-2">
            {{-- 1. The night --}}
            <section>
                <h3 class="mb-2 font-semibold text-white">{{ __("1. Which night do you want to observe?") }}</h3>
                <div class="max-w-xs">
                    <x-datetime-picker without-time="true" without-timezone="true" name="night" wire:model.live="night" value="{{ $night }}" :clearable="false" />
                </div>
                @error("night")
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-400">
                    @if ($dark === false)
                        {{ __("It does not get dark during this night at your location.") }}
                    @elseif ($dark)
                        @if ($dark["twilight"] === "astronomical")
                            {{ __("Dark from :from to :until.", $dark) }}
                        @else
                            {{ __("It does not get completely dark: from :from to :until the sky stays in nautical twilight.", $dark) }}
                        @endif
                    @endif
                </p>
            </section>

            {{-- 2. The part of the night --}}
            <section>
                <h3 class="mb-2 font-semibold text-white">{{ __("2. When during the night?") }}</h3>
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                    @foreach (["night" => __("The whole night"), "evening" => __("In the evening"), "midnight" => __("Around midnight"), "morning" => __("In the morning"), "custom" => __("From and until a time")] as $value => $label)
                        <label class="{{ $choice }}">
                            <input type="radio" wire:model.live="period" value="{{ $value }}" class="text-blue-600" />
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
                @if ($period === "custom")
                    {{-- WireUI time pickers, in 12 hours for observers who use inches, as the time of an observation --}}
                    @php $twelveHours = (bool) (auth()->user()?->showInches ?? false); @endphp
                    <div class="mt-2 flex flex-wrap items-center gap-2 text-sm text-gray-300">
                        {{ __("From") }}
                        <div class="w-36">
                            <x-time-picker name="from" wire:model.live="from" :military-time="! $twelveHours" without-seconds="true" />
                        </div>
                        {{ __("until") }}
                        <div class="w-36">
                            <x-time-picker name="until" wire:model.live="until" :military-time="! $twelveHours" without-seconds="true" />
                        </div>
                    </div>
                    @error("from")
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                    @error("until")
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                @endif
                <p class="mt-1 text-xs text-gray-500">{{ __("The evening, midnight and morning are each a third of the dark hours.") }}</p>
            </section>

            {{-- 3. The kind of objects --}}
            <section>
                <h3 class="mb-2 font-semibold text-white">{{ __("3. Which kind of objects?") }}</h3>
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 md:grid-cols-4">
                    @foreach ($typeLabels as $value => $label)
                        <label class="{{ $choice }}">
                            <input type="checkbox" wire:model.live="typeGroups" value="{{ $value }}" class="rounded text-blue-600" />
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
                <p class="mt-1 text-xs text-gray-500">{{ __("Choose nothing for all kinds of objects but double stars. Planets and comets come on top of the number of deep-sky objects.") }}</p>
            </section>

            {{-- 4. Catalogs and lists first --}}
            <section>
                <h3 class="mb-2 font-semibold text-white">{{ __("4. Which catalogs or observing lists come first?") }}</h3>
                <x-searchable-multiselect wire-model="sources" :options="$sourceOptions" :placeholder="__('Search catalogs and observing lists…')" />
                <div class="mt-2 flex flex-wrap gap-4">
                    <label class="{{ $choice }}">
                        <input type="radio" wire:model.live="sourceMode" value="first" class="text-blue-600" />
                        {{ __("Their objects first, then the other objects") }}
                    </label>
                    <label class="{{ $choice }}">
                        <input type="radio" wire:model.live="sourceMode" value="only" class="text-blue-600" />
                        {{ __("Only their objects") }}
                    </label>
                </div>
                <p class="mt-1 text-xs text-gray-500">{{ __("Without a choice, the objects observed most by all observers come first.") }}</p>
            </section>

            {{-- 5. Seen objects --}}
            <section>
                <h3 class="mb-2 font-semibold text-white">{{ __("5. Objects you have seen already?") }}</h3>
                <div class="flex flex-wrap gap-4">
                    @foreach (["none" => __("Only objects I have not seen yet"), "few" => __("A few old favourites"), "any" => __("It does not matter")] as $value => $label)
                        <label class="{{ $choice }}">
                            <input type="radio" wire:model.live="seen" value="{{ $value }}" class="text-blue-600" />
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
                <p class="mt-1 text-xs text-gray-500">{{ __("Seen means seen with your standard telescope; objects you only saw with other instruments count as new.") }}</p>
            </section>

            {{-- 6. Difficulty --}}
            <section>
                <h3 class="mb-2 font-semibold text-white">{{ __("6. How difficult?") }}</h3>
                <div class="flex flex-wrap gap-4">
                    @foreach (["mixed" => __("Mostly easy, a third challenging"), "easy" => __("Easy objects"), "challenging" => __("Challenging objects")] as $value => $label)
                        <label class="{{ $choice }}">
                            <input type="radio" wire:model.live="difficulty" value="{{ $value }}" class="text-blue-600" />
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
                <p class="mt-1 text-xs text-gray-500">{{ __("The difficulty follows from the contrast reserve with your standard telescope, under the sky of that night.") }}</p>
            </section>

            {{-- 7. How many --}}
            <section>
                <h3 class="mb-2 font-semibold text-white">{{ __("7. How many deep-sky objects?") }}</h3>
                <input type="number" wire:model.live.debounce.500ms="count" min="{{ \App\Services\TonightsBest::MIN_COUNT }}" max="{{ \App\Services\TonightsBest::MAX_COUNT }}" class="{{ $input }} w-28" />
                @error("count")
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </section>

            <div>
                <button type="button" wire:click="preview" wire:loading.attr="disabled" class="inline-flex items-center rounded-md bg-gray-700 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-200 hover:bg-gray-600">
                    <span wire:loading.remove wire:target="preview">{{ __("Show the objects") }}</span>
                    <span wire:loading wire:target="preview">{{ __("Choosing the objects…") }}</span>
                </button>
            </div>
        </div>
    </x-card>

    @if ($result)
        <x-card>
            <div class="space-y-1 px-2 text-sm text-gray-400">
                <p>
                    @if ($result["mode"] === "personal")
                        {{ __("For your :instrument from :location.", ["instrument" => $result["instrument"], "location" => $result["location"]]) }}
                    @else
                        {{ __("Without a standard telescope and location, the most observed objects are chosen, for a latitude of :latitude°.", ["latitude" => $result["latitude"]]) }}
                    @endif
                    @if ($result["period"])
                        {{ __("Well placed between :from and :until.", $result["period"]) }}
                    @endif
                </p>
                @if ($result["moon"])
                    <p>{{ __("The Moon (:fraction% illuminated) is above the horizon from :from to :until; the times and the visibility take the brighter sky into account.", ["fraction" => (int) round($result["moon"]["fraction"] * 100), "from" => $result["moon"]["from"], "until" => $result["moon"]["until"]]) }}</p>
                @endif
                @if ($result["pending"])
                    <p class="text-yellow-400">{{ __("The visibility of more objects is being calculated for your equipment; try again in a few minutes for a more complete choice.") }}</p>
                @endif
            </div>

            @if (empty($result["objects"]))
                <p class="mt-4 px-2 text-gray-300">{{ __("No objects match these answers. Choose another part of the night, other objects or fewer sources.") }}</p>
            @else
                <x-tonights-best-table :best="$result" />

                <div class="mt-6 space-y-3 px-2">
                    <h3 class="font-semibold text-white">{{ __("Make the observing list") }}</h3>
                    <div class="flex flex-wrap items-center gap-4">
                        <input type="text" wire:model="name" placeholder="{{ $defaultName }}" maxlength="250" class="{{ $input }} max-w-md" />
                        <label class="{{ $choice }}">
                            <input type="checkbox" wire:model="public" class="rounded text-blue-600" />
                            {{ __("Public") }}
                        </label>
                    </div>
                    @error("name")
                        <p class="text-sm text-red-400">{{ $message }}</p>
                    @enderror
                    @can("create", \App\Models\ObservingList::class)
                        <button type="button" wire:click="create" wire:loading.attr="disabled" class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-blue-500">
                            {{ __("Make the observing list (:count objects)", ["count" => count($result["objects"])]) }}
                        </button>
                    @else
                        <p class="text-sm text-gray-400">{{ __("Switch to the Observer role to create a list") }}</p>
                    @endcan
                </div>
            @endif
        </x-card>
    @endif
</div>
