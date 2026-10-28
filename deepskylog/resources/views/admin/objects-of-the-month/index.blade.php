<x-app-layout>
    <div class="mx-auto max-w-screen-xl bg-gray-900 px-6 py-6 sm:px-6 lg:px-8">
        <h1 class="mb-2 text-xl font-semibold text-white">
            {{
                __(
                    "Objects of the month",
                )
            }}
        </h1>
        <p class="mb-4 text-sm text-gray-300">
            {{
                __(
                    "Every day DeepskyLog reads the new threads of the objects of the month of Astroforum and proposes the objects named in them. A thread is only shown once its objects are confirmed: the first post also names objects only to compare or to find the way, so check them.",
                )
            }}
        </p>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-300">
                <thead class="text-gray-400">
                    <tr>
                        <th class="py-2 pr-4 font-normal">
                            {{ __("Months") }}
                        </th>
                        <th class="py-2 pr-4 font-normal">
                            {{
                                __(
                                    "Thread on Astroforum",
                                )
                            }}
                        </th>
                        <th class="py-2 pr-4 font-normal">
                            {{ __("Objects") }}
                        </th>
                        <th class="py-2 pr-4 font-normal">
                            {{ __("Status") }}
                        </th>
                        <th class="py-2 font-normal"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @forelse ($items as $item)
                        <tr>
                            <td class="py-2 pr-4 whitespace-nowrap">
                                {{ $item->periodLabel() }}
                            </td>
                            <td class="py-2 pr-4">
                                <a
                                    href="{{ $item->url }}"
                                    class="text-white hover:underline"
                                    rel="noopener"
                                    target="_blank"
                                    >{{ $item->title }}</a
                                >
                            </td>
                            <td class="py-2 pr-4">
                                {{
                                    collect(
                                        $item->confirmed_at ? $item->objects : $item->candidates,
                                    )
                                        ->pluck("name")
                                        ->implode(", ")
                                }}
                            </td>
                            <td class="py-2 pr-4 whitespace-nowrap">
                                @if ($item->hidden)
                                    <span
                                        class="text-gray-500"
                                        >{{ __("Hidden") }}</span
                                    >
                                @elseif ($item->confirmed_at)
                                    <span class="text-green-400">{{
                                        __(
                                            "Published",
                                        )
                                    }}</span>
                                    <div class="text-xs text-gray-500">
                                        {{
                                            $item->confirmer
                                                ?->name
                                        }}
                                    </div>
                                @else
                                    <span
                                        class="text-yellow-400"
                                        >{{ __("To check") }}</span
                                    >
                                @endif
                            </td>
                            <td class="py-2 text-right whitespace-nowrap">
                                @if ($item->isPublished())
                                    <a
                                        href="{{ route("objects-of-the-month.show", $item) }}"
                                        class="rounded px-2 py-0.5 text-gray-300 hover:bg-gray-700 hover:text-white"
                                        >{{ __("View") }}</a
                                    >
                                @endif
                                <a
                                    href="{{ route("admin.objects-of-the-month.edit", $item) }}"
                                    class="rounded px-2 py-0.5 text-gray-300 hover:bg-gray-700 hover:text-white"
                                    >{{ __("Edit") }}</a
                                >
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-2 text-gray-400">
                                {{
                                    __(
                                        "No threads have been read yet.",
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
