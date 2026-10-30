@props(["request", "slug" => null, "viewer" => null, "showObject" => true])

{{-- One observing request: the object, who asked, the aperture asked for, the note and the answers so far --}}
<div {{ $attributes->merge(["class" => "rounded border border-gray-700 bg-gray-900/60 p-3 text-sm text-gray-300"]) }}>
    <div class="flex flex-wrap items-baseline justify-between gap-2">
        <p>
            @if ($showObject)
                @if ($slug)
                    <a href="{{ route("object.show", $slug) }}" class="font-semibold text-white hover:underline">{{ $request->object_name }}</a>
                @else
                    <span class="font-semibold text-white">{{ $request->object_name }}</span>
                @endif
                &middot;
            @endif
            @if ($request->min_aperture_mm)
                {{ __("with at least :aperture", ["aperture" => \App\Models\ObservingRequest::apertureLabel($request->min_aperture_mm, $viewer)]) }}
            @else
                {{ __("with any instrument") }}
            @endif
            @if (($request->verdict ?? null) === "visible" && isset($request->contrast_reserve))
                <span class="ml-1 rounded bg-green-900 px-2 py-0.5 text-xs text-green-200">{{ __("Your telescope shows it") }}</span>
            @elseif (($request->verdict ?? null) === "pending")
                <span class="ml-1 rounded bg-gray-700 px-2 py-0.5 text-xs text-gray-300">{{ __("Being calculated for your telescope…") }}</span>
            @endif
        </p>
        <p class="text-xs text-gray-400">
            @if ($request->user)
                {{ __("Asked by") }}
                <a href="{{ route("observer.show", $request->user->slug) }}" class="hover:underline">{{ html_entity_decode($request->user->name, ENT_QUOTES | ENT_HTML5, "UTF-8") }}</a>
                &middot;
            @endif
            {{ $request->created_at?->translatedFormat("j M Y") }}
            &middot;
            @if ($request->closed_at)
                {{ __("closed") }}
            @elseif (!$request->isOpen())
                {{ __("expired") }}
            @else
                {{ __("open until :date", ["date" => $request->expires_on->translatedFormat("j M Y")]) }}
            @endif
        </p>
    </div>
    @if ($request->note)
        <p class="mt-1 break-words text-gray-100">{{ $request->note }}</p>
    @endif
    <div class="mt-1 flex flex-wrap items-center justify-between gap-2 text-xs text-gray-400">
        <span>{{ trans_choice("{0} No answers yet|{1} :count answer|[2,*] :count answers", $request->answers_count ?? 0, ["count" => $request->answers_count ?? 0]) }}</span>
        {{ $slot }}
    </div>
</div>
