<x-app-layout>
    @php
        $inputClass = "block w-full rounded border border-gray-600 bg-gray-900 px-3 py-2 text-gray-100 focus:ring-2 focus:ring-blue-500 focus:outline-none";
        $selectedSource = old("source", $challenge->source()["key"] ?? "");
    @endphp
    <div class="mx-auto max-w-screen-lg bg-gray-900 px-6 py-6 sm:px-6 lg:px-8">
        <h1 class="mb-4 text-xl font-semibold text-white">
            {{ $challenge->exists ? __("Edit the challenge") : __("New challenge") }}
        </h1>

        <form
            method="POST"
            action="{{ $challenge->exists ? route("admin.challenges.update", $challenge) : route("admin.challenges.store") }}"
            class="space-y-4 text-sm text-gray-200"
        >
            @csrf
            @if ($challenge->exists)
                @method ("PUT")
            @endif

            <div>
                <label for="name" class="mb-1 block font-medium text-gray-400">{{ __("Name") }}</label>
                <input id="name" name="name" type="text" maxlength="120" required value="{{ old("name", $challenge->name) }}" placeholder="{{ __("e.g. Hickson month") }}" class="{{ $inputClass }}" />
                @error ("name")
                    <p class="mt-1 text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label for="source" class="mb-1 block font-medium text-gray-400">{{ __("Catalog or observing list") }}</label>
                    <select id="source" name="source" class="{{ $inputClass }}">
                        <optgroup label="{{ __("Catalog") }}">
                            @foreach ($sources->where("type", "catalog") as $source)
                                <option value="{{ $source["key"] }}" @selected ($selectedSource === $source["key"])>{{ $source["label"] }}</option>
                            @endforeach
                        </optgroup>
                        @if ($sources->where("type", "list")->isNotEmpty())
                            <optgroup label="{{ __("Observing list") }}">
                                @foreach ($sources->where("type", "list") as $source)
                                    <option value="{{ $source["key"] }}" @selected ($selectedSource === $source["key"])>{{ $source["label"] }}</option>
                                @endforeach
                            </optgroup>
                        @endif
                    </select>
                    <p class="mt-1 text-xs text-gray-400">{{ __("Only public observing lists you own or subscribed to can be chosen.") }}</p>
                    @error ("source")
                        <p class="mt-1 text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="target" class="mb-1 block font-medium text-gray-400">{{ __("Number of objects") }}</label>
                    <input id="target" name="target" type="number" min="1" value="{{ old("target", $challenge->target_count) }}" placeholder="{{ __("All objects") }}" class="{{ $inputClass }}" />
                    <p class="mt-1 text-xs text-gray-400">{{ __("Leave empty for all objects of the catalog or list.") }}</p>
                    @error ("target")
                        <p class="mt-1 text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="starts_on" class="mb-1 block font-medium text-gray-400">{{ __("Start") }}</label>
                    <input id="starts_on" name="starts_on" type="date" required value="{{ old("starts_on", $challenge->starts_on?->toDateString()) }}" class="{{ $inputClass }}" />
                    @error ("starts_on")
                        <p class="mt-1 text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="ends_on" class="mb-1 block font-medium text-gray-400">{{ __("End") }}</label>
                    <input id="ends_on" name="ends_on" type="date" required value="{{ old("ends_on", $challenge->ends_on?->toDateString()) }}" class="{{ $inputClass }}" />
                    @error ("ends_on")
                        <p class="mt-1 text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex flex-wrap gap-x-6 gap-y-2">
                <label class="flex items-center gap-2">
                    <input type="radio" name="mode" value="observed" @checked (old("mode", $challenge->mode) !== "drawn") />
                    {{ __("See the objects") }}
                </label>
                <label class="flex items-center gap-2">
                    <input type="radio" name="mode" value="drawn" @checked (old("mode", $challenge->mode) === "drawn") />
                    {{ __("Draw the objects") }}
                </label>
                <label class="flex items-center gap-2">
                    <input type="hidden" name="new_only" value="0" />
                    <input type="checkbox" name="new_only" value="1" @checked (old("new_only", $challenge->new_only)) />
                    {{ __("Only objects the observer had not seen before the start") }}
                </label>
                <label class="flex items-center gap-2">
                    <input type="hidden" name="hidden" value="0" />
                    <input type="checkbox" name="hidden" value="1" @checked (old("hidden", $challenge->hidden)) />
                    {{ __("Hidden (also its news item)") }}
                </label>
            </div>

            <div>
                <label for="description" class="mb-1 block font-medium text-gray-400">{{ __("Text (optional)") }}</label>
                <textarea id="description" name="description" class="block h-64 w-full">{{ old("description", $challenge->description) }}</textarea>
                @error ("description")
                    <p class="mt-1 text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-3">
                <button type="submit" class="rounded bg-green-700 px-4 py-2 font-medium text-white hover:bg-green-600">{{ __("Save") }}</button>
                <a href="{{ route("admin.challenges") }}" class="rounded px-4 py-2 text-gray-300 hover:bg-gray-700">{{ __("Cancel") }}</a>
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
