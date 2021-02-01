<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown"
        aria-haspopup="true" aria-expanded="false">
        {{ _i('Objects') }}
    </a>
    <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
        <a class="dropdown-item disabled" href="/object/search">{{ _i('Search Objects') }}</a>
        <a class="dropdown-item disabled" href="#">───────────────────</a>
        <a class="dropdown-item" href="/catalogs">{{ _i('Catalogs') }}</a>
        <a class="dropdown-item disabled" href="/object/create">{{ _i("Add object") }}</a>
        @if (auth()->user()->isAdmin())
        <a class="dropdown-item disabled" href="#">───────────────────</a>
        <a class="dropdown-item" href="/object/check">{{ _i("Check Objects") }}</a>
        @endif
    </div>
</li>
