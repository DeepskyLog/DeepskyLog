<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>

<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:media="http://search.yahoo.com/mrss/">
    <channel>
        <title>{{ config('app.name', 'DeepskyLog') }} – {{ $observer->name }}</title>
        <link>{{ route('observer.blog', ['observer' => $observer->slug]) }}</link>
        <description>The sessions, observations, sketches of the week and month and public observing lists of {{ $observer->name }} on DeepskyLog.</description>
        <language>en</language>
        <lastBuildDate>{{ $feedUpdated->toRfc2822String() }}</lastBuildDate>
        <atom:link href="{{ route('feed.observer', ['observer' => $observer->slug]) }}" rel="self" type="application/rss+xml" />

        @foreach ($items as [$kind, $item])
            @if ($kind === 'sketch')
                @include('rss.partials.sketch-item', ['sketch' => $item, 'observer' => $observer])
            @elseif ($kind === 'session')
                @include('rss.partials.session-item', ['session' => $item])
            @elseif ($kind === 'observation')
                @include('rss.partials.observation-item', ['obs' => $item, 'cards' => $cards])
            @else
                @include('rss.partials.observing-list-item', ['list' => $item])
            @endif
        @endforeach
    </channel>
</rss>
