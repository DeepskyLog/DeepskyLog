@php use Carbon\Carbon; @endphp
    <!-- View Dropdown (converted to inline button-driven menu) -->
<div class="hidden lg:ml-1 lg:flex lg:items-center">
    <div class="relative mr-0 text-sm">
        <x-menu.dropdown :width="72">
            <x-slot name="trigger">
                <button x-ref="trigger" @click="open = !open" aria-haspopup="true" :aria-expanded="open.toString()" class="inline-flex items-center rounded px-3 py-2 text-sm font-medium text-gray-200 hover:bg-gray-800">
                    {{ __('View') }}
                </button>
            </x-slot>

            {{-- Content slot: keep the same items (icons restored) --}}
            <x-menu.item icon="sparkles" href="/sketch-of-the-week" data-first>{{ __('Sketch of the Week') }}</x-menu.item>

            <x-menu.item icon="sparkles" href="/sketch-of-the-month">{{ __('Sketch of the Month') }}</x-menu.item>

            <x-menu.item icon="star" href="{{ route('objects-of-the-month.index') }}">{{ __('Object of the Month') }}</x-menu.item>

            <x-menu.item icon="trophy" href="{{ route('challenges.index') }}">{{ __('Observing challenges') }}</x-menu.item>

            <x-menu.item icon="moon" href="{{ route('moon.show') }}">{{ __('The Moon tonight') }}</x-menu.item>

            <x-menu.item icon="globe-alt" href="{{ route('planet-moons.show') }}">{{ __('Jupiter and Saturn tonight') }}</x-menu.item>

            @if (!Auth::guest() && Auth::user()->isObserver())
                <x-menu.item href="{{ route('session.all') }}">
                    <x-slot:svg><x-outline.session-icon class="h-4 w-4" /></x-slot:svg>{{ __('All sessions') }}
                </x-menu.item>

                {{-- Instrument/eyepiece/filter/lens links moved to the user menu --}}
            @endif
            <x-menu.item icon="bars-3-center-left" href="/observations">{{ __('All observations') }}</x-menu.item>

            <x-menu.item icon="pencil-square" href="/drawings">{{ __('All drawings') }}</x-menu.item>

            <x-menu.item icon="calendar-days" href="{{ route('star-parties.index') }}">{{ __('Starparties') }}</x-menu.item>

            <x-menu.item separator icon="user-group" href="{{ route('observers.index') }}">{{ __('Observers') }}</x-menu.item>

            <x-menu.item href="{{ route('objects.popular') }}">
                <x-slot:svg><x-outline.popular-objects-icon class="h-4 w-4" /></x-slot:svg>{{ __('Popular objects') }}
            </x-menu.item>
            <x-menu.item href="/popular-observations">
                <x-slot:svg><x-outline.popular-objects-icon class="h-4 w-4" /></x-slot:svg>{{ __('Popular observations') }}
            </x-menu.item>

            <x-menu.item href="/popular-sessions">
                <x-slot:svg><x-outline.session-icon class="h-4 w-4" /></x-slot:svg>{{ __('Popular sessions') }}
            </x-menu.item>

            <x-menu.item icon="chart-bar-square" href="{{ route('statistics.index') }}">{{ __('Statistics') }}</x-menu.item>

            <x-menu.item separator icon="list-bullet" href="{{ route('observing-lists.discover') }}">{{ __('Observing lists') }}</x-menu.item>

            <x-menu.item icon="pencil-square" href="{{ url('/catalogs') }}" data-last>{{ __('Catalogs') }}</x-menu.item>

        </x-menu.dropdown>
    </div>
</div>
