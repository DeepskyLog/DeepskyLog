
<li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            {{ _i('View') }}
    </a>
    <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
        @auth
{{--            <a class="dropdown-item" href="/observation/{{ Auth::id() }}">{{ _i('My observations') }}</a>
            <a class="dropdown-item" href="/drawings/{{ Auth::id() }}">{{ _i('My drawings') }}</a>
            <a class="dropdown-item disabled" href="#">───────────────────</a>
            <a class="dropdown-item" href="/observingList">{{ _i('My observing lists') }}</a>
            <a class="dropdown-item" href="/session">{{ _i('My sessions') }}</a>
            <a class="dropdown-item disabled" href="#">───────────────────</a>
--}}            <a class="dropdown-item" href="/instrument">{{ _i('My instruments') }}</a>
            <a class="dropdown-item" href="/location">{{ _i('My locations') }}</a>
            <a class="dropdown-item" href="/eyepiece">{{ _i('My eyepieces') }}</a>
            <a class="dropdown-item" href="/filter">{{ _i('My filters') }}</a>
            <a class="dropdown-item" href="/lens">{{ _i('My lenses') }}</a>
            <a class="dropdown-item disabled" href="#">───────────────────</a>
        @endauth
{{--        <a class="dropdown-item" href="/observation/all">{{ _i('Latest observations') }}</a>
        <a class="dropdown-item" href="/drawings/all">{{ _i('Latest drawings') }}</a>
        <a class="dropdown-item disabled" href="#">───────────────────</a>
        <a class="dropdown-item" href="/observer/rank">{{ _i('Observers') }}</a>
        <a class="dropdown-item" href="/objects/rank">{{ _i('Popular objects') }}</a>
        <a class="dropdown-item" href="/statistics">{{ _i('Statistics') }}</a>
        <a class="dropdown-item disabled" href="#">───────────────────</a>
--}}        <a class="dropdown-item" href="/catalogs">{{ _i('Catalogs') }}</a>
    </div>
</li>
