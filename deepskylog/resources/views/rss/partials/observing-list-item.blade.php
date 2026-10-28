@php
    $link      = route('observing-list.show', $list->slug);
    $listName  = html_entity_decode($list->name ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $ownerName = $list->owner?->name ?? $list->owner?->username ?? '';
    $pubDate   = $list->created_at ? $list->created_at->toRfc2822String() : now()->toRfc2822String();
    $itemCount = $list->items_count ?? $list->items->count();

    // Object names (first 30, then indicate if more)
    $objectNames = $list->items->pluck('object_name')->filter()->values();
    $displayNames = $objectNames->take(30);
    $remaining    = $objectNames->count() - $displayNames->count();

    // Build HTML description
    $descParts = [];
    if ($ownerName) {
        $descParts[] = '<strong>Observer:</strong> ' . e($ownerName);
    }
    if ($list->created_at) {
        $descParts[] = '<strong>Created:</strong> ' . e($list->created_at->format('Y-m-d'));
    }
    $descParts[] = '<strong>Objects:</strong> ' . $itemCount;
    $plainDesc = strip_tags((string) ($list->description ?? ''));
    if ($plainDesc) {
        $descParts[] = e($plainDesc);
    }
    if ($displayNames->isNotEmpty()) {
        $objectStr = $displayNames->map(fn($n) => e($n))->implode(', ');
        if ($remaining > 0) {
            $objectStr .= ' <em>… and ' . $remaining . ' more</em>';
        }
        $descParts[] = '<strong>Object list:</strong> ' . $objectStr;
    }
    $htmlDesc = implode('<br />', $descParts);
@endphp
<item>
    <title>{{ $listName }}@if($ownerName) – {{ $ownerName }}@endif ({{ $itemCount }} objects)</title>
    <link>{{ $link }}</link>
    <guid isPermaLink="true">{{ $link }}</guid>
    <pubDate>{{ $pubDate }}</pubDate>
    @if ($htmlDesc)
    <description><![CDATA[{!! $htmlDesc !!}]]></description>
    @endif
</item>
