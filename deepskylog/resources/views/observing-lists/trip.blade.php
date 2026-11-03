<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl leading-tight font-semibold text-gray-200">
            {{
                __(
                    "What could I see from…?",
                )
            }}
        </h2>
    </x-slot>

    <script>
        // The map of the trip planner: a click, a dragged marker or a found
        // place is passed to the component, which proposes its time zone and sky.
        window.dslTripMap = function (el, wire, lat, lon) {
            const start = lat !== null && lon !== null;
            const map = L.map(el, { fullscreenControl: true }).setView(
                start ? [lat, lon] : [20, 0],
                start ? 6 : 2,
            );
            L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                maxZoom: 19,
                attribution: "© OpenStreetMap contributors",
            }).addTo(map);

            let marker = null;
            const place = (latlng, send) => {
                if (!marker) {
                    marker = L.marker(latlng, { draggable: true }).addTo(map);
                    marker.on("dragend", () => place(marker.getLatLng(), true));
                } else {
                    marker.setLatLng(latlng);
                }
                if (send) {
                    const wrapped = L.latLng(latlng).wrap();
                    wire.pick(wrapped.lat, wrapped.lng);
                }
            };
            if (start) {
                place([lat, lon], false);
            }
            map.on("click", (e) => place(e.latlng, true));

            const geocoder = new L.Control.Geocoder({
                geocoder: new L.Control.Geocoder.Nominatim(),
                defaultMarkGeocode: false,
            });
            geocoder.on("markgeocode", (e) => {
                map.fitBounds(e.geocode.bbox);
                place(e.geocode.center, true);
            });
            geocoder.addTo(map);
        };
    </script>

    <div class="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8">
        @livewire ("trip-planner")
    </div>
</x-app-layout>
