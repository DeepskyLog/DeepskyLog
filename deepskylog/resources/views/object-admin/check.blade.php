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

        @php
            $check = $constellationCheck;
            $checkState = $check['state'] ?? null;
            $checkComplete = $checkState === 'complete';
        @endphp
        <x-card>
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-white">{{ __('Objects constellation') }}</h3>
                    <p class="mt-1 text-sm text-gray-300">
                        @if ($constellationCheckInProgress && $checkState === 'queued')
                            {{ __('The check of all objects is waiting for the queue worker (queued :time).', ['time' => \Illuminate\Support\Carbon::parse($check['queued_at'])->diffForHumans()]) }}
                        @elseif ($constellationCheckInProgress)
                            {{ __('Checking all objects: :checked of :total done.', ['checked' => $check['checked'], 'total' => $check['total']]) }}
                        @elseif ($checkComplete)
                            {{ __('Checked :checked of :total objects. Correct: :correct. Skipped: :skipped.', ['checked' => $check['checked'], 'total' => $check['total'], 'correct' => $check['checked'] - $check['skipped'] - $check['mismatch_count'] + $check['repaired'], 'skipped' => $check['skipped']]) }}
                            {{ __('Last checked :time.', ['time' => \Illuminate\Support\Carbon::parse($check['finished_at'])->diffForHumans()]) }}
                        @elseif ($checkState !== null)
                            <span class="text-red-400">{{ __('The last constellation check did not finish.') }}</span>
                        @else
                            {{ __('The constellations of the objects have not been checked yet.') }}
                        @endif
                    </p>
                    @if ($constellationCheckInProgress)
                        <p class="mt-2 text-xs text-amber-400">
                            {{ __('The check takes a few minutes and needs a worker on the long-running queue (php artisan queue:work database-long). This page reloads by itself.') }}
                        </p>
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    @if ($checkComplete)
                        <span class="rounded bg-gray-800 px-3 py-1 text-sm text-gray-200">
                            @if ($check['repair'])
                                {{ trans_choice('{0} No constellation mismatches were corrected.|{1} Corrected 1 constellation mismatch.|[2,*] Corrected :count constellation mismatches.', $check['repaired'], ['count' => $check['repaired']]) }}
                            @else
                                {{ trans_choice('{0} No mismatches|{1} 1 mismatch|[2,*] :count mismatches', $check['mismatch_count'], ['count' => $check['mismatch_count']]) }}
                            @endif
                        </span>
                    @endif
                    @unless ($constellationCheckInProgress)
                        <form method="POST" action="{{ route('admin.objects.check.constellations') }}" class="inline">
                            @csrf
                            <x-button type="submit" class="whitespace-nowrap">{{ $checkComplete ? __('Check again') : __('Check all objects') }}</x-button>
                        </form>
                        @if ($checkComplete && ! $check['repair'] && $check['mismatch_count'] > 0)
                            <form method="POST" action="{{ route('admin.objects.check.repair') }}" class="inline">
                                @csrf
                                <x-button type="submit" class="whitespace-nowrap" onclick="return confirm('{{ __('Repair all constellation mismatches? This will update the constellation field for affected objects.') }}')">{{ __('Repair') }}</x-button>
                            </form>
                        @endif
                        @if ($checkComplete && $check['mismatch_count'] > 0)
                            <a href="{{ route('admin.objects.check.export-constellations') }}" class="inline-flex items-center rounded bg-blue-600 px-3 py-2 text-sm font-medium text-white hover:bg-blue-700">
                                {{ __('Export') }}
                            </a>
                        @endif
                    @endunless
                </div>
            </div>

            @if ($constellationCheckInProgress)
                <script>
                    setTimeout(() => window.location.reload(), 10000);
                </script>
            @elseif ($checkComplete && $check['mismatch_count'] > 0)
                @if ($check['repair'])
                    <p class="mt-4 text-sm text-green-300">{{ __('These objects had the wrong constellation and have been corrected.') }}</p>
                @endif
                @if ($check['mismatch_count'] > $check['display_limit'])
                    <p class="mt-4 text-sm text-amber-300">
                        {{ __('Showing the first :limit mismatches.', ['limit' => $check['display_limit']]) }}
                    </p>
                @endif

                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-700 text-sm text-gray-200">
                        <thead class="bg-gray-800 text-left text-xs uppercase tracking-wide text-gray-400">
                            <tr>
                                <th class="px-3 py-2">{{ __('Object') }}</th>
                                <th class="px-3 py-2">{{ $check['repair'] ? __('Before') : __('Stored') }}</th>
                                <th class="px-3 py-2">{{ $check['repair'] ? __('Corrected to') : __('Expected') }}</th>
                                <th class="px-3 py-2">{{ __('RA') }}</th>
                                <th class="px-3 py-2">{{ __('Declination') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                            @foreach ($check['examples'] as $row)
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
            @elseif ($checkComplete)
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
                                        <th class="px-3 py-2">{{ __('Observations') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-800">
                                    @foreach ($report['unknown_observations']['examples'] as $row)
                                        <tr>
                                            <td class="px-3 py-2">{{ $row['objectname'] }}</td>
                                            <td class="px-3 py-2">
                                                @if ($row['primary_name'])
                                                    {{ $row['primary_name'] }}
                                                @elseif (! empty($row['candidates']))
                                                    <span class="text-gray-400">{{ __('Possibly:') }}</span>
                                                    @foreach ($row['candidates'] as $candidate)
                                                        <a href="{{ $candidate['url'] }}" class="text-blue-400 hover:underline">{{ $candidate['name'] }}</a>@if (! $loop->last), @endif
                                                    @endforeach
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td class="px-3 py-2">
                                                @if ($row['status'] === 'alias')
                                                    <span class="text-green-300">{{ __('Alias (fixable)') }}</span>
                                                @else
                                                    <span class="text-amber-300">{{ __('Unknown') }}</span>
                                                @endif
                                            </td>
                                            <td class="px-3 py-2">{{ $row['observation_count'] }}</td>
                                            <td class="px-3 py-2">
                                                @foreach ($row['observations'] ?? [] as $observation)
                                                    <div>
                                                        @if ($observation['url'])
                                                            <a href="{{ $observation['url'] }}" class="text-blue-400 hover:underline">{{ $observation['observer'] }}, {{ $observation['date'] }}</a>
                                                        @else
                                                            {{ $observation['observer'] }}, {{ $observation['date'] ?? '—' }}
                                                        @endif
                                                    </div>
                                                @endforeach
                                                @if (count($row['observations'] ?? []) < $row['observation_count'] && ! empty($row['observations']))
                                                    <div class="text-gray-400">{{ __('and :count more', ['count' => $row['observation_count'] - count($row['observations'])]) }}</div>
                                                @endif
                                            </td>
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