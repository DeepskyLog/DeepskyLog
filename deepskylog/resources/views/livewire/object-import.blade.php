@php
    $input = 'block w-full rounded border border-gray-600 bg-gray-900 px-3 py-2 text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500';
    $labels = [
        'differences' => __('Differences'),
        'recognised' => __('Recognised'),
        'duplicate' => __('Possible duplicates'),
        'new' => __('New'),
        'invalid' => __('Invalid'),
    ];
    $fields = [
        'position' => __('Position'),
        'type' => __('Type'),
        'mag' => __('Magnitude'),
        'subr' => __('Surface brightness'),
        'diam1' => __('Major axis'),
        'diam2' => __('Minor axis'),
        'pa' => __('Position angle'),
    ];
@endphp

<div class="space-y-6 text-sm text-gray-200">
    @if ($applied !== null)
        <div class="rounded-md border border-green-700 bg-green-900/40 px-4 py-3">
            <p class="font-medium text-white">{{ __('The file is imported.') }}</p>
            <p class="mt-1">
                {{ trans_choice(':count object added|:count objects added', $applied['created'], ['count' => $applied['created']]) }},
                {{ trans_choice(':count alternative name added|:count alternative names added', $applied['names'], ['count' => $applied['names']]) }},
                {{ trans_choice(':count object updated|:count objects updated', $applied['updated'], ['count' => $applied['updated']]) }},
                {{ trans_choice(':count row skipped|:count rows skipped', $applied['skipped'], ['count' => $applied['skipped']]) }}.
            </p>
        </div>
    @endif

    @if ($checked === null)
        @if ($expired)
            <p class="rounded-md border border-yellow-700 bg-yellow-900/30 px-4 py-3">{{ __('The checked file is no longer available. Check it again.') }}</p>
        @endif
        <form method="POST" action="{{ route('admin.objects.import.check') }}" enctype="multipart/form-data" class="space-y-4 rounded-md bg-gray-800 p-4">
            @csrf
            <div>
                <label for="object-import-file" class="mb-1 block font-medium text-gray-300">{{ __('CSV file') }}</label>
                <input id="object-import-file" type="file" name="file" required accept=".csv,.txt,text/csv,text/plain" class="block text-gray-200" />
                @error('file') <p class="mt-1 text-red-400">{{ $message }}</p> @enderror
            </div>

            <div class="text-gray-400">
                <p>{{ __('The first line names the columns, separated by commas, semicolons or tabs, in any order. Only name is needed; a new object also needs type, ra and decl.') }}</p>
                <ul class="mt-1 list-inside list-disc">
                    <li><code>name</code>, <code>alternative_names</code> ({{ __('separated by ; or |') }})</li>
                    <li><code>type</code>: {{ __('the DeepskyLog type code, such as GALXY, OPNCL, PLNNB or EMINB') }}</li>
                    <li><code>ra</code> ({{ __('hours: 5.5875, 05 35 15, 05:35:15 or 05h35m15s; a number above 24 is degrees') }}), <code>decl</code> ({{ __('degrees: -5.39, -05 23 28 or -05°23\'28"') }})</li>
                    <li><code>mag</code>, <code>subr</code> ({{ __('magnitude per square arcminute') }}), <code>diam1</code>, <code>diam2</code> ({{ __('arcminutes') }}), <code>pa</code> ({{ __('degrees') }})</li>
                </ul>
                <p class="mt-1">{{ __('At most :max objects per file.', ['max' => \App\Services\Import\ObjectCsvImport::MAX_ROWS]) }}</p>
            </div>

            <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                @foreach ([
                    'position' => __('Position tolerance (arcseconds)'),
                    'magnitude' => __('Magnitude tolerance'),
                    'size' => __('Size tolerance (%)'),
                    'duplicate' => __('Duplicate distance (arcseconds)'),
                ] as $setting => $label)
                    <div>
                        <label for="object-import-{{ $setting }}" class="mb-1 block text-gray-400">{{ $label }}</label>
                        <input id="object-import-{{ $setting }}" name="{{ $setting }}" type="number" step="any" min="0" required
                            value="{{ old($setting, $settings[$setting]) }}" class="{{ $input }}" />
                        @error($setting) <p class="mt-1 text-red-400">{{ $message }}</p> @enderror
                    </div>
                @endforeach
            </div>
            <p class="text-gray-400">{{ __('A new name is a possible duplicate when an object of the same type lies within the duplicate distance, or overlaps it.') }}</p>

            <button type="submit" class="rounded bg-blue-600 px-4 py-2 font-medium text-white hover:bg-blue-700">
                {{ __('Check the file') }}
            </button>
        </form>
    @else
        <div class="flex flex-wrap items-center gap-3">
            <span class="font-medium text-white">{{ $checked['file'] }}</span>
            <a href="{{ route('admin.objects.import.report', ['token' => $token]) }}" class="rounded bg-blue-600 px-3 py-1.5 font-medium text-white hover:bg-blue-700">{{ __('Download the report') }}</a>
            <button type="button" wire:click="startOver" class="rounded px-3 py-1.5 text-gray-300 hover:bg-gray-700 hover:text-white">{{ __('Check another file') }}</button>
        </div>

        <div class="flex flex-wrap gap-2">
            @foreach ($labels as $status => $label)
                <button type="button" wire:click="$set('show', '{{ $status }}')"
                    class="rounded-full px-3 py-1 {{ $show === $status ? 'bg-blue-600 text-white' : 'bg-gray-800 text-gray-300 hover:bg-gray-700' }}">
                    {{ $label }} ({{ $counts[$status] }})
                </button>
            @endforeach
        </div>
        <p class="text-gray-400">
            @switch($show)
                @case('differences') {{ __('Objects DeepskyLog has, whose fields differ from the file beyond the tolerances, or with names in the file DeepskyLog does not know.') }} @break
                @case('recognised') {{ __('All objects of the file DeepskyLog has, by their name or an alternative name.') }} @break
                @case('duplicate') {{ __('Names DeepskyLog does not know, of an object of the same type at the same position: probably another name of that object.') }} @break
                @case('new') {{ __('Objects DeepskyLog does not have.') }} @break
                @case('invalid') {{ __('Rows that are not imported.') }} @break
            @endswitch
        </p>

        <div class="overflow-x-auto rounded-md bg-gray-800">
            <table class="min-w-full divide-y divide-gray-700">
                <thead class="text-left text-gray-400">
                    <tr>
                        <th class="px-3 py-2 font-medium">{{ __('Line') }}</th>
                        <th class="px-3 py-2 font-medium">{{ __('Name') }}</th>
                        <th class="px-3 py-2 font-medium">{{ __('Details') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @forelse ($shown as $row)
                        <tr wire:key="object-import-row-{{ $row['line'] }}" class="align-top">
                            <td class="px-3 py-2 text-gray-400">{{ $row['line'] }}</td>
                            <td class="px-3 py-2">
                                <span class="font-medium text-white">{{ $row['name'] }}</span>
                                @if ($row['object'] !== null && mb_strtolower($row['object']) !== mb_strtolower($row['name']))
                                    <span class="text-gray-400">&rarr;</span>
                                    <a href="{{ route('object.show', $row['slug']) }}" class="text-blue-400 hover:underline" target="_blank">{{ $row['object'] }}</a>
                                @elseif ($row['object'] !== null)
                                    <a href="{{ route('object.show', $row['slug']) }}" class="text-blue-400 hover:underline" target="_blank">&nearr;</a>
                                @endif
                                @if ($row['values']['type'] !== '' && $row['object'] === null)
                                    <div class="text-gray-400">{{ $types[$row['values']['type']] ?? $row['values']['type'] }}</div>
                                @endif
                            </td>
                            <td class="space-y-1 px-3 py-2">
                                @foreach ($row['errors'] as $error)
                                    <div class="text-red-400">{{ $error }}</div>
                                @endforeach
                                @foreach ($row['warnings'] as $warning)
                                    <div class="text-yellow-400">{{ $warning }}</div>
                                @endforeach
                                @if ($row['differences'] !== [])
                                    <table class="text-gray-300">
                                        <tr class="text-gray-400">
                                            <td class="pr-4"></td>
                                            <td class="pr-4">{{ __('File') }}</td>
                                            <td class="pr-4">DeepskyLog</td>
                                            <td></td>
                                        </tr>
                                        @foreach ($row['differences'] as $field => $difference)
                                            <tr>
                                                <td class="pr-4 text-gray-400">{{ $fields[$field] ?? $field }}</td>
                                                <td class="pr-4">{{ $difference['file'] }}</td>
                                                <td class="pr-4">{{ $difference['deepskylog'] }}</td>
                                                <td class="text-yellow-300">{{ $difference['difference'] }}</td>
                                            </tr>
                                        @endforeach
                                    </table>
                                @endif
                                @if ($row['names'] !== [] && $row['status'] === 'recognised')
                                    <div>{{ __('New names:') }} {{ implode(', ', $row['names']) }}</div>
                                @endif
                                @if ($row['suggestion'] !== null)
                                    <div>{{ __('Probably:') }} <span class="font-medium text-white">{{ $row['suggestion'] }}</span></div>
                                @endif
                                @if ($row['nearby'] !== [])
                                    <div class="text-gray-400">
                                        {{ __('Nearby:') }}
                                        @foreach ($row['nearby'] as $object)
                                            <a href="{{ route('object.show', $object['slug']) }}" class="text-blue-400 hover:underline" target="_blank">{{ $object['name'] }}</a>
                                            ({{ $object['type_label'] }}, {{ $object['separation'] }}'){{ $loop->last ? '' : ',' }}
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-3 py-2 text-gray-400">{{ __('None.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($more > 0)
            <p class="text-gray-400">{{ __('And :count more: download the report to see all of them.', ['count' => $more]) }}</p>
        @endif

        <form wire:submit="import" class="space-y-3 rounded-md bg-gray-800 p-4">
            <h2 class="font-semibold text-white">{{ __('Import') }}</h2>
            <label class="flex items-center gap-2">
                <input type="checkbox" wire:model="create" class="rounded border-gray-600 bg-gray-900" />
                {{ trans_choice('Add the :count new object|Add the :count new objects', $counts['new'], ['count' => $counts['new']]) }}
            </label>
            <div class="flex flex-wrap items-center gap-2">
                <label for="object-import-duplicates">{{ __('Possible duplicates (:count):', ['count' => $counts['duplicate']]) }}</label>
                <select id="object-import-duplicates" wire:model="duplicates" class="rounded border border-gray-600 bg-gray-900 px-2 py-1 text-gray-100">
                    <option value="skip">{{ __('skip them') }}</option>
                    <option value="alias">{{ __('add their names to the object they probably are') }}</option>
                    <option value="create">{{ __('add them as new objects') }}</option>
                </select>
            </div>
            <label class="flex items-center gap-2">
                <input type="checkbox" wire:model="names" class="rounded border-gray-600 bg-gray-900" />
                {{ __('Add the names of the file to the recognised objects that do not have them') }}
            </label>
            <label class="flex items-center gap-2">
                <input type="checkbox" wire:model="update" class="rounded border-gray-600 bg-gray-900" />
                {{ __('Replace the fields of the recognised objects that differ from the file') }}
            </label>
            <div class="max-w-md">
                <label for="object-import-datasource" class="mb-1 block text-gray-400">{{ __('Data source of the new objects') }}</label>
                <input id="object-import-datasource" type="text" maxlength="50" wire:model="datasource" class="{{ $input }}" />
                @error('datasource') <p class="mt-1 text-red-400">{{ $message }}</p> @enderror
            </div>
            <button type="submit" wire:loading.attr="disabled"
                wire:confirm="{{ __('Import the checked file into the object database?') }}"
                class="rounded bg-green-700 px-4 py-2 font-medium text-white hover:bg-green-600 disabled:opacity-50">
                {{ __('Import') }}
            </button>
        </form>
    @endif
</div>
