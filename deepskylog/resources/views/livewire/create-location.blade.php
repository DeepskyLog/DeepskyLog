<div>
    <div>
        <div class="max-w-screen mx-auto bg-gray-900 px-2 py-10 sm:px-6 lg:px-8">
            <h2 class="text-xl font-semibold leading-tight">
                @if ($update)
                    {{ __("Update ") . $name }}
                @else
                    {{ __("Create a new location") }}
                @endif
            </h2>
            <div class="mt-2">
                <x-card>
                    <form role="form" method="POST" wire:submit.prevent="save">
                        @csrf
                        <div class="col-span-6 sm:col-span-5">

                            <x-input
                                name="name"
                                label="{{ __('Name of the location') }}"
                                type="text"
                                wire:model.live="name"
                                class="mt-1 block w-full"
                                required
                                maxlength="255"
                                value="{{ old('name') }}"
                            />

                            <!-- Leaflet.js Map and Search -->
                            <div class="mt-4">
                                    <div id="map" class="w-full h-96 mt-4 rounded" style="z-index:1;" wire:ignore></div>
                            </div>


                            <x-input
                                name="latitude"
                                label="{{ __('Latitude') }}"
                                type="number"
                                step="any"
                                wire:model.live="latitude"
                                class="mt-1 block w-full"
                                value="{{ old('latitude') }}"
                            />

                            <x-input
                                name="longitude"
                                label="{{ __('Longitude') }}"
                                type="number"
                                step="any"
                                wire:model.live="longitude"
                                class="mt-1 block w-full"
                                value="{{ old('longitude') }}"
                            />

                            <x-toggle
                                name="hidden"
                                label="{{ __('Hidden') }}"
                                wire:model="hidden"
                                class="mt-2"
                                id="hidden"
                            />
                        </div>

                        <br/>

                        @if($update)
                            <x-button class="mt-5" type="submit" secondary label="{{ __('Update location') }}" />
                        @else
                            <x-button class="mt-5" type="submit" secondary label="{{ __('Add new location') }}" />
                        @endif
                        @if (session()->has('message'))
                            <div class="text-green-500 text-sm">{{ session('message') }}</div>
                        @endif
                    </form>
                </x-card>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        window.leafletMap = null;
        function initLeafletMap() {
            var mapDiv = document.getElementById('map');
            if (!mapDiv) return;
            if (window.leafletMap) {
                window.leafletMap.remove();
                window.leafletMap = null;
            }
            console.log('Initializing Leaflet map...');
            var lat = {{ is_numeric($latitude ?? null) ? $latitude : 'null' }};
            var lng = {{ is_numeric($longitude ?? null) ? $longitude : 'null' }};

            function initMap(latitude, longitude) {
                window.leafletMap = L.map('map', { fullscreenControl: true }).setView([latitude, longitude], 13);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap contributors'
                }).addTo(window.leafletMap);

                var marker = L.marker([latitude, longitude], {draggable: true}).addTo(window.leafletMap);
                marker.setZIndexOffset(1000);

                function updateLatLng(lat, lng) {
                    @this.set('latitude', lat);
                    @this.set('longitude', lng);
                }

                window.leafletMap.on('moveend', function () {
                    var center = window.leafletMap.getCenter();
                    marker.setLatLng(center);
                    updateLatLng(center.lat, center.lng);
                });

                marker.on('dragend', function (e) {
                    var pos = marker.getLatLng();
                    window.leafletMap.setView(pos);
                    updateLatLng(pos.lat, pos.lng);
                });

                // Geocoder search (v3+ API)
                var geocoder = new L.Control.Geocoder.Nominatim();
                var geocoderControl = new L.Control.Geocoder({
                    geocoder: geocoder,
                    defaultMarkGeocode: false
                });
                geocoderControl.on('markgeocode', function(e) {
                    var bbox = e.geocode.bbox;
                    var center = e.geocode.center;
                    window.leafletMap.fitBounds(bbox);
                    marker.setLatLng(center);
                    updateLatLng(center.lat, center.lng);
                });
                geocoderControl.addTo(window.leafletMap);

            }

            if (lat === null || lng === null) {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function(position) {
                        lat = position.coords.latitude;
                        lng = position.coords.longitude;
                        initMap(lat, lng);
                        @this.set('latitude', lat);
                        @this.set('longitude', lng);
                    }, function() {
                        // Fallback to default if denied
                        initMap(28.7606, -17.8892);
                        @this.set('latitude', 28.7606);
                        @this.set('longitude', -17.8892);
                    });
                } else {
                    // Geolocation not supported
                    initMap(28.7606, -17.8892);
                    @this.set('latitude', 28.7606);
                    @this.set('longitude', -17.8892);
                }
            }
        }
        window.addEventListener('init-map', initLeafletMap);
        document.addEventListener('DOMContentLoaded', initLeafletMap);
    </script>
@endpush

