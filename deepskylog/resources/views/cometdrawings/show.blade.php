<x-app-layout>
    <div>
        <div
            class="max-w-screen mx-auto bg-gray-900 px-2 py-10 sm:px-6 lg:px-8"
        >
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight">
                    {{ __("Comet sketches of " . $user->name) }}
                </h2>
                <x-button
                    gray
                    icon="eye"
                    class="mb-2"
                    href="/drawings/{{ $user->slug }}"
                >
                    {{ __("Show deep-sky sketches") }}
                </x-button>
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
                                <a
                                    href="{{ config("app.old_url") }}/index.php?indexAction=comets_detail_observation&observation={{ $sketch->id }}"
                                >
                                    <img
                                        width="400"
                                        src="/images/cometdrawings/{{ $sketch->id }}.jpg"
                                    />

                                    <div class="text-center">
                                        {{ $sketch->object->name }}
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
