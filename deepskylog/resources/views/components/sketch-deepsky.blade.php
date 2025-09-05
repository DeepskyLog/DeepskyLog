{{-- Avoid inline `use` in Blade views; use fully-qualified class names instead --}}
@props(["observation_id" => null, "observer_name" => null, "observer_username" => null, "observation_date" => null])
<div>
    <a href="{{ config("app.old_url") }}/index.php?indexAction=detail_observation&observation={{ $observation_id }}">
        <img width="400" src="/images/drawings/{{ $observation_id }}.jpg"/>

        <div class="text-center">
            {{ $observer_name }} -
            {{ \App\Models\ObservationsOld::find($observation_id)->objectname }}
            -
            {{ \Carbon\Carbon::create($observation_date)->translatedFormat("j M Y") }}
        </div>
    </a>

    <div class="text-center mt-2 mb-3 flex items-center justify-center gap-3">
        <div>
            {!!
                ShareButtons::page("https://www.deepskylog.org/index.php?indexAction=detail_observation&observation=" . $observation_id, __("Look at this sketch of ") . \App\Models\ObservationsOld::find($observation_id)->objectname . __(" by ") . $observer_name . __(" on #deepskylog"), [
                    "title" => __("Share this sketch"),
                    "class" => "text-gray-500 hover:text-gray-700",
                    "rel" => "nofollow noopener noreferrer",
                ])
                    ->facebook(["class" => "hover", "rel" => "follow"])
                    ->twitter(["class" => "hover", "rel" => "follow"])
                    ->copylink()
                    ->mailto(["class" => "hover", "rel" => "nofollow"])
                    ->whatsapp()
                    ->bluesky(["class" => "hover", "rel" => "follow"])
                    ->render()
            !!}
        </div>

        @php
            $likesCount = \App\Models\ObservationLike::where('observation_type', 'deepsky')->where('observation_id', $observation_id)->count();
            $liked = auth()->check() && \App\Models\ObservationLike::where('observation_type', 'deepsky')->where('observation_id', $observation_id)->where('user_id', auth()->id())->exists();
        @endphp

        <button data-observation-type="deepsky" data-observation-id="{{ $observation_id }}" class="like-button px-2 py-1 rounded bg-gray-800 hover:bg-gray-700 text-white">
            <span class="like-icon">{!! $liked ? '❤️' : '👍' !!}</span>
            <span class="like-count">{{ $likesCount }}</span>
        </button>

        @php
            $objectName = \App\Models\ObservationsOld::find($observation_id)->objectname;
            $messageSubject = __('About your sketch of :object', ['object' => $objectName]);
        @endphp

        <a href="{{ route('messages.create', ['to' => $observer_username ?? $observer_name, 'subject' => $messageSubject]) }}" class="inline-flex items-center p-2 rounded bg-blue-600 hover:bg-blue-700 text-white" aria-label="{{ __('Send message about this sketch') }}">
            {{-- envelope icon --}}
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path d="M2.94 6.94A2 2 0 014.828 6h10.344a2 2 0 011.888.94L10 11.586 2.94 6.94z" />
                <path d="M18 8.118V13a2 2 0 01-2 2H4a2 2 0 01-2-2V8.118l7.293 4.377a1 1 0 001.414 0L18 8.118z" />
            </svg>
        </a>
    </div>
</div>
