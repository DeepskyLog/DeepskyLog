@props(['observation'])
<div class="justify-left mt-5 flex">
    @php
        use App\Models\Constellation;
        use App\Models\Eyepiece;
        use App\Models\Filter;
        use App\Models\Instrument;
        use App\Models\Location;
        use App\Models\ObjectsOld;
        use App\Models\User;
        use Carbon\Carbon;
        use Stichoza\GoogleTranslate\GoogleTranslate;
    use Illuminate\Support\Facades\Log;

        $date = $observation->date;
        $observation_date = substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-' . substr($date, 6, 2);
    $user = User::where('username', html_entity_decode($observation->observerid))->first();
    $object = ObjectsOld::where('name', $observation->objectname)->first();
    $constellation = $object ? (Constellation::where('id', $object->con)->first()?->name ?? '') : '';

    // preload related models and guard against nulls when rendering
    $location = Location::where('id', $observation->locationid)->first();
        $instrument = Instrument::where('id', $observation->instrumentid)->first();
        $eyepiece = $observation->eyepieceid > 0 ? Eyepiece::where('id', $observation->eyepieceid)->first() : null;
        $filter = $observation->filterid > 0 ? Filter::where('id', $observation->filterid)->first() : null;

        // Collect context to help debugging when related models are missing
        $logContext = [
            'observation_id' => $observation->id,
            'observerid' => $observation->observerid ?? null,
            'objectname' => $observation->objectname ?? null,
            'locationid' => $observation->locationid ?? null,
            'instrumentid' => $observation->instrumentid ?? null,
            'eyepieceid' => $observation->eyepieceid ?? null,
            'filterid' => $observation->filterid ?? null,
            'route' => request()->route()?->getName() ?? null,
            'uri' => request()->getRequestUri() ?? null,
            'ip' => request()->ip() ?? null,
            'auth_user_id' => auth()->id(),
        ];

        if (!$user) {
            Log::warning('Observation component: missing user', $logContext);
        }
        if (!$object) {
            Log::warning('Observation component: missing object', $logContext);
        }
        if (!$location) {
            Log::warning('Observation component: missing location', $logContext);
        }
        if (!$instrument) {
            Log::warning('Observation component: missing instrument', $logContext);
        }
        if ($observation->eyepieceid > 0 && !$eyepiece) {
            Log::warning('Observation component: missing eyepiece', $logContext);
        }
        if ($observation->filterid > 0 && !$filter) {
            Log::warning('Observation component: missing filter', $logContext);
        }

        if (auth()->user()) {
                $tr = null;
                if (auth()->check() && auth()->user()->translate) {
                    $tr = new GoogleTranslate(auth()->user()->language);
                }
        }
    @endphp

    <div class="mr-4">
        <img src="{{ $user?->profile_photo_url ?? '/images/default-profile.png' }}" alt="{{ $user?->name ?? __('Unknown') }}" class="h-20 w-20 rounded-full object-cover"/>
    </div>

    <div class="max-w-[calc(100%-7rem)]">
        @if($user)
            <a href="/observers/{{ $user->slug }}" class="font-bold hover:underline">
                {{ $user->name }}
            </a>
        @else
            <span class="font-bold">{{ __('Unknown observer') }}</span>
        @endif
        @php
            $link = config('app.old_url') . "/index.php?indexAction=detail_object&object=" . $observation->objectname;
        @endphp

        {!! __(' observed :object', ['object' => Str::lower(__($object->long_type())) . ' <a href="' . $link . '" class="font-bold hover:underline">' . $observation->objectname . '</a>']) !!}

        {{ __(' in ') . $constellation }}

        {{ __(' on ') }}
        {{ Carbon::create($observation_date)->translatedFormat('j M Y') }}
        {{ __(' from ') }}
        @if($location)
            <a href="/location/{{ $user?->slug ?? 'unknown' }}/{{ $location->slug }}" class="font-bold hover:underline">
                {{ html_entity_decode($location->name) }}
            </a>
        @else
            <span class="font-bold">{{ __('Unknown location') }}</span>
        @endif

        {{-- Seeing --}}
        {{-- SQM or limiting magnitude --}}
        @if ($observation->SQM > 0)
            {{ __(' in a sky with an SQM of ') }}
            {{ $observation->SQM }}
        @endif
        @if ($observation->seeing > 0)
            {{ __(' under ') }}
            @if ($observation->seeing == 1)
                {{ __('excellent') }}
            @elseif ($observation->seeing == 2)
                {{ __('good') }}
            @elseif ($observation->seeing == 3)
                {{ __('moderate') }}
            @elseif ($observation->seeing == 4)
                {{ __('poor') }}
            @elseif ($observation->seeing == 5)
                {{ __('bad') }}
            @endif
            {{ __('seeing') }}
        @endif.<br/>
        {{ __('Used instrument was ') }}
        @if($instrument)
            <a href="/instrument/{{ $user?->slug ?? 'unknown' }}/{{ $instrument->slug }}" class="font-bold hover:underline">
                {!! html_entity_decode($instrument->fullName()) !!}
            </a>
        @else
            <span class="font-bold">{{ __('Unknown instrument') }}</span>
        @endif
        @if ($observation->magnification > 0)
            {{ __(' with a magnification of ') }}
            {{ $observation->magnification }}x
        @endif
        @if ($observation->eyepieceid > 0)
            {{ __(' using a ') }}
            @if($eyepiece)
                <a href="/eyepiece/{{ $user?->slug ?? 'unknown' }}/{{ $eyepiece->slug }}" class="font-bold hover:underline">
                    {{ html_entity_decode($eyepiece->fullName()) }}
                </a>
            @else
                <span class="font-bold">{{ __('Unknown eyepiece') }}</span>
            @endif
            {{ __(' eyepiece') }}

            @if ($observation->filterid > 0)
                {{ __(' and a ') }}
                @if($filter)
                    <a href="/filter/{{ $user?->slug ?? 'unknown' }}/{{ $filter->slug }}" class="font-bold hover:underline">
                        {{ html_entity_decode($filter->name) }}
                    </a>
                @else
                    <span class="font-bold">{{ __('Unknown filter') }}</span>
                @endif
                {{ __(' filter') }}
            @endif
        @elseif ($observation->filterid > 0)
            {{ __(' using a ') }}
            <a href="{{ config('app.old_url') }}/index.php?indexAction=detail_filter&filter={{ $observation->filterid }}"
               class="font-bold hover:underline">
                {{ html_entity_decode(Filter::where('id', $observation->filterid)->first()->name) }}
            </a>
            {{ __(' filter') }}
        @endif
        .

        <br/>
        {{ __(' The following notes where made: ') }}
        <br/>
        <div class="my-2 rounded-sm bg-gray-900 px-4 py-3">
            <div class="flex items-start space-x-4">
                @if ($observation->hasDrawing)
                    <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-deepsky-lightbox-{{ $observation->id }}'))" class="flex-shrink-0 focus:outline-none">
                        <img src="/images/drawings/{{ $observation->id }}.jpg" alt="drawing-{{ $observation->id }}" class="w-28 rounded" />
                    </button>
                @endif

                <div class="flex-1">
                    @if (auth()->user() && auth()->user()->translate)
                        {!! ($translated = $tr->translate(html_entity_decode($observation->description))) == null ? html_entity_decode($observation->description): $translated !!}
                    @else
                        {!! html_entity_decode($observation->description) !!}
                    @endif
                </div>
            </div>

            <!-- Modal / Lightbox (listens for custom event to open) -->
            <div x-data="{ open: false }" x-on:open-deepsky-lightbox-{{ $observation->id }}.window="open = true">
                <div x-cloak x-show="open" x-transition.opacity="" @click.self="open = false" @keydown.escape.window="open = false" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-70">
                    <div class="max-w-4xl max-h-[90vh] p-4">
                        <button type="button" @click="open = false" class="absolute top-4 right-4 z-50 rounded bg-gray-800 p-2 text-white">
                            &times;
                        </button>
                        <img src="/images/drawings/{{ $observation->id }}.jpg" alt="drawing-large-{{ $observation->id }}" class="max-w-full max-h-[85vh] rounded shadow-lg" />
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center space-x-3 mt-2 mb-2">
            <x-button gray icon="eye" class="align-middle"
                      href='{{ config("app.old_url") }}/index.php?indexAction=detail_observation&observation={{ $observation->id }}'
            >
                {{ __("More details") }}
            </x-button>

            {{-- DSL message button: opens internal composer with to=username and a prefilled subject --}}
            @auth
            <a href="{{ route('messages.create', ['to' => $user->username , 'subject' => 'About your observation of ' . $observation->objectname]) }}" class="inline-flex items-center px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white align-middle" aria-label="{{ __('Send message about this sketch') }}">
                {{-- envelope icon --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M2.94 6.94A2 2 0 014.828 6h10.344a2 2 0 011.888.94L10 11.586 2.94 6.94z" />
                    <path d="M18 8.118V13a2 2 0 01-2 2H4a2 2 0 01-2-2V8.118l7.293 4.377a1 1 0 001.414 0L18 8.118z" />
                </svg>
            </a>
            @endauth

            @php
                use App\Models\ObservationLike;
                $likesCount = ObservationLike::where('observation_type', 'deepsky')->where('observation_id', $observation->id)->count();
                $liked = auth()->check() && ObservationLike::where('observation_type', 'deepsky')->where('observation_id', $observation->id)->where('user_id', auth()->id())->exists();
            @endphp

            <button data-observation-type="deepsky" data-observation-id="{{ $observation->id }}" class="like-button px-2 py-1 rounded bg-gray-800 hover:bg-gray-700 text-white align-middle">
                <span class="like-icon">{!! $liked ? '❤️' : '👍' !!}</span>
                <span class="like-count">{{ $likesCount }}</span>
            </button>
        </div>

    </div>

</div>
