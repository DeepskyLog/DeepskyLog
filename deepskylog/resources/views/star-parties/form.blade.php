<x-app-layout>
    @php
        $input = "block w-full rounded border border-gray-600 bg-gray-900 px-3 py-2 text-gray-100 focus:ring-2 focus:ring-blue-500 focus:outline-none";
        $label = "mb-1 block font-medium text-gray-400";
    @endphp
    <div class="mx-auto max-w-screen-lg bg-gray-900 px-6 py-6 sm:px-6 lg:px-8">
        <h1 class="mb-4 text-xl font-semibold text-white">
            {{ $party->exists ? __("Edit starparty") : __("Organise a starparty") }}
        </h1>

        <form
            method="POST"
            action="{{ $party->exists ? route("star-parties.update", $party) : route("star-parties.store") }}"
            class="space-y-4 text-sm text-gray-200"
        >
            @csrf
            @if ($party->exists)
                @method ("PUT")
            @endif

            <div>
                <label for="name" class="{{ $label }}">{{ __("Name") }}</label>
                <input id="name" name="name" type="text" maxlength="255" required value="{{ old("name", html_entity_decode((string) $party->name, ENT_QUOTES | ENT_HTML5, "UTF-8")) }}" class="{{ $input }}" />
                @error ("name")
                    <p class="mt-1 text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="start_date" class="{{ $label }}">{{ __("First night") }}</label>
                    <input id="start_date" name="start_date" type="date" required value="{{ old("start_date", $party->start_date?->toDateString()) }}" class="{{ $input }}" />
                    @error ("start_date")
                        <p class="mt-1 text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="end_date" class="{{ $label }}">{{ __("Last night") }}</label>
                    <input id="end_date" name="end_date" type="date" required value="{{ old("end_date", $party->end_date?->toDateString()) }}" class="{{ $input }}" />
                    @error ("end_date")
                        <p class="mt-1 text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <p class="-mt-2 text-xs text-gray-400">
                {{ __("The date of the evening a night starts. A starparty lasts at most :max nights.", ["max" => \App\Models\StarParty::MAX_NIGHTS]) }}
            </p>

            <div>
                <label for="location_id" class="{{ $label }}">{{ __("Location") }}</label>
                @if ($locations->isEmpty())
                    <p class="text-gray-300">
                        {{ __("A starparty takes place at one of your locations. You have no location yet.") }}
                        <a href="{{ route("location.create") }}" class="text-blue-400 hover:underline">{{ __("Add a location") }}</a>
                    </p>
                @else
                    <select id="location_id" name="location_id" class="{{ $input }}" @required (! $party->exists)>
                        @if ($party->exists)
                            <option value="">{{ __("Keep the site :site", ["site" => $party->location_name]) }}</option>
                        @endif
                        @foreach ($locations as $location)
                            <option value="{{ $location->id }}" @selected ((int) old("location_id", $party->exists ? null : $party->location_id) === (int) $location->id)>
                                {{ html_entity_decode($location->name, ENT_QUOTES | ENT_HTML5, "UTF-8") }}@if ($location->country) ({{ $location->country }})@endif
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-400">
                        {{ __("The coordinates, time zone and sky quality of the location are used for the nights and the objects to observe.") }}
                        <a href="{{ route("location.create") }}" class="text-blue-400 hover:underline">{{ __("Add a location") }}</a>
                    </p>
                @endif
                @error ("location_id")
                    <p class="mt-1 text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="venue" class="{{ $label }}">{{ __("Address or directions (optional)") }}</label>
                <input id="venue" name="venue" type="text" maxlength="255" value="{{ old("venue", $party->venue) }}" class="{{ $input }}" />
                @error ("venue")
                    <p class="mt-1 text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="website" class="{{ $label }}">{{ __("Website (optional)") }}</label>
                <input id="website" name="website" type="url" maxlength="255" placeholder="https://" value="{{ old("website", $party->website) }}" class="{{ $input }}" />
                @error ("website")
                    <p class="mt-1 text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="{{ $label }}">{{ __("Description") }}</label>
                <textarea id="description" name="description" class="block h-64 w-full">{{ old("description", $party->description) }}</textarea>
                @error ("description")
                    <p class="mt-1 text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <fieldset>
                <legend class="{{ $label }}">{{ __("Who can see the starparty?") }}</legend>
                <label class="flex items-start gap-2">
                    <input type="radio" name="visibility" value="public" class="mt-1" @checked (old("visibility", $party->visibility) === "public") />
                    <span>{{ __("Everybody: the starparty is listed on DeepskyLog and every observer can come.") }}</span>
                </label>
                <label class="mt-1 flex items-start gap-2">
                    <input type="radio" name="visibility" value="private" class="mt-1" @checked (old("visibility", $party->visibility) === "private") />
                    <span>{{ __("Only the observers I invite.") }}</span>
                </label>
                @error ("visibility")
                    <p class="mt-1 text-red-400">{{ $message }}</p>
                @enderror
            </fieldset>

            <fieldset>
                <legend class="{{ $label }}">{{ __("Observed at the starparty") }}</legend>
                <p class="mb-2 text-xs text-gray-400">{{ __("After the first evening, the page of the starparty shows the sessions and observations recorded at a location near the site in its nights.") }}</p>
                <div class="flex flex-wrap items-center gap-4">
                    <label class="flex items-center gap-2">
                        <span>{{ __("Within") }}</span>
                        <select name="activity_radius_km" class="rounded border border-gray-600 bg-gray-900 px-2 py-1 text-gray-100">
                            @foreach (\App\Models\StarParty::RADIUS_OPTIONS as $km)
                                <option value="{{ $km }}" @selected ((float) old("activity_radius_km", $party->activity_radius_km) === (float) $km)>{{ $km }} km</option>
                            @endforeach
                        </select>
                        <span>{{ __("of the site") }}</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="hidden" name="activity_participants_only" value="0" />
                        <input type="checkbox" name="activity_participants_only" value="1" @checked (old("activity_participants_only", $party->activity_participants_only)) />
                        <span>{{ __("Only of the observers who come") }}</span>
                    </label>
                </div>
                @error ("activity_radius_km")
                    <p class="mt-1 text-red-400">{{ $message }}</p>
                @enderror
            </fieldset>

            <div class="flex flex-wrap gap-4">
                <div>
                    <label for="registration_deadline" class="{{ $label }}">{{ __("Answer before (optional)") }}</label>
                    <input id="registration_deadline" name="registration_deadline" type="date" value="{{ old("registration_deadline", $party->registration_deadline?->toDateString()) }}" class="{{ $input }}" />
                    @error ("registration_deadline")
                        <p class="mt-1 text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="max_participants" class="{{ $label }}">{{ __("Most observers (optional)") }}</label>
                    <input id="max_participants" name="max_participants" type="number" min="1" max="5000" value="{{ old("max_participants", $party->max_participants) }}" class="{{ $input }}" />
                    @error ("max_participants")
                        <p class="mt-1 text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="language" class="{{ $label }}">{{ __("Written in") }}</label>
                    <select id="language" name="language" class="{{ $input }}">
                        @foreach (config("app.available_locales") as $name => $code)
                            <option value="{{ $code }}" @selected (old("language", $party->language) === $code)>{{ __($name) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="rounded bg-green-700 px-4 py-2 font-medium text-white hover:bg-green-600">
                    {{ __("Save") }}
                </button>
                <a href="{{ $party->exists ? route("star-parties.show", $party) : route("star-parties.index") }}" class="rounded px-4 py-2 text-gray-300 hover:bg-gray-700">{{ __("Cancel") }}</a>
            </div>
        </form>
    </div>

    @push ("scripts")
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                var tries = 0;
                var timer = setInterval(function () {
                    if (typeof tinymce === "undefined" && ++tries < 20) return;
                    clearInterval(timer);
                    if (typeof tinymce === "undefined") return;
                    tinymce.init({
                        selector: "#description",
                        plugins: "lists link emoticons quickbars wordcount",
                        toolbar:
                            "undo redo | bold italic | bullist numlist | link | emoticons | wordcount",
                        menubar: false,
                        license_key: "gpl",
                        quickbars_insert_toolbar: false,
                        quickbars_image_toolbar: false,
                        quickbars_selection_toolbar: "bold italic link",
                        skin: "oxide-dark",
                        content_css: "dark",
                        convert_urls: false,
                        setup: function (editor) {
                            editor.on("change", function () {
                                editor.save();
                            });
                        },
                    });
                }, 250);
            });
        </script>
    @endpush
</x-app-layout>
