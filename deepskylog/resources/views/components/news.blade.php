@props (["items", "location" => null])

{{-- The current news (#960) on the home page: written items and the coming astronomical events --}}
<x-home-section :title="__('News')" {{ $attributes }}>
    <ul class="divide-y divide-gray-700 px-2">
        @foreach ($items as $item)
            <li class="py-2">
                <a
                    href="{{ route("news.show", $item) }}"
                    class="text-sm font-semibold text-white hover:underline"
                    >{{
                        $item->displayTitle(
                            $location,
                        )
                    }}</a
                >
                <p class="line-clamp-2 text-sm text-gray-400">
                    {{
                        $item->excerpt(
                            140,
                            $location,
                        )
                    }}
                </p>
            </li>
        @endforeach
    </ul>
    <x-slot name="footer">
        <a
            href="{{ route("news.index") }}"
            class="text-blue-400 hover:underline"
            >{{ __("All news") }} &rarr;</a
        >
        <a
            href="{{ route("feed.news") }}"
            class="text-orange-400 hover:underline"
            >RSS</a
        >
    </x-slot>
</x-home-section>
