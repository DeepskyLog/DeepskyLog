<!doctype html>
<html>

<head>
    <meta charset="utf-8" />
    <title>{{ $title }}</title>
    <style>
        @page {
            margin: 0;
        }

        body {
            margin: 0;
            padding: 0;
            line-height: 0;
        }

        .chart {
            width: 287mm;
            margin: 5mm auto 0;
            padding: 0;
            page-break-inside: avoid;
        }

        .chart img {
            width: 100%;
            height: auto;
            display: block;
            page-break-inside: avoid;
            page-break-before: avoid;
            page-break-after: avoid;
        }
    </style>
</head>

<body>
    <div class="chart">
        <img src="{{ $svg_data_uri }}" alt="{{ __('Star chart') }}" />
    </div>
</body>

</html>