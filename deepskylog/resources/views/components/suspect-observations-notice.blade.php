@props (["count"])

{{-- The number of the observer's observations that look wrong (SuspectObservations) on the home page --}}
<x-home-section :title="__('Observations to check')" {{ $attributes }}>
    <p class="px-2 text-sm text-gray-300">
        {{ trans_choice("{1} One of your observations looks wrong: the object was below the horizon, far too faint for the instrument, or the observation is a duplicate.|[2,*] :count of your observations look wrong: the object was below the horizon, far too faint for the instrument, or the observation is a duplicate.", $count, ["count" => $count]) }}
    </p>
    <x-slot name="footer">
        <a href="{{ route("observations.suspect") }}" class="text-blue-400 hover:underline">{{ __("Check them") }} &rarr;</a>
    </x-slot>
</x-home-section>
