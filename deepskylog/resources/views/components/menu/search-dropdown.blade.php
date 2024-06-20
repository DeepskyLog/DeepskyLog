<!-- Search Dropdown -->
<div class="hidden lg:ml-6 lg:flex lg:items-center">
    <div class="relative mr-3 text-sm">
        <x-dropdown width="48" position="bottom-start">
            <x-slot name="trigger">
                {{ __("Search") }}
            </x-slot>
            <x-dropdown.item
                icon="magnifying-glass"
                href="{{ config('app.old_url') }}/index.php?indexAction=quickpick&searchObjectQuickPickQuickPick"
                label="{{ __('Search object') }}"
            />

            <x-dropdown.item
                icon="globe-europe-africa"
                href="{{ config('app.old_url') }}/index.php?indexAction=quickpick&myLanguages=true&searchObservationsQuickPick"
                label="{{ __('Search Observations') }}"
            />

            <x-dropdown.item
                separator
                href="{{ config('app.old_url') }}/index.php?indexAction=result_selected_sessions"
                label="{{ __('All sessions') }}"
            />
        </x-dropdown>
    </div>
</div>
