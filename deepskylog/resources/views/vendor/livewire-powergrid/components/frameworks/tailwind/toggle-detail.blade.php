{{-- Overrides PowerGrid's detail toggle so that <x-expand-all-details> can open and
     close all rows (#516): a row follows the pg-details-expanded-{table} event, and a
     row rendered while all are expanded (new page, sort, filter) opens itself. The
     arrow also turns when a row is open; PowerGrid only turned it with collapseOthers. --}}
<td
    x-data="() => {
        return {
            collapsed: false,
            loading: false,
            collapseOthers: @js(data_get($setUp, 'detail.collapseOthers', false)),
            init() {
                if (this.$store.pgDetails?.[@js($tableName)]) {
                    this.toggleDetail();
                }
            },
            toggleDetail() {
                const isOpen = this.collapsed;

                if (this.collapseOthers) {
                    this.$dispatch('pg-toggle-detail-{{ $tableName }}-hidden-all');
                    expandedId = '{{ $rowId }}';
                }

                this.loading = true;
                this.collapsed = !isOpen;

                this.$dispatch('pg-toggle-detail-{{ $tableName }}-{{ $rowId }}', {
                    collapsed: this.collapsed
                });
            }
        }
    }"
    x-on:pg-toggle-detail-{{ $tableName }}-hidden-all.window="collapsed = false"
    x-on:pg-toggle-detail-{{ $tableName }}-loaded.window="loading = false;"
    x-on:pg-details-expanded-{{ $tableName }}.window="if ($event.detail.expanded !== collapsed) toggleDetail()"
    class="{{ theme_style($theme, 'table.body.td') }}"
>
    <div
        class="cursor-pointer flex items-center"
        x-on:click="toggleDetail"
    >
        <div x-show="loading">
            <x-livewire-powergrid::icons.loading
                class="text-pg-primary-300 dark:text-pg-primary-400 h-5 w-5 animate-spin"
            />
        </div>

        <div x-show="!loading">
            @includeIf(data_get($setUp, 'detail.viewIcon'))

            @unless (data_get($setUp, 'detail.viewIcon'))
                <div
                    x-bind:class="{
                        'rotate-90': collapsed && (!collapseOthers || expandedId == '{{ $rowId }}'),
                        '-rotate-0': !collapsed
                    }">
                    <x-livewire-powergrid::icons.arrow
                        class="text-pg-primary-600 w-5 h-5 transition-all duration-300 dark:text-pg-primary-200"
                    />
                </div>
            @endunless
        </div>
    </div>
</td>
