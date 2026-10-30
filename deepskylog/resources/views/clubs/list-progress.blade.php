<x-app-layout>
    @php
        $clubName = html_entity_decode($club->name, ENT_QUOTES | ENT_HTML5, "UTF-8");
        $listName = html_entity_decode($list->name, ENT_QUOTES | ENT_HTML5, "UTF-8");
        $filterLabels = [
            "all" => __("All objects"),
            "nobody" => __("Seen by nobody"),
            "not_me" => __("Not seen by me"),
        ];
    @endphp
    <div class="mx-auto max-w-screen-xl bg-gray-900 px-6 py-6 sm:px-6 lg:px-8">
        <p class="mb-2 text-sm">
            <a
                href="{{ route("clubs.show", $club) }}"
                class="text-blue-400 hover:underline"
                >&larr; {{ $clubName }}</a
            >
        </p>

        <div class="rounded-lg bg-gray-800 p-4">
            <h1 class="text-xl font-semibold text-white">
                {{ __("Who has seen what") }}:
                <a
                    href="{{ route("observing-list.show", $list) }}"
                    class="text-blue-400 hover:underline"
                    >{{ $listName }}</a
                >
            </h1>
            <p class="mt-1 text-sm text-gray-400">
                {{
                    __(
                        ":seen of the :total objects have been seen by a member of :club.",
                        [
                            "seen" => $seenByAnyone,
                            "total" => $total,
                            "club" => $clubName,
                        ],
                    )
                }}
                @if ($membersEdit)
                    {{
                        __(
                            "The members edit this list together.",
                        )
                    }}
                @endif
            </p>
            @if ($canEdit)
                <p class="mt-2 text-sm">
                    <a
                        href="{{ route("observing-list.show", $list) }}"
                        class="text-blue-400 hover:underline"
                        >{{
                            __(
                                "Add or remove objects on the page of the list",
                            )
                        }}</a
                    >
                </p>
            @endif
        </div>

        {{-- The members, most objects seen first --}}
        <div class="mt-4 rounded-lg bg-gray-800 p-4 text-sm">
            <h2 class="mb-2 text-lg font-semibold text-white">
                {{ __("Members") }}
            </h2>
            <ul class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($members as $member)
                    @php
                        $percent = $total > 0 ? (int) round((100 * $member["seen"]) / $total) : 0;
                    @endphp
                    <li>
                        <div class="flex justify-between gap-2">
                            <a
                                href="{{ route("observer.show", $member["slug"]) }}"
                                class="text-gray-200 hover:underline"
                                >{{
                                    $member[
                                        "name"
                                    ]
                                }}</a
                            >
                            <span class="text-gray-400"
                                >{{ $member["seen"] }} / {{ $total }}</span
                            >
                        </div>
                        <div class="mt-1 h-1.5 rounded bg-gray-700">
                            <div
                                class="h-1.5 rounded bg-green-500"
                                style="width: {{ $percent }}%"
                            ></div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- The objects with a tick per member who saw it --}}
        <div class="mt-4 rounded-lg bg-gray-800 p-4 text-sm">
            <div class="mb-3 flex flex-wrap items-center gap-2">
                <h2 class="mr-auto text-lg font-semibold text-white">
                    {{ __("Objects") }}
                </h2>
                @foreach ($filterLabels as $key => $label)
                    <a
                        href="{{ route("clubs.lists.progress", ["club" => $club, "list" => $list, "show" => $key]) }}"
                        class="rounded px-2 py-1 text-xs font-medium {{ $filter === $key ? "bg-blue-700 text-white" : "bg-gray-700 text-gray-300 hover:bg-gray-600" }}"
                        >{{ $label }}</a
                    >
                @endforeach
            </div>

            @if ($objects->isEmpty())
                <p class="text-gray-400">{{
                    __(
                        "No objects.",
                    )
                }}</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left">
                        <thead>
                            <tr
                                class="border-b border-gray-700 text-xs text-gray-400"
                            >
                                <th class="py-1 pr-3">{{ __("Object") }}</th>
                                <th class="py-1 pr-3">{{ __("Added by") }}</th>
                                <th class="py-1 pr-3 text-center">
                                    {{ __("Seen by") }}
                                </th>
                                @foreach ($members as $member)
                                    <th
                                        class="px-1 py-1 text-center font-normal whitespace-nowrap"
                                        title="{{ $member["name"] }}"
                                    >
                                        {{
                                            \Illuminate\Support\Str::limit(
                                                $member["name"],
                                                10,
                                                "…",
                                            )
                                        }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700">
                            @foreach ($objects as $object)
                                <tr>
                                    <td class="py-1 pr-3 whitespace-nowrap">
                                        @if ($object["slug"])
                                            <a
                                                href="{{ route("object.show", $object["slug"]) }}"
                                                class="text-blue-400 hover:underline"
                                                >{{
                                                    $object[
                                                        "name"
                                                    ]
                                                }}</a
                                            >
                                        @else
                                            <span class="text-gray-200">{{
                                                $object[
                                                    "name"
                                                ]
                                            }}</span>
                                        @endif
                                    </td>
                                    <td
                                        class="py-1 pr-3 whitespace-nowrap text-gray-400"
                                    >
                                        {{
                                            $object["added_by"] ??
                                                ""
                                        }}
                                    </td>
                                    <td
                                        class="py-1 pr-3 text-center text-gray-300"
                                    >
                                        {{
                                            count(
                                                $object["seen_by"],
                                            )
                                        }}
                                    </td>
                                    @foreach ($members as $member)
                                        <td class="px-1 py-1 text-center">
                                            @if (in_array($member["id"], $object["seen_by"], true))
                                                <span
                                                    class="text-green-400"
                                                    title="{{ __(":name has seen it", ["name" => $member["name"]]) }}"
                                                    >✓</span
                                                >
                                            @else
                                                <span class="text-gray-600"
                                                    >·</span
                                                >
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $objects->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
