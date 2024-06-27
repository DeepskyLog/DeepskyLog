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
                                <a
                                    href="{{ config("app.old_url") }}/index.php?indexAction=comets_detail_observation&observation={{ $sketch->id }}"
                                >
                                    <img
                                        width="400"
                                        src="/images/cometdrawings/{{ $sketch->id }}.jpg"
                                    />

                                    <div class="text-center">
                                        @if ($user == "")
                                            {{ \App\Models\User::where("username", $sketch->observerid)->first()->name }}
                                            -
                                        @endif

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
                                    <div class="text-center">
                                        {!!
                                            ShareButtons::page(
                                                "https://www.deepskylog.org/comets/cometdrawings/" . $sketch->id . ".jpg",
                                                __("Look at this sketch of " . $sketch->object->name . __(" by ") . \App\Models\User::where("username", $sketch->observerid)->first()->name . __(" on #deepskylog")),
                                                [
                                                    "title" => __("Share this sketch"),
                                                    "class" => "text-gray-500 hover:text-gray-700",
                                                    "rel" => "nofollow noopener noreferrer",
                                                ],
                                            )
                                                ->facebook(["class" => "hover", "rel" => "follow"])
                                                ->twitter(["class" => "hover", "rel" => "follow"])
                                                ->copylink()
                                                ->mailto(["class" => "hover", "rel" => "nofollow"])
                                                ->whatsapp()
                                                ->render();
                                        !!}
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
