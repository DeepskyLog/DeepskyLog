{{-- The observer's own note on a list they are working on (ObservingListPin::setNote()): $list, $note --}}
@php $note = $note ?? null; @endphp
<div class="mt-1 text-sm">
    @if (filled($note))
        <p class="text-amber-200/90 whitespace-pre-line break-words">{{ $note }}</p>
    @endif
    <details class="group">
        <summary class="cursor-pointer list-none text-xs text-gray-400 hover:text-gray-200 [&::-webkit-details-marker]:hidden">
            {{ filled($note) ? __('Edit note') : __('Add a note') }}
        </summary>
        <form method="POST" action="{{ route('observing-list.pin-note', $list) }}" class="mt-2 space-y-2">
            @csrf
            <textarea name="note" rows="2" maxlength="{{ \App\Models\ObservingListPin::NOTE_LENGTH }}"
                placeholder="{{ __('For example: dark skies only, with the binoculars, sketch them all') }}"
                class="w-full rounded border border-gray-600 bg-gray-900 px-2 py-1 text-sm text-gray-100 placeholder-gray-500 focus:border-amber-500 focus:ring-amber-500">{{ $note }}</textarea>
            <div class="flex items-center gap-2">
                <button type="submit" class="text-xs px-2 py-1 bg-amber-600 hover:bg-amber-500 text-white rounded">{{ __('Save note') }}</button>
                <span class="text-xs text-gray-500">{{ __('Only you see this note.') }}</span>
            </div>
        </form>
    </details>
</div>
