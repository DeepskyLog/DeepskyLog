<!-- Help Dropdown -->
<div class="hidden lg:ml-6 lg:flex lg:items-center">
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
                icon="banknotes"
                href="/sponsors"
                label="{{ __('Sponsor DeepskyLog') }}"
            />
            <x-dropdown.item
                icon="bolt"
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
