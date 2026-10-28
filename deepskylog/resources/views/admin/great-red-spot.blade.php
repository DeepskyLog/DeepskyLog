<x-app-layout>
    @php
        $inputClass =
            "block w-full rounded border border-gray-600 bg-gray-900 px-3 py-2 text-gray-100 focus:ring-2 focus:ring-blue-500 focus:outline-none";
        $date = fn($date) => \Carbon\Carbon::parse($date)
            ->locale(app()->getLocale())
            ->isoFormat("LL");
    @endphp
    <div class="mx-auto max-w-screen-lg bg-gray-900 px-6 py-6 sm:px-6 lg:px-8">
        <h1 class="mb-2 text-xl font-semibold text-white">
            {{
                __(
                    "Longitude of the Great Red Spot",
                )
            }}
        </h1>
        <p class="mb-2 text-sm text-gray-300">
            {{
                __(
                    "The moments the Great Red Spot crosses the central meridian of Jupiter (on the page Jupiter and Saturn tonight) follow from its System II longitude. The spot drifts irregularly, about 1.5 to 2° a month, so check it now and then against the measurements of JUPOS or the value Sky & Telescope uses, and enter a new value when it differs: every degree moves the transits by about 1.7 minutes.",
                )
            }}
        </p>
        <p class="mb-6 text-sm text-gray-300">
            {{
                __(
                    "Today the spot is assumed at :longitude°: :start° on :date, drifting :drift° a year.",
                    [
                        "longitude" => number_format($today, 1),
                        "start" => number_format($current["longitude"], 1),
                        "date" => $date($current["measured_on"]),
                        "drift" => number_format($current["drift"], 1),
                    ],
                )
            }}
            <a
                href="https://skyandtelescope.org/observing/interactive-sky-watching-tools/transit-times-of-jupiters-great-red-spot/"
                class="text-blue-400 hover:underline"
                rel="noopener"
                target="_blank"
                >Sky &amp; Telescope</a
            >
            &middot;
            <a
                href="http://jupos.privat.t-online.de/rGrs.htm"
                class="text-blue-400 hover:underline"
                rel="noopener"
                target="_blank"
                >JUPOS</a
            >
        </p>

        <form
            method="POST"
            action="{{ route("admin.great-red-spot.store") }}"
            class="space-y-4 text-sm text-gray-200"
        >
            @csrf
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>
                    <label
                        for="longitude"
                        class="mb-1 block font-medium text-gray-400"
                        >{{
                            __(
                                "System II longitude (°)",
                            )
                        }}</label
                    >
                    <input
                        id="longitude"
                        name="longitude"
                        type="number"
                        step="0.1"
                        min="0"
                        max="359.9"
                        required
                        value="{{ old("longitude", number_format($today, 1, ".", "")) }}"
                        class="{{ $inputClass }}"
                    />
                    @error ("longitude")
                        <p class="mt-1 text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label
                        for="measured_on"
                        class="mb-1 block font-medium text-gray-400"
                        >{{
                            __(
                                "Date of the measurement",
                            )
                        }}</label
                    >
                    <input
                        id="measured_on"
                        name="measured_on"
                        type="date"
                        required
                        max="{{ today()->toDateString() }}"
                        value="{{ old("measured_on", today()->toDateString()) }}"
                        class="{{ $inputClass }}"
                    />
                    @error ("measured_on")
                        <p class="mt-1 text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label
                        for="drift"
                        class="mb-1 block font-medium text-gray-400"
                        >{{
                            __(
                                "Drift (° a year)",
                            )
                        }}</label
                    >
                    <input
                        id="drift"
                        name="drift"
                        type="number"
                        step="0.1"
                        min="-60"
                        max="60"
                        required
                        value="{{ old("drift", number_format($current["drift"], 1, ".", "")) }}"
                        class="{{ $inputClass }}"
                    />
                    @error ("drift")
                        <p class="mt-1 text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <div>
                <label
                    for="source"
                    class="mb-1 block font-medium text-gray-400"
                    >{{ __("Source") }}</label
                >
                <input
                    id="source"
                    name="source"
                    type="text"
                    maxlength="255"
                    value="{{ old("source") }}"
                    placeholder="{{ __("e.g. JUPOS, Sky & Telescope") }}"
                    class="{{ $inputClass }}"
                />
                @error ("source")
                    <p class="mt-1 text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <button
                type="submit"
                class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase hover:bg-blue-500"
            >
                {{ __("Save") }}
            </button>
        </form>

        <h2 class="mt-8 mb-2 text-lg font-semibold text-white">
            {{
                __(
                    "Measurements",
                )
            }}
        </h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-300">
                <thead class="text-gray-400">
                    <tr>
                        <th class="py-2 pr-4 font-normal">{{ __("Date") }}</th>
                        <th class="py-2 pr-4 text-right font-normal">
                            {{
                                __(
                                    "Longitude",
                                )
                            }}
                        </th>
                        <th class="py-2 pr-4 text-right font-normal">
                            {{ __("Drift") }}
                        </th>
                        <th class="py-2 pr-4 font-normal">
                            {{ __("Source") }}
                        </th>
                        <th class="py-2 pr-4 font-normal">
                            {{
                                __(
                                    "Entered by",
                                )
                            }}
                        </th>
                        <th class="py-2 font-normal"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @foreach ($positions as $position)
                        <tr>
                            <td class="py-2 pr-4 whitespace-nowrap">
                                {{
                                    $date(
                                        $position->measured_on,
                                    )
                                }}
                            </td>
                            <td class="py-2 pr-4 text-right">
                                {{
                                    number_format(
                                        $position->longitude,
                                        1,
                                    )
                                }}°
                            </td>
                            <td class="py-2 pr-4 text-right">
                                {{
                                    number_format(
                                        $position->drift,
                                        1,
                                    )
                                }}°
                            </td>
                            <td class="py-2 pr-4">{{ $position->source }}</td>
                            <td class="py-2 pr-4">
                                {{
                                    $position->user
                                        ?->name
                                }}
                            </td>
                            <td class="py-2 text-right">
                                <form
                                    method="POST"
                                    action="{{ route("admin.great-red-spot.destroy", $position) }}"
                                    onsubmit="return confirm(@js(__('Remove this measurement?')))"
                                >
                                    @csrf
                                    @method ("DELETE")
                                    <button
                                        type="submit"
                                        class="text-red-400 hover:underline"
                                    >
                                        {{ __("Remove") }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
