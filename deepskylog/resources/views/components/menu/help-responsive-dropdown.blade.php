<!-- Help Dropdown -->
<div>
    <div class="border-t border-gray-400 pb-1 pt-4">
        <div class="flex items-center px-4">
            <div>
                <div class="text-base font-medium text-gray-200">
                    {{ __("Help") }}
                </div>
            </div>
        </div>
        <div class="mt-3 space-y-1">
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
        </div>
    </div>
</div>
