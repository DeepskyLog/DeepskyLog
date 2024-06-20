<nav x-data="{ open: false }" class="border-b border-gray-700 bg-gray-900">
    <!-- Primary Navigation Menu -->
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 justify-between">
            <div class="flex">
                <!-- Navigation Links -->

                <div class="-my-px ml-10 flex h-16 items-center space-x-8">
                    <x-nav-link
                        href="{{ route('dashboard') }}"
                        :active="request()->routeIs('dashboard')"
                    >
                        <div class="text-xl font-bold">
                            {{ __("DeepskyLog") }}
                        </div>
                    </x-nav-link>
                </div>

                <!-- View Dropdown -->
                <x-menu.view-dropdown />

                <!-- Search Dropdown -->
                <x-menu.search-dropdown />

                <!-- Add Dropdown -->
                <x-menu.add-dropdown />

                <!-- Administration Dropdown -->
                <x-menu.admin-dropdown />

                <!-- Downloads Dropdown -->
                <x-menu.downloads-dropdown />

                <!-- Help Dropdown -->
                <x-menu.help-dropdown />
            </div>
            <div class="flex">
                <div class="hidden lg:ml-6 lg:flex lg:items-center">
                    <!-- Teams Dropdown -->
                    <x-menu.team-dropdown />

                    <!-- Settings Dropdown -->
                    <x-menu.settings-dropdown />
                </div>

                <!-- Login / Register dropdown -->
                @if (Auth::guest())
                    <div class="flex h-16 items-center justify-between">
                        <!-- Navigation Links -->
                        <div class="space-x-8 sm:-my-px sm:ml-10 sm:flex-none">
                            <a
                                href="{{ route("login") }}"
                                class="text-sm text-gray-700 underline dark:text-gray-500"
                            >
                                {{ __("Log in") }}
                            </a>

                            @if (Route::has("register"))
                                <a
                                    href="{{ route("register") }}"
                                    class="ml-4 text-sm text-gray-700 underline dark:text-gray-500"
                                >
                                    {{ __("Register") }}
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
            <div class="flex items-center">
                {{-- Post box --}}
                @if (! Auth::guest())
                    <div class="space-x-2 lg:ml-6 lg:flex lg:items-center">
                        <x-nav-link
                            href="{{ config('app.old_url') }}/index.php?indexAction=show_messages"
                        >
                            <div class="flex space-x-2">
                                <x-icon name="inbox" class="h-5 w-5" />
                                <x-mini-badge
                                    rounded
                                    secondary
                                    label="{{ App\Models\MessagesOld::getNumberOfUnreadMails(Auth::user()->username) }}"
                                />
                            </div>
                        </x-nav-link>
                    </div>
                @endif

                <!-- Module Dropdown -->
                <div class="hidden lg:ml-6 lg:flex lg:items-center">
                    <div class="relative mr-3 text-sm">
                        <x-dropdown width="48" position="bottom-start">
                            <x-slot name="trigger">
                                {{ __("Deepsky") }}
                            </x-slot>
                            <x-dropdown.item
                                href="{{ config('app.old_url') }}/index.php?indexAction=modulecomets"
                                label="{{ __('Comets') }}"
                            />
                        </x-dropdown>
                    </div>
                </div>

                <!-- Hamburger -->
                <div class="-mr-2 flex items-center lg:hidden">
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
        <div
            :class="{ 'block': open, 'hidden': !open }"
            class="hidden sm:hidden"
        >
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
                        <form
                            method="POST"
                            action="{{ route("logout") }}"
                            x-data
                        >
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
    </div>
</nav>
