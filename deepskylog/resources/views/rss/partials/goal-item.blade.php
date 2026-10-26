@php
    $progress = $goal->progress();
    $title = html_entity_decode($goal->title(), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $link = route('observer.blog', ['observer' => $observer->slug]);

    $descParts = [
        '<strong>Goal:</strong> ' . e($title),
        '<strong>Period:</strong> ' . e($goal->starts_on->format('Y-m-d')) . ' – ' . e($goal->ends_on->format('Y-m-d')),
        '<strong>Objects:</strong> ' . e(min($progress['count'], $progress['target'])) . ' / ' . e($progress['target']),
    ];
@endphp
<item>
    <title>{{ $observer->name }} reached the observing goal "{{ $title }}"</title>
    <link>{{ $link }}</link>
    <guid isPermaLink="false">{{ $link }}#goal-{{ $goal->id }}</guid>
    <pubDate>{{ $progress['reached_on']->toRfc2822String() }}</pubDate>
    <description><![CDATA[{!! implode('<br />', $descParts) !!}]]></description>
</item>
