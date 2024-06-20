<!-- Administration Dropdown -->
@if (! Auth::guest())
    @if (Auth::user()->isAdministrator() || Auth::user()->isDatabaseExpert())
        <div class="hidden lg:ml-6 lg:flex lg:items-center">
            <div class="relative mr-3 text-sm">
                <x-dropdown position="bottom-start" width="48">
                    <x-slot name="trigger">
                        {{ __("Administration") }}
                    </x-slot>

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
                </x-dropdown>
            </div>
        </div>
    @endif
@endif
