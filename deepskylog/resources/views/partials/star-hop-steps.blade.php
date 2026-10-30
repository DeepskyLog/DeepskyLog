{{-- The steps of a star-hop route (FieldViewBuilder::starHop()), on the
     field views page, at the eyepiece and in the PDF. --}}
<ol class="{{ $class ?? '' }}" style="list-style: decimal; padding-left: 1.5rem; margin: 0;">
    @foreach ($starHop['steps'] as $step)
        @php
            $magnitude = $step['magnitude'] !== null ? number_format($step['magnitude'], 1) : null;
            $clock = $step['clock'] ? ' '.__('In the chart: :hour o\'clock.', ['hour' => $step['clock']]) : '';
        @endphp
        <li>
            @if ($step['kind'] === 'start')
                {{ __('Start at :star (magnitude :mag), seen with the naked eye.', ['star' => $step['label'] ?? __('the star marked 1'), 'mag' => $magnitude]) }}
            @elseif ($step['kind'] === 'star')
                @if ($step['label'] !== null)
                    {{ __('Hop :distance° :direction to :star (magnitude :mag).', ['distance' => number_format($step['distance'], 1), 'direction' => __($step['direction']), 'star' => $step['label'], 'mag' => $magnitude]) }}{{ $clock }}
                @else
                    {{ __('Hop :distance° :direction to a star of magnitude :mag.', ['distance' => number_format($step['distance'], 1), 'direction' => __($step['direction']), 'mag' => $magnitude]) }}{{ $clock }}
                @endif
            @else
                {{ __('Hop :distance° :direction to :object.', ['distance' => number_format($step['distance'], 1), 'direction' => __($step['direction']), 'object' => $object]) }}{{ $clock }}
            @endif
        </li>
    @endforeach
</ol>
