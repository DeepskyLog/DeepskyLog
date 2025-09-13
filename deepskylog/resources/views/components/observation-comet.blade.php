{{-- Avoid inline `use` in Blade; use fully-qualified class names within PHP blocks --}}
@props([
    "observation",
])
<div class="justify-left mt-5 flex">
        @php
        $date = $observation->date;
        $observation_date = substr($date, 0, 4) . "-" . substr($date, 4, 2) . "-" . substr($date, 6, 2);
        $user = \App\Models\User::where("username", html_entity_decode($observation->observerid))->first();
        // Stichoza\GoogleTranslate\GoogleTranslate used conditionally
        $tr = null;
        if (auth()->check() && auth()->user()->translate) {
            $tr = new \Stichoza\GoogleTranslate\GoogleTranslate(auth()->user()->language);
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

    {!! __(" observed :object", ["object" => '<a href="' . $link . '" class="font-bold hover:underline">' . \App\Models\CometObjectsOld::where("id", $observation->objectid)->first()->name . "</a>"]) !!}

        {{ __(" on ") }}
            {{ \Carbon\Carbon::create($observation_date)->translatedFormat("j M Y") }}
        @if ($observation->locationid > 0)
            {{ __(" from ") }}
            <a
                href="/location/{{$user->slug}}/{{ \App\Models\Location::where("id", $observation->locationid)->first()->slug }}"
                class="font-bold hover:underline"
            >
                {{ html_entity_decode(\App\Models\Location::where("id", $observation->locationid)->first()->name) }}
                .
            </a>
        @endif
        <br/>
        @if ($observation->instrumentid > 0)
            {{ __("Used instrument was ") }}
            <a
                href="/instrument/{{ $user->slug }}/{{ \App\Models\Instrument::where("id", $observation->instrumentid)->first()->slug }}"
                class="font-bold hover:underline"
            >
                {!! html_entity_decode(\App\Models\Instrument::where("id", $observation->instrumentid)->first()->fullName()) !!}
                .
            </a>
        @endif

        @if ($observation->description != "")
            <br/>
            {{ __(" The following notes where made: ") }}
            <br/>
                <div class="my-2 rounded-sm bg-gray-900 px-4 py-4">
                @if ($tr)
                    {!! ($translated = $tr->translate(html_entity_decode($observation->description))) == null ? html_entity_decode($observation->description): $translated !!}
                @else
                    {!! html_entity_decode($observation->description) !!}
                @endif
            </div>
        @endif

        <div class="flex items-center space-x-3 mt-2 mb-2">
            <x-button
                gray
                icon="eye"
                class="align-middle"
                href='{{ config("app.old_url") }}/index.php?indexAction=comets_detail_observation&observation={{ $observation->id }}'
            >
                {{ __("More details") }}
            </x-button>

            {{-- DSL message button: opens internal composer with to=username and a prefilled subject --}}
            @auth
            <a href="{{ route('messages.create', ['to' => $user->username, 'subject' => 'About your observation of ' . \App\Models\CometObjectsOld::where('id', $observation->objectid)->first()->name]) }}" class="inline-flex items-center px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white align-middle" aria-label="{{ __('Send message about this sketch') }}">
                {{-- envelope icon --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M2.94 6.94A2 2 0 014.828 6h10.344a2 2 0 011.888.94L10 11.586 2.94 6.94z" />
                    <path d="M18 8.118V13a2 2 0 01-2 2H4a2 2 0 01-2-2V8.118l7.293 4.377a1 1 0 001.414 0L18 8.118z" />
                </svg>
            </a>
            @endauth

            @php
                $likesCount = \App\Models\ObservationLike::where('observation_type', 'comet')->where('observation_id', $observation->id)->count();
                $liked = auth()->check() && \App\Models\ObservationLike::where('observation_type', 'comet')->where('observation_id', $observation->id)->where('user_id', auth()->id())->exists();
            @endphp

            <button data-observation-type="comet" data-observation-id="{{ $observation->id }}" class="like-button px-2 py-1 rounded bg-gray-800 hover:bg-gray-700 text-white align-middle">
                <span class="like-icon">{!! $liked ? '❤️' : '👍' !!}</span>
                <span class="like-count">{{ $likesCount }}</span>
            </button>
        </div>
    </div>
</div>
