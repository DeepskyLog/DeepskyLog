<!-- Administration Dropdown -->
@if (!Auth::guest())
    @if (Auth::user()->isAdministrator() || Auth::user()->isDatabaseExpert())
        <div class="hidden lg:ml-2 lg:flex lg:items-center">
            <div class="relative mr-0 text-sm">
                <x-menu.dropdown :width="56">
                    <x-slot name="trigger">
                        <button x-ref="trigger" @click="open = !open" aria-haspopup="true" :aria-expanded="open.toString()" class="inline-flex items-center rounded px-3 py-2 text-sm font-medium text-gray-200 hover:bg-gray-800">{{ __('Administration') }}</button>
                    </x-slot>

                    @if (Auth::user()->isAdministrator())
                        <x-menu.item icon="envelope-open" href="{{ route('messages.create', ['receiver' => 'all']) }}">{{ __('Send message to all') }}</x-menu.item>

                        <x-menu.item separator icon="users" href="/observers/admin">{{ __('Observers') }}</x-menu.item>
                    @endif

                    <x-menu.item separator icon="plus" href="/sketch-of-the-week/create">{{ __('Add sketch of the week') }}</x-menu.item>

                    <x-menu.item icon="plus" href="/sketch-of-the-month/create">{{ __('Add sketch of the month') }}</x-menu.item>

                    @if (Auth::user()->isAdministrator())
                        <x-menu.item separator icon="check-badge" href="{{ config('app.old_url') }}/index.php?indexAction=admin_check_objects">{{ __('Check Objects') }}</x-menu.item>
                    @else
                        <x-menu.item icon="check-badge" href="{{ config('app.old_url') }}/index.php?indexAction=admin_check_objects">{{ __('Check Objects') }}</x-menu.item>
                    @endif

                    @if (Auth::user()->isAdministrator())
                        <x-menu.item href="/admin/instrument">{{ __('Instrument Makes') }}</x-menu.item>

                        <x-menu.item href="/admin/eyepiece">{{ __('Eyepiece Makes') }}</x-menu.item>

                        <x-menu.item href="/admin/eyepiece-types">{{ __('Eyepiece Types') }}</x-menu.item>

                        <x-menu.item href="/admin/filter">{{ __('Filter Makes') }}</x-menu.item>

                        <x-menu.item href="/admin/lens">{{ __('Lens Makes') }}</x-menu.item>
                    @endif
                </x-menu.dropdown>
            </div>
        </div>
    @endif
@endif
