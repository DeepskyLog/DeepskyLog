@php $e = $ephemerides ?? null; @endphp

<tbody wire:key="object-ephemerides-{{ $objectId ?? 'none' }}">
	@if (!$e)
		<tr>
			<td class="pr-4 font-medium">{{ __('Rise / Transit / Set') }}</td>
			<td class="text-sm text-gray-500">{{ __('Ephemerides not available for this object or location.') }}</td>
		</tr>
		<tr>
			<td class="pr-4 font-medium">{{ __('Best time') }}</td>
			<td class="text-sm text-gray-500">—</td>
		</tr>
		<tr>
			<td class="pr-4 font-medium">{{ __('Maximum altitude') }}</td>
			<td class="text-sm text-gray-500">—</td>
		</tr>
	@else
		@php
			$r = $e['rising'] ?? null;
			$t = $e['transit'] ?? null;
			$s = $e['setting'] ?? null;
			$showR = $r ?: '—';
			$showT = $t ?: '—';
			$showS = $s ?: '—';
			$max = $e['max_height_at_night'] ?? ($e['max_height'] ?? null);
			$rTitle = '';
			$sTitle = '';
			if (is_null($r) && is_null($s)) {
				if (!is_null($max)) {
					if ((float) $max < 0.0) {
						$rTitle = $sTitle = __('Never rises at your location on this date');
					} else {
						$rTitle = $sTitle = __('Circumpolar — does not set at your location on this date');
					}
				} else {
					$rTitle = $sTitle = __('No rise/set data');
				}
			} else {
				if (is_null($r)) $rTitle = __('Does not rise at your location on this date');
				if (is_null($s)) $sTitle = __('Does not set at your location on this date');
			}
		@endphp
		<tr id="ephem-rts-row-live">
			<td class="pr-4 font-medium">{{ __('Rise / Transit / Set') }}</td>
			<td id="ephem-rts-cell">
				<span class="font-mono" @if($rTitle) title="{{ $rTitle }}" @endif>{{ $showR }}</span>
				<span class="text-gray-400 px-2">/</span>
				<span class="font-mono">{{ $showT }}</span>
				<span class="text-gray-400 px-2">/</span>
				<span class="font-mono" @if($sTitle) title="{{ $sTitle }}" @endif>{{ $showS }}</span>
			</td>
		</tr>

		<tr id="ephem-best-row-live">
			<td class="pr-4 font-medium">{{ __('Best time') }}</td>
			<td id="ephem-best-cell">{{ $e['best_time'] ?? '—' }}</td>
		</tr>

		<tr id="ephem-max-row-live">
			<td class="pr-4 font-medium">{{ __('Maximum altitude') }}</td>
			<td id="ephem-max-cell">
				@if(isset($e['max_height_at_night']) && $e['max_height_at_night'] !== null)
					{{ $e['max_height_at_night'] }}°
				<!-- @elseif(isset($e['max_height']) && $e['max_height'] !== null)
					{{ $e['max_height'] }}° -->
				@else
					—
				@endif
			</td>
		</tr>

		@if(!empty($e['altitude_graph']))
			<tr>
				<td colspan="2" class="pt-3">{!! $e['altitude_graph'] !!}</td>
			</tr>
			@if(!empty($e['year_graph']))
				<tr>
					<td colspan="2" class="pt-2">{!! $e['year_graph'] !!}</td>
				</tr>
			@endif
		@endif
	@endif
</tbody>

