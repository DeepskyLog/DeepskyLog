<x-app-layout>
    @php
        $obs = $observation;
        $user = ($preloaded_users ?? collect())->get($obs->observerid);
        $dateInt = (string) $obs->date;
        $obsDate =
            strlen($dateInt) === 8
                ? substr($dateInt, 0, 4) .
                    "-" .
                    substr($dateInt, 4, 2) .
                    "-" .
                    substr($dateInt, 6, 2)
                : $dateInt;
        $obsTime = !empty($obs->time)
            ? sprintf(
                "%02d:%02d",
                intdiv((int) $obs->time, 100),
                (int) $obs->time % 100,
            )
            : null;

        $backUrl =
            $observationType === "comet"
                ? route("observations.comet.index")
                : route("observations.index");
        if ($user && !empty($user->slug)) {
            $backUrl =
                $observationType === "comet"
                    ? route("observations.comet.show", $user->slug)
                    : route("observations.show", $user->slug);
        }

        // Translation
        $tr = null;
        if (auth()->check() && auth()->user()->translate) {
            $tr = new \Stichoza\GoogleTranslate\GoogleTranslate(
                auth()->user()->language,
            );
        }

        if ($observationType === "deepsky") {
            $object = ($preloaded_objects ?? collect())->get($obs->objectname);
            $location = ($preloaded_locations ?? collect())->get($obs->locationid);
            $instrument = ($preloaded_instruments ?? collect())->get(
                $obs->instrumentid,
            );
            $eyepiece = ($preloaded_eyepieces ?? collect())->get($obs->eyepieceid);
            $filterObj = ($preloaded_filters ?? collect())->get($obs->filterid);
            $constellations = $preloaded_constellations ?? collect();
            $constellation = $object
                ? $constellations[$object->con]->name ?? null
                : null;
            $objectSlug =
                $object?->slug ??
                \Illuminate\Support\Str::slug((string) ($obs->objectname ?? ""), "-");
        } else {
            $comet = ($preloaded_comets ?? collect())->get($obs->objectid);
            $location = ($preloaded_locations ?? collect())->get($obs->locationid);
            $instrument = ($preloaded_instruments ?? collect())->get(
                $obs->instrumentid,
            );
            $cometName =
                $comet?->name ?? ($obs->resolved_comet_name ?? __("Unknown comet"));
            $cometSlug =
                $comet?->slug ??
                \Illuminate\Support\Str::slug((string) $cometName, "-");
        }

        $drawingPath =
            $observationType === "comet"
                ? "/images/cometdrawings/{$obs->id}.jpg"
                : "/images/drawings/{$obs->id}.jpg";
    @endphp

    <div class="max-w-4xl mx-auto px-4 py-10 sm:px-6 lg:px-8">
        {{-- Back button --}}
        <div class="mb-6">
            <x-button gray icon="arrow-left" href="{{ $backUrl }}">
                {{
                    __(
                        "Back to observations",
                    )
                }}
            </x-button>
        </div>

        {{-- Sketch-of-the-week / month honour banners --}}
        @if (!empty($sketchOfTheWeek))
            <div
                class="mb-4 flex items-center gap-3 rounded-lg border border-yellow-400 bg-yellow-900/30 px-5 py-3"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 flex-shrink-0 text-yellow-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.37 2.448a1 1 0 00-.364 1.118l1.286 3.957c.3.921-.755 1.688-1.54 1.118l-3.37-2.448a1 1 0 00-1.175 0l-3.37 2.448c-.784.57-1.838-.197-1.539-1.118l1.286-3.957a1 1 0 00-.364-1.118L2.062 9.384c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.95-.69L9.049 2.927z" />
                </svg>
                <div>
                    <span class="font-bold text-yellow-300 text-lg">{{
                        __(
                            "DeepskyLog Sketch of the Week",
                        )
                    }}</span>
                    <span class="ml-2 text-yellow-200 text-sm"
                        >&mdash; {{
                            \Carbon\Carbon::parse(
                                $sketchOfTheWeek->date,
                            )->translatedFormat("j M Y")
                        }}</span
                    >
                </div>
            </div>
        @endif

        @if (!empty($sketchOfTheMonth))
            <div
                class="mb-4 flex items-center gap-3 rounded-lg border border-amber-400 bg-amber-900/30 px-5 py-3"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 flex-shrink-0 text-amber-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.37 2.448a1 1 0 00-.364 1.118l1.286 3.957c.3.921-.755 1.688-1.54 1.118l-3.37-2.448a1 1 0 00-1.175 0l-3.37 2.448c-.784.57-1.838-.197-1.539-1.118l1.286-3.957a1 1 0 00-.364-1.118L2.062 9.384c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.95-.69L9.049 2.927z" />
                </svg>
                <div>
                    <span class="font-bold text-amber-300 text-lg">{{
                        __(
                            "DeepskyLog Sketch of the Month",
                        )
                    }}</span>
                    <span class="ml-2 text-amber-200 text-sm"
                        >&mdash; {{
                            \Carbon\Carbon::parse(
                                $sketchOfTheMonth->date,
                            )->translatedFormat("j M Y")
                        }}</span
                    >
                </div>
            </div>
        @endif

        {{-- Drawing (large, at top if present) --}}
        @if ($obs->hasDrawing)
            <div x-data="{ open: false }" class="mb-8">
                <button
                    type="button"
                    @click="open = true"
                    class="block w-full focus:outline-none"
                >
                    <img
                        src="{{ $drawingPath }}"
                        alt="{{ __('Observation drawing') }}"
                        class="mx-auto max-h-[70vh] rounded-lg shadow-xl cursor-zoom-in"
                    />
                </button>
                {{-- Lightbox --}}
                <div
                    x-cloak
                    x-show="open"
                    x-transition.opacity
                    @click.self="open = false"
                    @keydown.escape.window="open = false"
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4"
                >
                    <button
                        type="button"
                        @click="open = false"
                        class="absolute top-4 right-4 z-50 rounded bg-gray-800 p-2 text-white text-xl leading-none"
                    >
                        &times;
                    </button>
                    <img
                        src="{{ $drawingPath }}"
                        alt="{{ __('Observation drawing') }}"
                        class="max-w-full max-h-[90vh] rounded shadow-2xl"
                    />
                </div>
            </div>
        @endif

        {{-- Header: observer + object --}}
        <div class="flex items-start gap-5 mb-6">
            <img
                src="{{ $user?->profile_photo_url ?? asset('images/placeholder-avatar.png') }}"
                alt="{{ $user?->name ?? __('Unknown observer') }}"
                class="h-16 w-16 rounded-full object-cover flex-shrink-0"
            />
            <div>
                <div class="text-2xl font-bold">
                    @if ($observationType === "deepsky")
                        {{ $obs->objectname }}
                    @else
                        {{ $cometName }}
                    @endif
                </div>
                <div class="text-gray-400 text-sm mt-1">
                    {{
                        __(
                            "Observed by",
                        )
                    }}
                    @if ($user)
                        <a
                            href="/observers/{{ $user->slug }}"
                            class="text-white font-semibold hover:underline"
                            >{{ $user->name }}</a
                        >
                    @else
                        <span
                            class="text-white font-semibold"
                            >{{ $obs->observerid }}</span
                        >
                    @endif
                    {{ __("on") }}
                    <span class="text-white font-semibold">{{
                        \Carbon\Carbon::create(
                            $obsDate,
                        )->translatedFormat("j M Y")
                    }}</span>
                    @if ($obsTime)
                        {{ __("at") }}
                        <span
                            class="text-white font-semibold"
                            >{{ $obsTime }}</span
                        >
                    @endif
                </div>
            </div>
        </div>

        {{-- Main details card --}}
        <div class="rounded-xl bg-gray-800 p-6 space-y-5">
            @if ($observationType === "deepsky")
                {{-- Deep-sky object details section --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    {{-- Object --}}
                    <div>
                        <dt
                            class="text-xs uppercase tracking-wide text-gray-400"
                        >
                            {{ __("Object") }}
                        </dt>
                        <dd class="mt-1 font-semibold">
                            <a
                                href="{{ route('object.show', $objectSlug) }}"
                                class="hover:underline text-blue-300"
                            >
                                {{ $obs->objectname }}
                            </a>
                            @if ($object)
                                <span class="text-gray-400 text-sm ml-1"
                                    >({{
                                        __(
                                            $object->long_type() ?? "",
                                        )
                                    }})</span
                                >
                            @endif
                        </dd>
                    </div>

                    {{-- Constellation --}}
                    @if ($constellation)
                        <div>
                            <dt
                                class="text-xs uppercase tracking-wide text-gray-400"
                            >
                                {{
                                    __(
                                        "Constellation",
                                    )
                                }}
                            </dt>
                            <dd class="mt-1 font-semibold">
                                {{ $constellation }}
                            </dd>
                        </div>
                    @endif

                    {{-- Location --}}
                    <div>
                        <dt
                            class="text-xs uppercase tracking-wide text-gray-400"
                        >
                            {{ __("Location") }}
                        </dt>
                        <dd class="mt-1 font-semibold">
                            @if ($location)
                                <a
                                    href="/location/{{ $user?->slug ?? 'unknown' }}/{{ $location->slug }}"
                                    class="hover:underline"
                                >
                                    {{
                                        html_entity_decode(
                                            $location->name,
                                        )
                                    }}
                                </a>
                            @else
                                &mdash;
                            @endif
                        </dd>
                    </div>

                    {{-- Instrument --}}
                    <div>
                        <dt
                            class="text-xs uppercase tracking-wide text-gray-400"
                        >
                            {{
                                __(
                                    "Instrument",
                                )
                            }}
                        </dt>
                        <dd class="mt-1 font-semibold">
                            @if ($instrument)
                                <a
                                    href="/instrument/{{ $user?->slug ?? 'unknown' }}/{{ $instrument->slug }}"
                                    class="hover:underline"
                                >
                                    {!!
                                        html_entity_decode(
                                            $instrument->fullName(),
                                        )
                                    !!}
                                </a>
                            @else
                                &mdash;
                            @endif
                        </dd>
                    </div>

                    {{-- Eyepiece --}}
                    @if ($eyepiece)
                        <div>
                            <dt
                                class="text-xs uppercase tracking-wide text-gray-400"
                            >
                                {{ __("Eyepiece") }}
                            </dt>
                            <dd class="mt-1 font-semibold">
                                {{
                                    html_entity_decode(
                                        $eyepiece->fullName(),
                                    )
                                }}
                            </dd>
                        </div>
                    @endif

                    {{-- Filter --}}
                    @if ($filterObj)
                        <div>
                            <dt
                                class="text-xs uppercase tracking-wide text-gray-400"
                            >
                                {{ __("Filter") }}
                            </dt>
                            <dd class="mt-1 font-semibold">
                                {{
                                    html_entity_decode(
                                        $filterObj->fullName(),
                                    )
                                }}
                            </dd>
                        </div>
                    @endif

                    {{-- Lens --}}
                    @if (!empty($lens))
                        <div>
                            <dt
                                class="text-xs uppercase tracking-wide text-gray-400"
                            >
                                {{ __("Lens") }}
                            </dt>
                            <dd class="mt-1 font-semibold">
                                {{
                                    html_entity_decode(
                                        $lens->fullName(),
                                    )
                                }}
                            </dd>
                        </div>
                    @endif

                    {{-- Magnification --}}
                    @if (!empty($obs->magnification) &&
                        $obs->magnification !== "" &&
                        $obs->magnification !== "0")
                        <div>
                            <dt
                                class="text-xs uppercase tracking-wide text-gray-400"
                            >
                                {{
                                    __(
                                        "Magnification",
                                    )
                                }}
                            </dt>
                            <dd class="mt-1 font-semibold">
                                {{ $obs->magnification }}&times;
                            </dd>
                        </div>
                    @endif

                    {{-- SQM --}}
                    @if ($obs->SQM > 0)
                        <div>
                            <dt
                                class="text-xs uppercase tracking-wide text-gray-400"
                            >
                                {{
                                    __(
                                        "Sky quality (SQM)",
                                    )
                                }}
                            </dt>
                            <dd class="mt-1 font-semibold">
                                {{
                                    number_format(
                                        $obs->SQM,
                                        2,
                                    )
                                }}
                            </dd>
                        </div>
                    @endif

                    {{-- Limiting magnitude --}}
                    @if (!empty($obs->limmag) && $obs->limmag > 0)
                        <div>
                            <dt
                                class="text-xs uppercase tracking-wide text-gray-400"
                            >
                                {{
                                    __(
                                        "Limiting magnitude",
                                    )
                                }}
                            </dt>
                            <dd class="mt-1 font-semibold">
                                {{
                                    number_format(
                                        $obs->limmag,
                                        1,
                                    )
                                }}
                            </dd>
                        </div>
                    @endif

                    {{-- Seeing --}}
                    @if ($obs->seeing > 0)
                        <div>
                            <dt
                                class="text-xs uppercase tracking-wide text-gray-400"
                            >
                                {{ __("Seeing") }}
                            </dt>
                            <dd class="mt-1 font-semibold">
                                @if ($obs->seeing == 1)
                                    {{
                                        __(
                                            "Excellent",
                                        )
                                    }}
                                @elseif ($obs->seeing == 2)
                                    {{ __("Good") }}
                                @elseif ($obs->seeing == 3)
                                    {{ __("Moderate") }}
                                @elseif ($obs->seeing == 4)
                                    {{ __("Poor") }}
                                @elseif ($obs->seeing == 5)
                                    {{ __("Bad") }}
                                @endif
                            </dd>
                        </div>
                    @endif

                    {{-- Visibility --}}
                    @if (!empty($obs->visibility) && $obs->visibility > 0)
                        <div>
                            <dt
                                class="text-xs uppercase tracking-wide text-gray-400"
                            >
                                {{
                                    __(
                                        "Object visibility",
                                    )
                                }}
                            </dt>
                            <dd class="mt-1 font-semibold">
                                @if ($obs->visibility == 1)
                                    {{ __("Obvious") }}
                                @elseif ($obs->visibility == 2)
                                    {{ __("Easy") }}
                                @elseif ($obs->visibility == 3)
                                    {{
                                        __(
                                            "Somewhat easy",
                                        )
                                    }}
                                @elseif ($obs->visibility == 4)
                                    {{
                                        __(
                                            "Difficult",
                                        )
                                    }}
                                @elseif ($obs->visibility == 5)
                                    {{
                                        __(
                                            "Very difficult",
                                        )
                                    }}
                                @elseif ($obs->visibility == 6)
                                    {{ __("Not seen") }}
                                @else
                                    {{ $obs->visibility }}
                                @endif
                            </dd>
                        </div>
                    @endif

                    {{-- Apparent size --}}
                    @if (!empty($obs->smallDiameter) && $obs->smallDiameter > 0)
                        <div>
                            <dt
                                class="text-xs uppercase tracking-wide text-gray-400"
                            >
                                {{
                                    __(
                                        "Apparent size",
                                    )
                                }}
                            </dt>
                            <dd class="mt-1 font-semibold">
                                @if (!empty($obs->largeDiameter) &&
                                    $obs->largeDiameter > 0 &&
                                    $obs->largeDiameter != $obs->smallDiameter)
                                    {{
                                        number_format(
                                            $obs->largeDiameter,
                                            1,
                                        )
                                    }}' &times; {{
                                        number_format(
                                            $obs->smallDiameter,
                                            1,
                                        )
                                    }}'
                                @else
                                    {{
                                        number_format(
                                            $obs->smallDiameter,
                                            1,
                                        )
                                    }}'
                                @endif
                            </dd>
                        </div>
                    @endif
                </div>
                {{-- Object characteristics (boolean flags) --}}
                @php
                    $chars = [];
                    if ((int) ($obs->stellar ?? -1) === 1) {
                        $chars[] = __("Stellar");
                    }
                    if ((int) ($obs->extended ?? -1) === 1) {
                        $chars[] = __("Extended");
                    }
                    if ((int) ($obs->resolved ?? -1) === 1) {
                        $chars[] = __("Resolved");
                    }
                    if ((int) ($obs->mottled ?? -1) === 1) {
                        $chars[] = __("Mottled");
                    }
                    if ((int) ($obs->unusualShape ?? -1) === 1) {
                        $chars[] = __("Unusual shape");
                    }
                    if ((int) ($obs->partlyUnresolved ?? -1) === 1) {
                        $chars[] = __("Partly unresolved");
                    }
                    if ((int) ($obs->colorContrasts ?? -1) === 1) {
                        $chars[] = __("Color contrasts");
                    }
                    if ((int) ($obs->equalBrightness ?? -1) === 1) {
                        $chars[] = __("Equal brightness");
                    }
                    if ((int) ($obs->niceField ?? -1) === 1) {
                        $chars[] = __("Nice field");
                    }
                    if ((int) ($obs->component1 ?? -1) === 1) {
                        $chars[] = __("Component 1");
                    }
                    if ((int) ($obs->component2 ?? -1) === 1) {
                        $chars[] = __("Component 2");
                    }
                @endphp
                @if (!empty($chars))
                    <div>
                        <dt
                            class="text-xs uppercase tracking-wide text-gray-400 mb-2"
                        >
                            {{
                                __(
                                    "Characteristics",
                                )
                            }}
                        </dt>
                        <dd class="flex flex-wrap gap-2">
                            @foreach ($chars as $char)
                                <span
                                    class="inline-block rounded-full bg-gray-700 px-3 py-1 text-sm font-medium text-gray-200"
                                    >{{ $char }}</span
                                >
                            @endforeach
                        </dd>
                    </div>
                @endif

            @else
                {{-- Comet observation details section --}}
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    {{-- Comet name --}}
                    <div>
                        <dt
                            class="text-xs uppercase tracking-wide text-gray-400"
                        >
                            {{ __("Comet") }}
                        </dt>
                        <dd class="mt-1 font-semibold">
                            <a
                                href="{{ route('object.show', $cometSlug) }}"
                                class="hover:underline text-blue-300"
                            >
                                {{ $cometName }}
                            </a>
                        </dd>
                    </div>

                    {{-- Magnitude --}}
                    @if (!empty($obs->mag) && $obs->mag > -90)
                        <div>
                            <dt
                                class="text-xs uppercase tracking-wide text-gray-400"
                            >
                                {{
                                    __(
                                        "Magnitude",
                                    )
                                }}
                            </dt>
                            <dd class="mt-1 font-semibold">
                                {{
                                    number_format(
                                        $obs->mag,
                                        1,
                                    )
                                }}
                                @if ($obs->maguncertain)
                                    <span class="text-gray-400 text-sm"
                                        >({{ __("uncertain") }})</span
                                    >
                                @endif
                                @if ($obs->lessmagnitude)
                                    <span class="text-gray-400 text-sm"
                                        >(&lt;)</span
                                    >
                                @endif
                            </dd>
                        </div>
                    @endif

                    {{-- Location --}}
                    <div>
                        <dt
                            class="text-xs uppercase tracking-wide text-gray-400"
                        >
                            {{ __("Location") }}
                        </dt>
                        <dd class="mt-1 font-semibold">
                            @if ($location)
                                <a
                                    href="/location/{{ $user?->slug ?? 'unknown' }}/{{ $location->slug }}"
                                    class="hover:underline"
                                >
                                    {{
                                        html_entity_decode(
                                            $location->name,
                                        )
                                    }}
                                </a>
                            @else
                                &mdash;
                            @endif
                        </dd>
                    </div>

                    {{-- Instrument --}}
                    <div>
                        <dt
                            class="text-xs uppercase tracking-wide text-gray-400"
                        >
                            {{
                                __(
                                    "Instrument",
                                )
                            }}
                        </dt>
                        <dd class="mt-1 font-semibold">
                            @if ($instrument)
                                <a
                                    href="/instrument/{{ $user?->slug ?? 'unknown' }}/{{ $instrument->slug }}"
                                    class="hover:underline"
                                >
                                    {!!
                                        html_entity_decode(
                                            $instrument->fullName(),
                                        )
                                    !!}
                                </a>
                            @else
                                &mdash;
                            @endif
                        </dd>
                    </div>

                    {{-- Magnification --}}
                    @if (!empty($obs->magnification) &&
                        $obs->magnification !== "" &&
                        $obs->magnification !== "0")
                        <div>
                            <dt
                                class="text-xs uppercase tracking-wide text-gray-400"
                            >
                                {{
                                    __(
                                        "Magnification",
                                    )
                                }}
                            </dt>
                            <dd class="mt-1 font-semibold">
                                {{ $obs->magnification }}&times;
                            </dd>
                        </div>
                    @endif

                    {{-- Degree of condensation --}}
                    @if (!empty($obs->dc))
                        <div>
                            <dt
                                class="text-xs uppercase tracking-wide text-gray-400"
                            >
                                {{
                                    __(
                                        "Degree of condensation (DC)",
                                    )
                                }}
                            </dt>
                            <dd class="mt-1 font-semibold">{{ $obs->dc }}</dd>
                        </div>
                    @endif

                    {{-- Coma --}}
                    @if (!empty($obs->coma) && $obs->coma > -90)
                        <div>
                            <dt
                                class="text-xs uppercase tracking-wide text-gray-400"
                            >
                                {{
                                    __(
                                        "Coma diameter",
                                    )
                                }}
                            </dt>
                            <dd class="mt-1 font-semibold">
                                {{ $obs->coma }}'
                            </dd>
                        </div>
                    @endif

                    {{-- Tail --}}
                    @if (!empty($obs->tail) && $obs->tail > -90)
                        <div>
                            <dt
                                class="text-xs uppercase tracking-wide text-gray-400"
                            >
                                {{
                                    __(
                                        "Tail length",
                                    )
                                }}
                            </dt>
                            <dd class="mt-1 font-semibold">
                                {{ $obs->tail }}'
                                @if (!empty($obs->pa) && $obs->pa > -90)
                                    &nbsp;/ {{ __("PA") }} {{ $obs->pa }}&deg;
                                @endif
                            </dd>
                        </div>
                    @endif

                    {{-- Method / chart --}}
                    @if (!empty($obs->methode))
                        <div>
                            <dt
                                class="text-xs uppercase tracking-wide text-gray-400"
                            >
                                {{ __("Method") }}
                            </dt>
                            <dd class="mt-1 font-semibold">
                                {{ $obs->methode }}
                            </dd>
                        </div>
                    @endif

                    @if (!empty($obs->chart))
                        <div>
                            <dt
                                class="text-xs uppercase tracking-wide text-gray-400"
                            >
                                {{ __("Chart") }}
                            </dt>
                            <dd class="mt-1 font-semibold">
                                {{ $obs->chart }}
                            </dd>
                        </div>
                    @endif
                </div>
            @endif

            {{-- Description / notes --}}
            @if (!empty($obs->description) && $obs->description !== "")
                <div class="border-t border-gray-700 pt-5">
                    <dt
                        class="text-xs uppercase tracking-wide text-gray-400 mb-2"
                    >
                        {{ __("Notes") }}
                    </dt>
                    <div
                        class="rounded-md bg-gray-900 px-4 py-4 text-sm leading-relaxed"
                    >
                        @if ($tr)
                            @php
                                $cacheKey =
                                    "obs_detail_translation:" .
                                    $observationType .
                                    ":" .
                                    $obs->id .
                                    ":" .
                                    auth()->user()->language;
                                $translatedDesc = \Illuminate\Support\Facades\Cache::remember(
                                    $cacheKey,
                                    60 * 24 * 30,
                                    function () use ($obs, $tr) {
                                        try {
                                            return $tr->translate(html_entity_decode($obs->description));
                                        } catch (\Throwable $e) {
                                            return null;
                                        }
                                    },
                                );
                            @endphp
                            {!!
                                $translatedDesc ??
                                    html_entity_decode($obs->description)
                            !!}
                        @else
                            {!!
                                html_entity_decode(
                                    $obs->description,
                                )
                            !!}
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- Action row --}}
        <div class="flex items-center gap-3 mt-6">
            @if (auth()->check())
                @php
                    $msgSubject =
                        $observationType === "deepsky"
                            ? "About your observation of " . ($obs->objectname ?? "")
                            : "About your observation of " . ($cometName ?? "");
                    $toUser = $user?->username ?? "";
                @endphp
                @if ($toUser)
                    <a
                        href="{{ route('messages.create', ['to' => $toUser, 'subject' => $msgSubject]) }}"
                        class="inline-flex items-center px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M2.94 6.94A2 2 0 014.828 6h10.344a2 2 0 011.888.94L10 11.586 2.94 6.94z" />
                            <path d="M18 8.118V13a2 2 0 01-2 2H4a2 2 0 01-2-2V8.118l7.293 4.377a1 1 0 001.414 0L18 8.118z" />
                        </svg>
                        {{
                            __(
                                "Send message",
                            )
                        }}
                    </a>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
