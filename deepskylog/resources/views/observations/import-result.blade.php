<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-200 leading-tight">
            {{ __('Import Results') }}
        </h2>
    </x-slot>

    <div class="px-4 sm:px-6 lg:px-8 pb-6 space-y-6">

            {{-- Summary card --}}
            <div class="overflow-hidden bg-gray-900 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-100 mb-4">{{ __('Summary') }}</h3>

                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="rounded-lg bg-green-900 border border-green-700 p-4 text-center">
                        <div class="text-3xl font-bold text-green-300">{{ $result->imported }}</div>
                        <div class="text-sm text-green-400 mt-1">{{ __('Imported') }}</div>
                    </div>
                    <div class="rounded-lg bg-yellow-900 border border-yellow-700 p-4 text-center">
                        <div class="text-3xl font-bold text-yellow-300">{{ $result->skipped }}</div>
                        <div class="text-sm text-yellow-400 mt-1">{{ __('Skipped') }}</div>
                    </div>
                    <div class="rounded-lg {{ $result->errors > 0 ? 'bg-red-900 border-red-700' : 'bg-gray-800 border-gray-700' }} border p-4 text-center">
                        <div class="text-3xl font-bold {{ $result->errors > 0 ? 'text-red-300' : 'text-gray-400' }}">{{ $result->errors }}</div>
                        <div class="text-sm {{ $result->errors > 0 ? 'text-red-400' : 'text-gray-500' }} mt-1">{{ __('Errors') }}</div>
                    </div>
                </div>

                {{-- Drawings uploaded with the file --}}
                @if ($result->drawings > 0 || count($result->unusedDrawings) > 0)
                    <div class="rounded-lg bg-blue-950 border border-blue-800 p-4 mb-4">
                        <h4 class="text-blue-300 font-semibold mb-3">{{ __('Drawings') }}</h4>
                        <p class="text-blue-400 text-sm">
                            {{ trans_choice('{0} No drawings were added to the observations.|{1} 1 drawing was added to an observation.|[2,*] :count drawings were added to the observations.', $result->drawings, ['count' => $result->drawings]) }}
                        </p>
                        @if (count($result->unusedDrawings) > 0)
                            <p class="text-blue-400 text-sm mt-3 mb-1">
                                {{ __('These uploaded drawings are not named by an imported observation and were not imported:') }}
                            </p>
                            <ul class="space-y-0.5">
                                @foreach (array_slice($result->unusedDrawings, 0, 50) as $name)
                                    <li class="text-sm text-blue-300">{{ $name }}</li>
                                @endforeach
                                @if (count($result->unusedDrawings) > 50)
                                    <li class="text-sm text-blue-500">{{ __('and :count more', ['count' => count($result->unusedDrawings) - 50]) }}</li>
                                @endif
                            </ul>
                        @endif
                    </div>
                @endif

                {{-- New objects created from OAL targets --}}
                @if (count($result->newObjects) > 0)
                    <div class="rounded-lg bg-blue-950 border border-blue-800 p-4 mb-4">
                        <h4 class="text-blue-300 font-semibold mb-3">{{ __('New objects added') }}</h4>
                        <p class="text-blue-400 text-sm mb-3">
                            {{ __('These objects were not in the DeepskyLog database and were added from the targets in the file. Please check their details.') }}
                        </p>
                        <ul class="mt-1 space-y-0.5">
                            @foreach ($result->newObjects as $name => $slug)
                                <li class="text-sm text-blue-300">
                                    <a href="{{ route('object.show', ['slug' => $slug]) }}" class="underline hover:text-white">{{ $name }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Sessions from the OAL file --}}
                @if (count($result->sessions) > 0)
                    <div class="rounded-lg bg-blue-950 border border-blue-800 p-4 mb-4">
                        <h4 class="text-blue-300 font-semibold mb-3">{{ __('Sessions') }}</h4>
                        <p class="text-blue-400 text-sm mb-3">
                            {{ __('These sessions were imported from the file. A session holds your observations between its begin and end date. Please check their names and add the other observers.') }}
                        </p>
                        <ul class="mt-1 space-y-0.5">
                            @foreach ($result->sessions as $session)
                                <li class="text-sm text-blue-300">
                                    <a href="{{ route('session.show', [$userSlug, $session['slug']]) }}" class="underline hover:text-white">{{ html_entity_decode($session['name'], ENT_QUOTES | ENT_HTML5, 'UTF-8') }}</a>
                                    @unless ($session['new'])
                                        <span class="text-blue-500">({{ __('updated') }})</span>
                                    @endunless
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- New equipment created --}}
                @php
                    $hasNewEquipment = count($result->newLocations)
                        + count($result->newInstruments)
                        + count($result->newEyepieces)
                        + count($result->newFilters)
                        + count($result->newLenses) > 0;
                @endphp

                @if ($hasNewEquipment)
                    <div class="rounded-lg bg-blue-950 border border-blue-800 p-4 mb-4">
                        <h4 class="text-blue-300 font-semibold mb-3">{{ __('New equipment created') }}</h4>
                        <p class="text-blue-400 text-sm mb-3">
                            {{ __('The following items were created automatically. Please review and complete their details.') }}
                        </p>

                        @if (count($result->newLocations) > 0)
                            <div class="mb-2">
                                <span class="text-xs font-semibold uppercase tracking-wide text-blue-500">{{ __('Locations') }}</span>
                                <ul class="mt-1 space-y-0.5">
                                    @foreach ($result->newLocations as $name => $slug)
                                        <li class="text-sm text-blue-300">
                                            <a href="{{ route('location.edit', ['user' => $userSlug, 'location' => $slug]) }}" class="underline hover:text-white">{{ $name }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if (count($result->newInstruments) > 0)
                            <div class="mb-2">
                                <span class="text-xs font-semibold uppercase tracking-wide text-blue-500">{{ __('Instruments') }}</span>
                                <ul class="mt-1 space-y-0.5">
                                    @foreach ($result->newInstruments as $name => $slug)
                                        <li class="text-sm text-blue-300">
                                            <a href="{{ route('instrument.edit', ['user' => $userSlug, 'instrument' => $slug]) }}" class="underline hover:text-white">{{ $name }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if (count($result->newEyepieces) > 0)
                            <div class="mb-2">
                                <span class="text-xs font-semibold uppercase tracking-wide text-blue-500">{{ __('Eyepieces') }}</span>
                                <ul class="mt-1 space-y-0.5">
                                    @foreach ($result->newEyepieces as $name => $slug)
                                        <li class="text-sm text-blue-300">
                                            <a href="{{ route('eyepiece.edit', ['user' => $userSlug, 'eyepiece' => $slug]) }}" class="underline hover:text-white">{{ $name }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if (count($result->newFilters) > 0)
                            <div class="mb-2">
                                <span class="text-xs font-semibold uppercase tracking-wide text-blue-500">{{ __('Filters') }}</span>
                                <ul class="mt-1 space-y-0.5">
                                    @foreach ($result->newFilters as $name => $slug)
                                        <li class="text-sm text-blue-300">
                                            <a href="{{ route('filter.edit', ['user' => $userSlug, 'filter' => $slug]) }}" class="underline hover:text-white">{{ $name }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if (count($result->newLenses) > 0)
                            <div class="mb-2">
                                <span class="text-xs font-semibold uppercase tracking-wide text-blue-500">{{ __('Lenses / Barlows') }}</span>
                                <ul class="mt-1 space-y-0.5">
                                    @foreach ($result->newLenses as $name => $slug)
                                        <li class="text-sm text-blue-300">
                                            <a href="{{ route('lens.edit', ['user' => $userSlug, 'lens' => $slug]) }}" class="underline hover:text-white">{{ $name }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                    </div>
                @endif

                {{-- Action buttons --}}
                <div class="flex flex-wrap items-center gap-3 mt-4">
                    <a href="{{ route('observation.import') }}"
                       class="inline-flex items-center px-5 py-2 bg-indigo-700 hover:bg-indigo-600 text-white text-sm font-semibold rounded-lg transition-colors">
                        {{ __('Import another file') }}
                    </a>
                    <a href="{{ route('observations.index') }}"
                       class="inline-flex items-center px-5 py-2 bg-gray-700 hover:bg-gray-600 text-gray-200 text-sm font-semibold rounded-lg transition-colors">
                        {{ __('View my observations') }}
                    </a>
                </div>
            </div>

            {{-- Import log --}}
            @if (count($result->log) > 0)
                <div class="overflow-hidden bg-gray-900 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-100 mb-4">{{ __('Import log') }}</h3>

                    <div class="overflow-y-auto max-h-[32rem] space-y-1 pr-1">
                        @foreach ($result->log as $entry)
                            @php
                                $colors = match($entry['type']) {
                                    'success' => 'text-green-300',
                                    'warning' => 'text-yellow-300',
                                    'error'   => 'text-red-300',
                                    default   => 'text-gray-400',
                                };
                                $badge = match($entry['type']) {
                                    'success' => 'bg-green-800 text-green-200',
                                    'warning' => 'bg-yellow-800 text-yellow-200',
                                    'error'   => 'bg-red-800 text-red-200',
                                    default   => 'bg-gray-700 text-gray-300',
                                };
                            @endphp
                            <div class="flex items-start space-x-2 py-0.5">
                                <span class="flex-shrink-0 inline-block px-1.5 py-0.5 rounded text-xs font-mono font-semibold {{ $badge }}">
                                    {{ strtoupper($entry['type']) }}
                                </span>
                                <span class="text-sm font-mono {{ $colors }}">{{ $entry['message'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Imported observations table --}}
            @if (count($result->importedObservations) > 0)
                <div class="overflow-hidden bg-gray-900 shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-100 mb-4">{{ __('Imported observations') }}</h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm text-gray-300">
                            <thead>
                                <tr class="border-b border-gray-700">
                                    <th class="py-2 pr-4 text-left text-gray-400 font-medium">#</th>
                                    <th class="py-2 pr-4 text-left text-gray-400 font-medium">{{ __('Object') }}</th>
                                    <th class="py-2 text-left text-gray-400 font-medium">{{ __('Date') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-800">
                                @foreach ($result->importedObservations as $obs)
                                    <tr>
                                        <td class="py-1.5 pr-4 text-gray-500 text-xs">{{ $obs['row'] }}</td>
                                        <td class="py-1.5 pr-4">{{ $obs['object'] }}</td>
                                        <td class="py-1.5 text-gray-400">{{ $obs['date'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

    </div>
</x-app-layout>
