<x-app-layout>
    <div>
        @php
            $isSunPageBlade = true;
            $canonicalSlug = $session->slug ?? null;
        @endphp

        <div class="mx-auto max-w-screen-xl xl:max-w-full bg-gray-900 px-6 py-6 sm:px-6 lg:px-8">
            <header class="mb-6">
                <h1 class="text-3xl font-extrabold">{{ __('Sun') }}</h1>
            </header>

            @include('object._summary')

            <div class="flex flex-col lg:flex-row gap-4 w-full items-stretch">
                <div class="flex-1" data-dsl-main-content>
                    <div class="mt-3">
                        <h2 class="text-xl font-semibold text-white">{{ __('Object details') }}</h2>
                        <table class="table-auto w-full text-sm text-gray-100">
                            {{-- SunDetails renders a <tbody> fragment with the ephemerides rows --}}
                            @livewire('sun-details', ['objectId' => (string) ($session->id ?? ''), 'initial' => $ephemerides ?? null])
                        </table>
                    </div>
                </div>

                @include('object._quick_links')
            </div>
        </div>
    </div>
</x-app-layout>
