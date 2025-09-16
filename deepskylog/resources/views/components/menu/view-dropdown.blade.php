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

            @if (!Auth::guest() && Auth::user()->isObserver())
                <x-menu.item href="{{ route('session.all') }}">
                    <x-outline.session-icon class="h-4 w-4 mr-2 text-gray-300" />
                    {{ __('All sessions') }}
                </x-menu.item>

                {{-- Instrument/eyepiece/filter/lens links moved to the user menu --}}
            @endif
            <x-menu.item icon="bars-3-center-left" href="{{ config('app.old_url') }}/index.php?indexAction=result_selected_observations&myLanguages=true&catalog=%&minyear={{ Carbon::now()->year - 1 }}&minmonth={{ Carbon::now()->month }}&minday={{ Carbon::now()->day }}&newobservations=true">{{ __('Latest observations') }}</x-menu.item>

            <x-menu.item icon="pencil-square" href="/drawings">{{ __('All drawings') }}</x-menu.item>

            <x-menu.item separator icon="user-group" href="{{ config('app.old_url') }}/index.php?indexAction=rank_observers">{{ __('Observers') }}</x-menu.item>

            <x-menu.item href="{{ config('app.old_url') }}/index.php?indexAction=rank_objects">
                <x-outline.popular-objects-icon class="h-4 w-4 mr-2 ml-0 text-gray-300" />
                {{ __('Popular objects') }}
            </x-menu.item>
            <x-menu.item href="/popular-observations">
                <x-outline.popular-objects-icon class="h-4 w-4 mr-2 text-gray-300" />
                {{ __('Popular observations') }}
            </x-menu.item>

            <x-menu.item href="/popular-sessions">
                <x-outline.session-icon class="h-4 w-4 mr-2 text-gray-300" />
                {{ __('Popular sessions') }}
            </x-menu.item>

            <x-menu.item icon="chart-bar-square" href="{{ config('app.old_url') }}/index.php?indexAction=statistics">{{ __('Statistics') }}</x-menu.item>

            <x-menu.item separator icon="pencil-square" href="{{ config('app.old_url') }}/index.php?indexAction=view_catalogs" data-last>{{ __('Catalogs') }}</x-menu.item>

        </x-menu.dropdown>
    </div>
</div>
