@props(['label', 'text', 'rows' => 8])

{{-- A text to paste elsewhere (Astroforum, Facebook, X, …) with a button that copies it. --}}
<div x-data="{ copied: false }" {{ $attributes->merge(['class' => 'mt-4']) }}>
    <div class="flex items-center justify-between">
        <span class="text-sm font-medium text-gray-300">{{ $label }}</span>
        <button type="button"
            class="rounded bg-gray-700 px-2 py-1 text-xs text-gray-100 hover:bg-gray-600"
            x-on:click="navigator.clipboard.writeText($refs.text.value); copied = true; setTimeout(() => copied = false, 2000)">
            <span x-show="! copied">{{ __('Copy') }}</span>
            <span x-show="copied" x-cloak>{{ __('Copied') }}</span>
        </button>
    </div>
    <textarea x-ref="text" readonly rows="{{ $rows }}"
        class="mt-1 w-full rounded border border-gray-700 bg-gray-800 px-3 py-2 font-mono text-sm text-gray-100">{{ $text }}</textarea>
</div>
