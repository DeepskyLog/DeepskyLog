<x-app-layout>
    <div class="bg-gray-900 pt-4 text-gray-300">
        <div class="flex min-h-screen flex-col items-center pt-6 sm:pt-0">
            <div>
                <x-authentication-card-logo />
            </div>

            <div
                class="prose prose-invert mt-6 w-full overflow-hidden bg-gray-800 p-6 text-gray-300 shadow-md sm:max-w-2xl sm:rounded-lg"
            >
                {!! $policy !!}
            </div>
        </div>
    </div>
</x-app-layout>
