<nav class="navbar navbar-expand-lg navbar-light bg-light border-top border-bottom">
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarHeader2" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarHeader2">
        <ul class="navbar-nav mr-auto">
            @auth
                @include('layout.subheader.location')
                @include('layout.subheader.instrument')
            @endauth

            @include('layout.subheader.list')
        </ul>
        <ul class="navbar-nav ml-auto">
            @include('layout.subheader.date')
        </ul>
    </div>
</nav>
