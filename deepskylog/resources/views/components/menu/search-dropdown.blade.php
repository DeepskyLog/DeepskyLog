<!-- Search Dropdown (converted to use reusable menu component) -->
<div class="hidden lg:ml-1 lg:flex lg:items-center">
    <div class="relative mr-0 text-sm">
        <x-menu.dropdown :width="48">
            <x-slot name="trigger">
                <button x-ref="trigger" @click="open = !open" aria-haspopup="true" :aria-expanded="open.toString()" class="inline-flex items-center rounded px-3 py-2 text-sm font-medium text-gray-200 hover:bg-gray-800">
                    {{ __("Search") }}
                </button>
            </x-slot>

            {{-- preserve original items as plain links (icons handled inside original x-dropdown.item) --}}
            <x-menu.item icon="magnifying-glass" href="{{ config('app.old_url') }}/index.php?indexAction=quickpick&searchObjectQuickPickQuickPick">{{ __('Search object') }}</x-menu.item>

            <x-menu.item icon="globe-europe-africa" href="{{ config('app.old_url') }}/index.php?indexAction=quickpick&myLanguages=true&searchObservationsQuickPick">{{ __('Search Observations') }}</x-menu.item>

            <x-menu.item separator href="{{ config('app.old_url') }}/index.php?indexAction=result_selected_sessions">
                <x-outline.session-icon class="h-4 w-4 mr-2 text-gray-300" />
                {{ __('All sessions') }}
            </x-menu.item>

        </x-menu.dropdown>
    </div>
</div>
