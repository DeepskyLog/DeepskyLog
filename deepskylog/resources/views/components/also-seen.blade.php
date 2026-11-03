@props (["objectName"])

{{-- Observers who saw this also saw…: the objects the same observers saw (object page) --}}
@php
    $alsoSeen = [];
    try {
        $alsoSeen = \App\Services\AlsoSeenObjects::forObject($objectName);
    } catch (\Throwable $_) {
        $alsoSeen = [];
    }
    $viewer = auth()->user();
    $seenByViewer = $viewer && !empty($alsoSeen)
        ? \App\Services\AlsoSeenObjects::seenAmong($viewer, array_column($alsoSeen, "name"))
        : [];
@endphp

@if (!empty($alsoSeen))
    <div class="mt-6">
        <div class="flex flex-wrap items-baseline gap-3">
            <h2 class="text-xl font-semibold text-white">
                {{ __("Observers who saw this also saw…") }}
            </h2>
            @auth
                <a
                    href="{{ route("observing-list.next-for-you") }}"
                    class="text-sm text-indigo-400 hover:text-indigo-300"
                >
                    {{ __("Next for you") }}
                </a>
            @endauth
        </div>
        <p class="mt-1 text-sm text-gray-400">
            {{
                __(
                    "The objects seen most by the observers who saw this one, compared with how many observers saw each.",
                )
            }}
        </p>
        <ul class="mt-2 grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($alsoSeen as $object)
                <li class="rounded bg-gray-800 p-3">
                    <a
                        href="{{ route("object.show", $object["slug"]) }}"
                        class="font-semibold text-white hover:text-indigo-300"
                        >{{ $object["name"] }}</a
                    >
                    @if (isset($seenByViewer[mb_strtolower($object["name"])]))
                        <span
                            class="ml-1 text-xs text-green-400"
                            title="{{ __("You have seen this object") }}"
                            >✓</span
                        >
                    @endif
                    <div class="text-xs text-gray-400">
                        {{ __($object["type"]) }}@if ($object["constellation"])
                            · {{ __($object["constellation"]) }}
                        @endif
                        @if ($object["magnitude"] !== null)
                            · {{ number_format($object["magnitude"], 1) }}
                        @endif
                    </div>
                    <div class="text-xs text-gray-500">
                        {{
                            trans_choice(
                                "{1} One observer saw both|[2,*] :count observers saw both",
                                $object["observers"],
                                ["count" => $object["observers"]],
                            )
                        }}
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
@endif
