<x-app-layout>
    <div class="mx-auto max-w-screen-lg bg-gray-900 px-6 py-6 sm:px-6 lg:px-8">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <h1 class="text-xl font-semibold text-white">{{ __("Starparties") }}</h1>
            <div class="flex items-center gap-3 text-sm">
                @auth
                    <a href="{{ route("star-parties.create") }}" class="rounded bg-green-700 px-3 py-2 font-medium text-white hover:bg-green-600">{{ __("Organise a starparty") }}</a>
                @endauth
                <a href="{{ route("feed.star-parties") }}" class="text-orange-400 hover:underline">RSS</a>
            </div>
        </div>
        <p class="mb-6 text-sm text-gray-400">
            {{ __("Observe together with other observers. A starparty shows the nights at the site, the objects worth observing there and, afterwards, everything that was observed.") }}
        </p>

        @if ($mine !== null && $hasMine)
            <section class="mb-6">
                <div class="mb-2 flex flex-wrap items-baseline justify-between gap-2">
                    <h2 class="text-lg font-semibold text-white">{{ __("My starparties") }}</h2>
                    <a href="{{ request()->fullUrlWithQuery(["mine" => $allMine ? null : "all", "mine_page" => null]) }}" class="text-sm text-blue-400 hover:underline">
                        {{ $allMine ? __("Only those that have not ended") : __("Show all my starparties") }}
                    </a>
                </div>
                @if ($mine->isEmpty())
                    <p class="text-gray-400">{{ __("None of your starparties is still to come.") }}</p>
                @else
                    <div class="rounded-lg bg-gray-800 px-4">
                        <ul class="divide-y divide-gray-700">
                            @foreach ($mine as $party)
                                @include ("star-parties.partials.row", ["party" => $party])
                            @endforeach
                        </ul>
                    </div>
                    <div class="mt-4">{{ $mine->links() }}</div>
                @endif
            </section>
        @endif

        <section class="mb-6">
            <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-lg font-semibold text-white">{{ __("Coming starparties") }}</h2>
                @if (count($countries) > 1 || $country !== null)
                    <form method="GET" action="{{ route("star-parties.index") }}" class="text-sm">
                        @if ($allMine)
                            <input type="hidden" name="mine" value="all" />
                        @endif
                        <label for="country" class="mr-1 text-gray-400">{{ __("Country") }}</label>
                        <select id="country" name="country" onchange="this.form.submit()" class="rounded border border-gray-600 bg-gray-900 px-2 py-1 text-gray-100">
                            <option value="">{{ __("All countries") }}</option>
                            @foreach ($countries as $name => $label)
                                <option value="{{ $name }}" @selected ($country === $name)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <noscript><button type="submit" class="ml-1 text-blue-400">{{ __("Show") }}</button></noscript>
                    </form>
                @endif
            </div>
            @if ($upcoming->isEmpty())
                <p class="text-gray-400">{{ __("There are no coming starparties.") }}</p>
            @else
                @php
                    $mapMarkers = $markers->map(fn ($party) => [
                        "lat" => $party->latitude,
                        "lng" => $party->longitude,
                        "name" => html_entity_decode($party->name, ENT_QUOTES | ENT_HTML5, "UTF-8"),
                        "dates" => $party->dateRange(),
                        "url" => route("star-parties.show", $party),
                    ])->values();
                @endphp
                <div id="star-parties-map" class="mb-4 w-full rounded" style="height: 20rem" data-markers="{{ json_encode($mapMarkers) }}"></div>
                <div class="rounded-lg bg-gray-800 px-4">
                    <ul class="divide-y divide-gray-700">
                        @foreach ($upcoming as $party)
                            @include ("star-parties.partials.row", ["party" => $party])
                        @endforeach
                    </ul>
                </div>
                <div class="mt-4">{{ $upcoming->links() }}</div>
            @endif
        </section>

        @if ($past->isNotEmpty())
            <section>
                <h2 class="mb-2 text-lg font-semibold text-white">{{ __("Past starparties") }}</h2>
                <div class="rounded-lg bg-gray-800 px-4">
                    <ul class="divide-y divide-gray-700">
                        @foreach ($past as $party)
                            @include ("star-parties.partials.row", ["party" => $party])
                        @endforeach
                    </ul>
                </div>
                <div class="mt-4">{{ $past->links() }}</div>
            </section>
        @endif
    </div>

    @push ("scripts")
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                var mapEl = document.getElementById("star-parties-map");
                if (!mapEl || typeof L === "undefined") return;
                var markers = JSON.parse(mapEl.getAttribute("data-markers") || "[]");
                var map = L.map("star-parties-map", { fullscreenControl: true }).setView([50, 5], 4);
                L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                    maxZoom: 19,
                    attribution: "© OpenStreetMap contributors",
                }).addTo(map);
                var bounds = [];
                markers.forEach(function (m) {
                    var popup = document.createElement("div");
                    var link = document.createElement("a");
                    link.href = m.url;
                    link.textContent = m.name;
                    link.style.fontWeight = "bold";
                    popup.appendChild(link);
                    popup.appendChild(document.createElement("br"));
                    popup.appendChild(document.createTextNode(m.dates));
                    L.marker([m.lat, m.lng]).addTo(map).bindPopup(popup);
                    bounds.push([m.lat, m.lng]);
                });
                if (bounds.length === 1) {
                    map.setView(bounds[0], 8);
                } else if (bounds.length > 1) {
                    map.fitBounds(bounds, { padding: [30, 30], maxZoom: 8 });
                }
            });
        </script>
    @endpush
</x-app-layout>
