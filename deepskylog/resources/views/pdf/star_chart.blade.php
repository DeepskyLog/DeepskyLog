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
            margin: 5mm 0 0 0;
            padding: 0;
            line-height: 0;
        }
    </style>
</head>

<body>
    <div class="chart">
        @if (!empty($svg))
            {!! $svg !!}
        @elseif (!empty($svg_data_uri))
            <img src="{{ $svg_data_uri }}" alt="{{ __('Star chart') }}" />
        @endif
    </div>
</body>

</html>