@props(["slug"])

{{-- "Something wrong with this object?": an observer reports an error, the object then needs attention on /admin/object-reviews --}}
@auth
    <div x-data="{ open: {{ $errors->has("kind") || $errors->has("note") ? "true" : "false" }} }" class="relative">
        <x-button type="button" xs flat gray icon="flag" label="{{ __('Something wrong with this object?') }}" x-on:click="open = ! open" />

        <div
            x-show="open"
            x-cloak
            x-on:click.outside="open = false"
            class="absolute left-0 z-30 mt-2 w-80 max-w-[90vw] rounded-lg border border-gray-700 bg-gray-800 p-4 shadow-lg"
        >
            <form method="POST" action="{{ route("object.report-error", ["slug" => $slug]) }}" class="space-y-3">
                @csrf
                <p class="text-sm text-gray-300">
                    {{ __("Tell the database experts what is wrong. They will check the object and correct it.") }}
                </p>
                <div>
                    <label for="report-error-kind" class="mb-1 block text-sm font-medium text-gray-300">{{ __("What is wrong?") }}</label>
                    <select
                        name="kind"
                        id="report-error-kind"
                        class="block w-full rounded-lg border-gray-600 bg-gray-900 px-3 py-2 text-sm text-white"
                    >
                        @foreach (array_keys(\App\Models\DeepskyObject::ERROR_KINDS) as $kind)
                            <option value="{{ $kind }}" @selected(old("kind") === $kind)>
                                {{ __(\App\Models\DeepskyObject::ERROR_KINDS[$kind]) }}
                            </option>
                        @endforeach
                    </select>
                    @error("kind")
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="report-error-note" class="mb-1 block text-sm font-medium text-gray-300">{{ __("Note") }}</label>
                    <textarea
                        name="note"
                        id="report-error-note"
                        rows="3"
                        required
                        maxlength="{{ \App\Models\DeepskyObject::ERROR_NOTE_LENGTH }}"
                        placeholder="{{ __("For example: the magnitude is 11.2 in the NGC/IC project, not 9.3") }}"
                        class="block w-full rounded-lg border-gray-600 bg-gray-900 px-3 py-2 text-sm text-white"
                    >{{ old("note") }}</textarea>
                    @error("note")
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex justify-end gap-2">
                    <x-button type="button" xs flat label="{{ __('Cancel') }}" x-on:click="open = false" />
                    <x-button type="submit" xs primary label="{{ __('Send') }}" />
                </div>
            </form>
        </div>
    </div>
@endauth
