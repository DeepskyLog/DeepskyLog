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
        img { width: 85mm; height: 85mm; }
        .message { margin-top: 10mm; color: #4b5563; }
        .warnings { margin-top: 4mm; color: #92400e; }
    </style>
</head>
<body>
    <h1>{{ __('Field views of :object', ['object' => $result['object']->name]) }}</h1>
    <div class="meta">
        {{ $result['moment']->format('Y-m-d H:i') }} ({{ $result['timezone'] }})
        @if ($result['location']) · {{ html_entity_decode($result['location']->name) }} @endif
        · {{ __('Limiting magnitude of the sky: :nelm', ['nelm' => number_format($result['nelm'], 1)]) }}
    </div>
    <table class="views">
        <tr>
            @foreach ($panels as $panel)
                <td>
                    <div class="title">{{ $panel['title'] }}</div>
                    <div class="sub">
                        @if (! empty($panel['subtitle'])){{ $panel['subtitle'] }}<br />@endif
                        @isset($panel['fov'])
                            {{ __('Field of view: :fov', ['fov' => $panel['fov']]) }} · {{ __('Limiting magnitude: :mag', ['mag' => number_format($panel['limit'], 1)]) }}
                        @endisset
                    </div>
                    @isset($panel['image'])
                        <img src="{{ $panel['image'] }}" alt="{{ $panel['title'] }}" />
                    @else
                        <div class="message">{{ $panel['message'] }}</div>
                    @endisset
                </td>
            @endforeach
        </tr>
    </table>
    @if ($result['warnings'])
        <div class="warnings">
            @foreach ($result['warnings'] as $warning)
                <div>{{ $warning }}</div>
            @endforeach
        </div>
    @endif
</body>
</html>
