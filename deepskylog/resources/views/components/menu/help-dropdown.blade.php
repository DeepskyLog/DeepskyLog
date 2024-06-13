<!-- Help Dropdown -->
<div class="hidden sm:ml-6 sm:flex sm:items-center">
    <div class="relative mr-3 text-sm">
        <x-dropdown width="48" align="right" position="bottom-start">
            <x-slot name="trigger">
                {{ __("Help") }}
            </x-slot>

            <x-dropdown.item
                icon="question-mark-circle"
                href="mailto:deepskylog@groups.io"
                label="{{ __('Ask a question') }}"
            />
            <x-dropdown.item
                icon="at-symbol"
                href="https://groups.io/g/deepskylog"
                label="{{ __('Subscribe to mailing list') }}"
            />
            <x-dropdown.item
                icon="cash"
                href="/sponsors"
                label="{{ __('Sponsor DeepskyLog') }}"
            />
            <x-dropdown.item
                icon="lightning-bolt"
                href="https://github.com/DeepskyLog/DeepskyLog/issues"
                label="{{ __('Report issue') }}"
            />
            <x-dropdown.item
                icon="rss"
                href="https://github.com/DeepskyLog/DeepskyLog/wiki/What's-New-in-DeepskyLog"
                label="{{ __('New in DeepskyLog') }}"
            />
        </x-dropdown>
    </div>
</div>