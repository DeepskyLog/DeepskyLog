@php use Carbon\Carbon; @endphp
<div>
    <div class="border-t border-gray-400 pb-1 pt-4">
        <div class="flex items-center px-4">
            <div>
                <div class="text-base font-medium text-gray-200">
                    {{ __("View") }}
                </div>
            </div>
        </div>

        <div class="mt-3 space-y-1">
            <!-- Observer-specific items moved to responsive settings menu -->

            <!-- Team Management -->
            <x-dropdown.item
                icon="sparkles"
                href="/sketch-of-the-week"
                label="{{ __('Sketch of the Week') }}"
            />

            <x-dropdown.item
                icon="sparkles"
                href="/sketch-of-the-month"
                label="{{ __('Sketch of the Month') }}"
            />

            <x-dropdown.item
                icon="bars-3-center-left"
                href="/observations"
                label="{{ __('All observations') }}"
            />
            <x-dropdown.item
                icon="pencil-square"
                href="/drawings"
                label="{{ __('All drawings') }}"
            />
            <x-dropdown.item
                icon="calendar-days"
                href="{{ route('star-parties.index') }}"
                label="{{ __('Starparties') }}"
            />

            <x-dropdown.item
                separator
                icon="user-group"
                href="{{ route('observers.index') }}"
                label="{{ __('Observers') }}"
            />

            <x-dropdown.item href="{{ route('objects.popular') }}">
                <x-outline.popular-objects-icon class="h-4 w-4 mr-2 ml-0 text-gray-300" />
                {{ __('Popular objects') }}
            </x-dropdown.item>

            <x-dropdown.item href="/popular-observations">
                <x-outline.popular-objects-icon class="h-4 w-4 mr-2 ml-0 text-gray-300" />
                {{ __('Popular observations') }}
            </x-dropdown.item>

            <x-dropdown.item href="/popular-sessions">
                <x-outline.session-icon class="h-4 w-4 mr-2 ml-0 text-gray-300" />
                {{ __('Popular sessions') }}
            </x-dropdown.item>

            <x-dropdown.item
                icon="chart-bar-square"
                href="{{ route('statistics.index') }}"
                label="{{ __('Statistics') }}"
            />

            <x-dropdown.item
                separator
                icon="pencil-square"
                href="{{ url('/catalogs') }}"
                label="{{ __('Catalogs') }}"
            />
        </div>
    </div>
</div>
