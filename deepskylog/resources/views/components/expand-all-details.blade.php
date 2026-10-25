@props(['table'])

{{-- Expands or collapses the detail rows of the PowerGrid table $table (#516).
     The state is kept in Alpine.store('pgDetails'), so the rows of a new page,
     sort or filter open as well (see the toggle-detail override). --}}
<button type="button"
        x-data="{
            expanded: false,
            init() {
                if (!Alpine.store('pgDetails')) {
                    Alpine.store('pgDetails', {});
                }
                Alpine.store('pgDetails')[@js($table)] = false;
            },
            toggle() {
                this.expanded = !this.expanded;
                Alpine.store('pgDetails')[@js($table)] = this.expanded;
                window.dispatchEvent(new CustomEvent(@js('pg-details-expanded-' . $table), {
                    detail: { expanded: this.expanded },
                }));
            },
        }"
        @click="toggle()"
        :aria-pressed="expanded.toString()"
        :class="expanded
            ? 'bg-amber-600 hover:bg-amber-500 text-white'
            : 'bg-indigo-600 hover:bg-indigo-500 text-white'"
        class="px-4 py-1.5 rounded text-sm font-medium transition-colors">
    <span x-text="expanded ? @js(__('Collapse all')) : @js(__('Expand all'))"></span>
</button>
