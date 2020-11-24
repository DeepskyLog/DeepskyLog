<li class="nav-item dropdown">
    <livewire:user.menu :user="$user" />

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
</li>
