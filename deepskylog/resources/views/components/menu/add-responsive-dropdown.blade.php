<!-- Add Dropdown -->
@if (! Auth::guest() && ! Auth::user()->isAdministrator() && ! Auth::user()->isDatabaseExpert())
    <div>
        <div class="border-t border-gray-400 pb-1 pt-4">
            <div class="flex items-center px-4">
                <div>
                    <div class="text-base font-medium text-gray-200">
                        {{ __("Add") }}
                    </div>
                </div>
            </div>
            <div class="mt-3 space-y-1">
                <x-dropdown.item
                    icon="user-plus"
                    href="{{ config('app.old_url') }}/index.php?indexAction=quickpick&titleobjectaction=Zoeken&source=quickpick&myLanguages=true&object=&newObservationQuickPick=Nieuwe%C2%A0waarneming"
                    label="{{ __('Observation') }}"
                />

                <x-dropdown.item
                    separator
                    icon="bars-4"
                    href=""
                    label="{{ __('Observing list') }}"
                />

                <x-dropdown.item
                    icon="user-plus"
                    href="{{ config('app.old_url') }}/index.php?indexAction=add_session"
                    label="{{ __('Sessions') }}"
                />

                <x-dropdown.item
                    separator
                    icon="plus-circle"
                    href="{{ config('app.old_url') }}/index.php?indexAction=add_instrument"
                    label="{{ __('Instruments') }}"
                />

                <x-dropdown.item
                    icon="globe-europe-africa"
                    href="{{ config('app.old_url') }}/index.php?indexAction=add_location"
                    label="{{ __('Locations') }}"
                />

                <x-dropdown.item
                    icon="plus-circle"
                    href="{{ config('app.old_url') }}/index.php?indexAction=add_eyepiece"
                    label="{{ __('Eyepieces') }}"
                />

                <x-dropdown.item
                    icon="plus-circle"
                    href="{{ config('app.old_url') }}/index.php?indexAction=add_filter"
                    label="{{ __('Filters') }}"
                />

                <x-dropdown.item
                    icon="plus-circle"
                    href="{{ config('app.old_url') }}/index.php?indexAction=add_lens"
                    label="{{ __('Lenses') }}"
                />

                <x-dropdown.item
                    separator
                    icon="plus-circle"
                    href="{{ config('app.old_url') }}/index.php?indexAction=add_object"
                    label="{{ __('Object') }}"
                />
            </div>
        </div>
    </div>
@endif
