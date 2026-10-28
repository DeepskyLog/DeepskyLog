<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>

<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:media="http://search.yahoo.com/mrss/">
    <channel>
        {{-- $channel is set for the personal feed of the observers someone follows --}}
        <title>{{ config('app.name', 'DeepskyLog') }} – {{ $channel['title'] ?? 'Latest Observations' }}</title>
        <link>{{ $channel['link'] ?? url('/observations') }}</link>
        <description>{{ $channel['description'] ?? 'The latest observations added to DeepskyLog.' }}</description>
        <language>en</language>
        <lastBuildDate>{{ $feedUpdated->toRfc2822String() }}</lastBuildDate>
        <atom:link href="{{ $channel['self'] ?? url('/feed/observations') }}" rel="self" type="application/rss+xml" />

        @foreach ($observations as $obs)
            @include('rss.partials.observation-item', ['obs' => $obs, 'cards' => $cards])
        @endforeach
    </channel>
</rss>
