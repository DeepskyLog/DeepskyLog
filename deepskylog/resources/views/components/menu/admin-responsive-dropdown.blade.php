<!-- Administration Dropdown -->
@if (! Auth::guest())
    @if (Auth::user()->isAdministrator() || Auth::user()->isDatabaseExpert())
        <div>
            <div class="border-t border-gray-400 pb-1 pt-4">
                <div class="flex items-center px-4">
                    <div>
                        <div class="text-base font-medium text-gray-200">
                            {{ __("Administration") }}
                        </div>
                    </div>
                </div>
                <div class="mt-3 space-y-1">
                    @if (Auth::user()->isAdministrator())
                        <x-dropdown.item
                            icon="envelope-open"
                            href="{{ config('app.old_url') }}/index.php?indexAction=new_message&receiver=all"
                            label="{{ __('Send message to all') }}"
                        />
                    @endif

                    @if (Auth::user()->isAdministrator())
                        <x-dropdown.item
                            icon="check-badge"
                            separator
                            href="{{ config('app.old_url') }}/index.php?indexAction=admin_check_objects"
                            label="{{ __('Check Objects') }}"
                        />
                    @else
                        <x-dropdown.item
                            icon="check-badge"
                            href="{{ config('app.old_url') }}/index.php?indexAction=admin_check_objects"
                            label="{{ __('Check Objects') }}"
                        />
                    @endif

                    @if (Auth::user()->isAdministrator())
                        <x-dropdown.item
                            icon="globe-europe-africa"
                            separator
                            href="{{ config('app.old_url') }}/index.php?indexAction=overview_locations"
                            label="{{ __('Locations') }}"
                        />

                        <x-dropdown.item
                            href="{{ config('app.old_url') }}/index.php?indexAction=overview_instruments"
                            label="{{ __('Instruments') }}"
                        />

                        <x-dropdown.item
                            href="{{ config('app.old_url') }}/index.php?indexAction=overview_eyepieces"
                            label="{{ __('Eyepieces') }}"
                        />

                        <x-dropdown.item
                            href="{{ config('app.old_url') }}/index.php?indexAction=overview_filters"
                            label="{{ __('Filters') }}"
                        />

                        <x-dropdown.item
                            href="{{ config('app.old_url') }}/index.php?indexAction=overview_lenses"
                            label="{{ __('Lenses') }}"
                        />
                    @endif
                </div>
            </div>
        </div>
    @endif
@endif
