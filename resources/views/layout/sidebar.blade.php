<nav class="col-md-2 d-none d-md-block bg-light sidebar">
    <div class="sidebar-sticky">
        <ul class="nav flex-column">
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
</nav>
