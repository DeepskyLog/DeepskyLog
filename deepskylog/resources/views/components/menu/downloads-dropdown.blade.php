<!-- Downloads Dropdown -->
<div class="hidden lg:ml-2 lg:flex lg:items-center">
    <div class="relative mr-0 text-sm">
    <x-menu.dropdown :width="48">
            <x-slot name="trigger">
                <button x-ref="trigger" @click="open = !open" aria-haspopup="true" :aria-expanded="open.toString()" class="inline-flex items-center rounded px-3 py-2 text-sm font-medium text-gray-200 hover:bg-gray-800">
                    {{ __("Downloads") }}
                </button>
            </x-slot>

            <x-menu.item icon="rectangle-stack" href="{{ config('app.old_url') }}/index.php?indexAction=downloadAstroImageCatalogs">{!! __('Image catalogs') !!}</x-menu.item>
            <x-menu.item icon="arrow-down-tray" href="{{ config('app.old_url') }}/index.php?indexAction=view_atlaspages">{{ __('Atlases') }}</x-menu.item>
            <x-menu.item icon="clipboard" href="/downloads/forms">{{ __('Forms') }}</x-menu.item>

            <x-menu.item icon="book-open" href="/downloads/magazines">{{ __('Deep-sky magazines') }}</x-menu.item>
        </x-menu.dropdown>
    </div>
</div>
