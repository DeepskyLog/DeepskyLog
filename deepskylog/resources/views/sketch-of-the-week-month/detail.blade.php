<x-app-layout>
    <div>
        <div class="max-w-screen mx-auto bg-gray-900 px-2 py-10 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight">
                    {{ __('Newly added sketch of the ') . $week_month . ': ' . $date }}
                </h2>
            </div>
            <div class="mt-2">
                <x-card>
                    <div class="flex flex-wrap px-5">
                        <div class="mt-3 max-w-xl pr-3">
                            @if ($observation_id < 0)
                                <a
                                    href="{{ config('app.old_url') }}/index.php?indexAction=comets_detail_observation&observation={{ -$observation_id }}">
                                    <img width="400" src="/images/cometdrawings/{{ -$observation_id }}.jpg"/>

                                    <div class="text-center">
                                        {!! ShareButtons::page("", $share, [
                                            'title' => __('Share this sketch'),
                                            'class' => 'text-gray-500 hover:text-gray-700',
                                            'rel' => 'nofollow noopener noreferrer',
                                        ])->facebook(['class' => 'hover', 'rel' => 'follow'])
                                        ->twitter(['class' => 'hover', 'rel' => 'follow'])
                                        ->copylink()
                                        ->mailto(['class' => 'hover', 'rel' => 'nofollow'])
                                        ->whatsapp()->bluesky(["class" => "hover", "rel" => "follow"])
                                        ->render() !!}
                                    </div>
                                </a>
                            @else
                                <a
                                    href="{{ config('app.old_url') }}/index.php?indexAction=detail_observation&observation={{ $observation_id }}">
                                    <img width="400" src="/images/drawings/{{ $observation_id }}.jpg"/>

                                    <div class="text-center">
                                        {!! ShareButtons::page("", $share, [
                                            'title' => __('Share this sketch'),
                                            'class' => 'text-gray-500 hover:text-gray-700',
                                            'rel' => 'nofollow noopener noreferrer',
                                        ])->facebook(['class' => 'hover', 'rel' => 'follow'])
                                        ->twitter(['class' => 'hover', 'rel' => 'follow'])
                                        ->copylink()
                                        ->mailto(['class' => 'hover', 'rel' => 'nofollow'])
                                        ->bluesky(["class" => "hover", "rel" => "follow"])
                                        ->whatsapp()->render() !!}
                                    </div>
                                </a>
                            @endif
                        </div>
                    </div>
                </x-card>
            </div>
        </div>
    </div>
</x-app-layout>
