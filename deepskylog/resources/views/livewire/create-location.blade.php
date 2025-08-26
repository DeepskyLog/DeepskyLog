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

                            <x-input
                                name="elevation"
                                label="{{ __('Elevation (meters)') }}"
                                type="number"
                                step="any"
                                wire:model.live="elevation"
                                class="mt-1 block w-full"
                                value="{{ old('elevation') }}"
                            />

                            <x-input
                                name="country"
                                label="{{ __('Country') }}"
                                type="text"
                                wire:model.live="country"
                                class="mt-1 block w-full"
                                maxlength="255"
                                value="{{ old('country') }}"
                            />

                            <x-input
                                name="timezone"
                                label="{{ __('Timezone') }}"
                                type="text"
                                wire:model.live="timezone"
                                class="mt-1 block w-full"
                                maxlength="255"
                                value="{{ old('timezone') }}"
                            />

                            <!-- SQM, NELM and Bortle on one line -->
                            <div class="mt-4 flex flex-col gap-3 md:flex-row">
                                <div class="flex-1">
                                    <x-input
                                        name="sqm"
                                        label="{{ __('SQM (mag/arcsec²)') }}"
                                        type="number"
                                        step="0.01"
                                        min="15"
                                        max="22"
                                        wire:model.live="sqm"
                                        class="mt-1 block w-full"
                                        value="{{ old('sqm') }}"
                                    />
                                </div>

                                <div class="flex-1">
                                    <x-input
                                        name="nelm"
                                        label="{{ __('NELM') }}"
                                        type="number"
                                        step="0.1"
                                        min="0"
                                        max="8"
                                        wire:model.live="nelm"
                                        class="mt-1 block w-full"
                                        value="{{ old('nelm') }}"
                                    />
                                </div>

                                <div class="flex-1">
                                    <x-select
                                        id="bortle"
                                        name="bortle"
                                        label="{{ __('Bortle') }}"
                                        wire:model.live="bortle"
                                        x-on:selected="$wire.set('bortle', $event.detail.value)"
                                        :options="[
                                            ['id' => 1, 'name' => '1 - Excellent dark-sky site'],
                                            ['id' => 2, 'name' => '2 - Typical truly dark site'],
                                            ['id' => 3, 'name' => '3 - Rural sky'],
                                            ['id' => 4, 'name' => '4 - Rural/suburban transition'],
                                            ['id' => 5, 'name' => '5 - Suburban sky'],
                                            ['id' => 6, 'name' => '6 - Bright suburban sky'],
                                            ['id' => 7, 'name' => '7 - Suburban/urban transition'],
                                            ['id' => 8, 'name' => '8 - City sky'],
                                            ['id' => 9, 'name' => '9 - Inner-city sky'],
                                        ]"
                                        option-label="name"
                                        option-value="id"
                                    />
                                </div>

                                <!-- Fetch button -->
                                <div class="flex items-end">
                                    <x-button
                                        type="button"
                                        secondary
                                        label="{{ __('Fetch from Light Pollution Map') }}"
                                        wire:click="fetchLightPollutionData"
                                    />
                                </div>
                            </div>

                            <br />

                            <x-toggle
                                name="hidden"
                                label="{{ __('Hide the exact location for other users') }}"
                                wire:model="hidden"
                                class="mt-2"
                                id="hidden"
                            />

                            <br />

                            <div class="col-span-6 text-sm text-gray-400 sm:col-span-5">
                                {{ __("Tell something about your location") }}
                            </div>
                            <div class="col-span-6 sm:col-span-5" wire:ignore>
                                <textarea
                                    wire:model.live="description"
                                    class="h-48 min-h-fit"
                                    name="description"
                                    id="description"
                                ></textarea>
                            </div>

                            <div class="mt-5">
                                <x-input type="file"
                                            label="{!! __('Upload image') !!}"
                                            wire:model="photo"/>

                                @error('photo') <span class="error">{{ $message }}</span> @enderror

                                @if ($update)
                                    @if($location->picture)
                                        <img
                                            alt="{{ __('Picture of location') }}"
                                            class="mt-2 h-40 w-40 object-cover"
                                            src="{{ '/storage/'.asset($location->picture) }}">

                                    @endif
                                @endif
                                @if ($photo)
                                    <img
                                        alt="{{ __('Picture of location') }}"
                                        class="mt-2 h-40 w-40 object-cover"
                                        src="{{ $photo->temporaryUrl() }}">
                                @endif
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            function initializeTinyMCE() {
                if (typeof tinymce === 'undefined') return;
                var el = document.querySelector('#description');
                if (!el) return;

                if (tinymce.get("description")) {
                    tinymce.get("description").remove();
                }

                tinymce.init({
                    selector: "#description",
                    plugins: "lists emoticons quickbars wordcount",
                    toolbar: "undo redo | bold italic | alignleft aligncenter alignright | indent outdent | bullist numlist | emoticons | wordcount",
                    menubar: false,
                    license_key: 'gpl',
                    quickbars_insert_toolbar: false,
                    quickbars_image_toolbar: false,
                    quickbars_selection_toolbar: "bold italic",
                    skin: "oxide-dark",
                    content_css: "dark",
                    setup: function (editor) {
                        editor.on("init", function () {
                            editor.save();
                            console.log('TinyMCE initialized for #description');
                        });
                        editor.on("change", function () {
                            editor.save();
                            if (typeof Livewire !== 'undefined') {
                                Livewire.emit('setDescription', editor.getContent());
                            }
                        });
                    }
                });
            }

            // Try to initialize immediately if possible.
            if (typeof tinymce !== 'undefined' && document.querySelector('#description')) {
                initializeTinyMCE();
            } else {
                // Retry for a short period in case assets are still loading or Livewire re-renders.
                var retryCount = 0;
                var retryMax = 20; // ~5 seconds
                var retryInterval = setInterval(function () {
                    retryCount++;
                    if (typeof tinymce !== 'undefined' && document.querySelector('#description')) {
                        initializeTinyMCE();
                        clearInterval(retryInterval);
                    } else if (retryCount >= retryMax) {
                        clearInterval(retryInterval);
                    }
                }, 250);
            }

            // Initialize on Livewire load and after messages processed to survive re-renders.
            document.addEventListener('livewire:load', function () {
                initializeTinyMCE();
            });

            // Attach hook when Livewire becomes available.
            if (typeof Livewire !== 'undefined' && Livewire.hook) {
                Livewire.hook('message.processed', function () {
                    initializeTinyMCE();
                });
            } else {
                window.addEventListener('livewire:load', function () {
                    if (typeof Livewire !== 'undefined' && Livewire.hook) {
                        Livewire.hook('message.processed', function () {
                            initializeTinyMCE();
                        });
                    }
                });
            }
        });
    </script>


@endpush

