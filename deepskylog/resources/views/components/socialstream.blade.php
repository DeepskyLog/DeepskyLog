<div class="mb-2 mt-6 space-y-6">
    @if (! empty(\JoelButcher\Socialstream\Socialstream::providers()))
        <div class="relative flex items-center">
            <div class="flex-grow border-t border-gray-400"></div>
            <span class="flex-shrink px-6 text-gray-400">
                {{ config("socialstream.prompt", "Or Login Via") }}
            </span>
            <div class="flex-grow border-t border-gray-400"></div>
        </div>
    @endif

    <x-input-error :for="'socialstream'" class="text-center" />

    <div class="grid gap-4">
        @foreach (\JoelButcher\Socialstream\Socialstream::providers() as $provider)
            <a
                class="flex w-full items-center justify-center gap-2 rounded-lg border border-gray-400 py-2.5 text-sm shadow-sm transition duration-200 hover:shadow-md"
                href="{{ route("oauth.redirect", $provider["id"]) }}"
            >
                <x-socialstream-icons.provider-icon
                    :provider="$provider['id']"
                    class="h-6 w-6"
                />
                <span class="block text-sm font-medium text-gray-200">
                    {{ $provider["buttonLabel"] }}
                </span>
            </a>
        @endforeach
    </div>
</div>
