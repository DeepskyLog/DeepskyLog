<!doctype html>
<html>

<head>
    <meta charset="utf-8" />
    <title>{{ __('Object sets') }}</title>
    <style>
        @page {
            size: {{ $pageWidthMm }}mm {{ $pageHeightMm }}mm;
            margin: 0;
        }

        html, body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, sans-serif;
            color: #111827;
        }

        .page {
            width: 100%;
            overflow: hidden;
            page-break-after: always;
        }

        .page:last-child {
            page-break-after: auto;
        }

        .sheet {
            padding: 10mm 15mm 8mm 15mm;
            font-size: 8.5pt;
            line-height: 1.3;
        }

        h1 {
            font-size: 15pt;
            text-align: center;
            margin: 0 0 1mm 0;
            padding-bottom: 1.5mm;
            border-bottom: 0.6pt solid #111827;
        }

        h2 {
            font-size: 10pt;
            margin: 4mm 0 1.5mm 0;
            padding-bottom: 0.8mm;
            border-bottom: 0.4pt solid #6b7280;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        td, th {
            vertical-align: top;
            padding: 0.6mm 1.5mm 0.6mm 0;
            text-align: left;
        }

        .facts td {
            width: 33%;
        }

        .label {
            color: #4b5563;
        }

        .year th, .year td {
            text-align: center;
            font-size: 7.5pt;
            padding: 0.5mm 0.3mm;
            border-bottom: 0.3pt solid #d1d5db;
        }

        .year th.rowhead, .year td.rowhead {
            text-align: left;
            width: 22mm;
            color: #4b5563;
        }

        .photos td {
            width: 50%;
            text-align: center;
            padding: 0 3mm;
        }

        .photo-frame {
            width: 118mm;
            height: 118mm;
            margin: 0 auto;
            border: 0.6pt solid #111827;
        }

        /* A hair smaller than the frame, or it covers the frame's top and left edge. */
        .photo {
            width: 117mm;
            height: 117mm;
            margin: 0.5mm;
            display: block;
        }

        .photo-missing {
            width: 118mm;
            height: 118mm;
            margin: 0 auto;
            border: 0.6pt solid #111827;
            line-height: 118mm;
            color: #6b7280;
            text-align: center;
        }

        .caption {
            font-size: 8pt;
            margin-top: 1mm;
        }

        .credit {
            font-size: 7pt;
            color: #6b7280;
            margin-top: 1.5mm;
        }

        .index th, .index td {
            font-size: 7.5pt;
            padding: 0.3mm 2mm 0.3mm 0;
            border-bottom: 0.3pt solid #e5e7eb;
        }

        .index th {
            color: #4b5563;
            border-bottom: 0.4pt solid #6b7280;
        }

        .target {
            font-weight: bold;
        }

        .muted {
            color: #6b7280;
        }

        /* Charts: scale to the page width, but never past its height. */
        .chart {
            position: relative;
            width: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
            line-height: 0;
        }

        .chart img {
            width: 100%;
            height: auto;
            max-width: 100%;
            max-height: {{ $pageHeightMm }}mm;
            display: block;
        }
    </style>
</head>

<body>
    @foreach ($pages as $page)
        <div class="page">
            @if ($page['kind'] === 'chart')
                @include('pdf.star_chart_fragment', ['title' => $page['title'], 'svg_path' => $page['svg_path']])
            @elseif ($page['kind'] === 'data')
                @include('pdf.object_sets.data_page', $page)
            @elseif ($page['kind'] === 'photos')
                @include('pdf.object_sets.photos_page', $page)
            @elseif ($page['kind'] === 'index')
                @include('pdf.object_sets.index_page', $page)
            @endif
        </div>
    @endforeach
</body>

</html>
