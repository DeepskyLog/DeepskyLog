@props (["table"])

{{-- Expands or collapses the detail rows of the PowerGrid table $table (#516).
     The table keeps the state (ObservationSearchTable::expandAllDetails()), so
     the rows of a new page, sort or filter open as well. --}}
<button
    type="button"
    x-data="{
            expanded: false,
            toggle() {
                this.expanded = !this.expanded;
                Livewire.dispatch('expand-all-details', { table: @js($table), expanded: this.expanded });
            },
        }"
    @click="toggle()"
    :aria-pressed="expanded.toString()"
    :class="expanded
        ? 'bg-amber-600 hover:bg-amber-500 text-white'
        : 'bg-indigo-600 hover:bg-indigo-500 text-white'"
    class="rounded px-4 py-1.5 text-sm font-medium transition-colors"
>
    <span
        x-text="expanded ? @js(__('Collapse all')) : @js(__('Expand all'))"
    ></span>
</button>
