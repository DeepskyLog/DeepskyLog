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
                href="{{ route('search.advanced') }}"
                label="{{ __('Search object') }}"
            />

            <x-dropdown.item
                icon="globe-europe-africa"
                href="{{ route('search.observations.advanced') }}"
                label="{{ __('Search Observations') }}"
            />
        </div>
    </div>
</div>
