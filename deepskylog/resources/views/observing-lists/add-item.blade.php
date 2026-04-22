<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-200 leading-tight">
            {{ __('Add object to') }}: {{ html_entity_decode($list->name, ENT_QUOTES | ENT_HTML5, 'UTF-8') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
            <div class="bg-gray-900 shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('observing-list.items.store', $list) }}">
                    @csrf

                    <div class="mb-4">
                        <label for="object_name" class="block text-sm font-medium text-gray-300 mb-1">{{ __('Object name') }} <span class="text-red-400">*</span></label>
                        <input type="text" id="object_name" name="object_name" maxlength="255" required
                               value="{{ old('object_name') }}"
                               placeholder="{{ __('e.g. M 31, NGC 224') }}"
                               class="w-full bg-gray-800 border border-gray-600 rounded-md text-gray-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('object_name') border-red-500 @enderror">
                        @error('object_name')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label for="item_description" class="block text-sm font-medium text-gray-300 mb-1">{{ __('Note (optional)') }}</label>
                        <textarea id="item_description" name="item_description" rows="3" maxlength="2000"
                                  placeholder="{{ __('Personal note about this object on the list…') }}"
                                  class="w-full bg-gray-800 border border-gray-600 rounded-md text-gray-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-y @error('item_description') border-red-500 @enderror">{{ old('item_description') }}</textarea>
                        @error('item_description')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-md text-sm font-semibold">
                            {{ __('Add to list') }}
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
</x-app-layout>
