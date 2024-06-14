<!-- View Dropdown -->
<div class="hidden sm:ml-6 sm:flex sm:items-center">
    <div class="relative mr-3 text-sm">
        <x-dropdown position="bottom-start">
            <x-slot name="trigger">
                {{ __("View") }}
            </x-slot>

            @if (! Auth::guest() && Auth::user()->isObserver())
                <x-dropdown.item
                    icon="bars-3-center-left"
                    href="{{ config('app.old_url') }}/index.php?indexAction=result_selected_observations&observer={{  Auth::user()->username }}"
                    label="{{ __('My observations') }}"
                />

                <x-dropdown.item
                    icon="pencil-square"
                    href="{{ config('app.old_url') }}/index.php?indexAction=show_drawings&user={{ Auth::user()->username }}"
                    label="{{ __('My drawings') }}"
                />

                <x-dropdown.item
                    separator
                    icon="bars-3-center-left"
                    href="{{ config('app.old_url') }}/index.php?indexAction=view_lists"
                    label="{{ __('My observing lists') }}"
                />

                <x-dropdown.item
                    href="{{ config('app.old_url') }}/index.php?indexAction=result_my_sessions"
                    label="{{ __('My sessions') }}"
                />

                <x-dropdown.item
                    separator
                    href="{{ config('app.old_url') }}/index.php?indexAction=view_instruments"
                    label="{{ __('My instruments') }}"
                />

                <x-dropdown.item
                    icon="globe-europe-africa"
                    href="{{ config('app.old_url') }}/index.php?indexAction=view_sites"
                    label="{{ __('My locations') }}"
                />

                <x-dropdown.item
                    href="{{ config('app.old_url') }}/index.php?indexAction=view_eyepieces"
                    label="{{ __('My eyepieces') }}"
                />

                <x-dropdown.item
                    href="{{ config('app.old_url') }}/index.php?indexAction=view_filters"
                    label="{{ __('My filters') }}"
                />

                <x-dropdown.item
                    href="{{ config('app.old_url') }}/index.php?indexAction=view_lenses"
                    label="{{ __('My lenses') }}"
                />

                <x-dropdown.item
                    separator
                    href="{{ config('app.old_url') }}/index.php?indexAction=result_selected_observations&myLanguages=true&catalog=%&minyear={{ \Carbon\Carbon::now()->year - 1 }}&minmonth={{ \Carbon\Carbon::now()->month }}&minday={{ \Carbon\Carbon::now()->day }}&newobservations=true"
                    label="{{ __('Latest observations') }}"
                />
            @else
                <x-dropdown.item
                    href="{{ config('app.old_url') }}/index.php?indexAction=result_selected_observations&myLanguages=true&catalog=%&minyear={{ \Carbon\Carbon::now()->year - 1 }}&minmonth={{ \Carbon\Carbon::now()->month }}&minday={{ \Carbon\Carbon::now()->day }}&newobservations=true"
                    label="{{ __('Latest observations') }}"
                />
            @endif
            <x-dropdown.item
                separator
                icon="user-group"
                href="{{ config('app.old_url') }}/index.php?indexAction=rank_observers"
                label="{{ __('Observers') }}"
            />

            <x-dropdown.item
                href="{{ config('app.old_url') }}/index.php?indexAction=rank_objects"
                label="{{ __('Popular objects') }}"
            />

            <x-dropdown.item
                href="{{ config('app.old_url') }}/index.php?indexAction=statistics"
                label="{{ __('Statistics') }}"
            />

            <x-dropdown.item
                separator
                href="{{ config('app.old_url') }}/index.php?indexAction=view_catalogs"
                label="{{ __('Catalogs') }}"
            />
        </x-dropdown>
    </div>
</div>
