<nav class="navbar sticky-top navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="/home">DeepskyLog</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
            @include('layout.header.view')
            @include('layout.header.search')
            @auth
                @include('layout.header.add')
            @endauth
            @admin('')
                @include('layout.header.admin')
            @endadmin
            @include('layout.header.downloads')
            @include('layout.header.help')
        </ul>

        <ul class="navbar-nav">
            <button class="btn btn-light bi bi-subtract" width="32" height="32" id="darkSwitch" style="margin-right:5px;border:0;" alt="Night Mode">
                <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-circle-half icon" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" d="M8 15V1a7 7 0 1 1 0 14zm0 1A8 8 0 1 1 8 0a8 8 0 0 1 0 16z"/>
                </svg>
            </button>

            @if (Auth::guest())
                @include('layout.header.register')
            @else
                @include('layout.header.user')

                <button class="btn" style="margin-right:5px;border:0;">
                    <a href="/messages">
                        <?php $count = Auth::user()->newThreadsCount(); ?>
                        <svg width="1em" height="1em" viewBox="0 0 16 16" class="bi bi-envelope-open icon" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M8.47 1.318a1 1 0 0 0-.94 0l-6 3.2A1 1 0 0 0 1 5.4v.818l5.724 3.465L8 8.917l1.276.766L15 6.218V5.4a1 1 0 0 0-.53-.882l-6-3.2zM15 7.388l-4.754 2.877L15 13.117v-5.73zm-.035 6.874L8 10.083l-6.965 4.18A1 1 0 0 0 2 15h12a1 1 0 0 0 .965-.738zM1 13.117l4.754-2.852L1 7.387v5.73zM7.059.435a2 2 0 0 1 1.882 0l6 3.2A2 2 0 0 1 16 5.4V14a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V5.4a2 2 0 0 1 1.059-1.765l6-3.2z"/>
                          </svg>
                        <span class="badge badge-pill badge-secondary">{{  $count }}</span>
                    </a>
                </button>
            @endif
        </ul>
</div>
</nav>
