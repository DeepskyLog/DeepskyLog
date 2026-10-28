<!-- Add Dropdown (converted to reusable menu component) -->
@if (!Auth::guest())
    <div class="hidden lg:ml-1 lg:flex lg:items-center">
        <div class="relative mr-0 text-sm">
            <x-menu.dropdown :width="56">
                <x-slot name="trigger">
                    <button x-ref="trigger" @click="open = !open" aria-haspopup="true" :aria-expanded="open.toString()" class="inline-flex items-center rounded px-3 py-2 text-sm font-medium text-gray-200 hover:bg-gray-800">
                        {{ __('Add') }}
                    </button>
                </x-slot>

                @unless(Auth::user()->isAdministrator() || Auth::user()->isDatabaseExpert())
                <x-menu.item icon="user-plus" href="{{ route('observation.add.select') }}">{{ __('Observation') }}</x-menu.item>
                <x-menu.item icon="arrow-up-tray" href="{{ route('observation.import') }}">{{ __('Import observations') }}</x-menu.item>

                {{-- observing list placeholder (kept commented) --}}

                <x-menu.item href="{{ route('session.create') }}">
                    <x-slot:svg><x-outline.session-icon class="h-4 w-4" /></x-slot:svg>{{ __('Session') }}
                </x-menu.item>

                <x-menu.item icon="calendar-days" href="{{ route('star-parties.create') }}">{{ __('Starparty') }}</x-menu.item>

                <x-menu.item separator href="{{ route('instrument.create') }}">
                    <x-slot:svg><x-outline.telescope-icon class="h-4 w-4" /></x-slot:svg>{{ __('Instruments') }}
                </x-menu.item>

                <x-menu.item icon="globe-europe-africa" href="{{ route('location.create') }}">{{ __('Locations') }}</x-menu.item>

                <x-menu.item href="{{ route('eyepiece.create') }}">
                    <x-slot:svg><x-outline.eyepiece-icon class="h-4 w-4" /></x-slot:svg>{{ __('Eyepieces') }}
                </x-menu.item>

                <x-menu.item href="/filter/create">
                    <x-slot:svg><x-outline.filter-icon class="h-4 w-4" /></x-slot:svg>{{ __('Filters') }}
                </x-menu.item>

                <x-menu.item href="/lens/create">
                    <x-slot:svg><x-outline.barlow-icon class="h-4 w-4" /></x-slot:svg>{{ __('Lenses') }}
                </x-menu.item>

                <x-menu.item href="{{ route('instrumentset.create') }}">
                    <x-slot:svg><x-outline.instrument-set-icon class="h-4 w-4" /></x-slot:svg>{{ __('Instrument sets') }}
                </x-menu.item>

                <x-menu.item separator icon="plus-circle" href="{{ route('object.create') }}">{{ __('Object') }}</x-menu.item>
                <x-menu.item icon="plus-circle" href="{{ route('object.create.comet') }}">{{ __('Comet') }}</x-menu.item>
                <x-menu.item icon="plus-circle" href="{{ route('object.create.asteroid') }}">{{ __('Asteroid') }}</x-menu.item>
                @else
                <x-menu.item icon="plus-circle" href="{{ route('object.create') }}">{{ __('Object') }}</x-menu.item>
                <x-menu.item icon="plus-circle" href="{{ route('object.create.comet') }}">{{ __('Comet') }}</x-menu.item>
                <x-menu.item icon="plus-circle" href="{{ route('object.create.asteroid') }}">{{ __('Asteroid') }}</x-menu.item>
                @endunless

            </x-menu.dropdown>
        </div>
    </div>
@endif
