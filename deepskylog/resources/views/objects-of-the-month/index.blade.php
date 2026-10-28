<x-app-layout>
    <div class="mx-auto max-w-screen-lg bg-gray-900 px-6 py-6 sm:px-6 lg:px-8">
        <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
            <h1 class="text-xl font-semibold text-white">
                {{
                    __(
                        "Object of the Month",
                    )
                }}
            </h1>
            @can ("manage_news", \App\Models\User::class)
                <a
                    href="{{ route("admin.objects-of-the-month") }}"
                    class="text-sm text-blue-400 hover:underline"
                    >{{ __("Objects of the month") }} ({{ __("Edit") }})</a
                >
            @endcan
        </div>
        <p class="mb-4 text-sm text-gray-400">
            {!!
                __(
                    "Every month the object of the month team of :astroforum chooses one or more objects to observe. Here are everyone's DeepskyLog observations of them in that month.",
                    [
                        "astroforum" =>
                            '<a href="https://www.astroforum.nl/forums/objecten-van-de-maand.49/" class="text-blue-400 hover:underline" rel="noopener" target="_blank">Astroforum</a>',
                    ],
                )
            !!}
        </p>

        <div class="space-y-4">
            @forelse ($items as $item)
                <div class="rounded-lg bg-gray-800 p-4">
                    <h2 class="text-lg font-semibold text-white">
                        <a
                            href="{{ route("objects-of-the-month.show", $item) }}"
                            class="hover:underline"
                            >{{
                                ucfirst(
                                    $item->periodLabel(),
                                )
                            }}: {{ $item->subtitle() }}</a
                        >
                    </h2>
                    <p class="text-sm text-gray-300">
                        {{-- The names joined by commas: on separate lines Blade would put a space before each comma --}}
                        {!!
                            collect($item->objects ?? [])
                                ->map(
                                    fn(array $object) => '<a href="' .
                                        e(route("object.show", $object["slug"])) .
                                        '" class="text-blue-400 hover:underline">' .
                                        e($object["name"]) .
                                        "</a>",
                                )
                                ->implode(", ")
                        !!}
                    </p>
                </div>
            @empty
                <p class="text-gray-300">{{
                    __(
                        "There are no objects of the month yet.",
                    )
                }}</p>
            @endforelse
        </div>

        <div class="mt-4">{{ $items->links() }}</div>
    </div>
</x-app-layout>
