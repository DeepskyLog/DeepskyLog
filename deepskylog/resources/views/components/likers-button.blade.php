{{--
    The "Who liked this?" button next to a like count, for logged-in
    observers. The names are fetched from likes.likers when it is clicked
    (the popover script is in layouts/app.blade.php). Hidden while there
    are no likes; the like toggle script shows it when the count rises.
--}}
@props(["kind", "id", "count" => 0])

@auth
    <button
        type="button"
        data-likers-url="{{ route("likes.likers", ["kind" => $kind, "id" => $id]) }}"
        title="{{ __("Who liked this?") }}"
        aria-label="{{ __("Who liked this?") }}"
        aria-haspopup="dialog"
        @if ((int) $count === 0) hidden @endif
        {{ $attributes->merge(["class" => "likers-button inline-flex items-center rounded px-1.5 py-1 text-gray-400 hover:bg-gray-700 hover:text-white align-middle"]) }}
    >
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
    </button>
@endauth
