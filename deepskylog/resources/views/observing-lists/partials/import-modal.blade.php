<div x-data="{ open: {{ $errors->has('file') ? 'true' : 'false' }} }" x-cloak>
    {{-- Import button --}}
    <button @click="open = true"
        class="inline-flex items-center gap-2 px-3 py-1.5 bg-green-700 hover:bg-green-600 text-white rounded text-xs font-semibold transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        {{ __('Import objects') }}
    </button>

    {{-- Modal --}}
    <div x-show="open" x-transition class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click.self="open = false">
        <div class="bg-gray-800 rounded-lg border border-gray-700 w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto">
            {{-- Modal header --}}
            <div class="sticky top-0 bg-gray-800 border-b border-gray-700 px-6 py-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-white">{{ __('Import objects') }}</h3>
                <button @click="open = false" class="text-gray-400 hover:text-gray-200 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Modal content --}}
            <div class="p-6 space-y-4">
                <div>
                    <p class="text-gray-300 text-sm mb-4">
                        {{ __('Select a file to import objects. Supported formats:') }}
                    </p>
                    <ul class="text-gray-400 text-xs space-y-1 ml-4 mb-4">
                        <li>• {{ __('Argo Navis (.argo)') }}</li>
                        <li>• {{ __('SkySafari (.skylist)') }}</li>
                        <li>• {{ __('SkyTools (.txt)') }}</li>
                        <li>• {{ __('AstroPlanner (.apd)') }}</li>
                        <li>• {{ __('CSV (object name in first column)') }}</li>
                    </ul>
                </div>

                <form method="POST" action="{{ route('observing-list.import', $list) }}" enctype="multipart/form-data">
                    @csrf

                    <div class="space-y-4">
                        <div>
                            <label for="import_file" class="block text-sm font-medium text-gray-300 mb-2">
                                {{ __('Select file') }}
                            </label>
                            <input 
                                type="file" 
                                id="import_file"
                                name="file"
                                accept=".argo,.txt,.skylist,.apd,.csv"
                                required
                                class="block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-500 cursor-pointer"
                            />
                            @error('file')
                                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center gap-2 justify-end pt-2">
                            <button
                                type="button"
                                @click="open = false"
                                class="px-4 py-2 text-sm font-medium text-gray-300 bg-gray-700 hover:bg-gray-600 rounded transition"
                            >
                                {{ __('Cancel') }}
                            </button>
                            <button
                                type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-green-700 hover:bg-green-600 rounded transition"
                            >
                                {{ __('Import') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
