@php
    $sessionName = html_entity_decode($session->name ?? '', ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $ownerSlug = $session->observer?->slug ?: $session->observerid;
    $link = route('session.show', [$ownerSlug, $session->slug ?: $session->id]);
    $pubDate = ($session->enddate ?? $session->begindate ?? now())->toRfc2822String();

    $picture = $session->preview ?? null;
    if ($picture && !str_starts_with($picture, 'http')) {
        $picture = url($picture);
    }

    $descParts = [];
    if ($session->begindate) {
        $dates = $session->begindate->format('Y-m-d');
        if ($session->enddate && $session->enddate->toDateString() !== $session->begindate->toDateString()) {
            $dates .= ' – ' . $session->enddate->format('Y-m-d');
        }
        $descParts[] = '<strong>Date:</strong> ' . e($dates);
    }
    if ($session->observer?->name) {
        $descParts[] = '<strong>Observer:</strong> ' . e($session->observer->name);
    }
    if (!empty($session->location_name)) {
        $descParts[] = '<strong>Location:</strong> ' . e(html_entity_decode($session->location_name, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }
    $comments = \App\Support\Html::purify($session->comments, true);
    if (trim(strip_tags($comments)) !== '') {
        $descParts[] = $comments;
    }
    if ($picture) {
        $descParts[] = '<img src="' . e($picture) . '" alt="' . e($sessionName) . '" style="max-width:400px" />';
    }
    $htmlDesc = implode('<br />', $descParts);
@endphp
<item>
    <title>{{ $sessionName }}</title>
    <link>{{ $link }}</link>
    <guid isPermaLink="true">{{ $link }}</guid>
    <pubDate>{{ $pubDate }}</pubDate>
    <description><![CDATA[{!! $htmlDesc !!}]]></description>
</item>
