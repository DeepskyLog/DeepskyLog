<x-app-layout>
    <div class="mx-auto max-w-screen-lg bg-gray-900 px-6 py-6 sm:px-6 lg:px-8">
        <h1 class="mb-4 text-xl font-semibold text-white">
            {{
                $item->exists
                    ? __("Edit news item")
                    : __("Write a news item")
            }}
        </h1>

        <form
            method="POST"
            action="{{ $item->exists ? route("admin.news.update", $item) : route("admin.news.store") }}"
            class="space-y-4 text-sm text-gray-200"
        >
            @csrf
            @if ($item->exists)
                @method ("PUT")
            @endif

            <div>
                <label
                    for="title"
                    class="mb-1 block font-medium text-gray-400"
                    >{{ __("Title") }}</label
                >
                <input
                    id="title"
                    name="title"
                    type="text"
                    maxlength="255"
                    required
                    value="{{ old("title", $item->title) }}"
                    class="block w-full rounded border border-gray-600 bg-gray-900 px-3 py-2 text-gray-100 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                />
                @error ("title")
                    <p class="mt-1 text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label
                    for="body"
                    class="mb-1 block font-medium text-gray-400"
                    >{{ __("Text") }}</label
                >
                <textarea id="body" name="body" class="block h-64 w-full">{{
                    old(
                        "body",
                        $item->body,
                    )
                }}</textarea>
                @error ("body")
                    <p class="mt-1 text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-wrap gap-4">
                <div>
                    <label
                        for="language"
                        class="mb-1 block font-medium text-gray-400"
                        >{{
                            __(
                                "Written in",
                            )
                        }}</label
                    >
                    <select
                        id="language"
                        name="language"
                        class="block rounded border border-gray-600 bg-gray-900 px-3 py-2 text-gray-100 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                    >
                        @foreach (config("app.available_locales") as $name => $code)
                            <option
                                value="{{ $code }}"
                                @selected (old("language", $item->language) === $code)
                            >
                                {{ __($name) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label
                        for="published_at"
                        class="mb-1 block font-medium text-gray-400"
                        >{{
                            __(
                                "Published from (UT)",
                            )
                        }}</label
                    >
                    <input
                        id="published_at"
                        name="published_at"
                        type="datetime-local"
                        required
                        value="{{ old("published_at", $item->published_at?->format("Y-m-d\TH:i")) }}"
                        class="block rounded border border-gray-600 bg-gray-900 px-3 py-2 text-gray-100 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                    />
                    @error ("published_at")
                        <p class="mt-1 text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label
                        for="expires_at"
                        class="mb-1 block font-medium text-gray-400"
                        >{{
                            __(
                                "Shown on the home page until (UT, optional)",
                            )
                        }}</label
                    >
                    <input
                        id="expires_at"
                        name="expires_at"
                        type="datetime-local"
                        value="{{ old("expires_at", $item->expires_at?->format("Y-m-d\TH:i")) }}"
                        class="block rounded border border-gray-600 bg-gray-900 px-3 py-2 text-gray-100 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                    />
                    @error ("expires_at")
                        <p class="mt-1 text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex gap-3">
                <button
                    type="submit"
                    class="rounded bg-green-700 px-4 py-2 font-medium text-white hover:bg-green-600"
                >
                    {{ __("Save") }}
                </button>
                <a
                    href="{{ route("admin.news") }}"
                    class="rounded px-4 py-2 text-gray-300 hover:bg-gray-700"
                    >{{ __("Cancel") }}</a
                >
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
                        selector: "#body",
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
