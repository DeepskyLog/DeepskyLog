<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>

<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>{{ config('app.name', 'DeepskyLog') }} – News</title>
        <link>{{ route('news.index') }}</link>
        <description>News of DeepskyLog and the coming astronomical events.</description>
        <language>en</language>
        <lastBuildDate>{{ $feedUpdated->toRfc2822String() }}</lastBuildDate>
        <atom:link href="{{ route('feed.news') }}" rel="self" type="application/rss+xml" />

        @foreach ($items as $item)
        @php
            $link = route('news.show', $item);
            $description = $item->isAutomatic() ? e($item->excerpt(1000)) : \App\Support\Html::purify($item->displayBody(), true);
        @endphp
        <item>
            <title>{{ $item->displayTitle() }}</title>
            <link>{{ $link }}</link>
            <guid isPermaLink="true">{{ $link }}</guid>
            <pubDate>{{ $item->published_at->toRfc2822String() }}</pubDate>
            <description><![CDATA[{!! $description !!}]]></description>
        </item>
        @endforeach
    </channel>
</rss>
