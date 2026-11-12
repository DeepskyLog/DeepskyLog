{{--
    The observations of a listing as a compact list, one row per observation
    (App\Services\Observations\ObservationListView). Expects $observations
    (the page, preloaded with ObservationCardData), $showUnread, $readMark and
    $currentSort.
--}}
@php
    $cards = \App\Services\Observations\ObservationCardData::instance();
    $visibilities = \App\Services\Observations\ObservationListView::visibilities($observations);
    $markPerRow = $showUnread && $currentSort === 'id';
@endphp
<div class="overflow-x-auto">
    <table class="min-w-full text-left text-sm text-gray-200" data-observation-list>
        <thead class="border-b border-gray-700 text-xs uppercase text-gray-400">
            <tr>
                @if ($showUnread)
                    <th class="px-2 py-2"><span class="sr-only">{{ __('New') }}</span></th>
                @endif
                <th class="px-2 py-2">{{ __('Date') }}</th>
                <th class="px-2 py-2">{{ __('Object') }}</th>
                <th class="hidden px-2 py-2 md:table-cell">{{ __('Type') }}</th>
                <th class="hidden px-2 py-2 md:table-cell">{{ __('Constellation') }}</th>
                <th class="px-2 py-2">{{ __('Observer') }}</th>
                <th class="hidden px-2 py-2 sm:table-cell">{{ __('Instrument') }}</th>
                <th class="px-2 py-2 text-center" title="{{ __('Visibility') }}">{{ __('Vis.') }}</th>
                <th class="px-2 py-2 text-center"><span class="sr-only">{{ __('Drawing') }}</span></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-800">
            @foreach ($observations as $observation)
                @php
                    $isUnified = $observation instanceof \App\Models\Observation;
                    $observer = $isUnified ? $cards->user($observation->observer_username) : null;
                    $object = $isUnified ? $cards->target($observation->target_name) : null;
                    $instrument = $isUnified ? $cards->instrument($observation->instrument_id) : null;
                    $objectName = (string) ($observation->target_name ?? '');
                    $objectUrl = $objectName !== '' ? route('object.show', $object?->slug ?? \Illuminate\Support\Str::slug($objectName, '-')) : null;
                    $detailUrl = $isUnified ? $observation->detailUrl($observer?->slug) : null;
                    $longType = $object ? $object->long_type() : null;
                    $typeLabel = $longType
                        ? __($longType)
                        : match ($observation->target_type ?? null) {
                            'comet' => __('Comet'),
                            'planet', 'solarsystem' => ($observation->target_table ?? null) === 'asteroids' ? __('Asteroid') : __('Solar system'),
                            'lunar' => __('Moon'),
                            default => '',
                        };
                    $constellation = $object ? $cards->constellationName($object->con) : '';
                    $visibility = $visibilities->get($observation->id);
                    $isUnread = $showUnread && $isUnified && \App\Services\Observations\ReadObservations::isUnread(auth()->user(), $readMark, $observation);
                @endphp
                <tr class="{{ $isUnread ? 'bg-amber-500/10' : '' }} hover:bg-gray-800">
                    @if ($showUnread)
                        <td class="whitespace-nowrap px-2 py-1.5">
                            @if ($isUnread)
                                <span class="rounded bg-amber-500 px-1.5 py-0.5 text-xs font-semibold uppercase text-gray-900">{{ __('New') }}</span>
                                @if ($markPerRow)
                                    {{-- Sorted newest id first: this one and all below it --}}
                                    <form method="POST" action="{{ route('observations.mark-read') }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="up_to" value="{{ $observation->id }}">
                                        <button type="submit" class="ml-1 text-gray-400 hover:text-gray-100"
                                            title="{{ __('Mark this one and the older ones as read') }}"
                                            aria-label="{{ __('Mark this one and the older ones as read') }}">✓</button>
                                    </form>
                                @endif
                            @endif
                        </td>
                    @endif
                    <td class="whitespace-nowrap px-2 py-1.5">
                        @php
                            $date = $observation->observed_on?->translatedFormat('j M Y');
                        @endphp
                        @if ($detailUrl)
                            <a href="{{ $detailUrl }}" class="hover:underline" title="{{ __('More details') }}">{{ $date }}</a>
                        @else
                            {{ $date }}
                        @endif
                    </td>
                    <td class="px-2 py-1.5 font-semibold">
                        @if ($objectUrl)
                            <a href="{{ $objectUrl }}" class="hover:underline">{{ $objectName }}</a>
                        @else
                            {{ $objectName }}
                        @endif
                        <x-observation-class :value="$observation->observer_class ?? null" class="ml-1 align-middle" />
                    </td>
                    <td class="hidden px-2 py-1.5 text-gray-300 md:table-cell">{{ $typeLabel }}</td>
                    <td class="hidden px-2 py-1.5 text-gray-300 md:table-cell">{{ $constellation }}</td>
                    <td class="px-2 py-1.5">
                        @if ($observer)
                            <a href="/observers/{{ $observer->slug }}" class="hover:underline">{{ $observer->name }}</a>
                        @else
                            {{ $observation->observer_username ?? __('Unknown observer') }}
                        @endif
                    </td>
                    <td class="hidden px-2 py-1.5 text-gray-300 sm:table-cell">
                        @if ($instrument)
                            <a href="/instrument/{{ $observer?->slug ?? 'unknown' }}/{{ $instrument->slug }}" class="hover:underline">{{ html_entity_decode($instrument->fullName()) }}</a>
                        @endif
                    </td>
                    <td class="px-2 py-1.5 text-center">
                        @if ($visibility === \App\Models\Observation::VISIBILITY_NOT_SEEN)
                            <span class="font-semibold text-red-400" title="{{ __('observations.visibility.7') }}">{{ __('Not seen') }}</span>
                        @elseif ($visibility)
                            <span title="{{ __('observations.visibility.' . $visibility) }}">{{ $visibility }}</span>
                        @endif
                    </td>
                    <td class="px-2 py-1.5 text-center">
                        @if ($observation->has_drawing ?? false)
                            <a href="{{ $detailUrl ?? '#' }}" title="{{ __('Drawing') }}" aria-label="{{ __('Drawing') }}">✏️</a>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
