<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-200 leading-tight">
            {{ __('Edit note for') }}: {{ $item->object_name }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
            <div class="bg-gray-900 shadow-sm sm:rounded-lg p-6">
                <p class="text-sm text-gray-400 mb-4">
                    {{ __('List') }}: <span class="text-gray-200 font-medium">{{ html_entity_decode($list->name, ENT_QUOTES | ENT_HTML5, 'UTF-8') }}</span>
                </p>

                <form method="POST" action="{{ route('observing-list.items.update', [$list, $item->id]) }}">
                    @csrf
                    @method('PATCH')

                    <div class="mb-6">
                        <div class="flex items-center justify-between gap-3 mb-1">
                            <label for="item_description" class="block text-sm font-medium text-gray-300">{{ __('Note (optional)') }}</label>
                            <button
                                type="button"
                                id="autofill-longest-observation"
                                class="px-3 py-1.5 bg-indigo-700 hover:bg-indigo-600 disabled:bg-gray-700 disabled:text-gray-400 text-white rounded-md text-xs font-semibold"
                                {{ empty($longestObservationNote) ? 'disabled' : '' }}
                            >
                                {{ __('Use longest observation note') }}
                            </button>
                        </div>
                        <textarea id="item_description" name="item_description" rows="4" maxlength="2000"
                                  placeholder="{{ __('Personal note about this object on the list…') }}"
                                  class="w-full bg-gray-800 border border-gray-600 rounded-md text-gray-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-y @error('item_description') border-red-500 @enderror">{{ old('item_description', $item->item_description) }}</textarea>
                        @if (!empty($longestObservationNote))
                            <p class="text-xs text-gray-500 mt-1">{{ __('Autofill inserts the longest existing observation description for this object.') }}</p>
                        @else
                            <p class="text-xs text-gray-500 mt-1">{{ __('No observation notes found for this object.') }}</p>
                        @endif
                        @error('item_description')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-md text-sm font-semibold">
                            {{ __('Save note') }}
                        </button>
                        <a href="{{ route('observing-list.show', $list) }}"
                           class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-gray-200 rounded-md text-sm">
                            {{ __('Cancel') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const btn = document.getElementById('autofill-longest-observation');
            const textarea = document.getElementById('item_description');
            if (!btn || !textarea) {
                return;
            }

            const longest = @json(!empty($longestObservationNote)
                ? html_entity_decode($longestObservationNote, ENT_QUOTES | ENT_HTML5, 'UTF-8')
                : '');

            btn.addEventListener('click', function () {
                if (!longest) {
                    return;
                }
                textarea.value = longest;
                textarea.dispatchEvent(new Event('input', { bubbles: true }));
                textarea.focus();
            });
        });
    </script>
</x-app-layout>
