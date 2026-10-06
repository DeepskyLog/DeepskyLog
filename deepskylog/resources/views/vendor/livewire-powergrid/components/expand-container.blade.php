{{-- Overrides PowerGrid's expand container: its wire:key used only the first six
     characters of the row id, so rows keyed on object names ("NGC 7317", "NGC 7315")
     shared a key and Livewire's morph misplaced the expand rows, after which the
     responsive script wrote the hidden columns into the next row's name cell. --}}
<tbody
    x-cloak
    expand
    wire:key="{{ 'expand-' . md5((string) $rowId) }}"
    x-show="hasHiddenElements"
>
    <tr
        x-show="expanded == @js((string) $rowId)"
        x-transition
        class="text-pg-primary-500 border-pg-primary-100 dark:text-pg-primary-200 break-words w-full text-sm"
    >
        <td colspan="999">
            <div class="flex gap-x-6 gap-y-2 flex-wrap p-2 responsive-row-expand-container"></div>
        </td>
    </tr>
</tbody>
