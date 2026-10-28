<!doctype html>
<html>
<head>
    <meta charset="utf-8" />
    <title>{{ __('Field views of :object', ['object' => $result['object']->name]) }}</title>
    <style>
        @page { margin: 12mm; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 9pt; color: #111827; }
        h1 { font-size: 15pt; margin: 0 0 2mm 0; }
        .meta { color: #4b5563; margin-bottom: 4mm; }
        table.views { width: 100%; border-collapse: collapse; table-layout: fixed; }
        table.views td { vertical-align: top; padding: 0 2mm; }
        .title { font-size: 11pt; font-weight: bold; }
        .sub { color: #4b5563; min-height: 9mm; }
        .side .title { font-size: 13pt; margin-bottom: 2mm; }
        .side .sub { font-size: 10pt; line-height: 1.5; }
        img.pair { width: 130mm; height: 130mm; }
        img.large { width: 172mm; height: 172mm; }
        .message { margin-top: 10mm; color: #4b5563; }
        .warnings { margin-top: 4mm; color: #92400e; }
        table.telescope { width: 273mm; border-collapse: collapse; }
        table.telescope td { vertical-align: top; padding: 0; }
        table.telescope td.image { width: 176mm; }
        table.telescope td.side { width: 97mm; }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>
    @php
        $telescope = $panels['telescope'] ?? null;
        $others = array_diff_key($panels, ['telescope' => true]);
    @endphp
    @include('pdf.partials.field_view_header')

    {{-- The naked eye and finder views side by side --}}
    <table class="views">
        <tr>
            @foreach ($others as $panel)
                <td>
                    <div class="title">{{ $panel['title'] }}</div>
                    <div class="sub">@include('pdf.partials.field_view_details', ['panel' => $panel])</div>
                    @isset($panel['image'])
                        <img class="pair" src="{{ $panel['image'] }}" alt="{{ $panel['title'] }}" />
                    @else
                        <div class="message">{{ $panel['message'] }}</div>
                    @endisset
                </td>
            @endforeach
        </tr>
    </table>

    {{-- The telescope view on a page of its own --}}
    @if ($telescope)
        <div class="page-break"></div>
        @isset($telescope['image'])
            <table class="telescope">
                <tr>
                    <td class="image">
                        <img class="large" src="{{ $telescope['image'] }}" alt="{{ $telescope['title'] }}" />
                    </td>
                    <td class="side">
                        @include('pdf.partials.field_view_header')
                        <div class="title">{{ $telescope['title'] }}</div>
                        <div class="sub">@include('pdf.partials.field_view_details', ['panel' => $telescope])</div>
                        @include('pdf.partials.field_view_warnings')
                    </td>
                </tr>
            </table>
        @else
            <div class="title">{{ $telescope['title'] }}</div>
            <div class="sub">@include('pdf.partials.field_view_details', ['panel' => $telescope])</div>
            <div class="message">{{ $telescope['message'] }}</div>
            @include('pdf.partials.field_view_warnings')
        @endisset
    @else
        @include('pdf.partials.field_view_warnings')
    @endif
</body>
</html>
