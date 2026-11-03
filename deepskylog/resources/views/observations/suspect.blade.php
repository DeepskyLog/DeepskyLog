@php
    use App\Models\SuspectObservation;

    $labels = SuspectObservation::reasonLabels();
    $route = $admin ? 'admin.suspect-observations' : 'observations.suspect';
    $query = fn (array $change) => array_filter(array_merge(['reason' => $reason, 'state' => $state, 'observer' => $observer], $change), fn ($v) => $v !== null && $v !== '');
@endphp
<x-app-layout>
    <div>
        <div class="mx-auto max-w-screen-xl bg-gray-900 px-6 py-6 sm:px-6 lg:px-8">
            <h1 class="mb-2 text-xl font-semibold text-white">
                {{ $admin ? __('Suspect observations') : __('Observations to check') }}
            </h1>
            <p class="mb-2 text-sm text-gray-300">
                {{ __('These observations look wrong: the object was below the horizon at the date, time and location entered, its contrast reserve with the instrument is far below 0 while it is marked as seen, or the observation is a copy of another one.') }}
            </p>
            <p class="mb-4 text-sm text-gray-400">
                @if ($admin)
                    {{ __('The observers see their own on the page Observations to check.') }}
                @else
                    {{ __('Correct the date, time, location or instrument, delete a duplicate, or mark the observation as right when it is.') }}
                @endif
                @if ($checkedUntil)
                    {{ __('Last checked :time.', ['time' => \Illuminate\Support\Carbon::parse($checkedUntil)->diffForHumans()]) }}
                @endif
            </p>

            @if (session('success'))
                <p class="mb-4 text-sm text-green-300">{{ session('success') }}</p>
            @endif

            <div class="mb-3 flex flex-wrap gap-2">
                <a href="{{ route($route, $query(['reason' => null, 'page' => null])) }}"
                   @class(['rounded px-3 py-1 text-sm', 'bg-blue-600 text-white' => $reason === null, 'bg-gray-700 text-gray-200 hover:bg-gray-600' => $reason !== null])>
                    {{ __('All') }} ({{ number_format($counts->sum()) }})
                </a>
                @foreach ($labels as $key => $label)
                    <a href="{{ route($route, $query(['reason' => $key, 'page' => null])) }}"
                       @class(['rounded px-3 py-1 text-sm', 'bg-blue-600 text-white' => $reason === $key, 'bg-gray-700 text-gray-200 hover:bg-gray-600' => $reason !== $key])>
                        {{ $label }} ({{ number_format($counts[$key] ?? 0) }})
                    </a>
                @endforeach
            </div>

            <form method="GET" action="{{ route($route) }}" class="mb-4 flex flex-wrap items-center gap-3 text-sm text-gray-300">
                @if ($reason)
                    <input type="hidden" name="reason" value="{{ $reason }}">
                @endif
                <label class="flex items-center gap-2">
                    {{ __('Show') }}
                    <select name="state" onchange="this.form.submit()" class="rounded border-gray-600 bg-gray-800 py-1 text-sm text-gray-200">
                        <option value="open" @selected($state === 'open')>{{ __('To check') }}</option>
                        <option value="dismissed" @selected($state === 'dismissed')>{{ __('Marked as right') }}</option>
                        <option value="all" @selected($state === 'all')>{{ __('All') }}</option>
                    </select>
                </label>
                @if ($admin)
                    <label class="flex items-center gap-2">
                        {{ __('Observer') }}
                        <input type="text" name="observer" value="{{ $observer }}" class="rounded border-gray-600 bg-gray-800 py-1 text-sm text-gray-200">
                    </label>
                    <x-button type="submit" sm>{{ __('Filter') }}</x-button>
                @endif
            </form>

            <x-card>
                @if ($suspects->isEmpty())
                    <p class="text-sm text-green-300">{{ __('No suspect observations found.') }}</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-700 text-sm text-gray-200">
                            <thead class="bg-gray-800 text-left text-xs uppercase tracking-wide text-gray-400">
                                <tr>
                                    <th class="px-3 py-2">{{ __('Observation') }}</th>
                                    @if ($admin)
                                        <th class="px-3 py-2">{{ __('Observer') }}</th>
                                    @endif
                                    <th class="px-3 py-2">{{ __('Problem') }}</th>
                                    <th class="px-3 py-2"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-800">
                                @foreach ($suspects as $suspect)
                                    @php
                                        $observation = $suspect->observation;
                                        $url = $observation?->detailUrl();
                                        $own = $observation && auth()->user()->username === $observation->observer_username;
                                    @endphp
                                    <tr @class(['opacity-60' => $suspect->dismissed_at !== null])>
                                        <td class="px-3 py-2 align-top">
                                            @if ($url)
                                                <a href="{{ $url }}" class="font-semibold text-blue-400 hover:underline">{{ $observation->target_name }}</a>
                                            @else
                                                {{ $observation?->target_name }}
                                            @endif
                                            <div class="text-xs text-gray-400">
                                                {{ $observation?->observed_on?->format('Y-m-d') }}
                                                @if ($observation?->time !== null)
                                                    {{ sprintf('%02d:%02d', intdiv($observation->time, 100), $observation->time % 100) }} {{ __('UT') }}
                                                @endif
                                            </div>
                                        </td>
                                        @if ($admin)
                                            <td class="px-3 py-2 align-top">
                                                <a href="{{ route($route, $query(['observer' => $observation?->observer_username, 'page' => null])) }}" class="hover:underline">{{ $observation?->observer_username }}</a>
                                            </td>
                                        @endif
                                        <td class="px-3 py-2 align-top">
                                            <span @class([
                                                'rounded px-2 py-0.5 text-xs',
                                                'bg-red-900 text-red-200' => $suspect->reason === SuspectObservation::REASON_BELOW_HORIZON,
                                                'bg-amber-900 text-amber-200' => $suspect->reason === SuspectObservation::REASON_TOO_FAINT,
                                                'bg-gray-700 text-gray-200' => $suspect->reason === SuspectObservation::REASON_DUPLICATE,
                                            ])>{{ $labels[$suspect->reason] ?? $suspect->reason }}</span>
                                            <div class="mt-1 text-gray-300">{{ $suspect->explanation() }}</div>
                                            @if ($suspect->reason === SuspectObservation::REASON_DUPLICATE && ($related = $suspect->relatedObservation) && ($relatedUrl = $related->detailUrl()))
                                                <a href="{{ $relatedUrl }}" class="text-xs text-blue-400 hover:underline">{{ __('The other observation') }}</a>
                                            @endif
                                            @if ($suspect->dismissed_at)
                                                <div class="mt-1 text-xs text-gray-400">{{ __('Marked as right :time.', ['time' => $suspect->dismissed_at->diffForHumans()]) }}</div>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-2 text-right align-top">
                                            @if ($own && $url)
                                                <a href="{{ $url }}/edit" class="mr-2 text-blue-400 hover:underline">{{ __('Edit') }}</a>
                                            @endif
                                            @if ($suspect->dismissed_at === null)
                                                <form method="POST" action="{{ route('suspect-observations.dismiss', $suspect) }}" class="inline">
                                                    @csrf
                                                    <x-button type="submit" sm>{{ __('This is right') }}</x-button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('suspect-observations.restore', $suspect) }}" class="inline">
                                                    @csrf
                                                    <x-button type="submit" sm flat>{{ __('Check again') }}</x-button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $suspects->links() }}</div>
                @endif
            </x-card>
        </div>
    </div>
</x-app-layout>
