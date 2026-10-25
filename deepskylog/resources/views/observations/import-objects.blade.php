<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-200 leading-tight">
            {{ __('Objects that are not in DeepskyLog') }}
        </h2>
    </x-slot>

    <div class="px-4 sm:px-6 lg:px-8 pb-6">
        <div class="overflow-hidden bg-gray-900 shadow-sm sm:rounded-lg p-6">

                @if ($errors->any())
                    <div class="mb-6 rounded-lg bg-red-900 border border-red-700 p-4">
                        <ul class="list-disc list-inside text-red-300 text-sm space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <p class="text-gray-300 mb-3">
                    {{ trans_choice('{1} :file holds observations of an object that is not in the DeepskyLog database.|[2,*] :file holds observations of :count objects that are not in the DeepskyLog database.', count($targets), ['file' => $fileName, 'count' => count($targets)]) }}
                    {{ __('Choose what to do with each of them: often it is an object DeepskyLog has under another name, such as one of the objects near its position. Add a new object only when it is not in DeepskyLog yet; the administrators check the objects that are added.') }}
                </p>
                <p class="text-gray-400 text-sm mb-6">
                    {{ __('Nothing is imported until you click Import. Your file is kept for :hours hours.', ['hours' => \App\Http\Controllers\ObservationImportController::KEEP_HOURS]) }}
                </p>

                <form method="POST" action="{{ route('observation.import.objects.store', $token) }}">
                    @csrf

                    <div class="space-y-6 mb-8">
                        @foreach ($targets as $i => $target)
                            @php
                                $default = $target['suggestion'] !== null ? 'object:' . $target['suggestion'] : 'new';
                                $selected = old("choice.{$i}", $default);
                            @endphp
                            <fieldset class="rounded-lg border border-gray-700 bg-gray-800 p-4">
                                <legend class="px-1 text-gray-100 font-semibold">{{ $target['name'] }}</legend>
                                <p class="text-gray-400 text-xs mb-3">
                                    {{ __($target['type_label']) }}
                                    · {{ \App\Models\DeepskyObject::formatRa($target['ra']) }} {{ \App\Models\DeepskyObject::formatDec($target['decl']) }}
                                    @if ($target['diameter'] !== null)
                                        · {{ __('diameter :size′', ['size' => round($target['diameter'], 1)]) }}
                                    @endif
                                    · {{ trans_choice('{1} 1 observation|[2,*] :count observations', $target['observations'], ['count' => $target['observations']]) }}
                                </p>

                                <div class="space-y-2 text-sm">
                                    @forelse ($target['nearby'] as $object)
                                        <label class="flex items-start space-x-3 cursor-pointer">
                                            <input type="radio" name="choice[{{ $i }}]" value="object:{{ $object['name'] }}"
                                                   class="mt-1 text-indigo-500 bg-gray-700 border-gray-600 focus:ring-indigo-500"
                                                   {{ $selected === 'object:' . $object['name'] ? 'checked' : '' }}>
                                            <span class="text-gray-200">
                                                {{ __('It is :name', ['name' => $object['name']]) }}
                                                <span class="text-gray-400">
                                                    ({{ __($object['type_label']) }}, {{ __(':distance′ away', ['distance' => $object['separation']]) }})
                                                </span>
                                                <a href="{{ route('object.show', ['slug' => $object['slug']]) }}" target="_blank" rel="noopener"
                                                   class="ml-1 text-indigo-400 hover:text-indigo-300 underline">{{ __('Show') }}</a>
                                                @if ($target['suggestion'] === $object['name'])
                                                    <span class="ml-1 text-xs text-green-400">{{ __('probably the same object') }}</span>
                                                @endif
                                            </span>
                                        </label>
                                    @empty
                                        <p class="text-gray-400">{{ __('DeepskyLog has no objects near its position.') }}</p>
                                    @endforelse

                                    <label class="flex items-start space-x-3 cursor-pointer">
                                        <input type="radio" name="choice[{{ $i }}]" value="other"
                                               class="mt-1 text-indigo-500 bg-gray-700 border-gray-600 focus:ring-indigo-500"
                                               {{ $selected === 'other' ? 'checked' : '' }}>
                                        <span class="flex flex-wrap items-center gap-2 text-gray-200">
                                            {{ __('It is another object:') }}
                                            <input type="text" name="other[{{ $i }}]" value="{{ old("other.{$i}") }}" placeholder="{{ __('NGC 2392, M 42, …') }}"
                                                   aria-label="{{ __('Name of the object :target is', ['target' => $target['name']]) }}"
                                                   class="w-48 rounded bg-gray-700 border-gray-600 text-gray-100 text-sm py-1 focus:ring-indigo-500 focus:border-indigo-500">
                                        </span>
                                    </label>

                                    <label class="flex items-start space-x-3 cursor-pointer">
                                        <input type="radio" name="choice[{{ $i }}]" value="new"
                                               class="mt-1 text-indigo-500 bg-gray-700 border-gray-600 focus:ring-indigo-500"
                                               {{ $selected === 'new' ? 'checked' : '' }}>
                                        <span class="text-gray-200">{{ __('Add :name to DeepskyLog as a new object', ['name' => $target['name']]) }}</span>
                                    </label>

                                    <label class="flex items-start space-x-3 cursor-pointer">
                                        <input type="radio" name="choice[{{ $i }}]" value="skip"
                                               class="mt-1 text-indigo-500 bg-gray-700 border-gray-600 focus:ring-indigo-500"
                                               {{ $selected === 'skip' ? 'checked' : '' }}>
                                        <span class="text-gray-200">{{ __('Do not import its observations') }}</span>
                                    </label>
                                </div>
                            </fieldset>
                        @endforeach
                    </div>

                    <div class="flex items-center space-x-4">
                        <button type="submit"
                                class="inline-flex items-center px-6 py-2 bg-indigo-700 hover:bg-indigo-600 text-white text-sm font-semibold rounded-lg
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors">
                            {{ __('Import') }}
                        </button>
                        <button type="submit" form="cancel-import"
                                class="text-sm text-gray-400 hover:text-gray-200 transition-colors">
                            {{ __('Cancel') }}
                        </button>
                    </div>
                </form>

                <form id="cancel-import" method="POST" action="{{ route('observation.import.cancel', $token) }}">
                    @csrf
                    @method('DELETE')
                </form>

        </div>
    </div>
</x-app-layout>
