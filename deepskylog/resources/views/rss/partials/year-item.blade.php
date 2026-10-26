@php
    $summary = (new \App\Services\ObserverYear($observer, $year))->summary();
    $totals = $summary['totals'];
    $link = \App\Services\ObserverYear::url($observer, $year);

    $descParts = [
        '<strong>Observations:</strong> ' . e($totals['observations']),
        '<strong>Nights:</strong> ' . e($totals['nights']),
        '<strong>Different objects:</strong> ' . e($totals['objects']),
        '<strong>Objects seen for the first time:</strong> ' . e($summary['new_objects']['count']),
    ];
    if ($totals['drawings'] > 0) {
        $descParts[] = '<strong>Drawings:</strong> ' . e($totals['drawings']);
    }
@endphp
<item>
    <title>The year {{ $year }} of {{ $observer->name }}</title>
    <link>{{ $link }}</link>
    <guid isPermaLink="false">{{ $link }}#year-{{ $year }}</guid>
    <pubDate>{{ \Carbon\Carbon::create($year, 12, 31)->endOfDay()->toRfc2822String() }}</pubDate>
    <description><![CDATA[{!! implode('<br />', $descParts) !!}]]></description>
</item>
