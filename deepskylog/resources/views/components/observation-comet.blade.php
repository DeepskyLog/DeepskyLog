@php use App\Models\User; @endphp
@php use App\Models\CometObjectsOld; @endphp
@php use Carbon\Carbon; @endphp
@php use App\Models\LocationsOld; @endphp
@php use App\Models\InstrumentsOld; @endphp
@props([
    "observation",
])
<div class="justify-left mt-5 flex">
    @php
        $date = $observation->date;
        $observation_date = substr($date, 0, 4) . "-" . substr($date, 4, 2) . "-" . substr($date, 6, 2);
        $user = User::where("username", html_entity_decode($observation->observerid))->first();
        use Stichoza\GoogleTranslate\GoogleTranslate;

        if (auth()->user()) {
            $tr = new GoogleTranslate(auth()->user()->language);
        }
    @endphp

    <div class="mr-4">
        <img
            src="{{ $user->profile_photo_url }}"
            alt="{{ $user->name }}"
            class="h-20 w-20 rounded-full object-cover"
        />
    </div>

    <div class="max-w-[calc(100%-7rem)]">
        <a
            href="/observers/{{ $user->slug }}"
            class="font-bold hover:underline"
        >
            {{ $user->name }}
        </a>

        @php
            $link = config("app.old_url") . "/index.php?indexAction=comets_detail_object&object=" . $observation->objectid;
        @endphp

        {!! __(" observed :object", ["object" => '<a href="' . $link . '" class="font-bold hover:underline">' . CometObjectsOld::where("id", $observation->objectid)->first()->name . "</a>"]) !!}

        {{ __(" on ") }}
        {{ Carbon::create($observation_date)->translatedFormat("j M Y") }}
        @if ($observation->locationid > 0)
            {{ __(" from ") }}
            <a
                href="{{ config("app.old_url") }}/index.php?indexAction=detail_location&location={{ $observation->locationid }}"
                class="font-bold hover:underline"
            >
                {{ html_entity_decode(LocationsOld::where("id", $observation->locationid)->first()->name) }}
                .
            </a>
        @endif
        <br/>
        @if ($observation->instrumentid > 0)
            {{ __("Used instrument was ") }}
            <a
                href="{{ config("app.old_url") }}/index.php?indexAction=detail_instrument&instrument={{ $observation->instrumentid }}"
                class="font-bold hover:underline"
            >
                {!! html_entity_decode(InstrumentsOld::where("id", $observation->instrumentid)->first()->name) !!}
                .
            </a>
        @endif

        @if ($observation->description != "")
            <br/>
            {{ __(" The following notes where made: ") }}
            <br/>
            <div class="my-2 rounded bg-gray-900 px-4 py-4">
                @if (auth()->user() && auth()->user()->translate)
                    {!! ($translated = $tr->translate(html_entity_decode($observation->description))) == null ? html_entity_decode($observation->description): $translated !!}
                @else
                    {!! html_entity_decode($observation->description) !!}
                @endif
            </div>
        @endif

        <div>
            <x-button
                gray
                icon="eye"
                class="mb-2 mt-2"
                href='{{ config("app.old_url") }}/index.php?indexAction=detail_observation&observation={{ $observation->id }}'
            >
                {{ __("More details") }}
            </x-button>
        </div>
    </div>
</div>
