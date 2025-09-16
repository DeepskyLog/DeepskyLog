<!-- Help Dropdown -->
<div class="hidden lg:ml-2 lg:flex lg:items-center">
    <div class="relative mr-0 text-sm">
    <x-menu.dropdown :width="60" align="right">
            <x-slot name="trigger">
                <button x-ref="trigger" @click="open = !open" aria-haspopup="true" :aria-expanded="open.toString()" class="inline-flex items-center rounded px-3 py-2 text-sm font-medium text-gray-200 hover:bg-gray-800">
                    {{ __("Help") }}
                </button>
            </x-slot>

            <x-menu.item icon="question-mark-circle" href="mailto:deepskylog@groups.io">{{ __('Ask a question') }}</x-menu.item>
            <x-menu.item icon="at-symbol" href="https://groups.io/g/deepskylog">{!! __('Subscribe to mailing list') !!}</x-menu.item>
            <x-menu.item icon="banknotes" href="/sponsors">{{ __('Sponsor DeepskyLog') }}</x-menu.item>
            <x-menu.item icon="bolt" href="https://github.com/DeepskyLog/DeepskyLog/issues">{{ __('Report issue') }}</x-menu.item>
            <x-menu.item icon="rss" href="https://github.com/DeepskyLog/DeepskyLog/wiki/What's-New-in-DeepskyLog">{{ __('New in DeepskyLog') }}</x-menu.item>
        </x-menu.dropdown>
    </div>
</div>
