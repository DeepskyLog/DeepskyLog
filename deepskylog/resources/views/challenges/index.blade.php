<x-app-layout>
    <div class="mx-auto max-w-screen-lg bg-gray-900 px-6 py-6 sm:px-6 lg:px-8">
        <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
            <h1 class="text-xl font-semibold text-white">{{ __("Observing challenges") }}</h1>
            @can ("manage_challenges", \App\Models\User::class)
                <a href="{{ route("admin.challenges") }}" class="text-sm text-blue-400 hover:underline">{{ __("Manage the challenges") }}</a>
            @endcan
        </div>
        <p class="mb-4 text-sm text-gray-400">
            {{ __("Observe the objects of a catalog or observing list together in a limited time. Join a challenge to follow your progress and see how the other participants are doing.") }}
        </p>

        @foreach ([
            "running" => [__("Running challenges"), $running],
            "upcoming" => [__("Coming challenges"), $upcoming],
            "ended" => [__("Past challenges"), $ended],
        ] as $group => [$heading, $challenges])
            @if ($challenges->isNotEmpty())
                <h2 class="mb-2 mt-6 text-lg font-semibold text-white">{{ $heading }}</h2>
                <div class="space-y-3">
                    @foreach ($challenges as $challenge)
                        @include ("challenges.partials.card", ["challenge" => $challenge])
                    @endforeach
                </div>
                @if ($group === "ended")
                    <div class="mt-4">{{ $ended->links() }}</div>
                @endif
            @endif
        @endforeach

        @if ($running->isEmpty() && $upcoming->isEmpty() && $ended->isEmpty())
            <p class="text-gray-300">{{ __("There are no observing challenges yet.") }}</p>
        @endif
    </div>
</x-app-layout>
