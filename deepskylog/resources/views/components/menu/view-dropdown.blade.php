@php use Carbon\Carbon; @endphp
    <!-- View Dropdown -->
<div class="hidden lg:ml-6 lg:flex lg:items-center">
    <div class="relative mr-3 text-sm">
        <x-dropdown width="xl" height="max-h-80" position="bottom-start">
            <x-slot name="trigger">
                {{ __('View') }}
            </x-slot>

            @if (!Auth::guest() && Auth::user()->isObserver())
                <x-dropdown.item icon="bars-3-center-left"
                                 href="{{ config('app.old_url') }}/index.php?indexAction=result_selected_observations&observer={{ Auth::user()->username }}"
                                 label="{{ __('My observations') }}"/>

                <x-dropdown.item icon="pencil-square" href="/drawings/{{ Auth::user()->slug }}"
                                 label="{{ __('My drawings') }}"/>
            @endif

            <x-dropdown.item icon="sparkles" href="/sketch-of-the-week" label="{{ __('Sketch of the Week') }}"/>

            <x-dropdown.item icon="sparkles" href="/sketch-of-the-month" label="{{ __('Sketch of the Month') }}"/>

            @if (!Auth::guest() && Auth::user()->isObserver())
                <x-dropdown.item separator icon="bars-3-center-left"
                                 href="{{ config('app.old_url') }}/index.php?indexAction=view_lists"
                                 label="{!! __('My observing lists') !!}"/>

                <x-dropdown.item href="{{ config('app.old_url') }}/index.php?indexAction=result_my_sessions"
                                 label="{{ __('My sessions') }}"/>

                <x-dropdown.item separator
                                 href="/instrument"
                                 label="{{ __('My instruments') }}"/>

                <x-dropdown.item
                                 href="/instrumentset"
                                 label="{{ __('My instrument sets') }}"/>

                <x-dropdown.item icon="globe-europe-africa"
                                 href="/location"
                                 label="{{ __('My locations') }}"/>

                <x-dropdown.item href="/eyepiece"
                                 label="{{ __('My eyepieces') }}"/>

                <x-dropdown.item href="/filter"
                                 label="{{ __('My filters') }}"/>

                <x-dropdown.item href="/lens"
                                 label="{{ __('My lenses') }}"/>

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

            <x-dropdown.item href="{{ config('app.old_url') }}/index.php?indexAction=statistics"
                             label="{{ __('Statistics') }}"/>

            <x-dropdown.item separator href="{{ config('app.old_url') }}/index.php?indexAction=view_catalogs"
                             label="{{ __('Catalogs') }}"/>
        </x-dropdown>
    </div>
</div>
