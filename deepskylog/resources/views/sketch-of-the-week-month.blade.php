<x-app-layout>
    <div>
        <div
            class="max-w-screen mx-auto bg-gray-900 px-2 py-10 sm:px-6 lg:px-8"
        >
            <h2 class="text-xl font-semibold leading-tight">
                @if ($week_month == "Week")
                    {{ __("DeepskyLog Sketch of the Week") }}
                @else
                    {{ __("DeepskyLog Sketch of the Month") }}
                @endif
            </h2>
            @if ($week_month != "Week")
                @can('add_sketch', \App\Models\User::class)
                    <div class="mt-2">
                        <a
                            href="{{ route("sketch-of-the-month.create") }}"
                            class="inline-block rounded bg-amber-600 px-3 py-2 text-sm text-white hover:bg-amber-500"
                            >{{ __("Choose the sketch of the month") }}</a
                        >
                    </div>
                @endcan
            @endif
            @if ($upcoming->isNotEmpty())
                <div class="mt-4">
                    <x-card>
                        <h3 class="px-5 text-lg font-semibold text-amber-400">
                            {{ __("Chosen, not yet published") }}
                        </h3>
                        <p class="px-5 text-sm text-gray-400">
                            {{ __("Only administrators and database experts see these sketches until their date.") }}
                        </p>
                        <div class="flex flex-wrap px-5">
                            @foreach ($upcoming as $sketch)
                                <x-sketch :sketch="$sketch" :use_selection_date="true" :unreleased="true" />
                            @endforeach
                        </div>
                    </x-card>
                </div>
            @endif
            <div class="mt-2">
                <x-card>
                    <div class="flex flex-wrap px-5">
                        @foreach ($sketches as $sketch)
                            <x-sketch :sketch="$sketch" :use_selection_date="true" />
                        @endforeach
                    </div>
                    {{ $sketches->links() }}
                </x-card>
            </div>
        </div>
    </div>
</x-app-layout>
