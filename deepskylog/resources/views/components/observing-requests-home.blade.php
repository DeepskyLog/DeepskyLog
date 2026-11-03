@props(["requests"])

{{-- Observing requests on the home page (ObservingRequests::forHomePage()): the newest open requests the observer's telescope can probably answer --}}
<x-home-section :title="__('Observing requests')" {{ $attributes }}>
    <div class="space-y-2 px-2 text-sm text-gray-300">
        <p>{{ trans_choice("One object other observers asked for is within reach of your telescope.|:count objects other observers asked for are within reach of your telescope.", $requests["count"], ["count" => $requests["count"]]) }}</p>
        <ul class="space-y-1">
            @foreach ($requests["requests"] as $request)
                <li>
                    <a href="{{ route("object.show", $request->slug ?: \Illuminate\Support\Str::slug($request->object_name)) }}#observing-requests" class="font-semibold text-white hover:underline">{{ $request->object_name }}</a>
                    @if ($request->min_aperture_mm)
                        <span class="text-gray-400">&middot; {{ __("with at least :aperture", ["aperture" => \App\Models\ObservingRequest::apertureLabel($request->min_aperture_mm, auth()->user())]) }}</span>
                    @endif
                    @if ($request->user)
                        <span class="text-gray-400">&middot; {{ html_entity_decode($request->user->name, ENT_QUOTES | ENT_HTML5, "UTF-8") }}</span>
                    @endif
                    @if ($request->note)
                        <span class="block truncate text-xs text-gray-400">{{ $request->note }}</span>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
    <x-slot name="footer">
        <a href="{{ route("observing-requests") }}" class="text-blue-400 hover:underline">{{ __("All requests for my telescope") }} &rarr;</a>
    </x-slot>
</x-home-section>
