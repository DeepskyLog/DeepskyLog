@props (["items", "location" => null])

{{-- The current news (#960) on the home page: written items and the coming astronomical events --}}
<div class="pt-4">
    <h2 class="mt-3 ml-3 text-xl leading-tight font-semibold">
        {{ __("News") }}
    </h2>
    <div class="mt-2">
        <x-card>
            <ul class="divide-y divide-gray-700 px-2">
                @foreach ($items as $item)
                    <li class="py-2">
                        <a
                            href="{{ route("news.show", $item) }}"
                            class="font-semibold text-white hover:underline"
                            >{{
                                $item->displayTitle(
                                    $location,
                                )
                            }}</a
                        >
                        <p class="text-sm text-gray-400">{{
                            $item->excerpt(
                                220,
                                $location,
                            )
                        }}</p>
                    </li>
                @endforeach
            </ul>
            <div class="mt-2 flex gap-4 px-2 text-sm">
                <a
                    href="{{ route("news.index") }}"
                    class="text-blue-400 hover:underline"
                    >{{ __("All news") }}</a
                >
                <a
                    href="{{ route("feed.news") }}"
                    class="text-orange-400 hover:underline"
                    >RSS</a
                >
            </div>
        </x-card>
    </div>
</div>
