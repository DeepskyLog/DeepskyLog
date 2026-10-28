@props (["title"])

{{-- A box of the home page: a heading and a card with the content and, in the footer slot, links to the full pages --}}
<section
    {{
        $attributes->merge([
            "class" => "min-w-0",
        ])
    }}
>
    <h2 class="mb-2 ml-3 text-xl leading-tight font-semibold">{{ $title }}</h2>
    <x-card>
        {{ $slot }}
        @isset ($footer)
            <div class="mt-3 flex flex-wrap gap-4 px-2 text-sm">
                {{ $footer }}
            </div>
        @endisset
    </x-card>
</section>
