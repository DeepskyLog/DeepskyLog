<x-app-layout>
    <div class="pb-6">
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="bg-gray-900 p-6 shadow-sm sm:rounded-lg">
                {{-- Page header inside the dark container --}}
                <h2
                    class="mb-6 text-xl leading-tight font-semibold text-gray-200"
                >
                    {{
                        __(
                            "All sessions",
                        )
                    }}
                </h2>

                <div
                    class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5"
                >
                    @foreach ($sessions as $session)
                        <x-session-card :session="$session" />
                    @endforeach
                </div>

                <div class="mt-6">{{ $sessions->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
