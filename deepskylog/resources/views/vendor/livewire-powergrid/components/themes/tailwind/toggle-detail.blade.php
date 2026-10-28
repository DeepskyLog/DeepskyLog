{{-- PowerGrid's detail toggle with the arrow turned when <x-expand-all-details>
     opened all rows (#516): a row is open when it differs from allDetailsOpen
     (ObservationSearchTable::isDetailOpen()). --}}
<td
    x-data="{
        loading: false,
        rowId: @js((string) $rowId),
        isOpen() {
            return !!$wire.allDetailsOpen !== !!$wire.openedDetailIds[this.rowId];
        },
        async toggleDetail() {
            this.loading = true;
            await $wire.toggleDetail(this.rowId);
            this.loading = false;
        },
    }"
    data-table-name="{{ $tableName }}"
    data-row-id="{{ $rowId }}"
    class="{{ theme('table.layout.td') }}"
>
    <div class="flex cursor-pointer items-center" x-on:click="toggleDetail()">
        <div x-show="loading" x-cloak>
            <x-livewire-powergrid::icons.loading
                class="size-4 animate-spin text-zinc-300 dark:text-zinc-400"
            />
        </div>

        <div x-show="!loading">
            @includeIf (data_get($setUp, "detail.viewIcon"))

            @unless (data_get($setUp, "detail.viewIcon"))
                <div x-bind:class="isOpen() ? 'rotate-90' : 'rotate-0'">
                    <x-livewire-powergrid::icons.arrow
                        class="h-5 w-5 text-zinc-600 transition-all duration-300 dark:text-zinc-200"
                    />
                </div>
            @endunless
        </div>
    </div>
</td>
