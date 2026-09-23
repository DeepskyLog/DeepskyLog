<div class="sheet">
    <h1>{{ __('Index for :object', ['object' => $object['name']]) }}</h1>

    @foreach ($charts as $chart)
        <h2>{{ __('Chart of :fov arcminutes (objects to magnitude :dsos, stars to magnitude :stars)', [
            'fov' => rtrim(rtrim(number_format($chart['fov'], 1), '0'), '.'),
            'dsos' => rtrim(rtrim(number_format($chart['dso_mag'], 1), '0'), '.'),
            'stars' => rtrim(rtrim(number_format($chart['star_mag'], 1), '0'), '.'),
        ]) }}</h2>

        @if ($chart['objects'] === [])
            <div class="muted">{{ __('No objects on this chart.') }}</div>
        @else
            <table class="index">
                <tr>
                    <th style="width: 30%;">{{ __('Object') }}</th>
                    <th style="width: 40%;">{{ __('Type') }}</th>
                    <th style="width: 10%;">{{ __('Mag') }}</th>
                    <th style="width: 20%;">{{ __('Size') }}</th>
                </tr>
                @foreach ($chart['objects'] as $row)
                    <tr>
                        <td class="{{ $row['is_target'] ? 'target' : '' }}">{{ $row['name'] }}</td>
                        <td>{{ $row['type'] }}</td>
                        <td>{{ $row['mag'] }}</td>
                        <td>{{ $row['size'] }}</td>
                    </tr>
                @endforeach
            </table>
        @endif
    @endforeach
</div>
