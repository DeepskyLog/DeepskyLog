@php
    $input = "rounded border border-gray-600 bg-gray-900 px-2 py-1 text-sm text-gray-100";
@endphp

<div id="observing-requests" class="mt-6">
    <h2 class="text-xl font-semibold text-white">{{ __("Observing requests") }}</h2>
    <div class="mt-2 rounded bg-gray-800 p-3 text-gray-100 shadow">
        <p class="mb-2 text-xs text-gray-400">{{ __("Observers who would like others to observe this object. Every observation logged while a request is open answers it, also when the object was not seen.") }}</p>

        @if ($requests->isEmpty())
            <p class="text-sm text-gray-400">{{ __("Nobody asked for observations of this object.") }}</p>
        @else
            <div class="space-y-2">
                @foreach ($requests as $request)
                    <x-observing-request :request="$request" :viewer="$user" :show-object="false" wire:key="object-request-{{ $request->id }}">
                        @if ($request->canBeClosedBy($user))
                            <button type="button" wire:click="closeRequest({{ $request->id }})" wire:confirm="{{ __("Close this request?") }}" class="hover:text-white hover:underline">{{ __("Close") }}</button>
                        @endif
                    </x-observing-request>
                @endforeach
            </div>
        @endif

        @auth
            @if ($canAsk)
                @if (!$asking)
                    <button type="button" wire:click="$set('asking', true)" class="mt-3 rounded bg-gray-700 px-2 py-1 text-xs font-medium text-white hover:bg-gray-600">{{ __("Ask others to observe this object") }}</button>
                @else
                    <form wire:submit="ask" class="mt-3 space-y-2">
                        <input type="text" wire:model="note" maxlength="{{ \App\Models\ObservingRequest::MAX_NOTE_LENGTH }}"
                            placeholder="{{ __("E.g. not seen with my 8-inch, who can try with a larger telescope?") }}" aria-label="{{ __("Note") }}"
                            class="{{ $input }} w-full" />
                        @error("note")
                            <p class="text-xs text-red-400">{{ $message }}</p>
                        @enderror
                        <div class="flex flex-wrap items-end gap-3 text-sm">
                            <label class="block">
                                <span class="mb-1 block text-xs text-gray-400">{{ __("Aperture of at least") }}</span>
                                <select wire:model="minApertureMm" class="{{ $input }}">
                                    <option value="">{{ __("Any instrument") }}</option>
                                    @foreach (\App\Models\ObservingRequest::APERTURES as $mm)
                                        <option value="{{ $mm }}">{{ \App\Models\ObservingRequest::apertureLabel($mm, $user) }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="block">
                                <span class="mb-1 block text-xs text-gray-400">{{ __("Open for") }}</span>
                                <select wire:model="months" class="{{ $input }}">
                                    @foreach ([1, 2, 4, 6, 12] as $months)
                                        <option value="{{ $months }}">{{ trans_choice(":count month|:count months", $months, ["count" => $months]) }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <button type="submit" class="rounded bg-blue-600 px-2 py-1 text-xs font-medium text-white hover:bg-blue-500">{{ __("Ask") }}</button>
                            <button type="button" wire:click="$set('asking', false)" class="rounded bg-gray-700 px-2 py-1 text-xs font-medium text-white hover:bg-gray-600">{{ __("Cancel") }}</button>
                        </div>
                        @error("minApertureMm")
                            <p class="text-xs text-red-400">{{ $message }}</p>
                        @enderror
                        @error("months")
                            <p class="text-xs text-red-400">{{ $message }}</p>
                        @enderror
                    </form>
                @endif
            @endif
            <p class="mt-2 text-xs"><a href="{{ route("observing-requests") }}" class="text-blue-400 hover:underline">{{ __("All observing requests") }}</a></p>
        @else
            <p class="mt-2 text-xs text-gray-400">
                <a href="{{ route("login") }}" class="hover:underline">{{ __("Log in to ask other observers to observe this object.") }}</a>
            </p>
        @endauth
    </div>
</div>
