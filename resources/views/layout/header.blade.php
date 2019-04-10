<nav class="navbar sticky-top navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="/">DeepskyLog</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
            @include('layout.header.view');
            @include('layout.header.search');
            @auth
                @include('layout.header.add');
            @endauth
            @admin('')
                @include('layout.header.admin');
            @endadmin
            @include('layout.header.downloads');
            @include('layout.header.help');
        </ul>

        <ul class="navbar-nav">
            <button class="btn btn-light fas fa-adjust" id="nightMode" style="margin-right:5px;border:0;" alt="Night Mode"></button>

            @if (Auth::guest())
                @include('layout.header.register');
            @else
                @include('layout.header.user');

                <button class="btn" style="margin-right:5px;border:0;">
                    <a href="/message/view">
                        <span style="color: #FFFFFF" class="fas fa-inbox"></span>&nbsp;
                        <span class="badge badge-pill badge-secondary">4</span>
                    </a>
                </button>
            @endif
        </ul>
</div>
</nav>
