<x-app-layout>
    <x-slot name="header">{{ __('New message') }}</x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-gray-900 shadow-sm sm:rounded-lg p-4">
                <div class="max-w-2xl mx-auto">
                    <div class="bg-gray-900 p-6 rounded-lg shadow">
        <form method="POST" action="{{ route('messages.store') }}">
            @csrf

            <div class="mb-2">
                <label class="block text-sm">{{ __('Receiver (username)') }}</label>
                <input type="text" name="receiver" class="w-full" placeholder="username or 'all' for admins" />
            </div>

            <div class="mb-2">
                <label class="block text-sm">{{ __('Subject') }}</label>
                <input type="text" name="subject" class="w-full" />
            </div>

            <div class="mb-2">
                <label class="block text-sm">{{ __('Message') }}</label>
                <textarea name="message" class="w-full h-48"></textarea>
            </div>

            <div>
                <button class="btn" type="submit">{{ __('Send') }}</button>
            </div>
        </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
