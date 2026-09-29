@php
    use App\Models\Observation;
    use App\Models\Location;
    use App\Models\Filter as FilterModel;
    use App\Models\Lens;
    use Illuminate\Support\Facades\Storage;

    $obs = Observation::with(['instrument', 'eyepiece', 'observerUser'])->find($id);
    if (!$obs) {
        return;
    }

    $instrument = $obs->instrument;
    $eyepiece   = $obs->eyepiece;
    $observer   = $obs->observerUser;

    $filter = $obs->filter_id ? FilterModel::find($obs->filter_id) : null;
    $lens   = $obs->lens_id   ? Lens::find($obs->lens_id) : null;
    $location = $obs->location_id ? Location::find($obs->location_id) : null;

    // Translation
    $tr = null;
    if (auth()->check() && auth()->user()->translate) {
        $tr = new \Stichoza\GoogleTranslate\GoogleTranslate(auth()->user()->language);
    }

    // Drawing URL
    $drawingUrl = null;
    if ($obs->has_drawing) {
        if (!empty($obs->drawing_filename)) {
            if (Storage::disk('public')->exists($obs->drawing_filename)) {
                $drawingUrl = Storage::disk('public')->url($obs->drawing_filename);
            } else {
                $drawingUrl = '/images/' . $obs->drawing_filename;
            }
        } else {
            if ($obs->legacy_source === 'comet') {
                $drawingUrl = '/images/cometdrawings/' . $obs->legacy_id . '.jpg';
            } else {
                $drawingUrl = '/images/drawings/' . $obs->legacy_id . '.jpg';
            }
        }
    }

    // Magnification
    $magnification = null;
    if ($instrument && $eyepiece) {
        $magnification = $instrument->magnification($eyepiece, $lens);
    }

    // Observation detail route
    $observerSlug   = $observer?->slug ?? $obs->observer_username;
    $detailUrl      = $obs->detailUrl($observerSlug);
@endphp

<div class="bg-gray-850 border border-gray-700 rounded-lg p-4 m-2">
    <div class="flex flex-col lg:flex-row gap-6">

        {{-- Detail info grid --}}
        <div class="flex-1 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-x-6 gap-y-3 text-sm">

            {{-- Location --}}
            @if($location)
                <div>
                    <div class="text-xs uppercase tracking-wide text-gray-400">{{ __('Location') }}</div>
                    <div class="mt-0.5 font-semibold">
                        <a href="/location/{{ $observerSlug }}/{{ $location->slug }}" class="hover:underline text-blue-400">
                            {{ html_entity_decode($location->name) }}
                        </a>
                    </div>
                </div>
            @endif

            {{-- Instrument --}}
            @if($instrument)
                <div>
                    <div class="text-xs uppercase tracking-wide text-gray-400">{{ __('Instrument') }}</div>
                    <div class="mt-0.5 font-semibold">
                        <a href="/instrument/{{ $observerSlug }}/{{ $instrument->slug }}" class="hover:underline text-blue-400">
                            {!! html_entity_decode($instrument->fullName()) !!}
                        </a>
                    </div>
                </div>
            @endif

            {{-- Eyepiece --}}
            @if($eyepiece)
                <div>
                    <div class="text-xs uppercase tracking-wide text-gray-400">{{ __('Eyepiece') }}</div>
                    <div class="mt-0.5 font-semibold">
                        <a href="/eyepiece/{{ $observerSlug }}/{{ $eyepiece->slug }}" class="hover:underline text-blue-400">
                            {{ html_entity_decode($eyepiece->fullName()) }}
                        </a>
                    </div>
                </div>
            @endif

            {{-- Magnification --}}
            @if($magnification)
                <div>
                    <div class="text-xs uppercase tracking-wide text-gray-400">{{ __('Magnification') }}</div>
                    <div class="mt-0.5 font-semibold">{{ $magnification }}</div>
                </div>
            @endif

            {{-- Filter --}}
            @if($filter)
                <div>
                    <div class="text-xs uppercase tracking-wide text-gray-400">{{ __('Filter') }}</div>
                    <div class="mt-0.5 font-semibold">
                        <a href="/filter/{{ $observerSlug }}/{{ $filter->slug }}" class="hover:underline text-blue-400">
                            {{ html_entity_decode($filter->fullName()) }}
                        </a>
                    </div>
                </div>
            @endif

            {{-- Lens --}}
            @if($lens)
                <div>
                    <div class="text-xs uppercase tracking-wide text-gray-400">{{ __('Lens') }}</div>
                    <div class="mt-0.5 font-semibold">
                        <a href="/lens/{{ $observerSlug }}/{{ $lens->slug }}" class="hover:underline text-blue-400">
                            {{ html_entity_decode($lens->fullName()) }}
                        </a>
                    </div>
                </div>
            @endif

            {{-- Limiting magnitude --}}
            @if(!empty($obs->limmag) && $obs->limmag > 0)
                <div>
                    <div class="text-xs uppercase tracking-wide text-gray-400">{{ __('Limiting magnitude') }}</div>
                    <div class="mt-0.5 font-semibold">{{ number_format($obs->limmag, 1) }}</div>
                </div>
            @endif

            {{-- SQM --}}
            @if($obs->SQM > 0)
                <div>
                    <div class="text-xs uppercase tracking-wide text-gray-400">{{ __('Sky quality (SQM)') }}</div>
                    <div class="mt-0.5 font-semibold">{{ number_format($obs->SQM, 2) }}</div>
                </div>
            @endif

            {{-- Seeing --}}
            @if(!empty($obs->seeing) && $obs->seeing > 0)
                <div>
                    <div class="text-xs uppercase tracking-wide text-gray-400">{{ __('Seeing') }}</div>
                    <div class="mt-0.5 font-semibold">{{ __('observations.seeing.' . $obs->seeing) }}</div>
                </div>
            @endif

            {{-- Transparency --}}
            @if(!empty($obs->transparency) && $obs->transparency > 0)
                <div>
                    <div class="text-xs uppercase tracking-wide text-gray-400">{{ __('Transparency') }}</div>
                    <div class="mt-0.5 font-semibold">{{ __('observations.transparency.' . $obs->transparency) }}</div>
                </div>
            @endif

            {{-- Visibility --}}
            @if(!empty($obs->visibility) && $obs->visibility > 0)
                <div>
                    <div class="text-xs uppercase tracking-wide text-gray-400">{{ __('Object visibility') }}</div>
                    <div class="mt-0.5 font-semibold">
                        @php
                            $visKey = (string)($obs->visibility ?? '');
                            $vis = __("observations.visibility.$visKey");
                        @endphp
                        @if($vis === "observations.visibility.$visKey")
                            {{ $obs->visibility }}
                        @else
                            {{ $vis }}
                        @endif
                    </div>
                </div>
            @endif

        </div>

        {{-- Drawing (right side) --}}
        @if($drawingUrl)
            <div class="flex-shrink-0 flex flex-col items-center">
                <div class="text-xs uppercase tracking-wide text-gray-400 mb-1">{{ __('Drawing') }}</div>
                <a href="{{ $drawingUrl }}" target="_blank">
                    <img src="{{ $drawingUrl }}" alt="{{ __('Drawing') }}" class="max-h-48 rounded border border-gray-600 cursor-zoom-in">
                </a>
            </div>
        @endif

    </div>

    {{-- Description --}}
    @if(!empty($obs->description))
        <div class="mt-4 border-t border-gray-700 pt-3">
            <div class="text-xs uppercase tracking-wide text-gray-400 mb-1">{{ __('Notes') }}</div>
            <div class="text-sm leading-relaxed">
                @if($tr)
                    @php
                        $cacheKey = 'obs_row_detail_translation:' . $obs->id . ':' . auth()->user()->language;
                        $translatedDesc = \Illuminate\Support\Facades\Cache::remember(
                            $cacheKey,
                            60 * 24 * 30,
                            function () use ($obs, $tr) {
                                try {
                                    return $tr->translate(html_entity_decode($obs->description));
                                } catch (\Throwable $e) {
                                    return null;
                                }
                            }
                        );
                    @endphp
                    {!! $translatedDesc ?? html_entity_decode($obs->description) !!}
                @else
                    {!! html_entity_decode($obs->description) !!}
                @endif
            </div>
        </div>
    @endif

    {{-- License --}}
    @if($observer && !empty($observer->copyright) && $observer->copyright !== 'No license (Not recommended)')
        <div class="mt-3 border-t border-gray-700 pt-3 text-sm">
            {!! $observer->getCopyright() !!}
        </div>
    @endif

    {{-- Link to full detail --}}
    <div class="mt-3 text-right">
        @if ($detailUrl)
            <a href="{{ $detailUrl }}" class="text-xs text-blue-400 hover:underline">{{ __('View full observation') }} →</a>
        @endif
    </div>
</div>
