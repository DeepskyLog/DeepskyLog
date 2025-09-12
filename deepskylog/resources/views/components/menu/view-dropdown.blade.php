@php use Carbon\Carbon; @endphp
    <!-- View Dropdown -->
<div class="hidden lg:ml-6 lg:flex lg:items-center">
    <div class="relative mr-3 text-sm">
    <x-dropdown width="xl" height="max-h-[70vh]" position="bottom-start">
            <x-slot name="trigger">
                {{ __('View') }}
            </x-slot>

            {{-- Observer-specific items moved to the user settings dropdown --}}

            <x-dropdown.item icon="sparkles" href="/sketch-of-the-week" label="{{ __('Sketch of the Week') }}"/>

            <x-dropdown.item icon="sparkles" href="/sketch-of-the-month" label="{{ __('Sketch of the Month') }}"/>

            @if (!Auth::guest() && Auth::user()->isObserver())
                <x-dropdown.item href="{{ route('session.all') }}" label="{{ __('All sessions') }}"/>

                {{-- Instrument/eyepiece/filter/lens links moved to the user menu --}}

                <x-dropdown.item separator
                                 href="{{ config('app.old_url') }}/index.php?indexAction=result_selected_observations&myLanguages=true&catalog=%&minyear={{ Carbon::now()->year - 1 }}&minmonth={{ Carbon::now()->month }}&minday={{ Carbon::now()->day }}&newobservations=true"
                                 label="{{ __('Latest observations') }}"/>
            @else
                <x-dropdown.item
                    href="{{ config('app.old_url') }}/index.php?indexAction=result_selected_observations&myLanguages=true&catalog=%&minyear={{ Carbon::now()->year - 1 }}&minmonth={{ Carbon::now()->month }}&minday={{ Carbon::now()->day }}&newobservations=true"
                    label="{{ __('Latest observations') }}"/>
            @endif
            <x-dropdown.item icon="pencil-square" href="/drawings" label="{{ __('All drawings') }}"/>

            <x-dropdown.item separator icon="user-group"
                             href="{{ config('app.old_url') }}/index.php?indexAction=rank_observers"
                             label="{{ __('Observers') }}"/>

            <x-dropdown.item href="{{ config('app.old_url') }}/index.php?indexAction=rank_objects"
                             label="{{ __('Popular objects') }}"/>
            <x-dropdown.item href="/popular-observations" label="{{ __('Popular observations') }}"/>

            <x-dropdown.item href="/popular-sessions" label="{{ __('Popular sessions') }}"/>

            <x-dropdown.item href="{{ config('app.old_url') }}/index.php?indexAction=statistics"
                             label="{{ __('Statistics') }}"/>

            <x-dropdown.item separator href="{{ config('app.old_url') }}/index.php?indexAction=view_catalogs"
                             label="{{ __('Catalogs') }}"/>
        </x-dropdown>
    </div>
</div>
