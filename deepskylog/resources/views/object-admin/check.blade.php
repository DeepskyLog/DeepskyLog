<x-app-layout>
    <div>
        <div class="max-w-screen mx-auto bg-gray-900 px-2 py-10 sm:px-6 lg:px-8">
            <h2 class="text-xl font-semibold leading-tight">
                {{ __('Check Objects') }}
            </h2>
            <p class="mt-2 text-sm text-gray-300">
                {{ __('Checks object constellations, orphan object names, and observations that still reference unknown objects.') }}
            </p>
        </div>
    </div>

    <div class="mt-2 space-y-6">
        @if (session('success'))
            <x-card>
                <p class="text-sm text-green-300">{{ session('success') }}</p>
            </x-card>
        @endif

        <x-card>
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-white">{{ __('Objects constellation') }}</h3>
                    <p class="mt-1 text-sm text-gray-300">
                        {{ __('Checked :checked of :total objects. Correct: :correct. Skipped: :skipped.', ['checked' => $report['constellation_check']['checked'], 'total' => $report['constellation_check']['total'], 'correct' => $report['constellation_check']['correct'], 'skipped' => $report['constellation_check']['skipped']]) }}
                    </p>
                    @if ($report['constellation_check']['note'])
                        <p class="mt-2 text-xs text-amber-400">
                            {{ $report['constellation_check']['note'] }}
                        </p>
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    <span class="rounded bg-gray-800 px-3 py-1 text-sm text-gray-200">
                        {{ trans_choice('{0} No mismatches|{1} 1 mismatch|[2,*] :count mismatches', $report['constellation_check']['mismatch_count'], ['count' => $report['constellation_check']['mismatch_count']]) }}
                    </span>
                    @if ($report['constellation_check']['mismatch_count'] > 0)
                        <form method="POST" action="{{ route('admin.objects.check.repair') }}" class="inline">
                            @csrf
                            <x-button type="submit" class="whitespace-nowrap" onclick="return confirm('{{ __('Repair all constellation mismatches? This will update the constellation field for affected objects.') }}')">{{ __('Repair') }}</x-button>
                        </form>
                        <a href="{{ route('admin.objects.check.export-constellations') }}" class="inline-flex items-center rounded bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700">
                            {{ __('Export') }}
                        </a>
                    @endif
                </div>
            </div>

            @if ($report['constellation_check']['mismatch_count'] > 0)
                @if ($report['constellation_check']['mismatch_count'] > $report['constellation_check']['display_limit'])
                    <p class="mt-4 text-sm text-amber-300">
                        {{ __('Showing the first :limit mismatches.', ['limit' => $report['constellation_check']['display_limit']]) }}
                    </p>
                @endif

                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-700 text-sm text-gray-200">
                        <thead class="bg-gray-800 text-left text-xs uppercase tracking-wide text-gray-400">
                            <tr>
                                <th class="px-3 py-2">{{ __('Object') }}</th>
                                <th class="px-3 py-2">{{ __('Stored') }}</th>
                                <th class="px-3 py-2">{{ __('Expected') }}</th>
                                <th class="px-3 py-2">{{ __('RA') }}</th>
                                <th class="px-3 py-2">{{ __('Declination') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                            @foreach ($report['constellation_check']['examples'] as $row)
                                <tr>
                                    <td class="px-3 py-2">{{ $row['name'] }}</td>
                                    <td class="px-3 py-2">
                                        <span>{{ $row['stored_code'] ?: '—' }}</span>
                                        @if ($row['stored_name'])
                                            <span class="text-gray-400">({{ $row['stored_name'] }})</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2">
                                        <span>{{ $row['expected_code'] ?: '—' }}</span>
                                        @if ($row['expected_name'])
                                            <span class="text-gray-400">({{ $row['expected_name'] }})</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2">{{ $row['ra'] ?: '—' }}</td>
                                    <td class="px-3 py-2">{{ $row['decl'] ?: '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="mt-4 text-sm text-green-300">{{ __('All checked objects have the expected constellation.') }}</p>
            @endif
        </x-card>

        <x-card>
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-white">{{ __('Objects and object names') }}</h3>
                    <p class="mt-1 text-sm text-gray-300">
                        {{ trans_choice('{0} No orphan object names found.|{1} 1 orphan object name found.|[2,*] :count orphan object names found.', $report['orphan_objectnames']['count'], ['count' => $report['orphan_objectnames']['count']]) }}
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <form method="POST" action="{{ route('admin.objects.check.cleanup') }}" class="inline">
                        @csrf
                        @if ($report['orphan_objectnames']['count'] === 0)
                            <x-button type="submit" class="whitespace-nowrap" disabled>
                                {{ __('Delete orphan object names') }}
                            </x-button>
                        @else
                            <x-button type="submit" class="whitespace-nowrap">
                                {{ __('Delete orphan object names') }}
                            </x-button>
                        @endif
                    </form>
                    @if ($report['orphan_objectnames']['count'] > 0)
                        <a href="{{ route('admin.objects.check.export-orphans') }}" class="inline-flex items-center rounded bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700">
                            {{ __('Export') }}
                        </a>
                    @endif
                </div>
            </div>

            @if ($report['orphan_objectnames']['count'] > 0)
                @if ($report['orphan_objectnames']['count'] > $report['orphan_objectnames']['display_limit'])
                    <p class="mt-4 text-sm text-amber-300">
                        {{ __('Showing the first :limit orphan object names.', ['limit' => $report['orphan_objectnames']['display_limit']]) }}
                    </p>
                @endif

                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-700 text-sm text-gray-200">
                        <thead class="bg-gray-800 text-left text-xs uppercase tracking-wide text-gray-400">
                            <tr>
                                <th class="px-3 py-2">{{ __('Object') }}</th>
                                <th class="px-3 py-2">{{ __('Catalog') }}</th>
                                <th class="px-3 py-2">{{ __('Index') }}</th>
                                <th class="px-3 py-2">{{ __('Alternative name') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                            @foreach ($report['orphan_objectnames']['examples'] as $row)
                                <tr>
                                    <td class="px-3 py-2">{{ $row->objectname }}</td>
                                    <td class="px-3 py-2">{{ $row->catalog }}</td>
                                    <td class="px-3 py-2">{{ $row->catindex }}</td>
                                    <td class="px-3 py-2">{{ $row->altname }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-card>

        <x-card>
            <div>
                <div class="flex items-start justify-between gap-4">
                    <h3 class="text-lg font-semibold text-white">{{ __('Observations on unknown objects') }}</h3>

                    <div class="flex items-center gap-2">
                        @if ($report['unknown_observations']['available'] && $report['unknown_observations']['alias_count'] > 0)
                            <form method="POST" action="{{ route('admin.objects.check.repair-observation-objectnames') }}" class="inline">
                                @csrf
                                <x-button type="submit" class="whitespace-nowrap" onclick="return confirm('{{ __('Update observation object names to their primary object names for all known aliases?') }}')">{{ __('Fix aliases in observations') }}</x-button>
                            </form>
                            <a href="{{ route('admin.objects.check.export-observation-alias-mappings') }}" class="inline-flex items-center rounded bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700">
                                {{ __('Export alias mappings') }}
                            </a>
                        @endif
                    </div>
                </div>

                @if (! $report['unknown_observations']['available'])
                    <p class="mt-2 text-sm text-amber-300">
                        {{ __('This check is unavailable locally: :message', ['message' => $report['unknown_observations']['message']]) }}
                    </p>
                @else
                    <p class="mt-1 text-sm text-gray-300">
                        {{ trans_choice('{0} No unknown observation targets found.|{1} 1 unknown observation target found.|[2,*] :count unknown observation targets found.', $report['unknown_observations']['count'], ['count' => $report['unknown_observations']['count']]) }}
                        {{ __('Total affected observations: :count.', ['count' => $report['unknown_observations']['observation_total']]) }}
                        {{ trans_choice('{0} No alias-based targets found.|{1} 1 alias-based target can be fixed.|[2,*] :count alias-based targets can be fixed.', $report['unknown_observations']['alias_count'], ['count' => $report['unknown_observations']['alias_count']]) }}
                        {{ __('Alias-based observations: :count.', ['count' => $report['unknown_observations']['alias_observation_total']]) }}
                    </p>

                    @if (($report['unknown_observations']['count'] + $report['unknown_observations']['alias_count']) > 0)
                        @if (($report['unknown_observations']['count'] + $report['unknown_observations']['alias_count']) > $report['unknown_observations']['display_limit'])
                            <p class="mt-4 text-sm text-amber-300">
                                {{ __('Showing the first :limit unknown or alias observation targets.', ['limit' => $report['unknown_observations']['display_limit']]) }}
                            </p>
                        @endif

                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-700 text-sm text-gray-200">
                                <thead class="bg-gray-800 text-left text-xs uppercase tracking-wide text-gray-400">
                                    <tr>
                                        <th class="px-3 py-2">{{ __('Observed object name') }}</th>
                                        <th class="px-3 py-2">{{ __('Primary object name') }}</th>
                                        <th class="px-3 py-2">{{ __('Status') }}</th>
                                        <th class="px-3 py-2">{{ __('Observation count') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-800">
                                    @foreach ($report['unknown_observations']['examples'] as $row)
                                        <tr>
                                            <td class="px-3 py-2">{{ $row['objectname'] }}</td>
                                            <td class="px-3 py-2">{{ $row['primary_name'] ?: '—' }}</td>
                                            <td class="px-3 py-2">
                                                @if ($row['status'] === 'alias')
                                                    <span class="text-green-300">{{ __('Alias (fixable)') }}</span>
                                                @else
                                                    <span class="text-amber-300">{{ __('Unknown') }}</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2">{{ $row['observation_count'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="mt-4 text-sm text-green-300">{{ __('No unknown or alias-based observation targets were found.') }}</p>
                    @endif
                @endif
            </div>
        </x-card>
    </div>
</x-app-layout>