<x-app-layout>
    @push ("head")
        <link
            rel="alternate"
            type="application/rss+xml"
            title="{{ config("app.name", "DeepskyLog") }} – {{ $user->name }}"
            href="{{ route("feed.observer", ["observer" => $user->slug]) }}"
        />
    @endpush

    @php
        $cards = \App\Services\Observations\ObservationCardData::instance();
        $language = \App\Services\Observations\DescriptionTranslations::language();
        $decode = fn($text) => html_entity_decode(
            (string) $text,
            ENT_QUOTES | ENT_HTML5,
            "UTF-8",
        );
    @endphp

    <div class="mx-auto max-w-screen-lg bg-gray-900 px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <img
                    src="{{ $user->profile_photo_url }}"
                    alt="{{ $user->name }}"
                    class="h-12 w-12 rounded-full object-cover"
                />
                <h1 class="text-xl font-semibold text-white">
                    {{
                        __("Blog of :name", [
                            "name" => $user->name,
                        ])
                    }}
                </h1>
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
                    href="{{ route("feed.observer", ["observer" => $user->slug]) }}"
                    class="text-orange-400 hover:underline"
                    >RSS</a
                >
            </div>
        </div>

        @if ($before)
            <p class="mb-4 text-sm text-gray-400">
                {{
                    __("Entries before :date", [
                        "date" => $before->locale(app()->getLocale())->isoFormat("LL"),
                    ])
                }} &middot;
                <a
                    href="{{ route("observer.blog", ["observer" => $user->slug]) }}"
                    class="text-blue-400 hover:underline"
                    >{{
                        __(
                            "Newest entries",
                        )
                    }}</a
                >
            </p>
        @endif

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

        <div class="space-y-6">
            @forelse ($entries as $entry)
                @php
                    $dateLabel = $entry["date"]
                        ->copy()
                        ->locale(app()->getLocale())
                        ->isoFormat("LL");
                @endphp

                @if ($entry["kind"] === "year")
                    @php
                        $yearTotals = $entry["summary"]["totals"];
                        $yearNew = $entry["summary"]["new_objects"]["count"];
                    @endphp

                    <article class="rounded-lg bg-gray-800 p-4">
                        <p class="text-xs tracking-wide text-blue-400 uppercase">
                            {{ __("Year in review") }} &middot; {{ $entry["year"] }}
                        </p>
                        <h2 class="mt-1 text-lg font-semibold text-white">
                            <a
                                href="{{ \App\Services\ObserverYear::url($user, $entry["year"]) }}"
                                class="hover:underline"
                                >{{
                                    __("The year :year of :name", [
                                        "year" => $entry["year"],
                                        "name" => $user->name,
                                    ])
                                }}</a
                            >
                        </h2>
                        <p class="mt-1 text-sm text-gray-300">
                            {{
                                trans_choice(
                                    ":count observation|:count observations",
                                    $yearTotals["observations"],
                                    ["count" => $yearTotals["observations"]],
                                )
                            }} &middot; {{
                                trans_choice(
                                    ":count night|:count nights",
                                    $yearTotals["nights"],
                                    ["count" => $yearTotals["nights"]],
                                )
                            }}
                            @if ($yearNew > 0)
                                &middot;
                                {{
                                    trans_choice(
                                        ":count object seen for the first time|:count objects seen for the first time",
                                        $yearNew,
                                        ["count" => $yearNew],
                                    )
                                }}
                            @endif
                        </p>
                    </article>
                @elseif ($entry["kind"] === "goal")
                    <article class="rounded-lg bg-gray-800 p-4">
                        <p class="text-xs tracking-wide text-green-400 uppercase">
                            {{ __("Observing goal reached") }} &middot; {{ $dateLabel }}
                        </p>
                        <x-observing-goal :goal="$entry['goal']" class="mt-1" />
                    </article>
                @elseif ($entry["kind"] === "sketch")
                    @php
                        $sketch = $entry["observation"];
                        $sketchName = $decode($sketch->target_name);
                        $sketchUrl = $sketch->detailUrl($user->slug);
                        $sketchDrawing = $sketch->drawingUrl();
                        $isMonth = $entry["sketch_of"] === "month";
                    @endphp

                    <article class="overflow-hidden rounded-lg bg-gray-800">
                        <div class="p-4">
                            <p class="text-xs tracking-wide text-amber-400 uppercase">
                                {{
                                    $isMonth
                                        ? __("Sketch of the month")
                                        : __("Sketch of the week")
                                }} &middot; {{ $dateLabel }}
                            </p>
                            <h2 class="mt-1 text-lg font-semibold text-white">
                                @if ($sketchUrl)
                                    <a
                                        href="{{ $sketchUrl }}"
                                        class="hover:underline"
                                    >
                                        {{
                                            $isMonth
                                                ? __(":object was chosen as sketch of the month", ["object" => $sketchName])
                                                : __(":object was chosen as sketch of the week", [
                                                    "object" => $sketchName,
                                                ])
                                        }}
                                    </a>
                                @else
                                    {{
                                        $isMonth
                                            ? __(":object was chosen as sketch of the month", ["object" => $sketchName])
                                            : __(":object was chosen as sketch of the week", [
                                                "object" => $sketchName,
                                            ])
                                    }}
                                @endif
                            </h2>
                            <p class="mt-1 text-sm text-gray-400">
                                {{
                                    __("Observed on :date", [
                                        "date" => $sketch->observed_on
                                            ->copy()
                                            ->locale(app()->getLocale())
                                            ->isoFormat("LL"),
                                    ])
                                }} &middot;
                                <a
                                    href="{{ route($isMonth ? "sketch-of-the-month" : "sketch-of-the-week") }}"
                                    class="text-blue-400 hover:underline"
                                    >{{
                                        $isMonth
                                            ? __("All sketches of the month")
                                            : __("All sketches of the week")
                                    }}</a
                                >
                            </p>
                            @if ($sketchDrawing)
                                <a
                                    href="{{ $sketchDrawing }}"
                                    target="_blank"
                                    rel="noopener"
                                    class="mt-3 block"
                                    onclick="
                                        var m =
                                            document.getElementById(
                                                'obs-drawing-modal',
                                            );
                                        var img =
                                            document.getElementById(
                                                'obs-drawing-img',
                                            );
                                        if (!m || !img) {
                                            return true;
                                        }
                                        img.src = this.href;
                                        m.classList.remove('hidden');
                                        return false;
                                    "
                                >
                                    <img
                                        src="{{ $sketchDrawing }}"
                                        alt="{{ __("Drawing of :object", ["object" => $sketchName]) }}"
                                        loading="lazy"
                                        class="mx-auto max-h-96 rounded"
                                    />
                                </a>
                            @endif
                        </div>
                    </article>
                @elseif ($entry["kind"] === "session")
                    @php
                        $session = $entry["session"];
                        $owner = $cards->user($session->observerid);
                        $sessionUrl = route("session.show", [
                            $owner?->slug ?: $session->observerid,
                            $session->slug ?: $session->id,
                        ]);
                        $sessionName = $decode(
                            $session->name ?: __("Session :id", ["id" => $session->id]),
                        );
                        $others = collect($session->otherObservers())
                            ->reject(fn($username) => $username === $session->observerid)
                            ->map(fn($username) => $cards->user($username))
                            ->filter()
                            ->unique("id")
                            ->values();

                        // A translation the session page stored, never one made here.
                        $comments = \App\Support\Html::purify($session->comments, true);
                        $translatedComments = null;
                        if (
                            $language !== null &&
                            $language !== ($session->language ?? "en") &&
                            trim(strip_tags($comments)) !== ""
                        ) {
                            $translatedComments = \Illuminate\Support\Facades\Cache::get(
                                "session_comments:" . $session->id . ":" . $language,
                            );
                        }
                        $weather = \App\Support\Html::purify($session->weather, true);
                    @endphp
                    <article class="overflow-hidden rounded-lg bg-gray-800">
                        @if (!empty($session->preview))
                            <a href="{{ $sessionUrl }}">
                                <img
                                    src="{{ $session->preview }}"
                                    alt="{{ $sessionName }}"
                                    loading="lazy"
                                    class="h-64 w-full object-cover"
                                />
                            </a>
                        @endif
                        <div class="p-4">
                            <p class="text-xs tracking-wide text-gray-400 uppercase">
                                {{ __("Session") }} &middot; {{ $dateLabel }}
                                @if ($session->enddate &&
                                    $session->enddate->toDateString() !==
                                        $session->begindate->toDateString())
                                    &ndash;
                                    {{
                                        $session->enddate
                                            ->copy()
                                            ->locale(app()->getLocale())
                                            ->isoFormat("LL")
                                    }}
                                @endif
                            </p>
                            <h2 class="mt-1 text-lg font-semibold text-white">
                                <a
                                    href="{{ $sessionUrl }}"
                                    class="hover:underline"
                                    >{{ $sessionName }}</a
                                >
                            </h2>
                            <p class="mt-1 text-sm text-gray-400">
                                @if ($session->location_name)
                                    {{ __("Location") }}: {{
                                        $decode(
                                            $session->location_name,
                                        )
                                    }}
                                @endif
                                @if ($session->observerid !== $user->username && $owner)
                                    &middot;
                                    {{
                                        __(
                                            "Session of",
                                        )
                                    }}
                                    <a
                                        href="{{ route("observer.show", ["observer" => $owner->slug]) }}"
                                        class="hover:underline"
                                        >{{ $owner->name }}</a
                                    >
                                @endif
                                @if ($others->isNotEmpty())
                                    &middot; {{ __("With") }}
                                    @foreach ($others as $other)
                                        <a
                                            href="{{ route("observer.show", ["observer" => $other->slug]) }}"
                                            class="hover:underline"
                                            >{{ $other->name }}</a
                                        >
                                        @if (!$loop->last) , @endif
                                    @endforeach
                                @endif
                            </p>

                            @if ($translatedComments)
                                <div
                                    x-data="{ original: false }"
                                    class="mt-3 text-sm text-gray-200"
                                >
                                    <div x-show="!original">
                                        {!!
                                            nl2br(
                                                e($translatedComments),
                                            )
                                        !!}
                                    </div>
                                    <div x-show="original" x-cloak>
                                        {!! $comments !!}
                                    </div>
                                    <p class="mt-2 text-xs text-gray-400">
                                        {{ __("Translated by Google") }} &middot;
                                        <button
                                            type="button"
                                            class="underline hover:text-gray-200"
                                            x-on:click="original = !original"
                                            x-text="original ? @js(__("Show translation")) : @js(__("Show original"))"
                                        >
                                            {{
                                                __(
                                                    "Show original",
                                                )
                                            }}
                                        </button>
                                    </p>
                                </div>
                            @elseif (trim(strip_tags($comments)) !== "")
                                <div class="mt-3 text-sm text-gray-200">
                                    {!! $comments !!}
                                </div>
                            @endif

                            @if (trim(strip_tags($weather)) !== "")
                                <div class="mt-3 text-sm text-gray-300">
                                    <span class="font-medium text-gray-200"
                                        >{{ __("Weather") }}:</span
                                    >
                                    {!! $weather !!}
                                </div>
                            @endif

                            @if ($entry["observations"]->isNotEmpty())
                                <h3
                                    class="mt-4 text-sm font-semibold text-gray-200"
                                >
                                    {{
                                        trans_choice(
                                            ":count observation|:count observations",
                                            $entry["observations"]->count(),
                                            ["count" => $entry["observations"]->count()],
                                        )
                                    }}
                                </h3>
                                @include ("observers.partials.blog-observations",
                                    ["observations" => $entry["observations"]])
                            @endif
                        </div>
                    </article>
                @elseif ($entry["kind"] === "night")
                    <article class="rounded-lg bg-gray-800 p-4">
                        <p class="text-xs tracking-wide text-gray-400 uppercase">
                            {{
                                trans_choice(
                                    ":count observation|:count observations",
                                    $entry["observations"]->count(),
                                    ["count" => $entry["observations"]->count()],
                                )
                            }}
                        </p>
                        <h2 class="mt-1 text-lg font-semibold text-white">
                            {{
                                __("The night of :date", [
                                    "date" => $dateLabel,
                                ])
                            }}
                        </h2>
                        @php
                            $nightLocations = $entry["observations"]
                                ->map(
                                    fn($observation) => $cards->location($observation->location_id)?->name,
                                )
                                ->filter()
                                ->unique()
                                ->map($decode);
                        @endphp
                        @if ($nightLocations->isNotEmpty())
                            <p class="mt-1 text-sm text-gray-400">
                                {{ __("Location") }}: {{
                                    $nightLocations->implode(
                                        ", ",
                                    )
                                }}
                            </p>
                        @endif
                        @include ("observers.partials.blog-observations",
                            ["observations" => $entry["observations"]])
                    </article>
                @else
                    @php
                        $list = $entry["list"];
                    @endphp
                    <article class="rounded-lg bg-gray-800 p-4">
                        <p class="text-xs tracking-wide text-gray-400 uppercase">
                            {{ __("Observing list") }} &middot; {{ $dateLabel }}
                        </p>
                        <h2 class="mt-1 text-lg font-semibold text-white">
                            <a
                                href="{{ route("observing-list.show", $list->slug) }}"
                                class="hover:underline"
                                >{{ $list->name_decoded }}</a
                            >
                        </h2>
                        <p class="mt-1 text-sm text-gray-400">
                            {{
                                trans_choice(
                                    ":count object|:count objects",
                                    $list->items_count,
                                    ["count" => $list->items_count],
                                )
                            }}
                        </p>
                        @if (trim(strip_tags((string) $list->description)) !== "")
                            <p class="mt-2 text-sm text-gray-300">
                                {{
                                    \Illuminate\Support\Str::limit(
                                        strip_tags($decode($list->description)),
                                        300,
                                    )
                                }}
                            </p>
                        @endif
                    </article>
                @endif
            @empty
                <p class="text-gray-400">{{
                    __(
                        "Nothing here yet.",
                    )
                }}</p>
            @endforelse
        </div>

        @if ($next)
            <div class="mt-6 text-center">
                <a
                    href="{{ route("observer.blog", ["observer" => $user->slug, "before" => $next]) }}"
                    class="inline-flex items-center rounded bg-gray-700 px-4 py-2 text-sm text-white hover:bg-gray-600"
                    >{{
                        __(
                            "Older entries",
                        )
                    }}</a
                >
            </div>
        @endif
    </div>

    <x-observation-drawing-modal />
</x-app-layout>
