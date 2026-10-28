<div class="sheet">
    <h1>{{ __('Images for :object', ['object' => $object['name']]) }}</h1>

    <table class="photos" style="margin-top: 4mm;">
        <tr>
            @foreach ($photos as $photo)
                <td>
                    @if (!empty($photo['path']) && is_file($photo['path']))
                        <div class="photo-frame"><img class="photo" src="{{ 'file://' . realpath($photo['path']) }}" alt="{{ __('DSS image') }}" /></div>
                    @else
                        <div class="photo-missing">{{ __('The DSS image could not be downloaded.') }}</div>
                    @endif
                    <div class="caption">{{ __('DSS image') }} - {{ __(':size x :size arcminutes', ['size' => $photo['size']]) }}</div>
                </td>
            @endforeach
        </tr>
    </table>

    <div class="credit">{{ __('(c) STScI Digitized Sky Survey. North is up, east is to the left; shown as a negative.') }}</div>

    @if ($with_summary)
        <table class="facts" style="margin-top: 3mm;">
            <tr>
                <td colspan="3"><span class="label">{{ __('Alternative names') }}:</span> {{ $object['alt_names'] === [] ? '-' : implode(', ', $object['alt_names']) }}</td>
            </tr>
            <tr>
                <td><span class="label">{{ __('Type') }}:</span> {{ $object['type'] }}</td>
                <td><span class="label">{{ __('Magnitude') }}:</span> {{ $object['mag'] }}</td>
                <td><span class="label">{{ __('Right ascension') }}:</span> {{ $object['ra'] }}</td>
            </tr>
            <tr>
                <td><span class="label">{{ __('Constellation') }}:</span> {{ $object['constellation'] }}</td>
                <td><span class="label">{{ __('Surface brightness') }}:</span> {{ $object['subr'] }}</td>
                <td><span class="label">{{ __('Declination') }}:</span> {{ $object['decl'] }}</td>
            </tr>
            <tr>
                <td><span class="label">{{ __('Size') }}:</span> {{ $object['size'] }}</td>
                <td><span class="label">{{ __('Position angle') }}:</span> {{ $object['pa'] }}</td>
                <td></td>
            </tr>
        </table>
    @endif
</div>
