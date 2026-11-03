{{-- A club in the list of /clubs: name, place, members and the start of its description --}}
<div class="rounded-lg bg-gray-800 p-4">
    <div class="flex flex-wrap items-baseline justify-between gap-x-3">
        <h3 class="text-lg font-semibold text-white">
            <a
                href="{{ route("clubs.show", $club) }}"
                class="hover:underline"
                >{{
                    html_entity_decode(
                        $club->name,
                        ENT_QUOTES | ENT_HTML5,
                        "UTF-8",
                    )
                }}</a
            >
        </h3>
        <span class="text-sm text-gray-400">
            {{
                trans_choice(
                    ":count member|:count members",
                    $club->members_count,
                    ["count" => $club->members_count],
                )
            }}
        </span>
    </div>
    @if ($club->placeLabel() !== "")
        <p class="text-sm text-gray-400">{{ $club->placeLabel() }}</p>
    @endif
    @if ($club->excerpt() !== "")
        <p class="mt-1 text-sm text-gray-300">{{ $club->excerpt() }}</p>
    @endif
</div>
