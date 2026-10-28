<x-app-layout>
    @php
        $buttonClass = "rounded px-3 py-2 text-sm font-medium text-white";
    @endphp
    <div class="mx-auto max-w-screen-lg bg-gray-900 px-6 py-6 sm:px-6 lg:px-8">
        <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
            <h1 class="text-xl font-semibold text-white">{{ __("Clubs") }}</h1>
            @auth
                <a
                    href="{{ route("clubs.create") }}"
                    class="{{ $buttonClass }} bg-green-700 hover:bg-green-600"
                    >{{
                        __(
                            "Make a club",
                        )
                    }}</a
                >
            @endauth
        </div>
        <p class="mb-6 text-sm text-gray-400">
            {{
                __(
                    "A club or group brings observers together: its members, the observing lists they share, its own challenges and starparties, and a ranking of the members. You can be a member of several clubs.",
                )
            }}
        </p>

        @if ($legacyClub)
            <div class="mb-6 rounded-lg bg-gray-800 p-4 text-sm text-gray-300">
                {{
                    __(
                        "Your profile on the old DeepskyLog named the club :club.",
                        ["club" => $legacyClub],
                    )
                }}
                <a
                    href="{{ route("clubs.index", ["q" => $legacyClub]) }}"
                    class="text-blue-400 hover:underline"
                    >{{
                        __(
                            "Look for it",
                        )
                    }}</a
                >
                &middot;
                <a
                    href="{{ route("clubs.create", ["name" => $legacyClub]) }}"
                    class="text-blue-400 hover:underline"
                    >{{ __("Make it") }}</a
                >
            </div>
        @endif

        @if ($invitations->isNotEmpty())
            <section class="mb-6">
                <h2 class="mb-2 text-lg font-semibold text-white">
                    {{
                        __(
                            "Invitations",
                        )
                    }}
                </h2>
                <div class="space-y-2 rounded-lg bg-gray-800 p-4">
                    @foreach ($invitations as $club)
                        <div
                            class="flex flex-wrap items-center justify-between gap-2 text-sm"
                        >
                            <a
                                href="{{ route("clubs.show", $club) }}"
                                class="font-semibold text-white hover:underline"
                                >{{ $club->name }}</a
                            >
                            <div class="flex gap-2">
                                <form
                                    method="POST"
                                    action="{{ route("clubs.join", $club) }}"
                                >
                                    @csrf
                                    <button
                                        type="submit"
                                        class="{{ $buttonClass }} bg-green-700 hover:bg-green-600"
                                    >
                                        {{ __("Accept") }}
                                    </button>
                                </form>
                                <form
                                    method="POST"
                                    action="{{ route("clubs.leave", $club) }}"
                                >
                                    @csrf
                                    @method ("DELETE")
                                    <button
                                        type="submit"
                                        class="{{ $buttonClass }} bg-gray-600 hover:bg-gray-500"
                                    >
                                        {{ __("Decline") }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($mine->isNotEmpty() || $requests->isNotEmpty())
            <section class="mb-6">
                <h2 class="mb-2 text-lg font-semibold text-white">
                    {{ __("My clubs") }}
                </h2>
                <div class="space-y-3">
                    @foreach ($mine as $club)
                        @include ("clubs.partials.card", ["club" => $club])
                    @endforeach
                    @foreach ($requests as $club)
                        <div class="rounded-lg bg-gray-800 p-4 text-sm">
                            <a
                                href="{{ route("clubs.show", $club) }}"
                                class="font-semibold text-white hover:underline"
                                >{{ $club->name }}</a
                            >
                            <span
                                class="ml-2 rounded bg-yellow-700 px-2 py-0.5 text-xs text-white"
                                >{{
                                    __(
                                        "Asked to join",
                                    )
                                }}</span
                            >
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <section>
            <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-lg font-semibold text-white">
                    {{
                        __(
                            "All clubs",
                        )
                    }}
                </h2>
                <form
                    method="GET"
                    action="{{ route("clubs.index") }}"
                    class="flex flex-wrap items-center gap-2 text-sm"
                >
                    <input
                        type="search"
                        name="q"
                        value="{{ $search }}"
                        placeholder="{{ __("Name or place") }}"
                        class="rounded border border-gray-600 bg-gray-900 px-2 py-1 text-gray-100"
                    />
                    @if (count($countries) > 1 || $country !== null)
                        <select
                            name="country"
                            onchange="this.form.submit()"
                            class="rounded border border-gray-600 bg-gray-900 px-2 py-1 text-gray-100"
                        >
                            <option value="">
                                {{
                                    __(
                                        "All countries",
                                    )
                                }}
                            </option>
                            @foreach ($countries as $code => $label)
                                <option
                                    value="{{ $code }}"
                                    @selected ($country === $code)
                                >
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    @endif
                    <button
                        type="submit"
                        class="rounded bg-gray-700 px-3 py-1 text-white hover:bg-gray-600"
                    >
                        {{ __("Search") }}
                    </button>
                </form>
            </div>
            @if ($clubs->isEmpty())
                <p class="text-gray-400">
                    {{
                        $search !== "" || $country !== null
                            ? __("No club matches your search.")
                            : __("There are no clubs yet.")
                    }}
                </p>
            @else
                <div class="space-y-3">
                    @foreach ($clubs as $club)
                        @include ("clubs.partials.card", ["club" => $club])
                    @endforeach
                </div>
                <div class="mt-4">{{ $clubs->links() }}</div>
            @endif
        </section>
    </div>
</x-app-layout>
