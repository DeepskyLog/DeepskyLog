<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-200 leading-tight">
            {{ __('Import Observations') }}
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

                <p class="text-gray-300 mb-6">
                    {{ __('Upload a CSV or OAL (OpenAstronomyLog) XML file to import your observations. Observations will be added to your account. Equipment (locations, instruments, eyepieces, filters, lenses) is matched by name. In a CSV file, it must already exist in your account; an OAL file creates the items that are not found. Every observation needs an instrument (use a naked-eye instrument for observations with the naked eye) and a description.') }}
                </p>

                <form method="POST" action="{{ route('observation.import.process') }}" enctype="multipart/form-data">
                    @csrf

                    {{-- Format selection --}}
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            {{ __('File format') }}
                        </label>
                        <div class="flex flex-col space-y-3 sm:flex-row sm:space-y-0 sm:space-x-8">

                            <label class="flex items-start space-x-3 cursor-pointer">
                                <input type="radio" name="format" value="csv"
                                       class="mt-1 text-indigo-500 bg-gray-700 border-gray-600 focus:ring-indigo-500"
                                       {{ old('format', 'csv') === 'csv' ? 'checked' : '' }}>
                                <span>
                                    <span class="block text-gray-100 font-medium">CSV</span>
                                    <span class="block text-gray-400 text-xs mt-0.5">
                                        {{ __('Semicolon-delimited, no header line.') }}<br>
                                        <code class="text-gray-500 text-xs">Object; Observer; Date(DD-MM-YYYY); Time(HH:MM); Location; Instrument; Eyepiece; Filter; Lens; Seeing(1–5); LimMag; SQM; Visibility(1–7); Language; Description</code>
                                    </span>
                                </span>
                            </label>

                            <label class="flex items-start space-x-3 cursor-pointer">
                                <input type="radio" name="format" value="oal"
                                       class="mt-1 text-indigo-500 bg-gray-700 border-gray-600 focus:ring-indigo-500"
                                       {{ old('format') === 'oal' ? 'checked' : '' }}>
                                <span>
                                    <span class="block text-gray-100 font-medium">OAL 2.1 (OpenAstronomyLog XML)</span>
                                    <span class="block text-gray-400 text-xs mt-0.5">
                                        {{ __('Standard XML format exported by AstroPlanner, SkySafari, and other astronomy software.') }}
                                    </span>
                                </span>
                            </label>

                        </div>
                    </div>

                    {{-- File input --}}
                    <div class="mb-8">
                        <label for="import_file" class="block text-sm font-medium text-gray-300 mb-2">
                            {{ __('File') }}
                        </label>
                        <input id="import_file" type="file" name="import_file" accept=".csv,.txt,.xml"
                               class="block w-full text-sm text-gray-300 bg-gray-800 border border-gray-600 rounded-lg cursor-pointer
                                      focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
                                      file:mr-4 file:py-2 file:px-4 file:rounded-l-lg file:border-0
                                      file:text-sm file:font-semibold file:bg-indigo-700 file:text-white
                                      hover:file:bg-indigo-600 p-1">
                        <p class="mt-1 text-xs text-gray-500">
                            {{ __('Accepted formats: .csv, .txt, .xml — Maximum 50 MB') }}
                        </p>
                    </div>

                    {{-- Objects below the horizon --}}
                    <div class="mb-8">
                        <label class="flex items-start space-x-3 cursor-pointer">
                            <input type="checkbox" name="import_below_horizon" value="1"
                                   class="mt-1 rounded text-indigo-500 bg-gray-700 border-gray-600 focus:ring-indigo-500"
                                   {{ old('import_below_horizon') ? 'checked' : '' }}>
                            <span>
                                <span class="block text-gray-100 font-medium">{{ __('Also import observations of objects below the horizon') }}</span>
                                <span class="block text-gray-400 text-xs mt-0.5">
                                    {{ __('Observations of an object that was below the horizon of the location at the date and time given are skipped, as the date, the time or the location is probably wrong. Tick this to import them anyway.') }}
                                </span>
                            </span>
                        </label>
                    </div>

                    {{-- Submit --}}
                    <div class="flex items-center space-x-4">
                        <button type="submit"
                                class="inline-flex items-center px-6 py-2 bg-indigo-700 hover:bg-indigo-600 text-white text-sm font-semibold rounded-lg
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors">
                            {{ __('Import') }}
                        </button>
                        <a href="{{ route('observations.index') }}"
                           class="text-sm text-gray-400 hover:text-gray-200 transition-colors">
                            {{ __('Cancel') }}
                        </a>
                    </div>

                </form>

                {{-- CSV format reference --}}
                <div class="mt-10 border-t border-gray-700 pt-6">
                    <h3 class="text-gray-200 font-semibold mb-3">{{ __('CSV format reference') }}</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-xs text-gray-300">
                            <thead>
                                <tr class="border-b border-gray-700">
                                    <th class="py-1 pr-4 text-left text-gray-400 font-medium">#</th>
                                    <th class="py-1 pr-4 text-left text-gray-400 font-medium">{{ __('Field') }}</th>
                                    <th class="py-1 pr-4 text-left text-gray-400 font-medium">{{ __('Required') }}</th>
                                    <th class="py-1 text-left text-gray-400 font-medium">{{ __('Notes') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-800">
                                <tr><td class="py-1 pr-4">1</td><td class="py-1 pr-4">{{ __('Object name') }}</td><td class="py-1 pr-4">✓</td><td class="py-1">{{ __('NGC 2392, M 42, …') }}</td></tr>
                                <tr><td class="py-1 pr-4">2</td><td class="py-1 pr-4">{{ __('Observer') }}</td><td></td><td class="py-1">{{ __('Informational only; logged-in user is the owner') }}</td></tr>
                                <tr><td class="py-1 pr-4">3</td><td class="py-1 pr-4">{{ __('Date') }}</td><td class="py-1 pr-4">✓</td><td class="py-1">DD-MM-YYYY</td></tr>
                                <tr><td class="py-1 pr-4">4</td><td class="py-1 pr-4">{{ __('UT Time') }}</td><td></td><td class="py-1">HH:MM</td></tr>
                                <tr><td class="py-1 pr-4">5</td><td class="py-1 pr-4">{{ __('Location') }}</td><td></td><td class="py-1">{{ __('Must exist in your account (optional)') }}</td></tr>
                                <tr><td class="py-1 pr-4">6</td><td class="py-1 pr-4">{{ __('Instrument') }}</td><td class="py-1 pr-4">✓</td><td class="py-1">{{ __('Must exist in your account; your naked-eye instrument for observations with the naked eye') }}</td></tr>
                                <tr><td class="py-1 pr-4">7</td><td class="py-1 pr-4">{{ __('Eyepiece') }}</td><td></td><td class="py-1">{{ __('Must exist in your account (optional)') }}</td></tr>
                                <tr><td class="py-1 pr-4">8</td><td class="py-1 pr-4">{{ __('Filter') }}</td><td></td><td class="py-1">{{ __('Must exist in your account (optional)') }}</td></tr>
                                <tr><td class="py-1 pr-4">9</td><td class="py-1 pr-4">{{ __('Lens / Barlow') }}</td><td></td><td class="py-1">{{ __('Must exist in your account (optional)') }}</td></tr>
                                <tr><td class="py-1 pr-4">10</td><td class="py-1 pr-4">{{ __('Seeing') }}</td><td></td><td class="py-1">{{ __('1–5 (Antoniadi)') }}</td></tr>
                                <tr><td class="py-1 pr-4">11</td><td class="py-1 pr-4">{{ __('Lim. magnitude') }}</td><td></td><td class="py-1">{{ __('Float; 0 = not measured') }}</td></tr>
                                <tr><td class="py-1 pr-4">12</td><td class="py-1 pr-4">{{ __('SQM') }}</td><td></td><td class="py-1">{{ __('Float; 0 = not measured') }}</td></tr>
                                <tr><td class="py-1 pr-4">13</td><td class="py-1 pr-4">{{ __('Visibility') }}</td><td></td><td class="py-1">{{ __('1–7') }}</td></tr>
                                <tr><td class="py-1 pr-4">14</td><td class="py-1 pr-4">{{ __('Language') }}</td><td></td><td class="py-1">{{ __('ISO code, e.g. nl, en, de') }}</td></tr>
                                <tr><td class="py-1 pr-4">15</td><td class="py-1 pr-4">{{ __('Description') }}</td><td class="py-1 pr-4">✓</td><td class="py-1">{{ __('Free text') }}</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <p class="mt-3 text-xs text-gray-500">
                        {{ __('Example:') }}<br>
                        <code class="text-gray-400">NGC 2392;Piet Janssens;21-01-2005;20:45;Aalst;Obsession 15";31mm Nagler;Lumicon O-III filter;Televue 2x Barlow;2;6.5;0;3;nl;Mooie planetaire nevel met een zeer heldere centrale ster!</code>
                    </p>
                </div>

        </div>
    </div>
</x-app-layout>
