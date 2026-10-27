@props(["kind", "equipment"])

{{-- The link to "What's new for me" on the page of the observer's own telescope or eyepiece; highlighted right after adding it --}}
@if (session("equipment_created"))
    <div class="mt-4 rounded border border-blue-800 bg-blue-950 p-3 text-sm text-gray-200">
        <p>
            {{
                $kind === \App\Services\EquipmentGain::EYEPIECE
                    ? __("See which objects you can see with this eyepiece that you could not see before.")
                    : __("See which objects you can see with this instrument that you could not see with your other instruments.")
            }}
        </p>
        <a href="{{ route($kind . ".whats-new", [$equipment->user->slug, $equipment->slug]) }}" class="mt-2 inline-block">
            <x-button primary>{{ __("What's new for me") }}</x-button>
        </a>
    </div>
@else
    <a href="{{ route($kind . ".whats-new", [$equipment->user->slug, $equipment->slug]) }}">
        <x-button secondary>{{ __("What's new for me") }}</x-button>
    </a>
@endif
