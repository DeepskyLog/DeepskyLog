<x-app-layout>
    @php
        $input =
            "block w-full rounded border border-gray-600 bg-gray-900 px-3 py-2 text-gray-100 focus:ring-2 focus:ring-blue-500 focus:outline-none";
        $label = "mb-1 block font-medium text-gray-400";
    @endphp
    <div class="mx-auto max-w-screen-lg bg-gray-900 px-6 py-6 sm:px-6 lg:px-8">
        <h1 class="mb-4 text-xl font-semibold text-white">
            {{
                $club->exists
                    ? __("Edit the club")
                    : __("Make a club")
            }}
        </h1>

        <form
            method="POST"
            action="{{ $club->exists ? route("clubs.update", $club) : route("clubs.store") }}"
            class="space-y-4 text-sm text-gray-200"
        >
            @csrf
            @if ($club->exists)
                @method ("PUT")
            @endif

            <div>
                <label for="name" class="{{ $label }}">{{ __("Name") }}</label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    maxlength="120"
                    required
                    value="{{ old("name", html_entity_decode((string) $club->name, ENT_QUOTES | ENT_HTML5, "UTF-8")) }}"
                    class="{{ $input }}"
                />
                @error ("name")
                    <p class="mt-1 text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label for="place" class="{{ $label }}">{{
                        __(
                            "Place or region (optional)",
                        )
                    }}</label>
                    <input
                        id="place"
                        name="place"
                        type="text"
                        maxlength="120"
                        value="{{ old("place", html_entity_decode((string) $club->place, ENT_QUOTES | ENT_HTML5, "UTF-8")) }}"
                        class="{{ $input }}"
                    />
                    @error ("place")
                        <p class="mt-1 text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label
                        for="country"
                        class="{{ $label }}"
                        >{{ __("Country") }}</label
                    >
                    <select id="country" name="country" class="{{ $input }}">
                        <option value="">{{ __("None") }}</option>
                        @foreach ($countries as $code => $name)
                            <option
                                value="{{ $code }}"
                                @selected (old("country", $club->country) === $code)
                            >
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                    @error ("country")
                        <p class="mt-1 text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div class="md:col-span-2">
                    <label for="website" class="{{ $label }}">{{
                        __(
                            "Website (optional)",
                        )
                    }}</label>
                    <input
                        id="website"
                        name="website"
                        type="url"
                        maxlength="255"
                        value="{{ old("website", $club->website) }}"
                        placeholder="https://"
                        class="{{ $input }}"
                    />
                    @error ("website")
                        <p class="mt-1 text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <fieldset>
                <legend class="{{ $label }}">
                    {{
                        __(
                            "Who can join?",
                        )
                    }}
                </legend>
                <label class="flex items-start gap-2">
                    <input
                        type="radio"
                        name="join_policy"
                        value="approval"
                        class="mt-1"
                        @checked (old("join_policy", $club->join_policy) === "approval")
                    />
                    <span>{{
                        __(
                            "Observers ask to join, and an administrator of the club lets them in.",
                        )
                    }}</span>
                </label>
                <label class="mt-1 flex items-start gap-2">
                    <input
                        type="radio"
                        name="join_policy"
                        value="open"
                        class="mt-1"
                        @checked (old("join_policy", $club->join_policy) === "open")
                    />
                    <span>{{
                        __(
                            "Every observer can join at once.",
                        )
                    }}</span>
                </label>
                @error ("join_policy")
                    <p class="mt-1 text-red-400">{{ $message }}</p>
                @enderror
            </fieldset>

            <div>
                <label for="description" class="{{ $label }}">{{
                    __(
                        "Text (optional)",
                    )
                }}</label>
                <textarea
                    id="description"
                    name="description"
                    class="block h-64 w-full"
                    >{{
                        old(
                            "description",
                            $club->description,
                        )
                    }}</textarea
                >
                @error ("description")
                    <p class="mt-1 text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-3">
                <button
                    type="submit"
                    class="rounded bg-green-700 px-4 py-2 font-medium text-white hover:bg-green-600"
                >
                    {{ __("Save") }}
                </button>
                <a
                    href="{{ $club->exists ? route("clubs.show", $club) : route("clubs.index") }}"
                    class="rounded px-4 py-2 text-gray-300 hover:bg-gray-700"
                    >{{ __("Cancel") }}</a
                >
            </div>
        </form>

        @if ($club->exists)
            <form
                method="POST"
                action="{{ route("clubs.destroy", $club) }}"
                class="mt-6"
            >
                @csrf
                @method ("DELETE")
                <button
                    type="submit"
                    class="rounded bg-red-700 px-4 py-2 text-sm font-medium text-white hover:bg-red-600"
                    onclick="return confirm({{ \Illuminate\Support\Js::from(__("Delete the club with its challenges? This cannot be undone.")) }})"
                >
                    {{
                        __(
                            "Delete the club",
                        )
                    }}
                </button>
            </form>
        @endif
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
