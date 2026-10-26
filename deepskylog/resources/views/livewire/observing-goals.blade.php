<div>
    @if ($goals->isNotEmpty() || $isOwner)
        <div class="mb-6"><x-card>
            <div class="flex flex-wrap items-center justify-between gap-2 px-5">
                <h2 class="mb-2 mt-2 text-xl font-bold">{{ __("Observing goals") }}</h2>
                @if ($isOwner && !$showForm)
                    <x-button xs primary icon="plus" wire:click="create" label="{{ __('Add a goal') }}" />
                @endif
            </div>

            <div class="space-y-4 px-5 pb-3 text-sm">
                @forelse ($goals as $goal)
                    <div wire:key="observing-goal-{{ $goal->id }}">
                        <x-observing-goal :goal="$goal" />
                        @if ($isOwner)
                            <div class="mt-1 flex gap-3 text-xs text-gray-400">
                                @if (!$goal->public)
                                    <span>{{ __("Private") }}</span>
                                @endif
                                <button type="button" wire:click="edit({{ $goal->id }})" class="hover:text-white hover:underline">{{ __("Edit") }}</button>
                                <button type="button" wire:click="delete({{ $goal->id }})" wire:confirm="{{ __('Remove this observing goal?') }}" class="hover:text-white hover:underline">{{ __("Remove") }}</button>
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-gray-400">
                        {{ __('Set yourself a goal, such as 200 Herschel objects this year or every Messier object in one season, and follow your progress here and in your blog.') }}
                    </p>
                @endforelse

                @if ($isOwner && $showForm)
                    <form wire:submit="save" class="space-y-3 rounded-md bg-gray-800 p-4">
                        <h3 class="font-semibold text-white">{{ $editingId ? __("Edit the observing goal") : __("New observing goal") }}</h3>

                        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                            <div>
                                {{-- Searchable: there are hundreds of catalogs --}}
                                <x-select
                                    label="{{ __('Catalog or observing list') }}"
                                    placeholder="{{ __('Search a catalog or observing list') }}"
                                    wire:model="source"
                                    :options="$sources"
                                    option-label="label"
                                    option-value="key"
                                    option-description="kind"
                                    :clearable="false"
                                />
                            </div>

                            <div>
                                <label for="goal-target" class="mb-1 block font-medium text-gray-400">{{ __("Number of objects") }}</label>
                                <input id="goal-target" type="number" min="1" wire:model="target" placeholder="{{ __('All objects') }}"
                                    class="block w-full rounded border border-gray-600 bg-gray-900 px-3 py-2 text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                <p class="mt-1 text-xs text-gray-400">{{ __("Leave empty for all objects of the catalog or list.") }}</p>
                                @error("target") <p class="mt-1 text-red-400">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="goal-starts" class="mb-1 block font-medium text-gray-400">{{ __("Start") }}</label>
                                <input id="goal-starts" type="date" wire:model="startsOn"
                                    class="block w-full rounded border border-gray-600 bg-gray-900 px-3 py-2 text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                @error("startsOn") <p class="mt-1 text-red-400">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="goal-ends" class="mb-1 block font-medium text-gray-400">{{ __("End") }}</label>
                                <input id="goal-ends" type="date" wire:model="endsOn"
                                    class="block w-full rounded border border-gray-600 bg-gray-900 px-3 py-2 text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                @error("endsOn") <p class="mt-1 text-red-400">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-3 text-xs">
                            <button type="button" wire:click="period('year')" class="text-blue-400 hover:underline">{{ __("This year") }}</button>
                            <button type="button" wire:click="period('next_year')" class="text-blue-400 hover:underline">{{ __("Next year") }}</button>
                            <button type="button" wire:click="period('twelve_months')" class="text-blue-400 hover:underline">{{ __("The coming twelve months") }}</button>
                        </div>

                        <div class="flex flex-wrap gap-x-6 gap-y-2">
                            <label class="flex items-center gap-2">
                                <input type="radio" wire:model="mode" value="observed" />
                                {{ __("See the objects") }}
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="radio" wire:model="mode" value="drawn" />
                                {{ __("Draw the objects") }}
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" wire:model="newOnly" />
                                {{ __("Only objects I had not seen before the start") }}
                            </label>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" wire:model="public" />
                                {{ __("Show on my observer page and in my blog") }}
                            </label>
                        </div>

                        <div>
                            <label for="goal-name" class="mb-1 block font-medium text-gray-400">{{ __("Name (optional)") }}</label>
                            <input id="goal-name" type="text" maxlength="120" wire:model="name" placeholder="{{ __('e.g. 200 Herschel objects this year') }}"
                                class="block w-full rounded border border-gray-600 bg-gray-900 px-3 py-2 text-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            @error("name") <p class="mt-1 text-red-400">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex gap-3">
                            <button type="submit" class="rounded bg-green-700 px-4 py-2 font-medium text-white hover:bg-green-600">{{ __("Save") }}</button>
                            <button type="button" wire:click="cancel" class="rounded px-4 py-2 text-gray-300 hover:bg-gray-700">{{ __("Cancel") }}</button>
                        </div>
                    </form>
                @endif
            </div>
        </x-card></div>
    @endif
</div>
