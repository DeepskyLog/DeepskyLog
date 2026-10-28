<div class="chart">
    @php
        $inlineSvg = null;
        $src = null;

        if (!empty($svg)) {
            // Raw SVG provided: render inline to avoid base64 copying in memory.
            $inlineSvg = $svg;
        } elseif (!empty($image_data_uri)) {
            $src = $image_data_uri;
        } elseif (!empty($svg_data_uri)) {
            $src = $svg_data_uri;
        } elseif (!empty($svg_path) && is_file($svg_path)) {
            $src = 'file://' . realpath($svg_path);
        }
    @endphp

    @if (!empty($inlineSvg))
        {{-- Render raw inline SVG so Dompdf can natively render vector content. --}}
        {!! $inlineSvg !!}
    @elseif (!empty($src))
        <img src="{{ $src }}" alt="{{ e($title ?? __('Star chart')) }}" />
    @elseif (!empty($image_path) && is_file($image_path))
        <img src="{{ 'file://' . realpath($image_path) }}" alt="{{ e($title ?? __('Star chart')) }}" />
    @endif
</div>
