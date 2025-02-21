<x-app-layout>
    <div>
        <div
            class="max-w-screen mx-auto bg-gray-900 px-2 py-10 sm:px-6 lg:px-8"
        >
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight">
                    @if ($user == "")
                        {{ __("Comet sketches") }}
                    @else
                        {{ __("Comet sketches of " . $user->name) }}
                    @endif
                </h2>
                @if ($user == "")
                    <x-button gray icon="eye" class="mb-2" href="/drawings">
                        {{ __("Show deep-sky sketches") }}
                    </x-button>
                @else
                    <x-button
                        gray
                        icon="eye"
                        class="mb-2"
                        href="/drawings/{{ $user->slug }}"
                    >
                        {{ __("Show deep-sky sketches") }}
                    </x-button>
                @endif
            </div>
            <div class="mt-2">
                <x-card>
                    <div class="flex flex-wrap px-5">
                        @if ($sketches->isEmpty())
                            <div class="text-center">
                                {{ __("No sketches yet...") }}
                            </div>
                        @endif

                        @foreach ($sketches as $sketch)
                            <div class="mt-3 max-w-xl pr-3">
                                {{-- Show the correct drawing --}}
                                @php
                                    $observer_name = \App\Models\User::where("username", $sketch->observerid)->first()->name;
                                    $observation_date = substr($sketch->date, 0, 4) . "-" . substr($sketch->date, 4, 2) . "-" . substr($sketch->date, 6, 2);
                                @endphp

                                <x-sketch-comet
                                    :observation_id="$sketch->id"
                                    :observer_name="$observer_name"
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
