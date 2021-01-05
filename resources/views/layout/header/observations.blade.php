<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown"
        aria-haspopup="true" aria-expanded="false">
        {{ _i('Observations') }}
    </a>
    <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
        <a class="dropdown-item disabled" href="/observation/create">{{ _i('Add observation') }}</a>
        <a class="dropdown-item disabled" href="#">───────────────────</a>
        <a class="dropdown-item disabled" href="/observation/search">{{ _i('Search observations') }}</a>
        <a class="dropdown-item disabled" href="#">───────────────────</a>
        <a class="dropdown-item disabled" href="/observingList">{{ _i('Observing lists') }}</a>
        <a class="dropdown-item disabled" href="/session">{{ _i("Sessions") }}</a>
        <a class="dropdown-item disabled" href="#">───────────────────</a>
        <a class="dropdown-item disabled" href="/observation/{{ Auth::id() }}">{{ _i('Show my observations') }}</a>
        <a class="dropdown-item disabled" href="/drawings/{{ Auth::id() }}">{{ _i('Show my drawings') }}</a>
        <a class="dropdown-item disabled" href="#">───────────────────</a>
        <a class="dropdown-item disabled" href="/observation/all">{{ _i('Show latest observations') }}</a>
        <a class="dropdown-item disabled" href="/drawings/all">{{ _i('Show latest drawings') }}</a>
    </div>
</li>
