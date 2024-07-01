<!-- Downloads Dropdown -->
<div>
    <div class="border-t border-gray-400 pb-1 pt-4">
        <div class="flex items-center px-4">
            <div>
                <div class="text-base font-medium text-gray-200">
                    {{ __("Downloads") }}
                </div>
            </div>
        </div>
        <div class="mt-3 space-y-1">
            <x-dropdown.item
                icon="rectangle-stack"
                href="{{ config('app.old_url') }}/index.php?indexAction=downloadAstroImageCatalogs"
                label="{!! __('Image catalogs') !!}"
            />
            <x-dropdown.item
                icon="arrow-down-tray"
                href="{{ config('app.old_url') }}/index.php?indexAction=view_atlaspages"
                label="{{ __('Atlases') }}"
            />
            <x-dropdown.item
                icon="clipboard"
                href="{{ config('app.old_url') }}/index.php?indexAction=downloadForms"
                label="{{ __('Forms') }}"
            />

            <x-dropdown.item
                icon="book-open"
                href="/downloads/magazines"
                label="{{ __('Deep-sky magazines') }}"
            />
        </div>
    </div>
</div>
