<div>
    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown"
        aria-haspopup="true" aria-expanded="false">
        <img height="24px" style="border-radius: 20%" src='{{ $picture }}'>
        {{ Auth::user()->name }}
    </a>

    <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
        <a class="dropdown-item" href="/users/{{ Auth::user()->slug }}">{{ _i('Details') }}</a>
        <a class="dropdown-item" href="/users/{{ Auth::user()->slug }}/settings">{{ _i('Settings') }}</a>
        <a class="dropdown-item disabled" href="#">───────────────────</a>
        <a class="dropdown-item" href="/messages">{{ _i('DeepskyLog Mailbox') }}</a>
        <a class="dropdown-item disabled" href="#">───────────────────</a>
        <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault();
            document.getElementById('logout-form').submit();">
            {{ _i('Log out') }}
        </a>

        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            {{ csrf_field() }}
        </form>
    </div>

</div>
