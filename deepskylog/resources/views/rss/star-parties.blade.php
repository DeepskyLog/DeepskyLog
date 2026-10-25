<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>

<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>{{ config('app.name', 'DeepskyLog') }} – Starparties</title>
        <link>{{ route('star-parties.index') }}</link>
        <description>The coming public starparties of DeepskyLog observers.</description>
        <language>en</language>
        <lastBuildDate>{{ $feedUpdated->toRfc2822String() }}</lastBuildDate>
        <atom:link href="{{ route('feed.star-parties') }}" rel="self" type="application/rss+xml" />

        @foreach ($parties as $party)
        @php
            $link = route('star-parties.show', $party);
            $description = '<p>' . e($party->dateRange()) . ' – ' . e($party->location_name) . ($party->country ? ' (' . e($party->country) . ')' : '')
                . ($party->isCancelled() ? ' – <strong>Cancelled</strong>' : '') . '</p>'
                . \App\Support\Html::purify($party->displayDescription(), true);
        @endphp
        <item>
            <title>{{ $party->name }}{{ $party->isCancelled() ? ' (cancelled)' : '' }}</title>
            <link>{{ $link }}</link>
            <guid isPermaLink="true">{{ $link }}</guid>
            <pubDate>{{ $party->created_at->toRfc2822String() }}</pubDate>
            <description><![CDATA[{!! $description !!}]]></description>
        </item>
        @endforeach
    </channel>
</rss>
