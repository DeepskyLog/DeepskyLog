@php
    $obs = $sketch['observation'];
    $objectName = html_entity_decode((string) $obs->target_name, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $label = $sketch['sketch_of'] === 'month' ? 'sketch of the month' : 'sketch of the week';
    $link = $obs->detailUrl($observer->slug) ?? route('observer.blog', ['observer' => $observer->slug]);

    $drawing = $obs->drawingUrl();
    if ($drawing && !str_starts_with($drawing, 'http')) {
        $drawing = url($drawing);
    }

    $descParts = ['<strong>' . e(ucfirst($label)) . ':</strong> ' . e($sketch['date']->format('Y-m-d'))];
    if ($obs->observed_on) {
        $descParts[] = '<strong>Observed:</strong> ' . e($obs->observed_on->format('Y-m-d'));
    }
    if ($drawing) {
        $descParts[] = '<img src="' . e($drawing) . '" alt="' . e($objectName) . '" style="max-width:400px" />';
    }
@endphp
<item>
    <title>{{ $objectName }} by {{ $observer->name }} chosen as {{ $label }}</title>
    <link>{{ $link }}</link>
    {{-- The detail page is also the link of the observation's own item --}}
    <guid isPermaLink="false">{{ $link }}#{{ str_replace(' ', '-', $label) }}</guid>
    <pubDate>{{ $sketch['date']->toRfc2822String() }}</pubDate>
    <description><![CDATA[{!! implode('<br />', $descParts) !!}]]></description>
    @if ($drawing)
    <enclosure url="{{ $drawing }}" length="0" type="image/jpeg" />
    @endif
</item>
