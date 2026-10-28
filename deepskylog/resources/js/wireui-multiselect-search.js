// WireUI's multiselect keeps the search text after an option is picked, so
// the list stays filtered on it (only single selects reset their search).
// Clear it after every pick, as x-searchable-multiselect does. The events
// do not bubble, hence the capture phase.
const resetMultiselectSearch = (event) => {
    const container = event.target;
    if (!(container instanceof Element) || !window.Alpine) return;
    if (container.getAttribute('x-data') !== 'wireui_select') return;

    const select = window.Alpine.$data(container);
    if (!select?.config?.multiselect || typeof select.resetSearch !== 'function') return;

    select.resetSearch();
    container.querySelector('input[x-ref="search"]')?.focus();
};

document.addEventListener('selected', resetMultiselectSearch, true);
document.addEventListener('un-selected', resetMultiselectSearch, true);
