@php
    $tabs = ["for-me" => __("For my telescope"), "all" => __("All open requests"), "mine" => __("My requests")];
@endphp

<div class="space-y-6">
    <x-card>
        <div class="space-y-2 px-2 text-sm text-gray-300">
            <p class="text-base text-gray-100">{{ __("Observers ask each other to observe an object: one they could not see with their own telescope, one nobody has logged with a small aperture, or one whose description they would like to compare.") }}</p>
            <p>{{ __("Ask for an object on its page, under the finding notes. Every observation of it that other observers log while the request is open answers it, also when they did not see it: you get a message for each.") }}</p>
        </div>
    </x-card>

    <nav class="flex flex-wrap gap-2" aria-label="{{ __("Observing requests") }}">
        @foreach ($tabs as $key => $label)
            <button type="button" wire:click="$set('view', '{{ $key }}')"
                class="rounded px-3 py-1.5 text-sm font-medium {{ $view === $key ? "bg-blue-600 text-white" : "bg-gray-700 text-gray-200 hover:bg-gray-600" }}"
                aria-pressed="{{ $view === $key ? "true" : "false" }}">{{ $label }}</button>
        @endforeach
    </nav>

    <x-card>
        <div class="space-y-3 px-2">
            @if ($view === "for-me")
                @if (!$hasEquipment)
                    <p class="text-sm text-yellow-400">{{ __("Set your standard instrument and location to see only the requests your telescope can probably answer. Until then, all open requests of others are shown.") }}</p>
                @else
                    <p class="text-xs text-gray-400">{{ __("The open requests of others that your standard telescope is large enough for and shows from your standard location, and that you have not answered yet.") }}</p>
                @endif
            @endif

            @forelse ($requests as $request)
                <x-observing-request :request="$request" :slug="$slugs[$request->object_name] ?? null" :viewer="$user" wire:key="request-{{ $request->id }}">
                    <span class="flex flex-wrap items-center gap-3">
                        @if ($request->isOpen() && $request->user_id !== $user->id)
                            <a href="{{ route("observation.create", ["slug" => $slugs[$request->object_name] ?? $request->object_name]) }}" class="text-blue-400 hover:underline">{{ __("Log an observation") }}</a>
                        @endif
                        @if ($request->isOpen() && $request->canBeClosedBy($user))
                            <button type="button" wire:click="closeRequest({{ $request->id }})" wire:confirm="{{ __("Close this request?") }}" class="hover:text-white hover:underline">{{ __("Close") }}</button>
                        @endif
                    </span>
                </x-observing-request>

                @if ($view === "mine" && $request->answers->isNotEmpty())
                    <ul class="-mt-2 ml-4 space-y-1 border-l border-gray-700 pl-3 text-xs text-gray-300">
                        @foreach ($request->answers as $answer)
                            @php($details = $answerDetails[$answer->id] ?? null)
                            <li>
                                @if ($url = $answer->detailUrl($details?->slug))
                                    <a href="{{ $url }}" class="text-blue-400 hover:underline">{{ $answer->observed_on?->translatedFormat("j M Y") }}</a>
                                @else
                                    {{ $answer->observed_on?->translatedFormat("j M Y") }}
                                @endif
                                &middot; {{ html_entity_decode($details?->name ?? $answer->observer_username, ENT_QUOTES | ENT_HTML5, "UTF-8") }}
                                @if ($details?->aperture_mm)
                                    &middot; {{ \App\Models\ObservingRequest::apertureLabel((int) round($details->aperture_mm), $user) }}
                                @endif
                                &middot;
                                @if ($details?->visibility === null)
                                    {{ __("observed") }}
                                @elseif ((int) $details->visibility === \App\Models\Observation::VISIBILITY_NOT_SEEN)
                                    <span class="text-yellow-400">{{ __("not seen") }}</span>
                                @else
                                    <span class="text-green-400">{{ __("seen") }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            @empty
                <p class="text-sm text-gray-400">
                    @if ($view === "mine")
                        {{ __("You have not asked for observations yet. Ask for an object on its page.") }}
                    @elseif ($view === "for-me")
                        {{ __("No open requests your telescope can probably answer.") }}
                    @else
                        {{ __("No open requests.") }}
                    @endif
                </p>
            @endforelse

            @if ($view === "for-me" && $requests->isNotEmpty())
                <button type="button" wire:click="createList" class="rounded bg-gray-700 px-3 py-1.5 text-sm font-medium text-white hover:bg-gray-600">{{ __("Make an observing list") }}</button>
            @endif
        </div>
    </x-card>
</div>
