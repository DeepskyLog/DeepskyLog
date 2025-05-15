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

                        <x-dropdown.item
                            icon="users"
                            separator
                            href="/observers/admin"
                            label="{{ __('Observers') }}"
                        />
                    @endif

                    <x-dropdown.item
                        icon="plus"
                        separator
                        href="/sketch-of-the-week/create"
                        label="{{ __('Add sketch of the week') }}"
                    />

                    <x-dropdown.item
                        icon="plus"
                        href="/sketch-of-the-month/create"
                        label="{{ __('Add sketch of the month') }}"
                    />

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
                            href="/admin/instrument"
                            label="{{ __('Instruments') }}"
                        />

                        <x-dropdown.item
                            href="/admin/eyepiece"
                            label="{{ __('Eyepiece Makes') }}"
                        />

                        <x-dropdown.item
                            href="/admin/eyepiece-type"
                            label="{{ __('Eyepiece Types') }}"
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
