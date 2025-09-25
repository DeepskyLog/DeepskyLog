<!-- Search Dropdown -->
<div>
    <div class="border-t border-gray-400 pb-1 pt-4">
        <div class="flex items-center px-4">
            <div>
                <div class="text-base font-medium text-gray-200">
                    {{ __("Search") }}
                </div>
            </div>
        </div>
        <div class="mt-3 space-y-1">
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
        </div>
    </div>
</div>
