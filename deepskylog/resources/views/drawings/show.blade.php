<x-app-layout>
    <div>
        <div
            class="max-w-screen mx-auto bg-gray-900 px-2 py-10 sm:px-6 lg:px-8"
        >
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight">
                    @if ($user == "")
                        {{ __("Sketches") }}
                    @else
                        {{ __("Sketches of " . $user->name) }}
                    @endif
                </h2>
                @if ($user == "")
                    <x-button
                        gray
                        icon="eye"
                        class="mb-2"
                        href="/cometdrawings"
                    >
                        {{ __("Show comet sketches") }}
                    </x-button>
                @else
                    <x-button
                        gray
                        icon="eye"
                        class="mb-2"
                        href="/cometdrawings/{{ $user->slug }}"
                    >
                        {{ __("Show comet sketches") }}
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
                                <a
                                    href="{{ config("app.old_url") }}/index.php?indexAction=detail_observation&observation={{ $sketch->id }}"
                                >
                                    <img
                                        width="400"
                                        src="/images/drawings/{{ $sketch->id }}.jpg"
                                    />

                                    <div class="text-center">
                                        @if ($user == "")
                                            {{ \App\Models\User::where("username", $sketch->observerid)->first()->name }}
                                            -
                                        @endif

                                        {{ $sketch->objectname }}
                                        -
                                        {{
                                            \Carbon\Carbon::create(
                                                substr($sketch->date, 0, 4),
                                                substr($sketch->date, 4, 2),
                                                substr($sketch->date, 6, 2),
                                            )->format("j M Y")
                                        }}
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                    {{ $sketches->links() }}
                </x-card>
            </div>
        </div>
    </div>
</x-app-layout>
