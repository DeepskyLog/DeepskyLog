<x-app-layout>
    @php
        $format = fn($date) => $date->copy()->locale(app()->getLocale())->isoFormat("LL");
    @endphp
    <div class="mx-auto max-w-screen-lg bg-gray-900 px-6 py-6 sm:px-6 lg:px-8">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <h1 class="text-xl font-semibold text-white">{{ __("Manage the challenges") }}</h1>
            <a
                href="{{ route("admin.challenges.create") }}"
                class="rounded bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-600"
                >{{ __("New challenge") }}</a
            >
        </div>
        <p class="mb-4 text-sm text-gray-400">
            {{ __("A challenge is announced in the news a week before it starts. Observers who join it get it as an observing goal and appear on its leaderboard.") }}
        </p>

        <table class="w-full text-left text-sm text-gray-300">
            <thead class="text-xs text-gray-400 uppercase">
                <tr>
                    <th class="py-2 pr-3">{{ __("Name") }}</th>
                    <th class="py-2 pr-3">{{ __("Period") }}</th>
                    <th class="py-2 pr-3 text-right">{{ __("Participants") }}</th>
                    <th class="py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse ($challenges as $challenge)
                    <tr>
                        <td class="py-2 pr-3">
                            <a href="{{ route("challenges.show", $challenge) }}" class="text-blue-400 hover:underline">{{ $challenge->name }}</a>
                            @if ($challenge->hidden)
                                <span class="ml-1 text-xs text-yellow-400">{{ __("Hidden") }}</span>
                            @endif
                            <div class="text-xs text-gray-400">{{ $challenge->task() }}</div>
                        </td>
                        <td class="py-2 pr-3">{{ $format($challenge->starts_on) }} &ndash; {{ $format($challenge->ends_on) }}</td>
                        <td class="py-2 pr-3 text-right">{{ $challenge->goals_count }}</td>
                        <td class="py-2 text-right whitespace-nowrap">
                            <a href="{{ route("admin.challenges.edit", $challenge) }}" class="text-blue-400 hover:underline">{{ __("Edit") }}</a>
                            <form method="POST" action="{{ route("admin.challenges.destroy", $challenge) }}" class="ml-2 inline">
                                @csrf
                                @method ("DELETE")
                                <button
                                    type="submit"
                                    class="text-red-400 hover:underline"
                                    onclick="return confirm({{ \Illuminate\Support\Js::from(__("Delete this challenge, its leaderboard and its news item?")) }})"
                                >
                                    {{ __("Delete") }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-2 text-gray-400">{{ __("There are no observing challenges yet.") }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-4">{{ $challenges->links() }}</div>
    </div>
</x-app-layout>
