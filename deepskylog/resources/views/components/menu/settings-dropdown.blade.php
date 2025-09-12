<!-- Settings Dropdown -->
<div class="relative ml-3">
    @if (Auth::user())
    <x-dropdown align="right" width="48" height="max-h-[70vh]">
            <x-slot name="trigger">
                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                    <x-avatar
                        sm
                        src="{{ Auth::user()->profile_photo_url }}"
                        alt="{{ Auth::user()->name }}"
                    />
                @else
                    <span class="inline-flex rounded-md">
                        <button
                            type="button"
                            class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition hover:text-gray-700 focus:bg-gray-50 focus:outline-hidden active:bg-gray-50"
                        >
                            {{ Auth::user()->name }}

                            <svg
                                class="-mr-0.5 ml-2 h-4 w-4"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M19.5 8.25l-7.5 7.5-7.5-7.5"
                                />
                            </svg>
                        </button>
                    </span>
                @endif
            </x-slot>

            <!-- Account Management -->
            {{-- Move observer-specific quick links here so they're available in the user menu --}}
            @if (!Auth::guest() && Auth::user()->isObserver())
                <x-dropdown.item icon="bars-3-center-left"
                                 href="{{ config('app.old_url') }}/index.php?indexAction=result_selected_observations&observer={{ Auth::user()->username }}"
                                 label="{{ __('My observations') }}"/>

                <x-dropdown.item icon="pencil-square" href="/drawings/{{ Auth::user()->slug }}"
                                 label="{{ __('My drawings') }}"/>

                @php $me = Auth::user(); $meSlug = $me ? ($me->slug ?? $me->username) : null; @endphp
                <x-dropdown.item href="{{ $meSlug ? route('session.user', [$meSlug]) : '#' }}"
                                 label="{{ __('My sessions') }}"/>

                <x-dropdown.item separator
                                 icon="bars-3-center-left"
                                 href="{{ config('app.old_url') }}/index.php?indexAction=view_lists"
                                 label="{!! __('My observing lists') !!}"/>

                <x-dropdown.item separator
                                 href="/instrument"
                                 label="{{ __('My instruments') }}"/>

                <x-dropdown.item
                                 href="/instrumentset"
                                 label="{{ __('My instrument sets') }}"/>

                <x-dropdown.item icon="globe-europe-africa"
                                 href="/location"
                                 label="{{ __('My locations') }}"/>

                <x-dropdown.item href="/eyepiece"
                                 label="{{ __('My eyepieces') }}"/>

                <x-dropdown.item href="/filter"
                                 label="{{ __('My filters') }}"/>

                <x-dropdown.item href="/lens"
                                 label="{{ __('My lenses') }}"/>

            @endif

            <x-dropdown.item  separator
                href="/observers/{{ Auth::user()->slug }}"
                label="{{ __('Details') }}"
            />

            <x-dropdown.item
                icon="cog"
                href="{{ route('profile.show') }}"
                label="{{ __('Profile') }}"
            />

            @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                <x-dropdown.item
                    label="{!! __('API Tokens') !!}"
                    href="{{ route('api-tokens.index') }}"
                />
            @endif

            <!-- Authentication -->
            <form method="POST" action="{{ route("logout") }}" x-data>
                @csrf

                <x-dropdown.item
                    separator
                    href="{{ route('logout') }}"
                    @click.prevent="$root.submit();"
                    label="{{ __('Log Out') }}"
                />
            </form>
        </x-dropdown>
    @endif
</div>
