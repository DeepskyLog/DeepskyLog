{{-- The observations of a blog entry (a session or a night) --}}
@php
    $cards = \App\Services\Observations\ObservationCardData::instance();
@endphp
<ul class="mt-4">
    @foreach ($observations as $observation)
        @php
            $detailUrl = $observation->detailUrl($user->slug);
            $drawingUrl = $observation->drawingUrl();
            $location = $cards->location($observation->location_id);
            $local = $observation->localObservedAt($location);
            $instrument = $cards->instrument($observation->instrument_id);
            $targetName = html_entity_decode(
                (string) $observation->target_name,
                ENT_QUOTES | ENT_HTML5,
                "UTF-8",
            );
        @endphp
        <li class="overflow-hidden border-t border-gray-700 py-3">
            @if ($drawingUrl)
                <a
                    href="{{ $drawingUrl }}"
                    target="_blank"
                    rel="noopener"
                    class="float-right mb-2 ml-4"
                    onclick="
                        var m = document.getElementById('obs-drawing-modal');
                        var img = document.getElementById('obs-drawing-img');
                        if (!m || !img) {
                            return true;
                        }
                        img.src = this.href;
                        m.classList.remove('hidden');
                        return false;
                    "
                >
                    <img
                        src="{{ $drawingUrl }}"
                        alt="{{ __("Drawing of :object", ["object" => $targetName]) }}"
                        loading="lazy"
                        class="h-32 w-32 rounded object-cover"
                    />
                </a>
            @endif
            <div class="flex flex-wrap items-baseline gap-x-2 text-sm">
                @if ($detailUrl)
                    <a
                        href="{{ $detailUrl }}"
                        class="font-semibold text-white hover:underline"
                        >{{ $targetName }}</a
                    >
                @else
                    <span
                        class="font-semibold text-white"
                        >{{ $targetName }}</span
                    >
                @endif
                @if ($local)
                    <span class="text-gray-400">{{
                        $local->format(
                            "H:i",
                        )
                    }}</span>
                @endif
                @if ($instrument)
                    <span class="text-gray-400"
                        >· {{
                            html_entity_decode(
                                $instrument->name,
                                ENT_QUOTES | ENT_HTML5,
                                "UTF-8",
                            )
                        }}</span
                    >
                @endif
                @if ($observation->blog_sketch_of === "month")
                    <span
                        class="rounded bg-amber-600 px-1.5 py-0.5 text-xs text-white"
                        >{{
                            __(
                                "Sketch of the month",
                            )
                        }}</span
                    >
                @elseif ($observation->blog_sketch_of === "week")
                    <span
                        class="rounded bg-amber-500 px-1.5 py-0.5 text-xs text-white"
                        >{{
                            __(
                                "Sketch of the week",
                            )
                        }}</span
                    >
                @endif
            </div>
            <div class="mt-1 text-sm text-gray-300">
                <x-observation-description :observation="$observation" />
            </div>
        </li>
    @endforeach
</ul>
