<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown"
        aria-haspopup="true" aria-expanded="false">
        {{ _i('Equipment') }}
    </a>
    <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
        @auth
        @if (!auth()->user()->isAdmin())
        <a class="dropdown-item" href="/set">{{ _i('Equipment sets') }}</a>
        <a class="dropdown-item disabled" href="#">───────────────────</a>
        <a class="dropdown-item" href="/instrument">{{ _i('Instruments') }}</a>
        <a class="dropdown-item" href="/location">{{ _i('Locations') }}</a>
        <a class="dropdown-item" href="/eyepiece">{{ _i('Eyepieces') }}</a>
        <a class="dropdown-item" href="/filter">{{ _i('Filters') }}</a>
        <a class="dropdown-item" href="/lens">{{ _i('Lenses') }}</a>
        @endif
        @endauth
    </div>
</li>
