@php use Carbon\Carbon; @endphp
<div>
    <div class="border-t border-gray-400 pb-1 pt-4">
        <div class="flex items-center px-4">
            <div>
                <div class="text-base font-medium text-gray-200">
                    {{ __("My quick links") }}
                </div>
            </div>
        </div>

        <div class="mt-3 space-y-1">
            @if (! Auth::guest() && Auth::user()->isObserver())
                @php $me = Auth::user(); $meSlug = $me ? ($me->slug ?? $me->username) : null; @endphp
                <x-dropdown.item
                    icon="bars-3-center-left"
                    href="{{ $meSlug ? url('/observations/'.$meSlug) : '#' }}"
                    label="{{ __('My observations') }}"/>

                <x-dropdown.item
                    icon="pencil-square"
                    href="/drawings/{{ Auth::user()->slug }}"
                    label="{{__('My drawings') }}"/>

                @php $me = Auth::user(); $meSlug = $me ? ($me->slug ?? $me->username) : null; @endphp
                <x-dropdown.item href="{{ $meSlug ? route('session.user', [$meSlug]) : '#' }}">
                    <x-outline.session-icon class="h-4 w-4 mr-3 text-gray-300 inline-block align-middle" />
                    {{ __('My sessions') }}
                </x-dropdown.item>

                <x-dropdown.item separator href="/instrument">
                    <x-outline.telescope-icon class="h-4 w-4 mr-3 text-gray-300 inline-block align-middle" />
                    {{ __('My instruments') }}
                </x-dropdown.item>

                <x-dropdown.item href="/instrumentset">
                    <x-outline.instrument-set-icon class="h-4 w-4 mr-3 text-gray-300 inline-block align-middle" />
                    {{ __('My instrument sets') }}
                </x-dropdown.item>

                <x-dropdown.item
                    icon="globe-europe-africa"
                    href="/location"
                    label="{{ __('My locations') }}"
                />

                <x-dropdown.item href="/eyepiece">
                    <x-outline.eyepiece-icon class="h-4 w-4 mr-3 text-gray-300 inline-block align-middle" />
                    {{ __('My eyepieces') }}
                </x-dropdown.item>

                <x-dropdown.item href="/filter">
                    <x-outline.filter-icon class="h-4 w-4 mr-3 text-gray-300 inline-block align-middle" />
                    {{ __('My filters') }}
                </x-dropdown.item>

                <x-dropdown.item href="/lens">
                    <x-outline.barlow-icon class="h-4 w-4 mr-3 text-gray-300 inline-block align-middle" />
                    {{ __('My lenses') }}
                </x-dropdown.item>

                <x-dropdown.item
                    separator
                    icon="bars-3-center-left"
                    href="{{ config('app.old_url') }}/index.php?indexAction=view_lists"
                    label="{!! __('My observing lists') !!}"
                />

            @endif
        </div>
    </div>
</div>
