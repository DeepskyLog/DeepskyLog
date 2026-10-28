<x-app-layout>
    <div class="mx-auto max-w-screen-lg bg-gray-900 px-6 py-6 sm:px-6 lg:px-8">
        <p class="mb-2 text-sm"><a href="{{ route("news.index") }}" class="text-blue-400 hover:underline">&larr; {{ __("All news") }}</a></p>
        <div class="rounded-lg bg-gray-800 p-4">
            <h1 class="text-xl font-semibold text-white">
                {{
                    $item->displayTitle(
                        $location,
                    )
                }}
            </h1>
            <p class="mb-3 text-xs text-gray-500">
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
            @if ($item->isAutomatic() && $location)
                <p class="mt-4 text-xs text-gray-500">
                    {{
                        __(
                            "Times and circumstances of the astronomical events are given for :location.",
                            ["location" => $location->name],
                        )
                    }}
                </p>
            @endif
        </div>
    </div>
</x-app-layout>
