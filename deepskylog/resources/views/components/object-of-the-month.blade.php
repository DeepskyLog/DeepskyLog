@props (["item", "drawings"])

{{-- The object of the month of Astroforum on the home page: its objects, how many observed them in its months, and the newest drawings of them --}}
@php ($counts = $item->counts())
@php ($observations = array_sum( array_column($counts, "observations") ))
<x-home-section
    :title="__('Object of the month :period', ['period' => $item->periodLabel()])"
    {{ $attributes }}
>
    <div class="px-2 text-sm">
        <p class="font-semibold text-white">{{ $item->subtitle() }}</p>
        <p class="mt-1 text-gray-300">
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
        <p class="mt-1 text-gray-400">
            @if ($observations === 0)
                {{
                    __(
                        "Nobody has observed them yet. Will you be the first?",
                    )
                }}
            @else
                {{
                    trans_choice(
                        "{1} One observation so far|[2,*] :count observations so far",
                        $observations,
                        ["count" => $observations],
                    )
                }},
                {{
                    trans_choice(
                        "{1} by one observer.|[2,*] by :count observers.",
                        $item->observerCount(),
                        ["count" => $item->observerCount()],
                    )
                }}
            @endif
        </p>
    </div>
    @if ($drawings->isNotEmpty())
        <div class="mt-3 grid grid-cols-2 gap-3 px-2">
            @foreach ($drawings as $drawing)
                <x-sketch-thumbnail :observation="$drawing" />
            @endforeach
        </div>
    @endif
    <x-slot name="footer">
        <a
            href="{{ route("objects-of-the-month.show", $item) }}"
            class="text-blue-400 hover:underline"
            >{{ __("Observations") }} &rarr;</a
        >
        <a
            href="{{ $item->url }}"
            class="text-blue-400 hover:underline"
            rel="noopener"
            target="_blank"
            >{{
                __(
                    "Discussion on Astroforum",
                )
            }}</a
        >
    </x-slot>
</x-home-section>
