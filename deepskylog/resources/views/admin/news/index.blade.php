<x-app-layout>
    <div class="mx-auto max-w-screen-xl bg-gray-900 px-6 py-6 sm:px-6 lg:px-8">
        <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
            <h1 class="text-xl font-semibold text-white">
                {{
                    __(
                        "Manage the news",
                    )
                }}
            </h1>
            <a
                href="{{ route("admin.news.create") }}"
                class="rounded bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-600"
                >{{
                    __(
                        "Write a news item",
                    )
                }}</a
            >
        </div>
        <p class="mb-4 text-sm text-gray-300">
            {{
                __(
                    "Written items are translated into the other languages of the site by Google. The coming eclipses, close conjunctions, oppositions, greatest elongations, meteor showers and bright comets are added every night and published a week before the event; hide the ones you do not want.",
                )
            }}
        </p>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-300">
                <thead class="text-gray-400">
                    <tr>
                        <th class="py-2 pr-4 font-normal">{{ __("Title") }}</th>
                        <th class="py-2 pr-4 font-normal">
                            {{
                                __(
                                    "Published",
                                )
                            }}
                        </th>
                        <th class="py-2 pr-4 font-normal">{{ __("Until") }}</th>
                        <th class="py-2 pr-4 font-normal">
                            {{ __("Status") }}
                        </th>
                        <th class="py-2 font-normal"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @forelse ($items as $item)
                        <tr>
                            <td class="py-2 pr-4">
                                <a
                                    href="{{ route("news.show", $item) }}"
                                    class="text-white hover:underline"
                                    >{{ $item->displayTitle() }}</a
                                >
                                <div class="text-xs text-gray-500">
                                    {{
                                        $item->isAutomatic()
                                            ? __("Automatic")
                                            : $item->author?->name ?? ""
                                    }}
                                </div>
                            </td>
                            <td class="py-2 pr-4 whitespace-nowrap">
                                {{
                                    $item->published_at->format(
                                        "Y-m-d H:i",
                                    )
                                }}
                            </td>
                            <td class="py-2 pr-4 whitespace-nowrap">
                                {{
                                    $item->expires_at?->format(
                                        "Y-m-d H:i",
                                    ) ?? "–"
                                }}
                            </td>
                            <td class="py-2 pr-4">
                                @if ($item->hidden)
                                    <span
                                        class="text-gray-500"
                                        >{{ __("Hidden") }}</span
                                    >
                                @elseif ($item->published_at > now())
                                    <span class="text-yellow-400">{{
                                        __(
                                            "Scheduled",
                                        )
                                    }}</span>
                                @elseif ($item->expires_at && $item->expires_at <= now())
                                    <span
                                        class="text-gray-400"
                                        >{{ __("Expired") }}</span
                                    >
                                @else
                                    <span class="text-green-400">{{
                                        __(
                                            "Published",
                                        )
                                    }}</span>
                                @endif
                            </td>
                            <td class="py-2 text-right whitespace-nowrap">
                                @if (!$item->isAutomatic())
                                    <a
                                        href="{{ route("admin.news.edit", $item) }}"
                                        class="rounded px-2 py-0.5 text-gray-300 hover:bg-gray-700 hover:text-white"
                                        >{{ __("Edit") }}</a
                                    >
                                @endif
                                <form
                                    method="POST"
                                    action="{{ route("admin.news.hidden", $item) }}"
                                    class="inline"
                                >
                                    @csrf
                                    <button
                                        type="submit"
                                        class="rounded px-2 py-0.5 text-gray-300 hover:bg-gray-700 hover:text-white"
                                    >
                                        {{
                                            $item->hidden
                                                ? __("Show")
                                                : __("Hide")
                                        }}
                                    </button>
                                </form>
                                @if (!$item->isAutomatic())
                                    <form
                                        method="POST"
                                        action="{{ route("admin.news.destroy", $item) }}"
                                        class="inline"
                                        onsubmit="return confirm('{{ __("Delete this news item?") }}')"
                                    >
                                        @csrf
                                        @method ("DELETE")
                                        <button
                                            type="submit"
                                            class="rounded px-2 py-0.5 text-red-400 hover:bg-gray-700"
                                        >
                                            {{ __("Delete") }}
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-2 text-gray-400">
                                {{
                                    __(
                                        "There is no news yet.",
                                    )
                                }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $items->links() }}</div>
    </div>
</x-app-layout>
