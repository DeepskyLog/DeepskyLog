<!doctype html>
<html>

<head>
    <meta charset="utf-8" />
    <title>{{ $title ?? 'DSL Atlas' }}</title>
    <style>
        @page {
            size: {{ $pageWidthMm ?? 287 }}mm {{ $pageHeightMm ?? 210 }}mm;
            margin: 0;
        }

        html, body {
            width: 100%;
            margin: 0;
            padding: 0;
            line-height: 0;
        }

        .page {
            width: 100%;
            overflow: hidden;
            page-break-after: always;
        }

        .page:last-child {
            page-break-after: auto;
        }

        .chart {
            position: relative;
            width: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }

        /*
         * Scale the chart to the page width, but never past the page height:
         * the portrait chart canvas is taller than A-series paper, and without
         * the cap it overflows onto a second page and leaves the first blank.
         */
        .chart img {
            width: 100%;
            height: auto;
            max-width: 100%;
            max-height: {{ $pageHeightMm ?? 210 }}mm;
            display: block;
        }
        .chart svg {
            width: 100%;
            height: auto;
            max-width: 100%;
            max-height: {{ $pageHeightMm ?? 210 }}mm;
            display: block;
        }
    </style>
</head>

<body>
    @foreach($charts as $chart)
        <div class="page">
            @include('pdf.star_chart_fragment', $chart)
        </div>
    @endforeach
</body>

</html>
