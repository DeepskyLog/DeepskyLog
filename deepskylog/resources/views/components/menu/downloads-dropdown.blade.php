<!-- Downloads Dropdown -->
<div class="hidden sm:ml-6 sm:flex sm:items-center">
    <div class="relative mr-3 text-sm">
        <x-dropdown width="48" position="bottom-start">
            <x-slot name="trigger">
                {{ __("Downloads") }}
            </x-slot>

            <x-dropdown.item
                icon="collection"
                href="{{ config('app.old_url') }}/index.php?indexAction=downloadAstroImageCatalogs"
                label="{{ __('Image catalogs') }}"
            />
            <x-dropdown.item
                icon="download"
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
        </x-dropdown>
    </div>
</div>
