<!-- Teams Dropdown -->
@if (Auth::user() && Laravel\Jetstream\Jetstream::hasTeamFeatures() && Auth::user()->teams->count() > 1)
    <div class="relative ml-3">
    <x-menu.dropdown align="right" :width="60">
            <x-slot name="trigger">
                <span class="inline-flex rounded-md">
                    <button
                        x-ref="trigger"
                        @click="open = !open"
                        type="button"
                        class="inline-flex items-center rounded-md border border-transparent bg-gray-700 px-3 py-2 text-sm font-medium leading-4 text-gray-300 transition hover:bg-gray-500 hover:text-gray-200 focus:bg-gray-500 focus:outline-hidden active:bg-gray-500"
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
            <x-menu.header label="{!! __('Manage Team') !!}">
                <!-- Team Settings -->
                <x-menu.item
                    :label="__('Team Settings')"
                    href="{{ route('teams.show', Auth::user()->currentTeam->slug) }}"
                />
            </x-menu.header>

            <!-- Team Switcher -->
            <x-menu.header label="{!! __('Switch Teams') !!}">
                @foreach (Auth::user()->allTeams() as $team)
                    <x-switchable-team :team="$team" component="link" />
                @endforeach
            </x-menu.header>
        </x-menu.dropdown>
    </div>
@endif
