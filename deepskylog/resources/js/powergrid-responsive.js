// PowerGrid 7 lays out a responsive table again after a Livewire update only
// when the component's setUp holds "responsive", but it sends the browser only
// setUp.footer, so after sorting, paging or a refresh the columns that do not
// fit were shown again. Do it for every component with a responsive table.
document.addEventListener("livewire:init", () => {
    Livewire.hook("commit", ({ component, succeed }) => {
        if (!component.el?.querySelector(".pg-table-responsive")) {
            return;
        }

        succeed(() => {
            queueMicrotask(() => {
                window.dispatchEvent(
                    new CustomEvent("pg-livewire-request-finished"),
                );
            });
        });
    });
});
