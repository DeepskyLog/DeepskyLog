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
