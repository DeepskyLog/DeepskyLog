@php
    $input =
        "rounded-md border-gray-600 bg-gray-900 text-sm text-gray-200 focus:border-blue-500 focus:ring-blue-500";
    $decode = fn($text) => html_entity_decode(
        (string) $text,
        ENT_QUOTES | ENT_HTML5,
        "UTF-8",
    );
    $distance = fn(?int $km) => $km === null
        ? __("within :km km", ["km" => \App\Services\ObservingPartners::NEAR_KM])
        : __("about :km km", ["km" => $km]);
    $format = fn($date) => \Carbon\Carbon::parse($date)
        ->locale(app()->getLocale())
        ->isoFormat("LL");
@endphp

<div class="space-y-6">
    <x-card>
        <div class="space-y-3 px-2 text-sm text-gray-300">
            <p class="text-base text-gray-100">{{ __("Looking for someone to observe with? Observers who turn this on can find each other when their standard locations are near.") }}</p>
            <p>{{ __("Your location is never shown: the others only see roughly how far away you are (the distance between squares of a quarter of a degree, about 25 km), your name, your standard telescope, your note and your clubs. Only observers who turned this on themselves see you, observers you blocked never do, and they contact you through DeepskyLog messages.") }}</p>

            @if (!$home)
                <p class="text-yellow-400">{{
                    __(
                        "Choose a standard location with coordinates first: partners are found near it.",
                    )
                }}</p>
                <p><a href="/location" class="text-blue-400 hover:underline">{{
                    __(
                        "My locations",
                    )
                }}</a></p>
            @else
                <form wire:submit="save" class="space-y-3">
                    <x-toggle
                        label="{{ __('Let observers near me who look for partners find me') }}"
                        name="partnerSearch"
                        id="partnerSearch"
                        wire:model="partnerSearch"
                    />
                    <label class="block max-w-2xl text-sm">
                        <span class="mb-1 block text-gray-400">{{
                            __(
                                "A note for the observers near you (optional)",
                            )
                        }}</span>
                        <input
                            type="text"
                            wire:model="note"
                            maxlength="{{ \App\Services\ObservingPartners::NOTE_LENGTH }}"
                            placeholder="{{ __('E.g. I observe on weekends with a 30 cm Dobsonian and can drive to a dark site.') }}"
                            class="{{ $input }} w-full"
                        />
                    </label>
                    @error ("note")
                        <p class="text-red-400">{{ $message }}</p>
                    @enderror
                    <x-button type="submit" primary :label="__('Save')" />
                    @if (session("partners_saved"))
                        <span class="ml-2 text-green-400">{{
                            session(
                                "partners_saved",
                            )
                        }}</span>
                    @endif
                </form>
                <p class="text-xs text-gray-500">{{
                    __("Standard location: :location", [
                        "location" => $decode($home->name),
                    ])
                }}</p>
            @endif
        </div>
    </x-card>

    @if ($home)
        <div
            class="flex flex-wrap items-center gap-3 px-2 text-sm text-gray-300"
        >
            <label for="radius">{{ __("Within") }}</label>
            <select id="radius" wire:model.live="radius" class="{{ $input }}">
                @foreach (\App\Services\ObservingPartners::RADII as $km)
                    <option value="{{ $km }}">{{ $km }} km</option>
                @endforeach
            </select>
        </div>

        <section>
            <h3 class="mb-2 ml-3 text-lg font-semibold text-white">
                {{
                    __(
                        "Observers near you",
                    )
                }}
            </h3>
            <x-card>
                <div class="px-2 text-sm text-gray-300">
                    @if (!auth()->user()->partner_search)
                        <p>
                            {{
                                trans_choice(
                                    "{0} No observer within :radius km looks for partners yet.|{1} One observer within :radius km looks for partners.|[2,*] :count observers within :radius km look for partners.",
                                    $hiddenCount,
                                    ["count" => $hiddenCount, "radius" => $radius],
                                )
                            }} {{
                                __(
                                    "Turn on being found to see them: they only see you once you do.",
                                )
                            }}
                        </p>
                    @elseif ($partners->isEmpty())
                        <p>{{ __("No observer within :radius km looks for partners yet. Try a larger distance, or look at the starparties and clubs below.", ["radius" => $radius]) }}</p>
                    @else
                        <ul class="divide-y divide-gray-700">
                            @foreach ($partners as $partner)
                                @php
                                    $observer = $partner["user"];
                                    $stats = $activity[mb_strtolower($observer->username)] ?? null;
                                    $instrument = $observer->standardInstrument;
                                @endphp
                                <li
                                    class="flex flex-wrap items-start justify-between gap-3 py-3"
                                >
                                    <div class="min-w-0 space-y-1">
                                        <p>
                                            <a
                                                href="{{ route("observer.show", $observer->slug) }}"
                                                class="font-medium text-blue-400 hover:underline"
                                                >{{ $observer->name }}</a
                                            >
                                            <span class="text-gray-400"
                                                >· {{
                                                    $distance(
                                                        $partner["distance"],
                                                    )
                                                }}</span
                                            >
                                        </p>
                                        @if ($observer->partner_note)
                                            <p class="text-gray-200">{{ $observer->partner_note }}</p>
                                        @endif
                                        <p class="text-xs text-gray-400">
                                            @if ($instrument)
                                                {{
                                                    __("Telescope: :instrument", [
                                                        "instrument" => $decode($instrument->fullName()),
                                                    ])
                                                }} ·
                                            @endif
                                            @if ($stats)
                                                {{
                                                    trans_choice(
                                                        "{1} 1 observation|[2,*] :count observations",
                                                        (int) $stats->observations,
                                                        ["count" => (int) $stats->observations],
                                                    ) .
                                                        ($stats->last_observed
                                                            ? ", " .
                                                                __("last on :date", [
                                                                    "date" => $format($stats->last_observed),
                                                                ])
                                                            : "")
                                                }}
                                            @else
                                                {{
                                                    __(
                                                        "No observations yet",
                                                    )
                                                }}
                                            @endif
                                        </p>
                                        @if ($observer->clubs->isNotEmpty())
                                            <p class="text-xs text-gray-400">
                                                {{ __("Clubs") }}:
                                                @foreach ($observer->clubs as $club)
                                                    <a
                                                        href="{{ route("clubs.show", $club) }}"
                                                        class="text-blue-400 hover:underline"
                                                        >{{
                                                            $decode(
                                                                $club->name,
                                                            )
                                                        }}</a
                                                    >
                                                    @if (!$loop->last) , @endif
                                                @endforeach
                                            </p>
                                        @endif
                                    </div>
                                    <a
                                        href="{{ route("messages.create", ["to" => $observer->username]) }}"
                                        class="rounded-md bg-blue-700 px-3 py-1.5 text-sm text-white hover:bg-blue-600"
                                        >{{
                                            __(
                                                "Send a message",
                                            )
                                        }}</a
                                    >
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </x-card>
        </section>

        <section>
            <h3 class="mb-2 ml-3 text-lg font-semibold text-white">
                {{
                    __(
                        "Starparties near you",
                    )
                }}
            </h3>
            <x-card>
                <div class="px-2 text-sm text-gray-300">
                    @if ($starParties->isEmpty())
                        <p>
                            {{
                                __(
                                    "No coming starparty within :radius km.",
                                    ["radius" => $radius],
                                )
                            }}
                            <a
                                href="{{ route("star-parties.index") }}"
                                class="text-blue-400 hover:underline"
                                >{{
                                    __(
                                        "All starparties",
                                    )
                                }}</a
                            >
                        </p>
                    @else
                        <ul class="divide-y divide-gray-700">
                            @foreach ($starParties as $row)
                                <li class="py-2">
                                    <a
                                        href="{{ route("star-parties.show", $row["party"]) }}"
                                        class="font-medium text-blue-400 hover:underline"
                                        >{{
                                            $decode(
                                                $row["party"]->name,
                                            )
                                        }}</a
                                    >
                                    <span class="text-gray-400"
                                        >· {{ $row["party"]->dateRange() }} · {{
                                            $decode(
                                                $row["party"]->location_name,
                                            )
                                        }} · {{
                                            __("about :km km", [
                                                "km" => (int) round($row["km"]),
                                            ])
                                        }}</span
                                    >
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </x-card>
        </section>

        @if ($clubs->isNotEmpty())
            <section>
                <h3 class="mb-2 ml-3 text-lg font-semibold text-white">
                    {{
                        __(
                            "Clubs of the observers near you",
                        )
                    }}
                </h3>
                <x-card>
                    <ul
                        class="divide-y divide-gray-700 px-2 text-sm text-gray-300"
                    >
                        @foreach ($clubs as $row)
                            <li class="py-2">
                                <a
                                    href="{{ route("clubs.show", $row["club"]) }}"
                                    class="font-medium text-blue-400 hover:underline"
                                    >{{
                                        $decode(
                                            $row["club"]->name,
                                        )
                                    }}</a
                                >
                                @if ($row["club"]->placeLabel() !== "")
                                    <span class="text-gray-400"
                                        >· {{ $row["club"]->placeLabel() }}</span
                                    >
                                @endif
                                <span class="text-gray-400"
                                    >· {{
                                        trans_choice(
                                            "{1} 1 observer near you|[2,*] :count observers near you",
                                            $row["partners"],
                                            ["count" => $row["partners"]],
                                        )
                                    }}</span
                                >
                            </li>
                        @endforeach
                    </ul>
                </x-card>
            </section>
        @endif
    @endif
</div>
