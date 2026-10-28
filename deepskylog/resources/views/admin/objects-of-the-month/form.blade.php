<x-app-layout>
    <div class="mx-auto max-w-screen-lg bg-gray-900 px-6 py-6 sm:px-6 lg:px-8">
        <p class="mb-2 text-sm">
            <a
                href="{{ route("admin.objects-of-the-month") }}"
                class="text-blue-400 hover:underline"
                >&larr; {{ __("Objects of the month") }}</a
            >
        </p>
        <h1 class="text-xl font-semibold text-white">{{ $item->title }}</h1>
        <p class="mb-4 text-sm">
            <a
                href="{{ $item->url }}"
                class="text-blue-400 hover:underline"
                rel="noopener"
                target="_blank"
                >{{
                    __(
                        "Read the thread on Astroforum",
                    )
                }}</a
            >
        </p>

        <form
            method="POST"
            action="{{ route("admin.objects-of-the-month.update", $item) }}"
            class="space-y-5 text-sm text-gray-200"
        >
            @csrf
            @method ("PUT")

            <fieldset>
                <legend class="mb-1 font-medium text-gray-400">
                    {{
                        __(
                            "Objects of the month",
                        )
                    }}
                </legend>
                <p class="mb-2 text-gray-400">
                    {{
                        __(
                            "The objects DeepskyLog found in the thread, those named in the title first, then those named most often. Tick the objects of the month; the others are only mentioned in it.",
                        )
                    }}
                </p>
                @if ($objects->isEmpty())
                    <p class="text-gray-300">{{
                        __(
                            "DeepskyLog found no objects in the thread.",
                        )
                    }}</p>
                @else
                    <div class="grid grid-cols-1 gap-1 sm:grid-cols-2">
                        @foreach ($objects as $object)
                            @php ($key = \App\Models\ObjectOfTheMonth::key($object))
                            <label class="flex items-center gap-2">
                                <input
                                    type="checkbox"
                                    name="objects[]"
                                    value="{{ $key }}"
                                    @checked (in_array($key, old("objects", $selected), true))
                                    class="rounded border-gray-600 bg-gray-900"
                                />
                                <a
                                    href="{{ route("object.show", $object["slug"]) }}"
                                    class="text-blue-400 hover:underline"
                                    target="_blank"
                                    >{{
                                        $object[
                                            "name"
                                        ]
                                    }}</a
                                >
                                @isset ($object["mentions"])
                                    <span class="text-xs text-gray-500">
                                        {{
                                            trans_choice(
                                                "{1} named once|[2,*] named :count times",
                                                $object["mentions"],
                                                ["count" => $object["mentions"]],
                                            )
                                        }}
                                        @if (!empty($object["in_title"])) ,{{
                                            __(
                                                "in the title",
                                            )
                                        }}@endif
                                    </span>
                                @endisset
                            </label>
                        @endforeach
                    </div>
                @endif
                @if (!empty($item->unresolved))
                    <p class="mt-2 text-yellow-400">
                        {{
                            __(
                                "Named in the thread but not in DeepskyLog: :names",
                                ["names" => implode(", ", $item->unresolved)],
                            )
                        }}
                    </p>
                @endif
            </fieldset>

            <div>
                <label
                    for="extra"
                    class="mb-1 block font-medium text-gray-400"
                    >{{
                        __(
                            "Other objects",
                        )
                    }}</label
                >
                <textarea
                    id="extra"
                    name="extra"
                    rows="3"
                    placeholder="NGC 7000, Albireo, Saturn"
                    class="block w-full rounded border border-gray-600 bg-gray-900 px-3 py-2 text-gray-100 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                    >{{ old("extra") }}</textarea
                >
                <p class="mt-1 text-gray-400">{{
                    __(
                        "Objects DeepskyLog did not find, by name, separated by commas or on separate lines.",
                    )
                }}</p>
                @error ("extra")
                    <p class="mt-1 text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-wrap gap-4">
                <div>
                    <label
                        for="starts_on"
                        class="mb-1 block font-medium text-gray-400"
                        >{{ __("From") }}</label
                    >
                    <input
                        id="starts_on"
                        name="starts_on"
                        type="date"
                        required
                        value="{{ old("starts_on", $item->starts_on->toDateString()) }}"
                        class="block rounded border border-gray-600 bg-gray-900 px-3 py-2 text-gray-100"
                    />
                </div>
                <div>
                    <label
                        for="ends_on"
                        class="mb-1 block font-medium text-gray-400"
                        >{{ __("Until") }}</label
                    >
                    <input
                        id="ends_on"
                        name="ends_on"
                        type="date"
                        required
                        value="{{ old("ends_on", $item->ends_on->toDateString()) }}"
                        class="block rounded border border-gray-600 bg-gray-900 px-3 py-2 text-gray-100"
                    />
                    @error ("ends_on")
                        <p class="mt-1 text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <p class="text-gray-400">{{ __("The observations of the objects made in these days are shown with the object of the month. DeepskyLog takes the months from the title of the thread.") }}</p>

            <label class="flex items-center gap-2">
                <input type="hidden" name="hidden" value="0" />
                <input
                    type="checkbox"
                    name="hidden"
                    value="1"
                    @checked (old("hidden", $item->hidden))
                    class="rounded border-gray-600 bg-gray-900"
                />
                {{
                    __(
                        "Hide this object of the month",
                    )
                }}
            </label>

            <button
                type="submit"
                class="rounded bg-green-700 px-4 py-2 font-medium text-white hover:bg-green-600"
            >
                {{ __("Save") }}
            </button>
        </form>
    </div>
</x-app-layout>
