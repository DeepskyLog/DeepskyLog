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

                {{-- <x-dropdown.item --}}
                {{-- separator --}}
                {{-- icon="bars-4" --}}
                {{-- href="" --}}
                {{-- label="{!! __('Observing list') !!}" --}}
                {{-- /> --}}

                <x-dropdown.item
                    icon="user-plus"
                    href="{{ route('session.create') }}"
                    label="{{ __('Session') }}"
                />


                <x-dropdown.item
                    separator
                    icon="plus-circle"
                    href="{{ route('instrument.create') }}"
                    label="{{ __('Instruments') }}"
                />

                <x-dropdown.item
                    icon="globe-europe-africa"
                    href="{{ route('location.create') }}"
                    label="{{ __('Locations') }}"
                />

                <x-dropdown.item
                    icon="plus-circle"
                    href="{{ route('eyepiece.create') }}"
                    label="{{ __('Eyepieces') }}"
                />

                <x-dropdown.item
                    icon="plus-circle"
                    href="/filter/create"
                    label="{{ __('Filters') }}"
                />

                <x-dropdown.item
                    icon="plus-circle"
                    href="/lens/create"
                    label="{{ __('Lenses') }}"
                />

                <x-dropdown.item
                    icon="plus-circle"
                    href="{{ route('instrumentset.create') }}"
                    label="{{ __('Instrument sets') }}"
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
