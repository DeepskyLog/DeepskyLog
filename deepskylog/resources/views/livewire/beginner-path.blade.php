@php
    $input =
        "rounded-md border-gray-600 bg-gray-900 text-sm text-gray-200 focus:border-blue-500 focus:ring-blue-500";
    $format = fn($date) => \Carbon\Carbon::parse($date)
        ->locale(app()->getLocale())
        ->isoFormat("LL");
    $progress = $goal?->progress();
@endphp

<div class="space-y-6">
    @if (!$goal || $restarting)
        <x-card>
            <div class="space-y-3 px-2 text-sm text-gray-300">
                @if (!$goal)
                    <p class="text-base text-gray-100">{{ __("New to observing? The beginner path picks about 30 of the finest objects of the sky for your telescope and location, and guides you to them one by one.") }}</p>
                @endif
                <p>{{ __("Each object comes with the months it is well placed, where to start looking, the finding tips of other observers and the views in the finder and the eyepiece. The path follows the seasons: it starts with what is up now. Every object you log counts towards your goal.") }}</p>

                @if (!$locations)
                    <p class="text-yellow-400">{{
                        __(
                            "Add the place where you observe first: the path depends on what rises high enough there.",
                        )
                    }}</p>
                    <p><a href="{{ route("location.create") }}" class="text-blue-400 hover:underline">{{
                            __(
                                "Add a location",
                            )
                        }}</a></p>
                @else
                    <form
                        wire:submit="start"
                        class="flex flex-wrap items-end gap-3"
                    >
                        <label class="block max-w-sm text-sm">
                            <span class="mb-1 block text-gray-400">{{
                                __(
                                    "I observe with",
                                )
                            }}</span>
                            <select
                                wire:model="instrument"
                                class="{{ $input }} w-full"
                            >
                                @if ($instruments)
                                    <optgroup
                                        label="{{ __("My instruments") }}"
                                    >
                                        @foreach ($instruments as $value => $label)
                                            <option value="{{ $value }}">
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
                                <optgroup
                                    label="{{ __("A typical instrument") }}"
                                >
                                    @foreach ($starters as $value => $label)
                                        <option value="{{ $value }}">
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </label>
                        <label class="block max-w-sm text-sm">
                            <span
                                class="mb-1 block text-gray-400"
                                >{{ __("Location") }}</span
                            >
                            <select
                                wire:model="location"
                                class="{{ $input }} w-full"
                            >
                                @foreach ($locations as $id => $label)
                                    <option value="{{ $id }}">
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                        <x-button
                            type="submit"
                            primary
                            :label="$goal ? __('Make a new path') : __('Start my beginner path')"
                        />
                        @if ($goal)
                            <x-button
                                type="button"
                                secondary
                                wire:click="$set('restarting', false)"
                                :label="__('Cancel')"
                            />
                        @endif
                    </form>
                    @error ("instrument")
                        <p class="text-red-400">{{ $message }}</p>
                    @enderror
                    @error ("location")
                        <p class="text-red-400">{{ $message }}</p>
                    @enderror
                    @if ($goal)
                        <p class="text-xs text-gray-500">{{
                            __(
                                "A new path replaces the current one; your observations stay.",
                            )
                        }}</p>
                    @endif
                    @if (!$instruments)
                        <p class="text-xs text-gray-500">
                            {!!
                                __(
                                    "No telescope yet? Choose the one closest to yours, or :linkadd your instrument:close_link to get a path for it.",
                                    [
                                        "link" =>
                                            '<a href="' .
                                            e(route("instrument.create")) .
                                            '" class="text-blue-400 hover:underline">',
                                        "close_link" => "</a>",
                                    ],
                                )
                            !!}
                        </p>
                    @endif
                @endif
            </div>
        </x-card>
    @endif

    @if ($goal)
        <x-card>
            <div class="space-y-3 px-2 text-sm text-gray-300">
                <x-observing-goal :goal="$goal" />
                @if ($goal->observingList?->description)
                    <p class="text-gray-400">{{
                        html_entity_decode(
                            $goal->observingList->description,
                            ENT_QUOTES | ENT_HTML5,
                            "UTF-8",
                        )
                    }}</p>
                @endif
                <p class="flex flex-wrap gap-x-4 gap-y-1">
                    <a
                        href="{{ route("observing-list.show", $goal->observingList) }}"
                        class="text-blue-400 hover:underline"
                        >{{
                            __(
                                "Open as observing list",
                            )
                        }}</a
                    >
                    <a
                        href="{{ route("observing-list.eyepiece", $goal->observingList) }}"
                        class="text-blue-400 hover:underline"
                        >{{
                            __(
                                "At the eyepiece",
                            )
                        }}</a
                    >
                    @unless ($restarting)
                        <button
                            type="button"
                            wire:click="$set('restarting', true)"
                            class="text-blue-400 hover:underline"
                        >
                            {{
                                __(
                                    "New telescope or location? Make a new path",
                                )
                            }}
                        </button>
                    @endunless
                </p>
            </div>
        </x-card>

        @if ($progress["status"] === "reached")
            <x-card>
                <p class="px-2 text-base text-green-400">{{ __("Congratulations: you have seen every object of your beginner path! Tonight's best on the home page and the observing lists of other observers show you where to go next.") }}</p>
            </x-card>
        @elseif ($next)
            <section>
                <h3 class="mb-2 ml-3 text-lg font-semibold text-white">
                    {{
                        __("Next: :object", [
                            "object" => $next["name"],
                        ])
                    }}
                </h3>
                <x-beginner-path-step :step="$next" highlight />
            </section>
        @endif

        <section>
            <h3 class="mb-2 ml-3 text-lg font-semibold text-white">
                {{
                    __(
                        "All objects of the path",
                    )
                }}
            </h3>
            <div class="space-y-2">
                @foreach ($steps as $step)
                    @if ($next && $step["number"] === $next["number"])
                        @continue
                    @endif
                    <x-beginner-path-step
                        :step="$step"
                        wire:key="step-{{ $step['number'] }}"
                    />
                @endforeach
            </div>
        </section>
    @endif
</div>
