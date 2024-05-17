@props([
    "provider",
    "createdAt" => null,
])

<div>
    <div class="flex items-center justify-between pl-3">
        <div class="flex items-center">
            <x-socialstream-icons.provider-icon
                :provider="$provider['id']"
                class="h-6 w-6"
            />

            <div class="ml-2">
                <div class="text-sm font-semibold text-gray-600">
                    {{ __($provider["name"]) }}
                </div>

                @if (! empty($createdAt))
                    <div class="text-xs text-gray-500">
                        {{ __("Connected :createdAt", ["createdAt" => $createdAt]) }}
                    </div>
                @else
                    <div class="text-xs text-gray-500">
                        {{ __("Not connected.") }}
                    </div>
                @endif
            </div>
        </div>

        <div>
            {{-- {{ $action }} --}}
        </div>
    </div>

    @error($provider["id"] . "_connect_error")
        <div class="mt-2 px-3 text-sm font-semibold text-red-500">
            {{ $message }}
        </div>
    @enderror
</div>
