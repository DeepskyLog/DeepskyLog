<x-app-layout>
    @php
        $locale = app()->getLocale();
        $totals = $summary["totals"];
        $previous = $summary["previous"];
        $title = __("The year :year of :name", [
            "year" => $year,
            "name" => $user->name,
        ]);
        $shareDesc = __(
            "The year :year of :name on DeepskyLog: :observations observations in :nights nights, :new new objects",
            [
                "year" => $year,
                "name" => $user->name,
                "observations" => $totals["observations"],
                "nights" => $totals["nights"],
                "new" => $summary["new_objects"]["count"],
            ],
        );
        $shareUrl = url(
            route("observer.year", [
                "observer" => $user->slug,
                "year" => $year,
            ]),
        );
        $shareImage = $drawings->first()?->drawingUrl();
        $date = fn(string $date) => \Carbon\Carbon::parse($date)
            ->locale($locale)
            ->isoFormat("LL");
        $kindLabels = [
            "deepsky" => __("Deepsky"),
            "double" => __("Double stars"),
            "comet" => __("Comets"),
            "planets" => __("Planets"),
            "sun" => __("Sun"),
            "moon" => __("Moon"),
            "asteroids" => __("Asteroids"),
        ];
        $tiles = [
            "observations" => __("Observations"),
            "nights" => __("Nights"),
            "objects" => __("Different objects"),
            "drawings" => __("Drawings"),
            "sessions" => __("Sessions"),
        ];
        $maxMonth = max(1, max($summary["months"]["observations"]));
    @endphp

    @push ("head")
        @php
            // WhatsApp skips large images: the drawing is shared as a preview of at most 300 KB.
            $ogPreview = $shareImage
                ? \App\Services\SharePreviewImage::for($shareImage)
                : null;
            $ogImage = $ogPreview["url"] ?? url("/images/logo2.png");
        @endphp

        <meta property="og:type" content="article" />
        <meta property="og:title" content="{{ $title }}" />
        <meta property="og:description" content="{{ $shareDesc }}" />
        <meta property="og:url" content="{{ $shareUrl }}" />
        <meta property="og:image" content="{{ $ogImage }}" />
        @if (!empty($ogPreview["width"]) && !empty($ogPreview["height"]))
            <meta
                property="og:image:width"
                content="{{ $ogPreview["width"] }}"
            />
            <meta
                property="og:image:height"
                content="{{ $ogPreview["height"] }}"
            />
        @endif

        <meta property="og:site_name" content="DeepskyLog" />
        <meta name="description" content="{{ $shareDesc }}" />
        <meta
            name="twitter:card"
            content="{{ $shareImage ? "summary_large_image" : "summary" }}"
        />
        <meta name="twitter:title" content="{{ $title }}" />
        <meta name="twitter:description" content="{{ $shareDesc }}" />
        <meta name="twitter:image" content="{{ $ogImage }}" />
    @endpush

    <div class="mx-auto max-w-screen-lg bg-gray-900 px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <img
                    src="{{ $user->profile_photo_url }}"
                    alt="{{ $user->name }}"
                    class="h-12 w-12 rounded-full object-cover"
                />
                <h1 class="text-xl font-semibold text-white">{{ $title }}</h1>
            </div>
            <div class="flex items-center gap-3 text-sm">
                <a
                    href="{{ route("observer.show", ["observer" => $user->slug]) }}"
                    class="text-blue-400 hover:underline"
                    >{{
                        __(
                            "Observer page",
                        )
                    }}</a
                >
                <a
                    href="{{ route("observer.blog", ["observer" => $user->slug]) }}"
                    class="text-blue-400 hover:underline"
                    >{{ __("Blog") }}</a
                >
            </div>
        </div>

        @if ($years->count() > 1)
            <nav class="mb-6 flex flex-wrap gap-2 text-sm">
                @foreach ($years as $otherYear)
                    <a
                        href="{{ route("observer.year", ["observer" => $user->slug, "year" => $otherYear]) }}"
                        class="{{ $otherYear === $year ? "bg-blue-600 text-white" : "bg-gray-800 text-gray-300 hover:bg-gray-700" }} rounded px-2 py-1"
                        >{{ $otherYear }}</a
                    >
                @endforeach
            </nav>
        @endif

        {{-- The totals, compared with the year before --}}
        <section class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-5">
            @foreach ($tiles as $key => $label)
                @php
                    $difference = $totals[$key] - $previous[$key];
                @endphp

                <div class="rounded-lg bg-gray-800 p-4">
                    <div class="text-sm text-gray-400">{{ $label }}</div>
                    <div class="text-2xl font-bold text-white">
                        {{ $totals[$key] }}
                    </div>
                    @if ($difference !== 0)
                        <div
                            class="{{ $difference > 0 ? "text-green-400" : "text-gray-400" }} text-xs"
                        >
                            {{
                                __(":difference compared with :year", [
                                    "difference" => ($difference > 0 ? "+" : "") . $difference,
                                    "year" => $year - 1,
                                ])
                            }}
                        </div>
                    @endif
                </div>
            @endforeach
        </section>

        {{-- Per month --}}
        <section class="mb-6 rounded-lg bg-gray-800 p-4">
            <h2 class="mb-3 text-lg font-semibold text-white">
                {{
                    __(
                        "Observations per month",
                    )
                }}
            </h2>
            <div class="flex h-40 items-end gap-1">
                @foreach ($summary["months"]["observations"] as $index => $count)
                    @php
                        $monthName = \Carbon\Carbon::create($year, $index + 1, 1)
                            ->locale($locale)
                            ->isoFormat("MMMM");
                        $nights = $summary["months"]["nights"][$index];
                    @endphp

                    <div
                        class="flex h-full flex-1 flex-col items-center justify-end"
                        title="{{ $monthName }}: {{ trans_choice(":count observation|:count observations", $count, ["count" => $count]) }}, {{ trans_choice(":count night|:count nights", $nights, ["count" => $nights]) }}"
                    >
                        <span class="text-xs text-gray-400">
                            {{ $count ?: "" }}
                        </span>
                        <div
                            class="w-full rounded-t bg-blue-500"
                            style="height: {{ round((80 * $count) / $maxMonth) }}%"
                        ></div>
                    </div>
                @endforeach
            </div>
            <div class="mt-1 flex gap-1">
                @foreach ($summary["months"]["observations"] as $index => $count)
                    <div class="flex-1 text-center text-xs text-gray-400">
                        {{
                            \Carbon\Carbon::create(
                                $year,
                                $index + 1,
                                1,
                            )
                                ->locale($locale)
                                ->isoFormat("MMM")
                        }}
                    </div>
                @endforeach
            </div>

            @if (count($summary["kinds"]) > 1)
                <p class="mt-4 text-sm text-gray-300">
                    @foreach ($summary["kinds"] as $kind => $count)
                        <span class="mr-3 whitespace-nowrap"
                            >{{ $kindLabels[$kind] ?? $kind }}:
                            <strong
                                class="text-white"
                                >{{ $count }}</strong
                            ></span
                        >
                    @endforeach
                </p>
            @endif
        </section>

        {{-- Highlights --}}
        @if ($summary["best_night"] || $summary["faintest"] || $summary["rarest"])
            <section class="mb-6 grid gap-3 sm:grid-cols-3">
                @if ($summary["best_night"])
                    @php
                        $best = $summary["best_night"];
                    @endphp

                    <a
                        href="{{ $best["url"] }}"
                        class="block rounded-lg bg-gray-800 p-4 hover:bg-gray-700"
                    >
                        <div class="text-sm text-gray-400">
                            {{
                                __(
                                    "Best night",
                                )
                            }}
                        </div>
                        <div class="text-lg font-semibold text-white">
                            {{
                                $date(
                                    $best["date"],
                                )
                            }}
                        </div>
                        <div class="text-sm text-gray-300">
                            {{
                                trans_choice(
                                    ":count different object|:count different objects",
                                    $best["objects"],
                                    ["count" => $best["objects"]],
                                )
                            }}
                            @if ($best["location"])
                                &middot; {{
                                    $best[
                                        "location"
                                    ]
                                }}
                            @endif
                        </div>
                    </a>
                @endif

                @if ($summary["faintest"])
                    <a
                        href="{{ $summary["faintest"]["url"] }}"
                        class="block rounded-lg bg-gray-800 p-4 hover:bg-gray-700"
                    >
                        <div class="text-sm text-gray-400">
                            {{
                                __(
                                    "Faintest object",
                                )
                            }}
                        </div>
                        <div class="text-lg font-semibold text-white">
                            {{
                                $summary["faintest"][
                                    "name"
                                ]
                            }}
                        </div>
                        <div class="text-sm text-gray-300">
                            {{
                                __("Magnitude :magnitude", [
                                    "magnitude" => number_format($summary["faintest"]["magnitude"], 1),
                                ])
                            }}
                        </div>
                    </a>
                @endif

                @if ($summary["rarest"])
                    <a
                        href="{{ $summary["rarest"]["url"] }}"
                        class="block rounded-lg bg-gray-800 p-4 hover:bg-gray-700"
                    >
                        <div class="text-sm text-gray-400">
                            {{
                                __(
                                    "Rarest object",
                                )
                            }}
                        </div>
                        <div class="text-lg font-semibold text-white">
                            {{
                                $summary["rarest"][
                                    "name"
                                ]
                            }}
                        </div>
                        <div class="text-sm text-gray-300">
                            {{
                                trans_choice(
                                    "Seen by :count observer on DeepskyLog|Seen by :count observers on DeepskyLog",
                                    $summary["rarest"]["observers"],
                                    ["count" => $summary["rarest"]["observers"]],
                                )
                            }}
                        </div>
                    </a>
                @endif
            </section>
        @endif

        {{-- Objects seen for the first time --}}
        @if ($summary["new_objects"]["count"] > 0)
            <section class="mb-6 rounded-lg bg-gray-800 p-4">
                <h2 class="mb-3 text-lg font-semibold text-white">
                    {{
                        trans_choice(
                            ":count object seen for the first time|:count objects seen for the first time",
                            $summary["new_objects"]["count"],
                            ["count" => $summary["new_objects"]["count"]],
                        )
                    }}
                </h2>
                <ul class="grid gap-x-4 gap-y-1 text-sm sm:grid-cols-2">
                    @foreach ($summary["new_objects"]["shown"] as $object)
                        <li>
                            @if ($object["url"])
                                <a
                                    href="{{ $object["url"] }}"
                                    class="font-semibold text-white hover:underline"
                                    >{{
                                        $object[
                                            "name"
                                        ]
                                    }}</a
                                >
                            @else
                                <span class="font-semibold text-white">{{
                                    $object[
                                        "name"
                                    ]
                                }}</span>
                            @endif
                            <span class="text-gray-400">
                                {{
                                    $date(
                                        $object["date"],
                                    )
                                }}
                            </span>
                        </li>
                    @endforeach
                </ul>
                @if ($summary["new_objects"]["count"] > count($summary["new_objects"]["shown"]))
                    <p class="mt-2 text-sm text-gray-400">
                        {{
                            __("and :count more", [
                                "count" =>
                                    $summary["new_objects"]["count"] -
                                    count($summary["new_objects"]["shown"]),
                            ])
                        }}
                    </p>
                @endif
            </section>
        @endif

        {{-- Catalog progress --}}
        @if (count($summary["catalogs"]) > 0)
            <section class="mb-6 rounded-lg bg-gray-800 p-4">
                <h2 class="mb-3 text-lg font-semibold text-white">
                    {{ __("Catalogs") }}
                </h2>
                <div class="space-y-3">
                    @foreach ($summary["catalogs"] as $catalog)
                        <div>
                            <div
                                class="flex justify-between text-sm text-gray-300"
                            >
                                <span
                                    ><strong class="text-white">{{
                                        $catalog[
                                            "catalog"
                                        ]
                                    }}</strong>
                                    +{{ $catalog["gained"] }}</span
                                >
                                <span
                                    >{{ $catalog["seen"] }} / {{ $catalog["total"] }}</span
                                >
                            </div>
                            <div class="mt-1 h-2 rounded bg-gray-700">
                                <div
                                    class="h-2 rounded bg-green-500"
                                    style="width: {{ $catalog["total"] > 0 ? min(100, round((100 * $catalog["seen"]) / $catalog["total"])) : 0 }}%"
                                ></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Drawings --}}
        @if ($drawings->isNotEmpty())
            <section class="mb-6 rounded-lg bg-gray-800 p-4">
                <h2 class="mb-1 text-lg font-semibold text-white">
                    {{ __("Drawings") }}
                </h2>
                @if ($summary["sketches"]["week"] > 0 || $summary["sketches"]["month"] > 0)
                    <p class="mb-3 text-sm text-amber-400">
                        @if ($summary["sketches"]["month"] > 0)
                            {{
                                trans_choice(
                                    ":count sketch of the month|:count sketches of the month",
                                    $summary["sketches"]["month"],
                                    ["count" => $summary["sketches"]["month"]],
                                )
                            }}
                        @endif

                        @if ($summary["sketches"]["week"] > 0)
                            @if ($summary["sketches"]["month"] > 0)
                                &middot;
                            @endif

                            {{
                                trans_choice(
                                    ":count sketch of the week|:count sketches of the week",
                                    $summary["sketches"]["week"],
                                    ["count" => $summary["sketches"]["week"]],
                                )
                            }}
                        @endif
                    </p>
                @endif

                <div class="grid grid-cols-3 gap-2 sm:grid-cols-6">
                    @foreach ($drawings as $drawing)
                        @php
                            $drawingName = html_entity_decode(
                                (string) $drawing->target_name,
                                ENT_QUOTES | ENT_HTML5,
                                "UTF-8",
                            );
                        @endphp

                        <a
                            href="{{ $drawing->detailUrl($user->slug) ?? $drawing->drawingUrl() }}"
                            title="{{ $drawingName }}"
                        >
                            <img
                                src="{{ $drawing->drawingUrl() }}"
                                alt="{{ __("Drawing of :object", ["object" => $drawingName]) }}"
                                loading="lazy"
                                class="aspect-square w-full rounded object-cover"
                            />
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Equipment --}}
        @if (count($summary["instruments"]) > 0 || count($summary["locations"]) > 0)
            <section class="mb-6 grid gap-3 sm:grid-cols-2">
                @foreach ([
                        "instruments" => __("Most used instruments"),
                        "locations" => __("Most used locations")
                    ]
                    as $key => $label)
                    @if (count($summary[$key]) > 0)
                        <div class="rounded-lg bg-gray-800 p-4">
                            <h2 class="mb-2 text-lg font-semibold text-white">
                                {{ $label }}
                            </h2>
                            <ul class="space-y-1 text-sm text-gray-300">
                                @foreach ($summary[$key] as $used)
                                    <li class="flex justify-between gap-2">
                                        <span>{{ $used["name"] }}</span>
                                        <span class="text-gray-400">{{
                                            trans_choice(
                                                ":count observation|:count observations",
                                                $used["count"],
                                                ["count" => $used["count"]],
                                            )
                                        }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                @endforeach
            </section>
        @endif

        {{-- Goals --}}
        @if ($goals->isNotEmpty())
            <section class="mb-6 rounded-lg bg-gray-800 p-4">
                <h2 class="mb-3 text-lg font-semibold text-white">
                    {{
                        __(
                            "Observing goals",
                        )
                    }}
                </h2>
                <div class="space-y-4">
                    @foreach ($goals as $goal)
                        <x-observing-goal :goal="$goal" />
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Shared and received --}}
        @php
            $social = array_filter([
                trans_choice(
                    ":count like received|:count likes received",
                    $summary["social"]["likes"],
                    ["count" => $summary["social"]["likes"]],
                ) => $summary["social"]["likes"],
                trans_choice(
                    ":count comment received|:count comments received",
                    $summary["social"]["comments"],
                    ["count" => $summary["social"]["comments"]],
                ) => $summary["social"]["comments"],
                trans_choice(
                    ":count public observing list|:count public observing lists",
                    $summary["social"]["lists"],
                    ["count" => $summary["social"]["lists"]],
                ) => $summary["social"]["lists"],
                trans_choice(
                    ":count starparty|:count starparties",
                    $summary["social"]["star_parties"],
                    ["count" => $summary["social"]["star_parties"]],
                ) => $summary["social"]["star_parties"],
            ]);
        @endphp

        @if (count($social) > 0)
            <section class="mb-6 rounded-lg bg-gray-800 p-4">
                <ul
                    class="flex flex-wrap gap-x-6 gap-y-1 text-sm text-gray-300"
                >
                    @foreach ($social as $label => $count)
                        <li>{{ $label }}</li>
                    @endforeach
                </ul>
            </section>
        @endif

        {{-- Share --}}
        @php
            $shareTags = "#astronomy #deepsky #deepskylog";
            $shareText = $shareDesc . "\n\n" . $shareUrl . "\n\n" . $shareTags;
            $twitterHref =
                "https://twitter.com/intent/tweet?text=" . rawurlencode($shareText);
            $facebookHref =
                "https://www.facebook.com/sharer/sharer.php?u=" . rawurlencode($shareUrl);
            $whatsappHref = "https://wa.me/?text=" . rawurlencode($shareText);
            $mailtoHref =
                "mailto:?subject=" .
                rawurlencode($title) .
                "&body=" .
                rawurlencode($shareDesc . "\n\n" . $shareUrl);
        @endphp

        <div class="flex justify-end">
            <div
                id="social-buttons"
                data-share
                data-share-image="{{ $shareImage ?? "" }}"
                data-share-title="{{ $title }}"
                data-share-text="{{ $shareDesc . "\n\n" . $shareTags }}"
                data-share-url="{{ $shareUrl }}"
                data-share-image-notice="{{ __("The picture has been copied: paste it (Ctrl+V) into your post on X.") }}"
                data-share-image-mail-notice="{{ __("The picture has been copied: paste it (Ctrl+V) into your mail.") }}"
                data-share-retry-notice="{{ __("The picture is ready: tap the share button again.") }}"
            >
                <a
                    href="{{ $facebookHref }}"
                    class="social-button hover"
                    data-share-text="{{ $shareDesc . "\n\n" . $shareTags }}"
                    title="{{ __("Share on Facebook") }}"
                    rel="follow"
                    ><span class="fab fa-facebook-square"></span
                ></a>
                <a
                    href="{{ $twitterHref }}"
                    class="social-button hover"
                    title="{{ __("Share on X") }}"
                    rel="follow"
                    ><span class="fab fa-square-x-twitter"></span
                ></a>
                <a
                    href="{{ $shareUrl }}"
                    class="social-button hover"
                    id="clip"
                    title="{{ __("Copy link") }}"
                    rel="nofollow noopener noreferrer"
                    ><span class="fas fa-share"></span
                ></a>
                <a
                    href="{{ $mailtoHref }}"
                    class="social-button hover"
                    title="{{ __("Share by email") }}"
                    rel="nofollow"
                    ><span class="fas fa-envelope"></span
                ></a>
                <a
                    href="{{ $whatsappHref }}"
                    class="social-button hover"
                    title="{{ __("Share on WhatsApp") }}"
                    target="_blank"
                    rel="nofollow"
                    ><span class="fab fa-square-whatsapp"></span
                ></a>
                <a
                    href="{{ $shareUrl }}"
                    class="social-button native-share hover"
                    title="{{ __("Share…") }}"
                    rel="nofollow"
                    ><span class="fas fa-share-nodes"></span
                ></a>
            </div>
        </div>
    </div>
</x-app-layout>
