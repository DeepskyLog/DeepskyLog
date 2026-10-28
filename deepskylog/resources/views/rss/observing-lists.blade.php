<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>

<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>{{ config('app.name', 'DeepskyLog') }} – New Public Observing Lists</title>
        <link>{{ url('/observing-lists/discover') }}</link>
        <description>New public observing lists added to DeepskyLog.</description>
        <language>en</language>
        <lastBuildDate>{{ $feedUpdated->toRfc2822String() }}</lastBuildDate>
        <atom:link href="{{ url('/feed/observing-lists') }}" rel="self" type="application/rss+xml" />

        @foreach ($lists as $list)
            @include('rss.partials.observing-list-item', ['list' => $list])
        @endforeach
    </channel>
</rss>
