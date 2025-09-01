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

        $date = $observation->date;
        $observation_date = substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-' . substr($date, 6, 2);
        $user = User::where('username', html_entity_decode($observation->observerid))->first();
        $object = ObjectsOld::where('name', $observation->objectname)->first();
        $constellation = Constellation::where('id', $object->con)->first()->name;

        if (auth()->user()) {
            $tr = new GoogleTranslate(auth()->user()->language);
        }
    @endphp

    <div class="mr-4">
        <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="h-20 w-20 rounded-full object-cover"/>
    </div>

    <div class="max-w-[calc(100%-7rem)]">
        <a href="/observers/{{ $user->slug }}" class="font-bold hover:underline">
            {{ $user->name }}
        </a>
        @php
            $link = config('app.old_url') . "/index.php?indexAction=detail_object&object=" . $observation->objectname;
        @endphp

        {!! __(' observed :object', ['object' => Str::lower(__($object->long_type())) . ' <a href="' . $link . '" class="font-bold hover:underline">' . $observation->objectname . '</a>']) !!}

        {{ __(' in ') . $constellation }}

        {{ __(' on ') }}
        {{ Carbon::create($observation_date)->translatedFormat('j M Y') }}
        {{ __(' from ') }}
        <a href="/location/{{ $user->slug }}/{{ Location::where('id', $observation->locationid)->first()->slug }}"
           class="font-bold hover:underline">
            {{ html_entity_decode(Location::where('id', $observation->locationid)->first()->name) }}
        </a>

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
        <a href="/instrument/{{ $user->slug }}/{{ Instrument::where('id', $observation->instrumentid)->first()->slug }}"
           class="font-bold hover:underline">
            {!! html_entity_decode(Instrument::where('id', $observation->instrumentid)->first()->fullName()) !!}
        </a>
        @if ($observation->magnification > 0)
            {{ __(' with a magnification of ') }}
            {{ $observation->magnification }}x
        @endif
        @if ($observation->eyepieceid > 0)
            {{ __(' using a ') }}
            <a href="/eyepiece/{{ $user->slug }}/{{ Eyepiece::where('id', $observation->eyepieceid)->first()->slug }}"
               class="font-bold hover:underline">
                {{ html_entity_decode(Eyepiece::where('id', $observation->eyepieceid)->first()->fullName()) }}
            </a>
            {{ __(' eyepiece') }}

            @if ($observation->filterid > 0)
                {{ __(' and a ') }}
                <a href="/filter/{{ $user->slug }}/{{ Filter::where('id', $observation->filterid)->first()->slug }}"
                   class="font-bold hover:underline">
                    {{ html_entity_decode(Filter::where('id', $observation->filterid)->first()->name) }}
                </a>
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
        <div class="px-4 py-3 rounded-sm bg-gray-900">
            @if (auth()->user() && auth()->user()->translate)
                {!! ($translated = $tr->translate(html_entity_decode($observation->description))) == null ? html_entity_decode($observation->description): $translated !!}
            @else
                {!! html_entity_decode($observation->description) !!}
            @endif
        </div>

        <x-button gray icon="eye" class="mb-2 mt-2"
                  href='{{ config("app.old_url") }}/index.php?indexAction=detail_observation&observation={{ $observation->id }}'
        >
            {{ __("More details") }}
        </x-button>

        <div class="inline-block align-middle ml-3">
            @php
                use App\Models\ObservationLike;
                $likesCount = ObservationLike::where('observation_type', 'deepsky')->where('observation_id', $observation->id)->count();
                $liked = auth()->check() && ObservationLike::where('observation_type', 'deepsky')->where('observation_id', $observation->id)->where('user_id', auth()->id())->exists();
            @endphp

            <button data-observation-type="deepsky" data-observation-id="{{ $observation->id }}" class="like-button px-2 py-1 rounded bg-gray-800 hover:bg-gray-700 text-white">
                <span class="like-icon">{!! $liked ? '❤️' : '👍' !!}</span>
                <span class="like-count">{{ $likesCount }}</span>
            </button>
        </div>

    </div>

</div>
