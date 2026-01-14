<div class="mt-3">
    <h2 class="text-xl font-semibold text-white">{{ __('Object details') }}</h2>
    <table class="table-auto w-full text-sm text-gray-100">
        <tr>
            <td class="pr-4 font-medium">{{ __('Moon (Rise / Set)') }}</td>
            <td id="dsl-moon-rise" class="font-mono">—</td>
        </tr>
        <tr>
            <td class="pr-4 font-medium">{{ __('Illumination') }}</td>
            <td id="dsl-main-illum">—</td>
        </tr>
        <tr>
            <td class="pr-4 font-medium">{{ __('Next new moon') }}</td>
            <td id="dsl-next-new-moon">—</td>
        </tr>
    </table>

    {{-- Mount Moon-specific Livewire component (authoritative) --}}
    @livewire('moon-details', ['objectId' => (string) ($session->id ?? ''), 'initial' => $ephemerides ?? null])
</div>
