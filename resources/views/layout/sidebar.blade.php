<div id="sidebar-wrapper">
    <div class="list-group list-group-flush">
        <ul class="nav flex-column list-group-item dslsidebar">
            <br />
            <!-- Language -->
            @if (Auth::guest())
            @include('layout.sidebar.language')
            @endif

            <!-- Quickpick -->
            @include('layout.sidebar.quickpick')

            <!-- Change date and moon -->
            @include('layout.sidebar.date')
        </ul>
    </div>
</div>
