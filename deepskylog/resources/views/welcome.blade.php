<x-app-layout>
    <div class="mx-auto max-w-screen bg-gray-900 px-2 py-6 sm:px-6 lg:px-8">
        @guest
            <x-card>
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                    <x-application-logo
                        class="block h-12 w-auto flex-shrink-0"
                    />
                    <div class="min-w-0 flex-1 space-y-2 text-sm">
                        <p>
                            <strong>{{
                                __(
                                    "Welcome to DeepskyLog!",
                                )
                            }}</strong>
                            {!!
                                __(
                                    "DeepskyLog is an extended, comprehensive and free database for deepsky objects and has been developed by the Deepsky section of the Astronomical Society of Belgium (:linkVereniging Voor Sterrenkunde (VVS):close_link).",
                                    ["link" => "<a href='https://www.vvs.be'>", "close_link" => "</a>"],
                                )
                            !!}
                        </p>
                        <p>
                            {!!
                                __(
                                    "To start recording your observations and share your observations with other observers, you are kindly requested to :register_linkregister:close_link to DeepskyLog. Your personal data will be handled in accordance with our :privacy_linkprivacy policy:close_link.",
                                    [
                                        "register_link" => "<a href='" . route("register") . "'>",
                                        "privacy_link" => "<a href='" . route("privacy") . "'>",
                                        "close_link" => "</a>",
                                    ],
                                )
                            !!}
                        </p>
                        <details>
                            <summary
                                class="cursor-pointer text-gray-400 select-none hover:text-gray-200"
                            >
                                {{
                                    __(
                                        "More about DeepskyLog",
                                    )
                                }}
                            </summary>
                            <div class="mt-2 space-y-2">
                                <p>{{ __("The database is open for consultation and already contains tens of thousands observations and thousands of sketches and drawings made by amateur astronomers around the world.") }}</p>
                                <p>{{ __("You can consult and create observing lists with different deepsky objects and see suggestions for objects visible in your instrument and from your observation sites. You can create your own file with maps and DSS images of the objects and have access to an interactive and detailed free star atlas. You can also consult the observations and sketches of other observers and share your own observations and sketches with the community.") }}</p>
                                <p>
                                    {!!
                                        __(
                                            "Please contact the :linkDeepskyLog developers:close_link if you encounter problems or have questions.",
                                            [
                                                "link" => '<a href="mailto:deepskylog@groups.io">',
                                                "close_link" => "</a>",
                                            ],
                                        )
                                    !!}
                                </p>
                            </div>
                        </details>
                    </div>
                    <div class="flex flex-shrink-0 gap-2 sm:flex-col">
                        <a
                            href="{{ route("register") }}"
                            class="rounded-md bg-blue-600 px-4 py-2 text-center text-sm font-semibold text-white hover:bg-blue-500"
                            >{{ __("Register") }}</a
                        >
                        <a
                            href="{{ route("login") }}"
                            class="rounded-md bg-gray-700 px-4 py-2 text-center text-sm font-semibold text-gray-100 hover:bg-gray-600"
                            >{{ __("Log in") }}</a
                        >
                    </div>
                </div>
            </x-card>
        @endguest

        {{--
            Two columns on large screens: tonight's best and the newest observations, sketches
            and sessions on the left, the news, the featured sketches and the newest lists on the
            right. On smaller screens the columns are `contents`, so the boxes form one column
            in the order of their order-* classes.
        --}}
        <div
            class="mt-6 flex flex-col gap-6 lg:grid lg:grid-cols-3 lg:items-start"
        >
            <div class="contents lg:col-span-2 lg:flex lg:flex-col lg:gap-6">
                @if (!empty($tonightsBest))
                    <x-tonights-best
                        :best="$tonightsBest"
                        class="order-2 lg:order-none"
                    />
                @endif

                @if ($sessionsWithoutObservations->isNotEmpty())
                    <x-session-reminders
                        :sessions="$sessionsWithoutObservations"
                        class="order-1 lg:order-none"
                    />
                @endif

                @if (!empty($nightForecast))
                    <x-night-forecast
                        :nights="$nightForecast"
                        :location="$forecastLocation"
                        class="order-3 lg:order-none"
                    />
                @endif

                @if ($observations->isNotEmpty())
                    <x-home-section
                        :title="__('Newest observations')"
                        class="order-4 lg:order-none"
                    >
                        <ul class="divide-y divide-gray-700 px-2">
                            @foreach ($observations as $observation)
                                <x-observation-row
                                    :observation="$observation"
                                />
                            @endforeach
                        </ul>
                        <x-slot name="footer">
                            <a
                                href="{{ route("observations.index") }}"
                                class="text-blue-400 hover:underline"
                                >{{ __("All observations") }} &rarr;</a
                            >
                        </x-slot>
                    </x-home-section>
                @endif

                @if ($sketches->isNotEmpty())
                    <x-home-section
                        :title="__('Newest sketches')"
                        class="order-5 lg:order-none"
                    >
                        <div class="grid grid-cols-2 gap-3 px-2 sm:grid-cols-4">
                            @foreach ($sketches as $sketch)
                                <x-sketch-thumbnail :observation="$sketch" />
                            @endforeach
                        </div>
                        <x-slot name="footer">
                            <a
                                href="{{ route("drawings.index") }}"
                                class="text-blue-400 hover:underline"
                                >{{ __("All drawings") }} &rarr;</a
                            >
                        </x-slot>
                    </x-home-section>
                @endif

                @if ($sessions->isNotEmpty())
                    <x-home-section
                        :title="__('Newest sessions')"
                        class="order-6 lg:order-none"
                    >
                        <div class="grid grid-cols-1 gap-4 px-2 md:grid-cols-3">
                            @foreach ($sessions as $session)
                                <x-session-card :session="$session" />
                            @endforeach
                        </div>
                        <x-slot name="footer">
                            <a
                                href="{{ route("session.all") }}"
                                class="text-blue-400 hover:underline"
                                >{{ __("All sessions") }} &rarr;</a
                            >
                        </x-slot>
                    </x-home-section>
                @endif
            </div>

            <div class="contents lg:flex lg:flex-col lg:gap-6">
                @if ($news->isNotEmpty())
                    <x-news
                        :items="$news"
                        :location="$newsLocation"
                        class="order-1 lg:order-none"
                    />
                @endif

                @if ($objectOfTheMonth)
                    <x-object-of-the-month
                        :item="$objectOfTheMonth"
                        :drawings="$objectOfTheMonthDrawings"
                        class="order-2 lg:order-none"
                    />
                @endif

                @if ($starParties->isNotEmpty())
                    <x-star-parties
                        :parties="$starParties"
                        class="order-2 lg:order-none"
                    />
                @endif

                @if ($sketchOfTheWeek?->observation || $sketchOfTheMonth?->observation)
                    <x-home-section
                        :title="__('Featured sketches')"
                        class="order-3 lg:order-none"
                    >
                        <div class="grid grid-cols-2 gap-3 px-2">
                            @foreach ([
                                    [
                                        $sketchOfTheWeek,
                                        __("Sketch of the Week"),
                                        route("sketch-of-the-week")
                                    ],
                                    [
                                        $sketchOfTheMonth,
                                        __("Sketch of the Month"),
                                        route("sketch-of-the-month")
                                    ]
                                ]
                                as [$featured, $label, $href])
                                @if ($featured?->observation)
                                    <div class="min-w-0">
                                        <a
                                            href="{{ $href }}"
                                            class="mb-1 block truncate text-xs font-semibold text-gray-400 uppercase hover:underline"
                                            >{{ $label }}</a
                                        >
                                        <x-sketch-thumbnail
                                            :observation="$featured->observation"
                                        />
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </x-home-section>
                @endif

                @if ($observingLists->isNotEmpty())
                    <x-home-section
                        :title="__('Newest observing lists')"
                        class="order-7 lg:order-none"
                    >
                        <ul class="divide-y divide-gray-700 px-2">
                            @foreach ($observingLists as $list)
                                <li class="py-2 text-sm">
                                    <a
                                        href="{{ route("observing-list.show", $list) }}"
                                        class="font-semibold text-white hover:underline"
                                        >{{
                                            html_entity_decode(
                                                $list->name,
                                                ENT_QUOTES | ENT_HTML5,
                                                "UTF-8",
                                            )
                                        }}</a
                                    >
                                    <p class="text-xs text-gray-400">
                                        @if ($list->owner)
                                            {{ __("by") }}
                                            <a
                                                href="{{ route("observer.show", $list->owner->slug) }}"
                                                class="text-gray-300 hover:underline"
                                                >{{
                                                    html_entity_decode(
                                                        $list->owner->name,
                                                        ENT_QUOTES | ENT_HTML5,
                                                        "UTF-8",
                                                    )
                                                }}</a
                                            >
                                            &middot;
                                        @endif
                                        {{ $list->created_at?->diffForHumans() }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ $list->items_count }} {{ __("objects") }} &middot;
                                        <span title="{{ __("Likes") }}"
                                            >&#9829; {{ $list->likes_count }}</span
                                        >
                                        &middot; {{ $list->subscriptions_count }} {{
                                            __(
                                                "subscribers",
                                            )
                                        }}
                                    </p>
                                </li>
                            @endforeach
                        </ul>
                        <x-slot name="footer">
                            <a
                                href="{{ route("observing-lists.discover") }}"
                                class="text-blue-400 hover:underline"
                                >{{
                                    __(
                                        "Discover all public observing lists",
                                    )
                                }} &rarr;</a
                            >
                        </x-slot>
                    </x-home-section>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
