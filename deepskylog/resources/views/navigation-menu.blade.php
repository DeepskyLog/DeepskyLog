<nav x-data="{ open: false }" class="border-b border-gray-700 bg-gray-900">
    <!-- Primary Navigation Menu -->
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 justify-between">
            <div class="flex">
                <!-- Navigation Links -->

                <div class="-my-px flex h-16 items-center space-x-8">
                    <x-nav-link href="/">
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

                <!-- Language selection -->
                <x-language_selection />

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
                                    {!! __("Register") !!}
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
            </div>

            <!-- Hamburger -->
            <div class="-mr-2 flex items-center lg:hidden">
                <button
                    @click="open = ! open"
                    class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition hover:bg-gray-800 hover:text-gray-500 focus:bg-gray-800 focus:text-gray-500 focus:outline-none"
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

        <!-- Responsive Navigation Menu -->
        <div :class="{ 'block': open, 'hidden': !open }" class="hidden">
            <!-- Responsive View Dropdown -->
            <x-menu.view-responsive-dropdown />

            <!-- Responsive Search Dropdown -->
            <x-menu.search-responsive-dropdown />

            <!-- Responsive Add Dropdown -->
            <x-menu.add-responsive-dropdown />

            <!-- Responsive Administration Dropdown -->
            <x-menu.admin-responsive-dropdown />

            <!-- Responsive Downloads Dropdown -->
            <x-menu.downloads-responsive-dropdown />

            <!-- Responsive Help Dropdown -->
            <x-menu.help-responsive-dropdown />

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
                            <div class="text-base font-medium text-gray-400">
                                {{ Auth::user()->name }}
                            </div>
                            <div class="text-sm font-medium text-gray-400">
                                {{ Auth::user()->email }}
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 space-y-1">
                        <x-dropdown.item
                            href="/observers/{{ Auth::user()->slug }}"
                            label="{{ __('Details') }}"
                        />

                        <!-- Account Management -->
                        <x-dropdown.item
                            href="{{ route('profile.show') }}"
                            label="{{ __('Profile') }}"
                        />

                        @if (Laravel\Jetstream\Jetstream::hasApiFeatures())
                            <x-dropdown.item
                                href="{{ route('api-tokens.index') }}"
                                label="{{ __('API Tokens') }}"
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
                                href="{{ route('logout') }}"
                                @click.prevent="$root.submit();"
                                label='{{ __("Log Out") }}'
                            />
                        </form>

                        <!-- Team Management -->
                        @if (Auth::user() && Laravel\Jetstream\Jetstream::hasTeamFeatures() && Auth::user()->teams->count() > 1)
                            <div class="border-t border-gray-400"></div>

                            <div class="block px-4 py-2 text-xs text-gray-400">
                                {!! __("Manage Team") !!}
                            </div>

                            <!-- Team Settings -->
                            <x-dropdown.item
                                href="{{ route('teams.show', Auth::user()->currentTeam->slug) }}"
                                :active="request()->routeIs('teams.show')"
                                label='{!! __("Team Settings") !!}'
                            />

                            <div class="border-t border-gray-400"></div>

                            <!-- Team Switcher -->
                            <div class="block px-4 py-2 text-xs text-gray-400">
                                {!! __("Switch Teams") !!}
                            </div>

                            @foreach (Auth::user()->allTeams() as $team)
                                <x-switchable-team
                                    :team="$team"
                                    component="x-dropdown.item"
                                />
                            @endforeach
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</nav>
