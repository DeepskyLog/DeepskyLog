<!-- Add Dropdown (converted to reusable menu component) -->
@if (!Auth::guest() && !Auth::user()->isAdministrator() && !Auth::user()->isDatabaseExpert())
    <div class="hidden lg:ml-1 lg:flex lg:items-center">
        <div class="relative mr-0 text-sm">
            <x-menu.dropdown :width="56">
                <x-slot name="trigger">
                    <button x-ref="trigger" @click="open = !open" aria-haspopup="true" :aria-expanded="open.toString()" class="inline-flex items-center rounded px-3 py-2 text-sm font-medium text-gray-200 hover:bg-gray-800">
                        {{ __('Add') }}
                    </button>
                </x-slot>

                <x-menu.item icon="user-plus" href="{{ config('app.old_url') }}/index.php?indexAction=quickpick&titleobjectaction=Zoeken&source=quickpick&myLanguages=true&object=&newObservationQuickPick=Nieuwe%C2%A0waarneming">{{ __('Observation') }}</x-menu.item>

                {{-- observing list placeholder (kept commented) --}}

                <x-menu.item href="{{ route('session.create') }}">
                    <x-outline.session-icon class="h-4 w-4 mr-3 text-gray-300" />
                    {{ __('Session') }}
                </x-menu.item>

                <x-menu.item separator href="{{ route('instrument.create') }}">
                    <x-outline.telescope-icon class="h-4 w-4 mr-3 text-gray-300" />
                    {{ __('Instruments') }}
                </x-menu.item>

                <x-menu.item icon="globe-europe-africa" href="{{ route('location.create') }}">{{ __('Locations') }}</x-menu.item>

                <x-menu.item href="{{ route('eyepiece.create') }}">
                    <x-outline.eyepiece-icon class="h-4 w-4 mr-4 text-gray-300 inline-block align-middle" />
                    {{ __('Eyepieces') }}
                </x-menu.item>

                <x-menu.item href="/filter/create">
                    <x-outline.filter-icon class="h-4 w-4 mr-4 text-gray-300 inline-block align-middle" />
                    {{ __('Filters') }}
                </x-menu.item>

                <x-menu.item href="/lens/create">
                    <x-outline.barlow-icon class="h-4 w-4 mr-4 text-gray-300 inline-block align-middle" />
                    {{ __('Lenses') }}
                </x-menu.item>

                <x-menu.item href="{{ route('instrumentset.create') }}">
                    <x-outline.instrument-set-icon class="h-4 w-4 mr-4 text-gray-300 inline-block align-middle" />
                    {{ __('Instrument sets') }}
                </x-menu.item>

                <x-menu.item separator icon="plus-circle" href="{{ config('app.old_url') }}/index.php?indexAction=add_object">{{ __('Object') }}</x-menu.item>

            </x-menu.dropdown>
        </div>
    </div>
@endif
