<!doctype html>
<html>

<head>
    <meta charset="utf-8" />
    <title>{{ $title ?? 'Nearby object names' }}</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 12px;
            color: #111;
        }

        h1 {
            font-size: 16px;
            margin-bottom: 12px;
        }

        ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        li {
            display: block;
            padding: 6px 8px;
        }

        /* Alternate row backgrounds for better readability in PDF */
        li:nth-child(odd) {
            background-color: #ffffff;
        }

        li:nth-child(even) {
            background-color: #f3f4f6;
        }

        /* Add a thin border between rows for crisp separation in PDF renderers */
        li+li {
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>

<body>
    <h1>{{ $title ?? 'Nearby object names' }}</h1>
    <ul>
        @foreach ($names ?? [] as $n)
            <li>{{ $n }}</li>
        @endforeach
    </ul>
</body>

</html>
