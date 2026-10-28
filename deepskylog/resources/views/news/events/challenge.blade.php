{{-- An automatic news item: the announcement of an observing challenge --}}
@php
    $challenge = \App\Models\ObservingChallenge::find($data["challenge_id"] ?? null);
@endphp
@if ($challenge)
    @if ($challenge->description)
        <div class="prose prose-invert max-w-none text-gray-300">
            {!! \App\Support\Html::purify($challenge->description, true) !!}
        </div>
    @endif
    <p>{{ \App\Services\News\EventTexts::summary($item, $location) }}</p>
    <p>
        <a
            href="{{ route("challenges.show", $challenge) }}"
            class="text-blue-400 hover:underline"
            >{{ __("To the challenge and its leaderboard") }} &rarr;</a
        >
    </p>
@endif
