<x-app-layout>
    <div>
        <div class="mx-auto max-w-7xl bg-gray-900 py-10 sm:px-6 lg:px-8">
            <h2 class="text-xl font-semibold leading-tight">
                {{ __("DeepskyLog Sketches of the " . $week_month) }}
            </h2>
            <div class="mt-2">
                <x-card>
                    <div class="flex flex-wrap px-5">
                        @foreach ($sketches as $sketch)
                            <div class="mt-3 max-w-xl pr-3">
                                {{-- Show the correct drawing --}}
                                @if ($sketch->observation_id < 0)
                                    <a
                                        class="no-underline"
                                        href="{{ config("app.old_url") }}/index.php?indexAction=comets_detail_observation&observation={{ -$sketch->observation_id }}"
                                    >
                                        <x-avatar
                                            borderless="true"
                                            size="w-80 h-80"
                                            rounded="md"
                                            src="/images/cometdrawings/{{ -$sketch->observation_id }}.jpg"
                                            primary
                                        />
                                        <div class="text-center">
                                            {{ $sketch->user->name }} -
                                            {{ \App\Models\CometObservationsOld::find(-$sketch->observation_id)->object->name }}
                                            -
                                            {{ \Carbon\Carbon::create($sketch->date)->format("j M Y") }}
                                        </div>
                                    </a>
                                @else
                                    <a
                                        href="{{ config("app.old_url") }}/index.php?indexAction=detail_observation&observation={{ $sketch->observation_id }}"
                                    >
                                        <x-avatar
                                            borderless="true"
                                            rounded="md"
                                            size="w-80 h-80"
                                            src="/images/drawings/{{ $sketch->observation_id }}.jpg"
                                            primary
                                        />

                                        <div class="text-center">
                                            {{ $sketch->user->name }} -
                                            {{ $sketch->observation->objectname }}
                                            -
                                            {{ \Carbon\Carbon::create($sketch->date)->format("j M Y") }}
                                        </div>
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    {{ $sketches->links() }}
                </x-card>
            </div>
        </div>
    </div>
</x-app-layout>
