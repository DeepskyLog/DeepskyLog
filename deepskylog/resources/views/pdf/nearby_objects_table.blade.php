<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Nearby objects' }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        body {
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #000;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid #444;
            padding: 4px 6px;
            vertical-align: middle;
        }

        th {
            background: #eee;
            font-weight: bold;
            font-size: 10px;
        }

        /* Striped rows in PDF for readability */
        tbody tr:nth-child(odd) {
            background-color: #f7f7f7;
        }

        tbody tr:nth-child(even) {
            background-color: #ffffff;
        }

        .col-name {
            width: 14%;
        }

        .col-astro {
            width: 11%;
        }

        .col-type {
            width: 10%;
        }

        .col-const {
            width: 8%;
        }

        .col-numeric {
            width: 6%;
            text-align: center;
        }

        .col-diameter {
            width: 9%;
            text-align: center;
        }

        .small {
            font-size: 9px;
        }
    </style>
</head>

<body>
    <h2 style="text-align:center; margin-bottom:6px;">{{ $title ?? 'Nearby objects' }}</h2>

    <table>
        <thead>
            <tr>
                <th class="col-name">Name</th>
                <th class="col-astro">Right Ascension</th>
                <th class="col-astro">Declination</th>
                <th class="col-type">Type</th>
                <th class="col-const">Constellation</th>
                <th class="col-numeric">Mag.</th>
                <th class="col-numeric">SB</th>
                <th class="col-diameter">Diameter</th>
                <th class="col-numeric">Atlas page</th>
                <th class="col-numeric">CR</th>
                <th class="col-numeric">Opt. mag.</th>
                <th class="col-numeric">Seen</th>
                <th class="col-numeric">Last seen</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $r)
                <tr>
                    <td class="col-name small">{!! htmlentities($r['name'] ?? '') !!}</td>
                    <td class="col-astro small">{!! htmlentities($r['ra'] ?? '') !!}</td>
                    <td class="col-astro small">{!! htmlentities($r['decl'] ?? '') !!}</td>
                    <td class="col-type small">{!! htmlentities($r['type'] ?? '') !!}</td>
                    <td class="col-const small">{!! htmlentities($r['constellation'] ?? '') !!}</td>
                    <td class="col-numeric small">{!! htmlentities($r['mag'] ?? '') !!}</td>
                    <td class="col-numeric small">{!! htmlentities($r['sb'] ?? '') !!}</td>
                    <td class="col-diameter small">{!! htmlentities($r['diameter'] ?? '') !!}</td>
                    <td class="col-numeric small">{!! htmlentities($r['atlas_page'] ?? '') !!}</td>
                    <td class="col-numeric small">{!! htmlentities($r['cr'] ?? '') !!}</td>
                    <td class="col-numeric small">{!! htmlentities($r['best_mag'] ?? '') !!}</td>
                    <td class="col-numeric small">{!! htmlentities($r['seen'] ?? '') !!}</td>
                    <td class="col-numeric small">{!! htmlentities($r['last_seen'] ?? '') !!}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
