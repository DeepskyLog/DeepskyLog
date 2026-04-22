<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-200 leading-tight">{{ __('Edit observing list') }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
            <div class="bg-gray-900 shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('observing-list.update', $list) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-300 mb-1">{{ __('Name') }} <span class="text-red-400">*</span></label>
                        <input type="text" id="name" name="name" maxlength="255" required
                               value="{{ old('name', $list->name) }}"
                               class="w-full bg-gray-800 border border-gray-600 rounded-md text-gray-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror">
                        @error('name')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-gray-300 mb-1">{{ __('Description') }}</label>
                        <textarea id="description" name="description" rows="4" maxlength="5000"
                                  class="w-full bg-gray-800 border border-gray-600 rounded-md text-gray-100 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-y @error('description') border-red-500 @enderror">{{ old('description', $list->description) }}</textarea>
                        @error('description')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="hidden" name="public" value="0">
                            <input type="checkbox" name="public" value="1"
                                   {{ old('public', $list->public) ? 'checked' : '' }}
                                   class="rounded border-gray-600 bg-gray-800 text-blue-600 focus:ring-blue-500">
                            <div>
                                <span class="text-sm font-medium text-gray-300">{{ __('Make public') }}</span>
                                <p class="text-xs text-gray-500">{{ __('Public lists can be discovered and subscribed to by other observers.') }}</p>
                            </div>
                        </label>
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-md text-sm font-semibold">
                            {{ __('Save changes') }}
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
