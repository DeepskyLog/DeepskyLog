<div class="text-sm text-gray-200">
    <ul class="divide-y divide-gray-700 rounded-md bg-gray-800 px-3">
        @forelse ($complete as $entry)
            <li class="flex flex-wrap items-center gap-2 py-2" wire:key="complete-catalog-{{ $entry->id }}">
                <div class="min-w-0 flex-1">
                    <span class="font-medium text-white">{{ $entry->catalog }}</span>
                    <span class="text-gray-400">
                        &middot; {{ trans_choice(':count designation|:count designations', $designations[mb_strtolower($entry->catalog)] ?? 0, ['count' => $designations[mb_strtolower($entry->catalog)] ?? 0]) }}
                    </span>
                </div>
                <button type="button" wire:click="remove({{ $entry->id }})"
                    wire:confirm="{{ __('Allow observers to add objects to :catalog again?', ['catalog' => $entry->catalog]) }}"
                    class="rounded px-2 py-0.5 text-gray-400 hover:bg-gray-700 hover:text-white">
                    {{ __('Remove') }}
                </button>
            </li>
        @empty
            <li class="py-2 text-gray-400">{{ __('There are no complete catalogs.') }}</li>
        @endforelse
    </ul>

    <form wire:submit="add" class="mt-4 flex flex-wrap items-end gap-3">
        <div>
            <label for="complete-catalog" class="mb-1 block text-sm font-medium text-gray-400">{{ __('Catalog') }}</label>
            <select id="complete-catalog" wire:model="catalog"
                class="block rounded border border-gray-600 bg-gray-900 px-3 py-2 text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">-- {{ __('Select catalog') }} --</option>
                @foreach ($catalogs as $code)
                    <option value="{{ $code }}">{{ $code }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="rounded bg-green-700 px-4 py-2 font-medium text-white hover:bg-green-600">
            {{ __('Mark as complete') }}
        </button>
    </form>
    @error('catalog') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
</div>
