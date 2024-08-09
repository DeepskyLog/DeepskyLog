@props(['observation'])
<div class="justify-left mt-5 flex">
    @php
        $date = $observation->date;
        $observation_date = substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-' . substr($date, 6, 2);
        $user = \App\Models\User::where('username', html_entity_decode($observation->observerid))->first();
        $object = \App\Models\ObjectsOld::where('name', $observation->objectname)->first();
        $constellation = \App\Models\Constellation::where('id', $object->con)->first()->name;
    @endphp

    <div class="mr-4">
        <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="h-20 w-20 rounded-full object-cover" />
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
        {{ \Carbon\Carbon::create($observation_date)->translatedFormat('j M Y') }}
        {{ __(' from ') }}
        <a href="{{ config('app.old_url') }}/index.php?indexAction=detail_location&location={{ $observation->locationid }}"
           class="font-bold hover:underline">
            {{ html_entity_decode(\App\Models\LocationsOld::where('id', $observation->locationid)->first()->name) }}
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
        @endif.<br />
        {{ __('Used instrument was ') }}
        <a href="{{ config('app.old_url') }}/index.php?indexAction=detail_instrument&instrument={{ $observation->instrumentid }}"
           class="font-bold hover:underline">
            {!! html_entity_decode(\App\Models\InstrumentsOld::where('id', $observation->instrumentid)->first()->name) !!}
        </a>
        @if ($observation->magnification > 0)
            {{ __(' with a magnification of ') }}
            {{ $observation->magnification }}x
        @endif
        @if ($observation->eyepieceid > 0)
            {{ __(' using a ') }}
            <a href="{{ config('app.old_url') }}/index.php?indexAction=detail_eyepiece&eyepiece={{ $observation->eyepieceid }}"
               class="font-bold hover:underline">
                {{ html_entity_decode(\App\Models\EyepiecesOld::where('id', $observation->eyepieceid)->first()->name) }}
            </a>
            {{ __(' eyepiece') }}
            @if ($observation->filterid > 0)
                {{ __(' and a ') }}
                <a href="{{ config('app.old_url') }}/index.php?indexAction=detail_filter&filter={{ $observation->filterid }}"
                   class="font-bold hover:underline">
                    {{ html_entity_decode(\App\Models\FiltersOld::where('id', $observation->filterid)->first()->name) }}
                </a>
                {{ __(' filter') }}
            @endif
        @elseif ($observation->filterid > 0)
            {{ __(' using a ') }}
            <a href="{{ config('app.old_url') }}/index.php?indexAction=detail_filter&filter={{ $observation->filterid }}"
               class="font-bold hover:underline">
                {{ html_entity_decode(\App\Models\FiltersOld::where('id', $observation->filterid)->first()->name) }}
            </a>
            {{ __(' filter') }}
        @endif
        .

        <br />
        {{ __(' The following notes where made: ') }}
        <br />
        <div class="px-4 py-3 rounded bg-gray-900">
            {!! html_entity_decode($observation->description) !!}
        </div>

        <x-button gray icon="eye" class="mb-2 mt-2"
                  href='{{ config("app.old_url") }}/index.php?indexAction=detail_observation&observation={{ $observation->id }}'
        >
            {{ __("More details") }}
        </x-button>

    </div>

</div>
