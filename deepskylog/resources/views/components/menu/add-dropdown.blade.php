<!-- Add Dropdown -->
@if (!Auth::guest() && !Auth::user()->isAdministrator() && !Auth::user()->isDatabaseExpert())
    <div class="hidden lg:ml-6 lg:flex lg:items-center">
        <div class="relative mr-3 text-sm">
            <x-dropdown height="max-h-80" position="bottom-start">
                <x-slot name="trigger">
                    {{ __('Add') }}
                </x-slot>

                <x-dropdown.item icon="user-plus"
                                 href="{{ config('app.old_url') }}/index.php?indexAction=quickpick&titleobjectaction=Zoeken&source=quickpick&myLanguages=true&object=&newObservationQuickPick=Nieuwe%C2%A0waarneming"
                                 label="{{ __('Observation') }}"/>

                {{-- <x-dropdown.item --}}
                {{-- separator --}}
                {{-- icon="bars-4" --}}
                {{-- href="" --}}
                {{-- label="{!! __('Observing list') !!}" --}}
                {{-- /> --}}

                <x-dropdown.item icon="user-plus" href="{{ config('app.old_url') }}/index.php?indexAction=add_session"
                                 label="{{ __('Sessions') }}"/>

                <x-dropdown.item separator icon="plus-circle"
                                 href="{{ route('instrument.create') }}"
                                 label="{{ __('Instruments') }}"/>

                <x-dropdown.item icon="globe-europe-africa"
                                 href="{{ config('app.old_url') }}/index.php?indexAction=add_location"
                                 label="{{ __('Locations') }}"/>

                <x-dropdown.item icon="plus-circle"
                                 href="{{ route('eyepiece.create') }}"
                                 label="{{ __('Eyepieces') }}"/>

                <x-dropdown.item icon="plus-circle" href="{{ config('app.old_url') }}/index.php?indexAction=add_filter"
                                 label="{{ __('Filters') }}"/>

                <x-dropdown.item icon="plus-circle" href="/lens/create"
                                 label="{{ __('Lenses') }}"/>

                <x-dropdown.item separator icon="plus-circle"
                                 href="{{ config('app.old_url') }}/index.php?indexAction=add_object"
                                 label="{{ __('Object') }}"/>
            </x-dropdown>
        </div>
    </div>
@endif
