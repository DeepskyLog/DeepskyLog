@props(["slug"])

{{-- Marks an object other observers asked to observe (ObservingRequests::requestedAmong()) --}}
<a href="{{ route("object.show", $slug) }}#observing-requests" class="ml-1 whitespace-nowrap rounded bg-amber-900 px-1.5 py-0.5 text-xs text-amber-100 hover:underline" title="{{ __("Other observers asked to observe this object: your observation answers their request") }}">{{ __("Requested") }}</a>
