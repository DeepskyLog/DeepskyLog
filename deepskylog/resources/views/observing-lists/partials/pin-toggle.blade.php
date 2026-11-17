{{-- "Working on" toggle (ObservingListPin): $list, $pinned; $compact for the cards of /observing-lists --}}
@php $compact = $compact ?? false; @endphp
<form method="POST" action="{{ route($pinned ? 'observing-list.unpin' : 'observing-list.pin', $list) }}">
    @csrf
    <button type="submit"
        title="{{ $pinned ? __('Remove from "Working on"') : __('Add to "Working on"') }}"
        aria-pressed="{{ $pinned ? 'true' : 'false' }}"
        class="inline-flex items-center gap-1 {{ $compact ? 'text-xs px-2 py-1' : 'px-3 py-1.5 text-xs font-semibold' }} rounded {{ $pinned ? 'bg-amber-600 hover:bg-amber-500 text-white' : 'bg-gray-700 hover:bg-gray-600 text-gray-200' }}">
        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="{{ $pinned ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linejoin="round" d="M12 3l2.8 5.7 6.2.9-4.5 4.4 1.1 6.2L12 17.3 6.4 20.2l1.1-6.2L3 9.6l6.2-.9L12 3z"/>
        </svg>
        {{ __('Working on') }}
    </button>
</form>
