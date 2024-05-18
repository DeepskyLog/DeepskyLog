<nav x-data="{ open: false }" class="border-b border-gray-700 bg-gray-900">
    <!-- Primary Navigation Menu -->
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 justify-between">
            <div class="flex">
                <!-- Logo -->
                <div class="flex shrink-0 items-center">
                    <a href="{{ route("dashboard") }}">
                        <x-application-mark class="block h-9 w-auto" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <x-nav-link
                        href="{{ route('dashboard') }}"
                        :active="request()->routeIs('dashboard')"
                    >
                        {{ __("Dashboard") }}
                    </x-nav-link>
                </div>

                <!-- Help Dropdown -->
                <div class="hidden sm:ml-6 sm:flex sm:items-center">
                    <div class="relative mr-3 text-sm">
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">Help</x-slot>

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
            </div>

            <div class="hidden sm:ml-6 sm:flex sm:items-center">
                <!-- Teams Dropdown -->
                @if (Auth::user() && Laravel\Jetstream\Jetstream::hasTeamFeatures() && Auth::user()->teams->count() > 1)
                    <div class="relative ml-3">
                        <x-dropdown align="right" width="60">
                            <x-slot name="trigger">
                                <span class="inline-flex rounded-md">
                                    <button
                                        type="button"
                                        class="inline-flex items-center rounded-md border border-transparent bg-gray-700 px-3 py-2 text-sm font-medium leading-4 text-gray-300 transition hover:bg-gray-500 hover:text-gray-200 focus:bg-gray-500 focus:outline-none active:bg-gray-500"
                                    >
                                        {{ Auth::user()->currentTeam->name }}

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
                                                d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9"
                                            />
                                        </svg>
                                    </button>
                                </span>
                            </x-slot>

                            <!-- Team Management -->
                            <x-dropdown.header label="{{ __('Manage Team') }}">
                                <!-- Team Settings -->
                                <x-dropdown.item
                                    label="{{ __('Team Settings') }}"
                                    href="{{ route('teams.show', Auth::user()->currentTeam->slug) }}"
                                />
                            </x-dropdown.header>

                            <!-- Team Switcher -->
                            <x-dropdown.header label="{{ __('Switch Teams') }}">
                                @foreach (Auth::user()->allTeams() as $team)
                                    <x-switchable-team :team="$team" />
                                @endforeach
                            </x-dropdown.header>
                        </x-dropdown>
                    </div>
                @endif

                <!-- Settings Dropdown -->
                <div class="relative ml-3">
                    @if (Auth::user())
                        <x-dropdown align="right" width="48">
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
                                            class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition hover:text-gray-700 focus:bg-gray-50 focus:outline-none active:bg-gray-50"
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

                            <x-dropdown.header
                                label="{{ __('Manage Account') }}"
                            >
                                <!-- Account Management -->

                                <x-dropdown.item
                                    icon="cog"
                                    href="{{ route('profile.show') }}"
                                    label="{{ __('Profile') }}"
                                />

                                @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                                    <x-dropdown.item
                                        label="{{ __('API Tokens') }}"
                                        href="{{ route('api-tokens.index') }}"
                                    />
                                @endif

                                <!-- Authentication -->
                                <form
                                    method="POST"
                                    action="{{ route("logout") }}"
                                    x-data
                                >
                                    @csrf

                                    <x-dropdown.item
                                        separator
                                        href="{{ route('logout') }}"
                                        @click.prevent="$root.submit();"
                                        label="{{ __('Log Out') }}"
                                    />
                                </form>
                            </x-dropdown.header>
                        </x-dropdown>
                    @endif
                </div>
            </div>

            <!-- Hamburger -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button
                    @click="open = ! open"
                    class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none"
                >
                    <svg
                        class="h-6 w-6"
                        stroke="currentColor"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <path
                            :class="{ 'hidden': open, 'inline-flex': !open }"
                            class="inline-flex"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"
                        />
                        <path
                            :class="{ 'hidden': !open, 'inline-flex': open }"
                            class="hidden"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"
                        />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden">
        <div class="space-y-1 pb-3 pt-2">
            <x-responsive-nav-link
                href="{{ route('dashboard') }}"
                :active="request()->routeIs('dashboard')"
            >
                {{ __("Dashboard") }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        @if (Auth::user())
            <div class="border-t border-gray-400 pb-1 pt-4">
                <div class="flex items-center px-4">
                    @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                        <div class="mr-3 shrink-0">
                            <img
                                class="h-10 w-10 rounded-full object-cover"
                                src="{{ Auth::user()->profile_photo_url }}"
                                alt="{{ Auth::user()->name }}"
                            />
                        </div>
                    @endif

                    <div>
                        <div class="text-base font-medium text-gray-800">
                            {{ Auth::user()->name }}
                        </div>
                        <div class="text-sm font-medium text-gray-500">
                            {{ Auth::user()->email }}
                        </div>
                    </div>
                </div>

                <div class="mt-3 space-y-1">
                    <!-- Account Management -->
                    <x-responsive-nav-link
                        href="{{ route('profile.show') }}"
                        :active="request()->routeIs('profile.show')"
                    >
                        {{ __("Profile") }}
                    </x-responsive-nav-link>

                    @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                        <x-responsive-nav-link
                            href="{{ route('api-tokens.index') }}"
                            :active="request()->routeIs('api-tokens.index')"
                        >
                            {{ __("API Tokens") }}
                        </x-responsive-nav-link>
                    @endif

                    <!-- Authentication -->
                    <form method="POST" action="{{ route("logout") }}" x-data>
                        @csrf

                        <x-responsive-nav-link
                            href="{{ route('logout') }}"
                            @click.prevent="$root.submit();"
                        >
                            {{ __("Log Out") }}
                        </x-responsive-nav-link>
                    </form>

                    <!-- Team Management -->
                    @if (Laravel\Jetstream\Jetstream::hasTeamFeatures())
                        <div class="border-t border-gray-400"></div>

                        <div class="block px-4 py-2 text-xs text-gray-400">
                            {{ __("Manage Team") }}
                        </div>

                        <!-- Team Settings -->
                        <x-responsive-nav-link
                            href="{{ route('teams.show', Auth::user()->currentTeam->slug) }}"
                            :active="request()->routeIs('teams.show')"
                        >
                            {{ __("Team Settings") }}
                        </x-responsive-nav-link>

                        @can("create", Laravel\Jetstream\Jetstream::newTeamModel())
                            <x-responsive-nav-link
                                href="{{ route('teams.create') }}"
                                :active="request()->routeIs('teams.create')"
                            >
                                {{ __("Create New Team") }}
                            </x-responsive-nav-link>
                        @endcan

                        <div class="border-t border-gray-400"></div>

                        <!-- Team Switcher -->
                        <div class="block px-4 py-2 text-xs text-gray-400">
                            {{ __("Switch Teams") }}
                        </div>

                        @foreach (Auth::user()->allTeams() as $team)
                            <x-switchable-team
                                :team="$team"
                                component="responsive-nav-link"
                            />
                        @endforeach
                    @endif
                </div>
            </div>
        @endif
    </div>
</nav>
