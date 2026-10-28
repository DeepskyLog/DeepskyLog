<x-app-layout>
    <div>
        <div class="max-w-screen mx-auto bg-gray-900 px-2 py-10 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-xl font-semibold leading-tight">
                    @if ($user == '')
                        {{ __('Comet sketches') }}
                    @else
                        {{ __('Comet sketches of ' . $user->name) }}
                    @endif
                </h2>
                {{-- Only show the deep-sky sketches button for the global page or a real user. --}}
                @if ($user == '' || (is_object($user) && isset($user->username) && $user->username))
                    @if ($user == '')
                        <x-button gray icon="eye" class="mb-2" href="/drawings">
                            {{ __('Show deep-sky sketches') }}
                        </x-button>
                    @else
                        <x-button gray icon="eye" class="mb-2" href="/drawings/{{ $user->slug }}">
                            {{ __('Show deep-sky sketches') }}
                        </x-button>
                    @endif
                @endif
            </div>
            <div class="mt-2">
                <x-card>
                    <div class="flex flex-wrap px-5">
                        @if ($sketches->isEmpty())
                            <div class="text-center">
                                {{ __('No sketches yet...') }}
                            </div>
                        @endif

                        @foreach ($sketches as $sketch)
                            <div class="mt-3 max-w-xl pr-3">
                                {{-- Show the correct drawing --}}
                                @php
                                    $sketchUser = \App\Services\Observations\ObservationCardData::instance()->user($sketch->observer_username);
                                    $observer_name = $sketchUser ? $sketchUser->name : $sketch->observer_username;
                                    $observation_date = optional($sketch->observed_on)?->format('Y-m-d');
                                @endphp

                                <x-sketch-comet
                                    :observation_id="$sketch->legacy_id"
                                    :unified_observation="$sketch"
                                    :observer_name="$observer_name"
                                    :observer_username="$sketch->observer_username"
                                    :observer_slug="$sketchUser?->slug"
                                    :observation_date="$observation_date"
                                />
                            </div>
                        @endforeach
                    </div>
                    {{ $sketches->links() }}
                </x-card>
            </div>
        </div>
    </div>
</x-app-layout>
