<x-app-layout>
    @php
        $typeNames = \App\Models\DeepskyType::pluck("name", "code");
        $cards = \App\Services\Observations\ObservationCardData::instance();
        $cards->preload($observations->values());
    @endphp
    <x-slot name="header">
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <h2 class="text-xl leading-tight font-semibold text-gray-200">
                {{
                    __(
                        "Aperture records",
                    )
                }}
            </h2>

            <nav
                class="inline-flex flex-wrap rounded-lg border border-gray-700 bg-gray-950 p-1"
                aria-label="{{ __('Aperture') }}"
            >
                @foreach (array_keys(\App\Services\Observations\ApertureRecords::LIMITS)
                    as $option)
                    <a
                        href="{{ route('records.aperture', ['aperture' => $option]) }}"
                        class="rounded-md px-3 py-2 text-sm font-medium transition {{ $option === $limit ? 'bg-sky-500 text-gray-950' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}"
                        @if ($option === $limit) aria-current="page" @endif
                    >
                        {{
                            \App\Services\Observations\ApertureRecords::limitLabel(
                                $option,
                                $inches,
                            )
                        }}
                    </a>
                @endforeach
            </nav>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-screen sm:px-6 lg:px-8">
            <div
                class="overflow-hidden bg-gray-900 p-4 shadow-sm sm:rounded-lg"
            >
                <p class="mb-4 text-sm text-gray-400">
                    {{
                        $limit === 0
                            ? __("The faintest deep-sky objects reported seen with the naked eye.")
                            : __(
                                "The faintest deep-sky objects reported seen with an aperture of at most :size, each with the observation that saw it with the smallest aperture.",
                                [
                                    "size" => $inches
                                        ? \App\Services\Observations\ApertureRecords::LIMITS[
                                                $limit
                                            ][1] . "″"
                                        : $limit . " mm",
                                ],
                            )
                    }} {{
                        __(
                            "Observations the suspect-observation check doubts, and objects fainter than the aperture can show under a very dark sky, are left out; so are double stars, variable stars, supernovae, clusters and asterisms, whose magnitude does not say how hard they are to see.",
                        )
                    }}
                </p>

                @if (empty($objects))
                    <p class="text-gray-300">{{
                        __(
                            "No objects were reported seen with this aperture yet.",
                        )
                    }}</p>
                @else
                    <div class="grid gap-6 xl:grid-cols-3">
                        <section class="xl:col-span-2">
                            <h3 class="mb-2 text-lg font-semibold text-white">
                                {{
                                    __(
                                        "Faintest objects",
                                    )
                                }}
                            </h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm text-gray-300">
                                    <thead
                                        class="text-left text-xs text-gray-400 uppercase"
                                    >
                                        <tr>
                                            <th class="px-2 py-1">#</th>
                                            <th class="px-2 py-1">
                                                {{ __("Object") }}
                                            </th>
                                            <th class="px-2 py-1">
                                                {{ __("Type") }}
                                            </th>
                                            <th class="px-2 py-1 text-right">
                                                {{
                                                    __(
                                                        "Magnitude",
                                                    )
                                                }}
                                            </th>
                                            <th class="px-2 py-1">
                                                {{ __("Aperture") }}
                                            </th>
                                            <th class="px-2 py-1">
                                                {{ __("Observer") }}
                                            </th>
                                            <th class="px-2 py-1">
                                                {{ __("Date") }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-800">
                                        @foreach ($objects as $row)
                                            @php
                                                $observation = $observations[$row["observation_id"]] ?? null;
                                                $user = $cards->user($row["observer"]);
                                            @endphp
                                            <tr>
                                                <td
                                                    class="px-2 py-1 text-gray-400"
                                                >
                                                    {{ $row["rank"] }}
                                                </td>
                                                <td class="px-2 py-1">
                                                    <a
                                                        href="{{ route('object.show', ['slug' => $row['slug']]) }}"
                                                        class="text-indigo-400 hover:text-indigo-300"
                                                        >{{
                                                            html_entity_decode(
                                                                $row["name"],
                                                                ENT_QUOTES | ENT_HTML5,
                                                                "UTF-8",
                                                            )
                                                        }}</a
                                                    >
                                                </td>
                                                <td class="px-2 py-1">
                                                    {{
                                                        __(
                                                            $typeNames[$row["type"]] ?? $row["type"],
                                                        )
                                                    }}
                                                </td>
                                                <td
                                                    class="px-2 py-1 text-right"
                                                >
                                                    {{
                                                        number_format(
                                                            $row["mag"],
                                                            1,
                                                        )
                                                    }}
                                                </td>
                                                <td class="px-2 py-1">
                                                    {{
                                                        \App\Services\Observations\ApertureRecords::apertureLabel(
                                                            $row["aperture_mm"],
                                                            $row["naked_eye"],
                                                            $inches,
                                                        )
                                                    }}
                                                </td>
                                                <td class="px-2 py-1">
                                                    @if ($user)
                                                        <a
                                                            href="{{ route('observer.show', $user->slug) }}"
                                                            class="hover:text-white"
                                                            >{{ $user->name }}</a
                                                        >
                                                    @else
                                                        {{
                                                            $row[
                                                                "observer"
                                                            ]
                                                        }}
                                                    @endif
                                                </td>
                                                <td
                                                    class="px-2 py-1 whitespace-nowrap"
                                                >
                                                    @if ($url = $observation?->detailUrl($user?->slug))
                                                        <a
                                                            href="{{ $url }}"
                                                            class="text-indigo-400 hover:text-indigo-300"
                                                            >{{
                                                                $row[
                                                                    "observed_on"
                                                                ]
                                                            }}</a
                                                        >
                                                    @else
                                                        {{
                                                            $row[
                                                                "observed_on"
                                                            ]
                                                        }}
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <section>
                            <h3 class="mb-2 text-lg font-semibold text-white">
                                {{
                                    __(
                                        "Observers",
                                    )
                                }}
                            </h3>
                            <p class="mb-2 text-xs text-gray-400">{{
                                __(
                                    "By the faintest object they saw with this aperture.",
                                )
                            }}</p>
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-sm text-gray-300">
                                    <thead
                                        class="text-left text-xs text-gray-400 uppercase"
                                    >
                                        <tr>
                                            <th class="px-2 py-1">#</th>
                                            <th class="px-2 py-1">
                                                {{ __("Observer") }}
                                            </th>
                                            <th class="px-2 py-1">
                                                {{ __("Object") }}
                                            </th>
                                            <th class="px-2 py-1 text-right">
                                                {{
                                                    __(
                                                        "Magnitude",
                                                    )
                                                }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-800">
                                        @foreach ($observers as $row)
                                            @php
                                                $observation = $observations[$row["observation_id"]] ?? null;
                                                $user = $cards->user($row["observer"]);
                                                $url = $observation?->detailUrl($user?->slug);
                                            @endphp
                                            <tr>
                                                <td
                                                    class="px-2 py-1 text-gray-400"
                                                >
                                                    {{ $row["rank"] }}
                                                </td>
                                                <td class="px-2 py-1">
                                                    @if ($user)
                                                        <a
                                                            href="{{ route('observer.show', $user->slug) }}"
                                                            class="hover:text-white"
                                                            >{{ $user->name }}</a
                                                        >
                                                    @else
                                                        {{
                                                            $row[
                                                                "observer"
                                                            ]
                                                        }}
                                                    @endif
                                                </td>
                                                <td class="px-2 py-1">
                                                    <a
                                                        href="{{ $url ?? route('object.show', ['slug' => $row['slug']]) }}"
                                                        class="text-indigo-400 hover:text-indigo-300"
                                                        >{{
                                                            html_entity_decode(
                                                                $row["name"],
                                                                ENT_QUOTES | ENT_HTML5,
                                                                "UTF-8",
                                                            )
                                                        }}</a
                                                    >
                                                    <span
                                                        class="text-xs text-gray-400"
                                                        >({{
                                                            \App\Services\Observations\ApertureRecords::apertureLabel(
                                                                $row["aperture_mm"],
                                                                $row["naked_eye"],
                                                                $inches,
                                                            )
                                                        }})</span
                                                    >
                                                </td>
                                                <td
                                                    class="px-2 py-1 text-right"
                                                >
                                                    {{
                                                        number_format(
                                                            $row["mag"],
                                                            1,
                                                        )
                                                    }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
