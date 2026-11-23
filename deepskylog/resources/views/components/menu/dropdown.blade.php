@props(['width' => '72', 'columns' => 1])
{{-- A long menu gets columns (each $width wide) instead of a scroll bar; the panel is moved left to stay inside the window.
     The position is set before x-show shows the panel (the panel's width follows from the props): measured after showing,
     the panel stuck out for a frame, which made a tablet's browser widen and scroll the page (a white strip on the right),
     after which window.innerWidth was the widened width and the panel stayed outside the window. --}}
@php $panelRem = $columns > 1 ? $width * $columns / 4 : $width / 4; @endphp
<div class="relative" x-data="{ open: false }" x-init="$watch('open', value => { if (value) {
        const panel = $refs.panel;
        const viewport = document.documentElement.clientWidth;
        const width = Math.min({{ $panelRem }} * parseFloat(getComputedStyle(document.documentElement).fontSize), viewport - 16);
        const left = $el.getBoundingClientRect().left;
        const overflow = left + width - (viewport - 8);
        panel.style.left = overflow > 0 ? (-Math.max(0, Math.min(overflow, left - 8))) + 'px' : '';
        $nextTick(() => { const first = $el.querySelector('[data-first]'); if (first) first.focus(); });
    } })" @keydown.escape.prevent="open = false; $refs.trigger.focus()" @keydown.tab="if (open) {
    const focusable = $el.querySelectorAll('a[href], button:not([disabled]), [tabindex]:not([tabindex=\'-1\'])');
        if (focusable.length === 0) return;
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if ($event.shiftKey && document.activeElement === first) { $event.preventDefault(); last.focus(); }
        else if (! $event.shiftKey && document.activeElement === last) { $event.preventDefault(); first.focus(); }
    }">
    {{-- Trigger slot: expects element with x-ref="trigger" --}}
    <div>
        {{ $trigger }}
    </div>

    {{-- Panel --}}
    <div x-show="open" x-cloak x-ref="panel"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 transform translate-y-1 scale-95"
         x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 transform translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 transform translate-y-1 scale-95"
         @click.away="open = false"
         role="menu"
         aria-label="Dropdown menu"
         tabindex="-1"
         class="absolute mt-2 {{ $columns > 1 ? '' : 'w-'.$width }} rounded bg-gray-800 p-2 shadow-lg z-50 overflow-auto"
         :class="open ? 'ring-1 ring-blue-400 ring-opacity-60' : ''"
         style="max-height:calc(100vh - 5rem); max-width:calc(100vw - 1rem);{{ $columns > 1 ? ' width:'.($width * $columns / 4).'rem; column-count:'.$columns.'; column-gap:0.5rem;' : '' }}">
        {{ $slot }}
    </div>
</div>
