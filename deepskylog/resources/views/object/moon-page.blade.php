<x-app-layout>
    <div>
        @php
            $isMoonPageBlade = true;
            $canonicalSlug = $session->slug ?? null;
        @endphp

        <div
            class="mx-auto max-w-screen-xl bg-gray-900 px-6 py-6 sm:px-6 lg:px-8 xl:max-w-full"
        >
            <header class="mb-6">
                @php
                    $objSlugTop =
                        $canonicalSlug ??
                        ($session->slug ?? \Illuminate\Support\Str::slug($session->name ?? ""));
                @endphp
                <h1 class="text-3xl font-extrabold">
                    <a
                        href="{{ route('object.show', ['slug' => $objSlugTop]) }}"
                        class="hover:underline"
                        >{{
                            html_entity_decode(
                                $session->name ?? __("Moon"),
                                ENT_QUOTES | ENT_HTML5,
                                "UTF-8",
                            )
                        }}</a
                    >
                </h1>
            </header>

            @include ("object._summary")

            <div class="flex w-full flex-col items-stretch gap-4 lg:flex-row">
                {{-- The layout includes the ephemeris aside via @livewire('ephemeris-aside') in layouts.app --}}
                <div class="flex-1" data-dsl-main-content>
                    <div class="mt-3">
                        <h2 class="text-xl font-semibold text-white">
                            {{
                                __(
                                    "Object details",
                                )
                            }}
                        </h2>
                        <table class="w-full table-auto text-sm text-gray-100">
                            {{-- MoonDetails renders a <tbody> fragment with the ephemerides rows --}}
                            @livewire ("moon-details",
                                [
                                    "objectId" => (string) ($session->id ?? ""),
                                    "initial" => $ephemerides ?? null
                                ])
                        </table>
                    </div>

                    @if (($session->source_type_raw ?? "") === "moon")
                        <x-moon-object-terminator />
                    @else
                        <x-lunar-feature-terminator
                            :slug="$canonicalSlug ?? ($session->slug ?? '')"
                            :name="$session->name ?? ''"
                        />
                    @endif
                </div>

                @include ("object._quick_links")
            </div>
        </div>
    </div>
</x-app-layout>

{{-- Moon page uses Livewire-only updates; no inline JS here. --}}
