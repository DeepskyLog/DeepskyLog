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
                <x-dropdown.item
                    icon="bars-3-center-left"
                    href="{{ config('app.old_url') }}/index.php?indexAction=result_selected_observations&observer={{  Auth::user()->username }}"
                    label="{{ __('My observations') }}"/>

                <x-dropdown.item
                    icon="pencil-square"
                    href="/drawings/{{ Auth::user()->slug }}"
                    label="{{__('My drawings') }}"/>

                @php $me = Auth::user(); $meSlug = $me ? ($me->slug ?? $me->username) : null; @endphp
                <x-dropdown.item
                    href="{{ $meSlug ? route('session.user', [$meSlug]) : '#' }}"
                    label="{{ __('My sessions') }}"
                />

                <x-dropdown.item
                    separator
                    href="/instrument"
                    label="{{ __('My instruments') }}"
                />

                <x-dropdown.item
                    href="/instrumentset"
                    label="{{ __('My instrument sets') }}"
                />

                <x-dropdown.item
                    icon="globe-europe-africa"
                    href="/location"
                    label="{{ __('My locations') }}"
                />

                <x-dropdown.item
                    href="/eyepiece"
                    label="{{ __('My eyepieces') }}"
                />

                <x-dropdown.item
                    href="/filter"
                    label="{{ __('My filters') }}"
                />

                <x-dropdown.item
                    href="/lens"
                    label="{{ __('My lenses') }}"
                />

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
