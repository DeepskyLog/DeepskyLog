<x-app-layout>
    <div class="mx-auto max-w-screen-lg bg-gray-900 px-6 py-6 sm:px-6 lg:px-8">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <h1 class="text-xl font-semibold text-white">{{ __("News") }}</h1>
            <div class="flex items-center gap-3 text-sm">
                @can ("manage_news", \App\Models\User::class)
                    <a
                        href="{{ route("admin.news") }}"
                        class="text-blue-400 hover:underline"
                        >{{
                            __(
                                "Manage the news",
                            )
                        }}</a
                    >
                @endcan
                <a
                    href="{{ route("feed.news") }}"
                    class="text-orange-400 hover:underline"
                    >RSS</a
                >
            </div>
        </div>
        @if ($location)
            <p class="mb-4 text-sm text-gray-400">
                {{
                    __(
                        "Times and circumstances of the astronomical events are given for :location.",
                        ["location" => $location->name],
                    )
                }}
                @guest
                    {{
                        __(
                            "Log in and set your standard location to see them for your own location.",
                        )
                    }}
                @endguest
            </p>
        @endif

        <div class="space-y-4">
            @forelse ($items as $item)
                <div class="rounded-lg bg-gray-800 p-4">
                    <h2 class="text-lg font-semibold text-white">
                        <a
                            href="{{ route("news.show", $item) }}"
                            class="hover:underline"
                            >{{
                                $item->displayTitle(
                                    $location,
                                )
                            }}</a
                        >
                    </h2>
                    <p class="mb-2 text-xs text-gray-500">
                        {{
                            $item->published_at
                                ->locale(app()->getLocale())
                                ->isoFormat("LL")
                        }}
                        @if ($item->author)
                            &middot; {{
                                $item->author
                                    ->name
                            }}
                        @endif
                    </p>
                    @include ("news.partials.item", ["item" => $item, "location" => $location])
                </div>
            @empty
                <p class="text-gray-400">{{
                    __(
                        "There is no news yet.",
                    )
                }}</p>
            @endforelse
        </div>

        <div class="mt-4">{{ $items->links() }}</div>
    </div>
</x-app-layout>
