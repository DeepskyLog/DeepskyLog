@props([
    "observation",
])
<div class="justify-left mt-5 flex">
    @php
        $date = $observation->date;
        $observation_date = substr($date, 0, 4) . "-" . substr($date, 4, 2) . "-" . substr($date, 6, 2);
        $user = \App\Models\User::where("username", html_entity_decode($observation->observerid))->first();
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

        {!! __(" observed :object", ["object" => '<a href="' . $link . '" class="font-bold hover:underline">' . \App\Models\CometObjectsOld::where("id", $observation->objectid)->first()->name . "</a>"]) !!}

        {{ __(" on ") }}
        {{ \Carbon\Carbon::create($observation_date)->translatedFormat("j M Y") }}
        {{ __(" from ") }}
        <a
            href="{{ config("app.old_url") }}/index.php?indexAction=detail_location&location={{ $observation->locationid }}"
            class="font-bold hover:underline"
        >
            {{ html_entity_decode(\App\Models\LocationsOld::where("id", $observation->locationid)->first()->name) }}.
        </a>

        <br />
        {{ __("Used instrument was ") }}
        <a
            href="{{ config("app.old_url") }}/index.php?indexAction=detail_instrument&instrument={{ $observation->instrumentid }}"
            class="font-bold hover:underline"
        >
            {!! html_entity_decode(\App\Models\InstrumentsOld::where("id", $observation->instrumentid)->first()->name) !!}
            .
        </a>

        @if ($observation->description != "")
            <br />
            {{ __(" The following notes where made: ") }}
            <br />
            <div class="my-2 rounded bg-gray-900 px-4 py-4">
                {!! html_entity_decode($observation->description) !!}
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
