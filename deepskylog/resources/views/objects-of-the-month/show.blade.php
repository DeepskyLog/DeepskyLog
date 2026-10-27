<x-app-layout>
    <div class="mx-auto max-w-screen-lg bg-gray-900 px-6 py-6 sm:px-6 lg:px-8">
        <p class="mb-2 text-sm">
            <a
                href="{{ route("objects-of-the-month.index") }}"
                class="text-blue-400 hover:underline"
                >&larr; {{
                    __(
                        "All objects of the month",
                    )
                }}</a
            >
        </p>

        <div class="space-y-6">
            <section class="rounded-lg bg-gray-800 p-4">
                <h1 class="text-xl font-semibold text-white">
                    {{
                        __("Object of the month :period", [
                            "period" => $item->periodLabel(),
                        ])
                    }}
                </h1>
                <p class="text-gray-300">{{ $item->subtitle() }}</p>
                <p class="mt-2 text-sm text-gray-400">
                    {{
                        __(
                            "Chosen by the object of the month team of Astroforum.",
                        )
                    }}
                    <a
                        href="{{ $item->url }}"
                        class="text-blue-400 hover:underline"
                        rel="noopener"
                        target="_blank"
                        >{{
                            __(
                                "Read about it and join the discussion on Astroforum",
                            )
                        }} &rarr;</a
                    >
                </p>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-sm text-gray-300">
                        <thead
                            class="text-left text-xs text-gray-400 uppercase"
                        >
                            <tr>
                                <th class="py-1 pr-2">{{ __("Object") }}</th>
                                <th class="py-1 pr-2 text-right">
                                    {{
                                        __(
                                            "Observers",
                                        )
                                    }}
                                </th>
                                <th class="py-1 pr-2 text-right">
                                    {{
                                        __(
                                            "Observations",
                                        )
                                    }}
                                </th>
                                <th class="py-1 text-right">
                                    {{ __("Drawings") }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700">
                            @foreach ($item->objects ?? [] as $object)
                                @php ($count = $counts[ \App\Models\ObjectOfTheMonth::key($object) ] ?? ["observers" => 0, "observations" => 0, "drawings" => 0])
                                <tr>
                                    <td class="py-1 pr-2">
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
                                    <td class="py-1 pr-2 text-right">
                                        {{
                                            $count[
                                                "observers"
                                            ]
                                        }}
                                    </td>
                                    <td class="py-1 pr-2 text-right">
                                        {{
                                            $count[
                                                "observations"
                                            ]
                                        }}
                                    </td>
                                    <td class="py-1 text-right">
                                        {{
                                            $count["drawings"] ?:
                                                ""
                                        }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="mt-2 text-xs text-gray-500">
                    {{
                        __(
                            "Observations made from :from to :until.",
                            [
                                "from" => $item->starts_on->locale(app()->getLocale())->isoFormat("LL"),
                                "until" => $item->ends_on->locale(app()->getLocale())->isoFormat("LL"),
                            ],
                        )
                    }}
                </p>

                <div class="mt-4 flex flex-wrap gap-3">
                    @auth
                        @can ("create", \App\Models\ObservingList::class)
                            @if ($item->isPublished())
                                <form
                                    method="POST"
                                    action="{{ route("objects-of-the-month.observing-list", $item) }}"
                                >
                                    @csrf
                                    <button
                                        type="submit"
                                        class="inline-flex items-center rounded-md bg-blue-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase hover:bg-blue-500"
                                    >
                                        {{
                                            __(
                                                "Make an observing list",
                                            )
                                        }}
                                    </button>
                                </form>
                            @endif
                        @endcan
                    @endauth
                    @can ("manage_news", \App\Models\User::class)
                        <a
                            href="{{ route("admin.objects-of-the-month.edit", $item) }}"
                            class="inline-flex items-center rounded-md bg-gray-700 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase hover:bg-gray-600"
                            >{{ __("Edit") }}</a
                        >
                    @endcan
                </div>
            </section>

            @if ($drawings->isNotEmpty())
                <section class="rounded-lg bg-gray-800 p-4">
                    <h2 class="mb-2 text-lg font-semibold text-white">
                        {{ __("Drawings") }}
                    </h2>
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                        @foreach ($drawings as $drawing)
                            <x-sketch-thumbnail :observation="$drawing" />
                        @endforeach
                    </div>
                </section>
            @endif

            <section class="rounded-lg bg-gray-800 p-4">
                <h2 class="mb-2 text-lg font-semibold text-white">
                    {{
                        __(
                            "Observations",
                        )
                    }}
                </h2>
                @if ($observations->isEmpty())
                    <p class="text-gray-300">{{
                        __(
                            "Nobody has recorded an observation of these objects in these months yet.",
                        )
                    }}</p>
                    @auth
                        <p class="mt-2 text-sm"><a href="{{ route("observation.create") }}" class="text-blue-400 hover:underline">{{
                                __(
                                    "Add an observation",
                                )
                            }}</a></p>
                    @endauth
                @else
                    <ul class="divide-y divide-gray-700">
                        @foreach ($observations as $observation)
                            <x-observation-row :observation="$observation" />
                        @endforeach
                    </ul>
                    <div class="mt-4">{{ $observations->links() }}</div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
