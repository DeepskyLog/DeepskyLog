<x-app-layout>
    <div>
        <div class="max-w-screen mx-auto bg-gray-900 px-2 py-10 sm:px-6 lg:px-8">
            <h2 class="text-xl font-semibold leading-tight">
                @if ($week_month == 'Week')
                    {{ __('DeepskyLog Sketch of the Week') }}
                @else
                    {{ __('DeepskyLog Sketch of the Month') }}
                @endif
            </h2>
            <div class="mt-2">
                <x-card>
                    <div class="flex flex-wrap px-5">
                        @foreach ($sketches as $sketch)
                            <div class="mt-3 max-w-xl pr-3">
                                {{-- Show the correct drawing --}}
                                @php
                                    if ($sketch->observation_id < 0) {
                                        $sketch_id = -$sketch->observation_id;
                                    } else {
                                        $sketch_id = $sketch->observation_id;
                                    }
                                @endphp

                                @if ($sketch->observation_id < 0)
                                    <a class="no-underline"
                                        href="{{ config('app.old_url') }}/index.php?indexAction=comets_detail_observation&observation={{ -$sketch->observation_id }}">
                                        <img width="400"
                                            src="/images/cometdrawings/{{ -$sketch->observation_id }}.jpg" />

                                        <div class="text-center">
                                            {{ $sketch->user->name }} -
                                            {{ \App\Models\CometObservationsOld::find(-$sketch->observation_id)->object->name }}
                                            -
                                            {{ \Carbon\Carbon::create($sketch->date)->translatedFormat('j M Y') }}
                                        </div>
                                        <div class="text-center">
                                            {!! ShareButtons::page(
                                                'https://www.deepskylog.org/comets/cometdrawings/' . -$sketch->observation_id . '.jpg',
                                                __('Look at this sketch of ') .
                                                    \App\Models\CometObservationsOld::find(-$sketch->observation_id)->object->name .
                                                    __(' by ') .
                                                    $sketch->user->name .
                                                    __(' on #deepskylog'),
                                                [
                                                    'title' => __('Share this sketch'),
                                                    'class' => 'text-gray-500 hover:text-gray-700',
                                                    'rel' => 'nofollow noopener noreferrer',
                                                ],
                                            )->facebook(['class' => 'hover', 'rel' => 'follow'])->twitter(['class' => 'hover', 'rel' => 'follow'])->copylink()->mailto(['class' => 'hover', 'rel' => 'nofollow'])->whatsapp()->render() !!}
                                        </div>
                                    </a>
                                @else
                                    <a
                                        href="{{ config('app.old_url') }}/index.php?indexAction=detail_observation&observation={{ $sketch->observation_id }}">
                                        <img width="400" src="/images/drawings/{{ $sketch->observation_id }}.jpg" />

                                        <div class="text-center">
                                            {{ $sketch->user->name }} -
                                            {{ $sketch->observation->objectname }}
                                            -
                                            {{ \Carbon\Carbon::create($sketch->date)->translatedFormat('j M Y') }}
                                        </div>

                                        <div class="text-center">
                                            {!! ShareButtons::page(
                                                'https://www.deepskylog.org/deepsky/drawings/' . $sketch->observation_id . '.jpg',
                                                __('Look at this sketch of ') .
                                                    $sketch->observation->objectname .
                                                    __(' by ') .
                                                    $sketch->user->name .
                                                    __(' on #deepskylog'),
                                                [
                                                    'title' => __('Share this sketch'),
                                                    'class' => 'text-gray-500 hover:text-gray-700',
                                                    'rel' => 'nofollow noopener noreferrer',
                                                ],
                                            )->facebook(['class' => 'hover', 'rel' => 'follow'])->twitter(['class' => 'hover', 'rel' => 'follow'])->copylink()->mailto(['class' => 'hover', 'rel' => 'nofollow'])->whatsapp()->render() !!}
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
