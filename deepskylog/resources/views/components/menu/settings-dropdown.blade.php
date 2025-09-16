<!-- Settings Dropdown -->
<div class="relative ml-3">
    @if (Auth::user())
        <x-menu.dropdown align="right" :width="56">
            <x-slot name="trigger">
                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                    <button x-ref="trigger" @click="open = !open" aria-haspopup="true" :aria-expanded="open.toString()" class="inline-flex items-center rounded px-2 py-1 text-sm text-gray-200 hover:bg-gray-800">
                        <x-avatar
                            sm
                            src="{{ Auth::user()->profile_photo_url }}"
                            alt="{{ Auth::user()->name }}"
                        />
                    </button>
                @else
                    <span class="inline-flex rounded-md">
                        <button
                            x-ref="trigger"
                            @click="open = !open"
                            aria-haspopup="true"
                            :aria-expanded="open.toString()"
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
                <x-menu.item icon="bars-3-center-left" href="{{ config('app.old_url') }}/index.php?indexAction=result_selected_observations&observer={{ Auth::user()->username }}">{{ __('My observations') }}</x-menu.item>

                <x-menu.item icon="pencil-square" href="/drawings/{{ Auth::user()->slug }}">{{ __('My drawings') }}</x-menu.item>

                @php $me = Auth::user(); $meSlug = $me ? ($me->slug ?? $me->username) : null; @endphp
                <x-menu.item href="{{ $meSlug ? route('session.user', [$meSlug]) : '#' }}">
                    <x-outline.session-icon class="h-4 w-4 mr-3 text-gray-300 inline-block align-middle" />
                    {{ __('My sessions') }}
                </x-menu.item>

                <x-menu.item separator icon="bars-3-center-left" href="{{ config('app.old_url') }}/index.php?indexAction=view_lists">{!! __('My observing lists') !!}</x-menu.item>

                <x-menu.item separator href="/instrument">
                    <x-outline.telescope-icon class="h-4 w-4 mr-3 text-gray-300 inline-block align-middle" />
                    {{ __('My instruments') }}
                </x-menu.item>

                <x-menu.item href="/instrumentset">
                    <x-outline.instrument-set-icon class="h-4 w-4 mr-3 text-gray-300 inline-block align-middle" />
                    {{ __('My instrument sets') }}
                </x-menu.item>

                <x-menu.item icon="globe-europe-africa" href="/location">{{ __('My locations') }}</x-menu.item>

                <x-menu.item href="/eyepiece">
                    <x-outline.eyepiece-icon class="h-4 w-4 mr-3 text-gray-300 inline-block align-middle" />
                    {{ __('My eyepieces') }}
                </x-menu.item>

                <x-menu.item href="/filter">
                    <x-outline.filter-icon class="h-4 w-4 mr-3 text-gray-300 inline-block align-middle" />
                    {{ __('My filters') }}
                </x-menu.item>

                <x-menu.item href="/lens">
                    <x-outline.barlow-icon class="h-4 w-4 mr-3 text-gray-300 inline-block align-middle" />
                    {{ __('My lenses') }}
                </x-menu.item>

                <x-menu.item separator icon="user-circle" href="/observers/{{ Auth::user()->slug }}">{{ __('Details') }}</x-menu.item>
            @else
                <x-menu.item icon="user-circle" href="/observers/{{ Auth::user()->slug }}">{{ __('Details') }}</x-menu.item>
            @endif


            <x-menu.item icon="cog" href="{{ route('profile.show') }}">{{ __('Profile') }}</x-menu.item>

            @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                <x-menu.item href="{{ route('api-tokens.index') }}">{!! __('API Tokens') !!}</x-menu.item>
            @endif

            <!-- Authentication -->
            <form method="POST" action="{{ route("logout") }}" x-data>
                @csrf

                <x-menu.item separator icon="arrow-left-start-on-rectangle" href="{{ route('logout') }}" @click.prevent="$root.submit();">{{ __('Log Out') }}</x-menu.item>
            </form>
        </x-menu.dropdown>
    @endif
</div>
