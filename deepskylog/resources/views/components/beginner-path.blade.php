@props (["path"])

{{-- The beginner path on the home page (BeginnerPath::forHomePage()): the next object of a running path, or an invitation --}}
<x-home-section :title="__('Beginner path')" {{ $attributes }}>
    <div class="space-y-2 px-2 text-sm text-gray-300">
        @if ($path["goal"])
            <x-observing-goal :goal="$path['goal']" />
            @if ($next = $path["next"])
                <p>
                    {{ __("Next:") }}
                    @if ($next["slug"])
                        <a
                            href="{{ route("object.show", $next["slug"]) }}"
                            class="font-semibold text-white hover:underline"
                            >{{ $next["name"] }}</a
                        >
                    @else
                        <span
                            class="font-semibold text-white"
                            >{{ $next["name"] }}</span
                        >
                    @endif
                    @if ($next["details"]["type"])
                        <span class="text-gray-400"
                            >&middot; {{ $next["details"]["type"] }}{{
                                $next["details"]["constellation"]
                                    ? " · " . $next["details"]["constellation"]
                                    : ""
                            }}</span
                        >
                    @endif
                    @if ($next["now"])
                        <span
                            class="ml-1 rounded bg-blue-900 px-2 py-0.5 text-xs text-blue-200"
                            >{{
                                __(
                                    "Well placed this month",
                                )
                            }}</span
                        >
                    @endif
                </p>
            @endif
        @else
            <p>{{ __("New to observing? The beginner path picks about 30 of the finest objects of the sky for your telescope and location, and guides you to them one by one.") }}</p>
        @endif
    </div>
    <x-slot name="footer">
        <a
            href="{{ route("beginner-path") }}"
            class="text-blue-400 hover:underline"
            >{{
                $path["goal"]
                    ? __("My beginner path")
                    : __("Start my beginner path")
            }} &rarr;</a
        >
    </x-slot>
</x-home-section>
