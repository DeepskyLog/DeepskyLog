<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        {{ _i('Administration') }}
    </a>
    <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
        <a class="dropdown-item" href="/messages/createAll">{{ _i('Send message to all') }}</a>
        <a class="dropdown-item disabled" href="#">───────────────────</a>
        <a class="dropdown-item" href="/users">{{ _i('Observers') }}</a>
        <a class="dropdown-item disabled" href="#">───────────────────</a>
        <a class="dropdown-item" href="/object/check">{{ _i("Check Objects") }}</a>
        <a class="dropdown-item disabled" href="#">───────────────────</a>
        <a class="dropdown-item" href="/instrument/admin">{{ _i("Instruments") }}</a>
        <a class="dropdown-item" href="/location/admin">{{ _i("Locations") }}</a>
        <a class="dropdown-item" href="/eyepiece/admin">{{ _i("Eyepieces") }}</a>
        <a class="dropdown-item" href="/filter/admin">{{ _i("Filters") }}</a>
        <a class="dropdown-item" href="/lens/admin">{{ _i("Lenses") }}</a>
    </div>
</li>
